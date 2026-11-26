<?php

namespace Tests\Feature;

use App\Models\BillPayment;
use App\Models\BillingItem;
use App\Models\Expense\Expense;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use App\Services\Finance\SchoolVaultService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class SchoolVaultServiceTest extends TestCase
{
    private SchoolVaultService $service;

    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->createSchema();
        $this->service = app(SchoolVaultService::class);
    }

    public function test_single_vault_is_created_once_with_zero_initial_balance(): void
    {
        $first = $this->service->vault();
        $second = $this->service->vault();

        $this->assertSame($first->id, $second->id);
        $this->assertSame(SchoolVault::CURRENCY, $first->currency);
        $this->assertSame(SchoolVault::STATUS_ACTIVE, $first->status);
        $this->assertSame(1, SchoolVault::count());
        $this->assertSame('0.00', $this->service->availableBalance());
        $this->assertSame(['credits' => '0.00', 'debits' => '0.00', 'balance' => '0.00'], $this->service->totals());
    }

    public function test_valid_feeding_credit_and_expense_debit_produce_accurate_balance(): void
    {
        $payment = $this->payment('125.50', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, '2026-01-10 09:30:00');
        $credit = $this->service->postCredit(
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '125.50',
            BillPayment::class,
            $payment->id,
            'Feeding Fee receipt '.$payment->receipt_no,
        );
        $expense = $this->expense('40.25', '2026-09-15');
        $debit = $this->service->postDebit(
            SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE,
            '40.25',
            Expense::class,
            $expense->id,
            'Kitchen supplies',
        );

        $this->assertSame(SchoolVaultTransaction::DIRECTION_CREDIT, $credit->direction);
        $this->assertSame(SchoolVaultTransaction::DIRECTION_DEBIT, $debit->direction);
        $this->assertStringStartsWith('SVT-2026-', $credit->transaction_number);
        $this->assertSame(['credits' => '125.50', 'debits' => '40.25', 'balance' => '85.25'], $this->service->totals());
        $this->assertSame('85.25', $this->service->availableBalance());
        $this->assertSame(2, $this->service->history()->count());
    }

    public function test_multiple_transactions_remain_continuous_across_academic_periods(): void
    {
        foreach ([
            ['amount' => '10.10', 'date' => '2025-09-01 08:00:00'],
            ['amount' => '20.20', 'date' => '2026-01-15 08:00:00'],
            ['amount' => '30.30', 'date' => '2026-05-20 08:00:00'],
        ] as $line) {
            $payment = $this->payment($line['amount'], BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $line['date']);
            $this->service->postCredit(
                SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
                $line['amount'],
                BillPayment::class,
                $payment->id,
                'Termly Feeding Fee '.$payment->receipt_no,
            );
        }

        $this->assertSame('60.60', $this->service->availableBalance());
        $this->assertSame(3, SchoolVaultTransaction::count());
    }

    public function test_opening_balance_and_adjustment_infrastructure_is_controlled_and_idempotent(): void
    {
        $vault = $this->service->vault();
        $opening = $this->service->postCredit(
            SchoolVaultTransaction::TYPE_OPENING_BALANCE,
            '10.00',
            SchoolVault::class,
            $vault->id,
            'Authorized opening balance',
        );
        $retry = $this->service->postCredit(
            SchoolVaultTransaction::TYPE_OPENING_BALANCE,
            '10.00',
            SchoolVault::class,
            $vault->id,
            'Authorized opening balance',
        );
        $this->service->postDebit(
            SchoolVaultTransaction::TYPE_AUTHORIZED_ADJUSTMENT,
            '2.50',
            SchoolVault::class,
            $vault->id,
            'Authorized test adjustment',
        );

        $this->assertSame($opening->id, $retry->id);
        $this->assertSame('7.50', $this->service->availableBalance());
        $this->assertSame(2, SchoolVaultTransaction::count());

        try {
            $vault->currency = 'USD';
            $vault->save();
            $this->fail('Vault currency was changed.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->expectException(LogicException::class);
        $vault->delete();
    }

    public function test_invalid_amounts_and_unsupported_types_are_rejected_without_posting(): void
    {
        $vault = $this->service->vault();

        foreach (['0', '-1.00', '1.001', 'not-money'] as $amount) {
            try {
                $this->service->postCredit(
                    SchoolVaultTransaction::TYPE_OPENING_BALANCE,
                    $amount,
                    SchoolVault::class,
                    $vault->id,
                    'Opening balance test',
                );
                $this->fail('Invalid amount '.$amount.' was accepted.');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }

        try {
            $this->service->postCredit('unknown_type', '1.00', SchoolVault::class, $vault->id, 'Unknown');
            $this->fail('Unsupported type was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, SchoolVaultTransaction::count());
    }

    public function test_direction_rules_and_inactive_vault_are_enforced(): void
    {
        $payment = $this->payment('20.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE);

        try {
            $this->service->postDebit(
                SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
                '20.00',
                BillPayment::class,
                $payment->id,
                'Wrong direction',
            );
            $this->fail('Wrong direction was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $vault = $this->service->vault();
        $vault->status = SchoolVault::STATUS_INACTIVE;
        $vault->save();

        $this->expectException(InvalidArgumentException::class);
        $this->service->postCredit(
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '20.00',
            BillPayment::class,
            $payment->id,
            'Inactive vault',
            vault: $vault,
        );
    }

    public function test_duplicate_source_is_idempotent_and_conflicting_retry_is_rejected(): void
    {
        $payment = $this->payment('50.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE);
        $arguments = [
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '50.00',
            BillPayment::class,
            $payment->id,
            'Feeding Fee receipt '.$payment->receipt_no,
        ];

        $first = $this->service->postCredit(...$arguments);
        $retry = $this->service->postCredit(...$arguments);

        $this->assertSame($first->id, $retry->id);
        $this->assertSame(1, SchoolVaultTransaction::count());

        $this->expectException(LogicException::class);
        $this->service->postCredit(
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '50.00',
            BillPayment::class,
            $payment->id,
            'Conflicting description',
        );
    }

    public function test_school_fees_and_invalid_feeding_sources_cannot_credit_vault(): void
    {
        $schoolPayment = $this->payment('75.00', BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES);

        try {
            $this->service->postCredit(
                SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
                '75.00',
                BillPayment::class,
                $schoolPayment->id,
                'School Fees must not post',
            );
            $this->fail('School Fees credited the vault.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $feedingPayment = $this->payment('60.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE);

        try {
            $this->service->postCredit(
                SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
                '59.99',
                BillPayment::class,
                $feedingPayment->id,
                'Incorrect amount',
            );
            $this->fail('Mismatched Feeding Fee amount credited the vault.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, SchoolVaultTransaction::count());
        $this->assertSame('0.00', $this->service->availableBalance());
    }

    public function test_locked_balance_prevents_overspending_and_rolls_back_failed_debit(): void
    {
        $payment = $this->payment('100.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE);
        $this->service->postCredit(
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '100.00',
            BillPayment::class,
            $payment->id,
            'Feeding credit',
        );

        $firstExpense = $this->expense('75.00');
        $this->service->postDebit(
            SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE,
            '75.00',
            Expense::class,
            $firstExpense->id,
            'First expense',
        );
        $secondExpense = $this->expense('75.00');

        try {
            $this->service->postDebit(
                SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE,
                '75.00',
                Expense::class,
                $secondExpense->id,
                'Second expense',
            );
            $this->fail('Overspending was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertSame('25.00', $this->service->availableBalance());
        $this->assertSame(2, SchoolVaultTransaction::count());
        $this->assertDatabaseMissing('school_vault_transactions', [
            'source_type' => Expense::class,
            'source_id' => $secondExpense->id,
        ]);
    }

    public function test_posted_transactions_are_immutable_and_reversals_are_compensating_entries(): void
    {
        $payment = $this->payment('30.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE);
        $original = $this->service->postCredit(
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '30.00',
            BillPayment::class,
            $payment->id,
            'Original collection',
        );
        $reversal = $this->service->reverse($original, 'Reverse original collection', '2026-11-26 15:00:00');
        $retry = $this->service->reverse($original, 'Reverse original collection', '2026-11-26 15:00:00');

        $this->assertSame($reversal->id, $retry->id);
        $this->assertSame($original->id, $reversal->reversal_of_id);
        $this->assertSame(SchoolVaultTransaction::DIRECTION_DEBIT, $reversal->direction);
        $this->assertSame('0.00', $this->service->availableBalance());
        $this->assertSame(2, SchoolVaultTransaction::count());

        try {
            $original->description = 'Changed';
            $original->save();
            $this->fail('Posted transaction was edited.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        try {
            $original->delete();
            $this->fail('Posted transaction was deleted.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->service->reverse($reversal, 'Invalid reversal of reversal');
    }

    private function payment(string $amount, string $financialCategory, string $paidAt = '2026-11-26 10:00:00'): BillPayment
    {
        $payment = BillPayment::create([
            'student_id' => 1,
            'financial_category' => $financialCategory,
            'receipt_no' => 'TEST-'.str_pad((string) (BillPayment::count() + 1), 4, '0', STR_PAD_LEFT),
            'amount' => $amount,
            'credit_applied' => 0,
            'credit_generated' => 0,
            'payment_method' => 'Cash',
            'paid_at' => $paidAt,
        ]);

        $billId = DB::table('student_bills')->insertGetId([
            'student_id' => 1,
            'financial_category' => $financialCategory,
            'amount_due' => $amount,
            'amount_paid' => $amount,
            'balance' => '0.00',
            'status' => 'Paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('bill_payment_allocations')->insert([
            'bill_payment_id' => $payment->id,
            'student_bill_id' => $billId,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $payment;
    }

    private function expense(string $amount, string $expenseDate = '2026-11-26'): Expense
    {
        return Expense::create([
            'expense_category_id' => 1,
            'expense_date' => $expenseDate,
            'amount' => $amount,
            'payee' => 'Test Supplier',
            'payment_method' => 'Cash',
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('school_vaults', function (Blueprint $table) {
            $table->id(); $table->string('account_code')->unique(); $table->string('name');
            $table->char('currency', 3); $table->string('status'); $table->timestamps();
        });
        Schema::create('school_vault_transactions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('school_vault_id'); $table->string('transaction_number')->unique();
            $table->string('direction'); $table->string('transaction_type'); $table->decimal('amount', 12, 2);
            $table->string('source_type'); $table->unsignedBigInteger('source_id'); $table->string('description');
            $table->dateTime('transaction_date'); $table->unsignedBigInteger('recorded_by')->nullable();
            $table->unsignedBigInteger('reversal_of_id')->nullable()->unique(); $table->timestamp('created_at')->nullable();
            $table->unique(['school_vault_id', 'transaction_type', 'source_type', 'source_id'], 'vault_source_unique');
        });
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->string('financial_category')->nullable();
            $table->string('receipt_no')->unique(); $table->decimal('amount', 12, 2); $table->decimal('credit_applied', 12, 2)->default(0);
            $table->decimal('credit_generated', 12, 2)->default(0); $table->string('payment_method'); $table->string('reference')->nullable();
            $table->string('payment_channel')->nullable(); $table->string('gateway_transaction_id')->nullable(); $table->dateTime('paid_at');
            $table->text('notes')->nullable(); $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->string('financial_category');
            $table->decimal('amount_due', 12, 2); $table->decimal('amount_paid', 12, 2); $table->decimal('balance', 12, 2);
            $table->string('status'); $table->timestamps();
        });
        Schema::create('bill_payment_allocations', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('bill_payment_id'); $table->unsignedBigInteger('student_bill_id');
            $table->decimal('amount', 12, 2); $table->timestamps();
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('expense_category_id'); $table->date('expense_date'); $table->decimal('amount', 12, 2);
            $table->string('payee'); $table->string('payment_method'); $table->string('reference')->nullable(); $table->text('notes')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable(); $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
    }
}
