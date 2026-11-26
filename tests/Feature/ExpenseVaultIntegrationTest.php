<?php

namespace Tests\Feature;

use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use App\Services\Finance\ExpenseVaultService;
use App\Services\Finance\SchoolVaultReconciliationService;
use App\Services\Finance\SchoolVaultService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class ExpenseVaultIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        $this->createSchema();
        DB::table('expense_categories')->insert([
            'id' => 1,
            'name' => 'Utilities',
            'status' => 'Active',
        ]);
        DB::table('school_vaults')->insert([
            'id' => 1,
            'account_code' => SchoolVault::ACCOUNT_CODE,
            'name' => 'School Vault',
            'currency' => SchoolVault::CURRENCY,
            'status' => SchoolVault::STATUS_ACTIVE,
        ]);
        $this->seedVaultCredit('100.00');
    }

    public function test_valid_expenses_debit_exact_amount_and_equal_balance_is_accepted(): void
    {
        $first = $this->createExpense('40.00', 'create-1');
        $second = $this->createExpense('60.00', 'create-2');

        $this->assertSame('0.00', app(SchoolVaultService::class)->availableBalance());
        $this->assertSame(2, Expense::count());
        $this->assertSame(2, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)->count());
        $this->assertSame('40.00', $first->vaultPostings->first()->amount);
        $this->assertSame('60.00', $second->vaultPostings->first()->amount);
    }

    public function test_expense_exceeding_balance_is_rejected_and_creation_rolls_back(): void
    {
        try {
            $this->createExpense('100.01', 'too-much');
            $this->fail('Expense exceeding the Vault balance was accepted.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('Insufficient funds in the School Vault', $exception->getMessage());
        }

        $this->assertSame(0, Expense::count());
        $this->assertSame(0, ExpenseVaultPosting::count());
        $this->assertSame('100.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_expense_is_rejected_with_clear_message_when_vault_is_empty(): void
    {
        DB::table('school_vault_transactions')->delete();

        try {
            $this->createExpense('1.00', 'empty-vault');
            $this->fail('An empty Vault accepted an expense.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('Insufficient funds in the School Vault', $exception->getMessage());
            $this->assertStringContainsString('Available: GHS 0.00', $exception->getMessage());
        }

        $this->assertSame(0, Expense::count());
    }

    public function test_zero_and_negative_expenses_are_rejected(): void
    {
        foreach (['0.00', '-1.00'] as $index => $amount) {
            try {
                $this->createExpense($amount, 'invalid-'.$index);
                $this->fail('Invalid expense amount was accepted.');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }

        $this->assertSame(0, Expense::count());
        $this->assertSame(0, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)->count());
    }

    public function test_duplicate_creation_token_is_idempotent(): void
    {
        $first = $this->createExpense('25.00', 'same-token');
        $retry = $this->createExpense('25.00', 'same-token');

        $this->assertSame($first->id, $retry->id);
        $this->assertSame(1, Expense::count());
        $this->assertSame(1, ExpenseVaultPosting::count());
        $this->assertSame(1, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)->count());

        $this->expectException(InvalidArgumentException::class);
        $this->createExpense('26.00', 'same-token');
    }

    public function test_vault_failure_rolls_back_expense_and_posting(): void
    {
        $vault = SchoolVault::findOrFail(1);
        $vault->status = SchoolVault::STATUS_INACTIVE;
        $vault->save();

        try {
            $this->createExpense('20.00', 'inactive-vault');
            $this->fail('Inactive Vault accepted an expense.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, Expense::count());
        $this->assertSame(0, ExpenseVaultPosting::count());
    }

    public function test_non_financial_edit_preserves_debit_and_financial_corrections_are_versioned(): void
    {
        $service = app(ExpenseVaultService::class);
        $expense = $this->createExpense('60.00', 'original');
        $originalDebit = $expense->vaultPostings->first()->vaultTransactions->first();
        $data = $this->expenseData('60.00');
        $data['notes'] = 'Updated note only';

        $service->update($expense, $data, 7, 'note-edit', null);
        $this->assertSame(1, ExpenseVaultPosting::count());
        $this->assertSame(1, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)->count());

        $data['amount'] = '40.00';
        $corrected = $service->update($expense->fresh(), $data, 7, 'correction-1', 'Vendor corrected the invoice.');

        $this->assertSame('40.00', $corrected->amount);
        $this->assertSame(2, ExpenseVaultPosting::count());
        $this->assertSame(1, SchoolVaultTransaction::where('reversal_of_id', $originalDebit->id)->count());
        $this->assertSame('60.00', app(SchoolVaultService::class)->availableBalance());

        $data['amount'] = '30.00';
        $service->update($corrected->fresh(), $data, 7, 'correction-2', 'Applied final supplier discount.');

        $this->assertSame(3, ExpenseVaultPosting::count());
        $this->assertSame(3, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)->count());
        $this->assertSame(2, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_REVERSAL)->count());
        $this->assertSame('70.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_cancellation_restores_balance_once_and_excludes_expense_from_active_scope(): void
    {
        $service = app(ExpenseVaultService::class);
        $expense = $this->createExpense('35.00', 'cancel-me');

        $service->cancel($expense, 'Order cancelled by supplier.', 7);
        $service->cancel($expense->fresh(), 'Repeated request.', 7);

        $this->assertSame(Expense::STATUS_CANCELLED, $expense->fresh()->status);
        $this->assertSame(0, Expense::active()->count());
        $this->assertSame(1, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_REVERSAL)->count());
        $this->assertSame('100.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_reconciliation_detects_unposted_and_mismatched_expenses(): void
    {
        Expense::create([
            ...$this->expenseData('10.00'),
            'status' => Expense::STATUS_ACTIVE,
            'created_by' => 7,
        ]);
        $posted = $this->createExpense('20.00', 'recon-posted');
        DB::table('school_vault_transactions')
            ->where('source_type', ExpenseVaultPosting::class)
            ->where('source_id', $posted->vaultPostings->first()->id)
            ->update(['amount' => '19.00']);

        $report = app(SchoolVaultReconciliationService::class)->report();

        $this->assertSame(1, $report['active_expenses_without_debits']->count());
        $this->assertSame(1, $report['expense_amount_mismatches']->count());
        $this->assertSame(1, $report['incorrect_expense_net_expenditure']->count());
    }

    private function createExpense(string $amount, string $token): Expense
    {
        $hash = md5($token);
        $uuid = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-4'.substr($hash, 13, 3)
            .'-8'.substr($hash, 17, 3).'-'.substr($hash, 20, 12);

        return app(ExpenseVaultService::class)->create($this->expenseData($amount), 7, $uuid);
    }

    private function expenseData(string $amount): array
    {
        return [
            'expense_category_id' => 1,
            'expense_date' => '2026-11-26',
            'amount' => $amount,
            'payee' => 'Test Supplier',
            'payment_method' => 'Cash',
            'reference' => 'TEST-REF',
            'notes' => null,
            'academic_year_id' => null,
        ];
    }

    private function seedVaultCredit(string $amount): void
    {
        DB::table('school_vault_transactions')->insert([
            'school_vault_id' => 1,
            'transaction_number' => 'SVT-OPENING-TEST',
            'direction' => SchoolVaultTransaction::DIRECTION_CREDIT,
            'transaction_type' => SchoolVaultTransaction::TYPE_OPENING_BALANCE,
            'amount' => $amount,
            'source_type' => SchoolVault::class,
            'source_id' => 1,
            'description' => 'Isolated test credit',
            'transaction_date' => '2026-11-26 00:00:00',
            'created_at' => now(),
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id(); $table->string('financial_category')->nullable(); $table->string('receipt_no')->nullable();
            $table->decimal('amount', 12, 2)->default(0); $table->timestamps();
        });
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('status'); $table->timestamps();
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('expense_category_id'); $table->date('expense_date');
            $table->decimal('amount', 12, 2); $table->string('payee'); $table->string('payment_method');
            $table->string('reference')->nullable(); $table->text('notes')->nullable(); $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('status')->default(Expense::STATUS_ACTIVE); $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable(); $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
        Schema::create('expense_vault_postings', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('expense_id'); $table->unsignedInteger('version'); $table->decimal('amount', 12, 2);
            $table->date('expense_date'); $table->unsignedBigInteger('expense_category_id'); $table->string('payee');
            $table->string('payment_method'); $table->string('reference')->nullable(); $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('status'); $table->uuid('request_token')->unique(); $table->text('change_reason')->nullable();
            $table->timestamp('ended_at')->nullable(); $table->unsignedBigInteger('ended_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); $table->timestamp('created_at')->nullable();
            $table->unique(['expense_id', 'version']);
        });
        Schema::create('school_vaults', function (Blueprint $table) {
            $table->id(); $table->string('account_code')->unique(); $table->string('name'); $table->char('currency', 3); $table->string('status'); $table->timestamps();
        });
        Schema::create('school_vault_transactions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('school_vault_id'); $table->string('transaction_number')->unique();
            $table->string('direction'); $table->string('transaction_type'); $table->decimal('amount', 12, 2);
            $table->string('source_type'); $table->unsignedBigInteger('source_id'); $table->string('description');
            $table->dateTime('transaction_date'); $table->unsignedBigInteger('recorded_by')->nullable();
            $table->unsignedBigInteger('reversal_of_id')->nullable()->unique(); $table->timestamp('created_at')->nullable();
            $table->unique(['school_vault_id', 'transaction_type', 'source_type', 'source_id'], 'vault_source_unique');
        });
    }
}
