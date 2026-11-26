<?php

namespace Tests\Feature;

use App\Models\BillingItem;
use App\Http\Controllers\Billing\BillPaymentController;
use App\Models\BillPayment;
use App\Models\BillPendingPayment;
use App\Models\Expense\Expense;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use App\Services\Billing\BillPaymentProcessor;
use App\Services\Billing\DevelopmentPaymentCorrectionService;
use App\Services\Billing\PaymentMethodEligibility;
use App\Services\Finance\ExpenseVaultService;
use App\Services\Finance\SchoolVaultService;
use App\Services\Finance\SchoolVaultReconciliationService;
use App\Support\Money;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Tests\TestCase;

class BillPaymentFinancialSeparationTest extends TestCase
{
    private Student $student;
    private Student $otherStudent;
    private StudentBill $schoolBill;
    private StudentBill $feedingBill;

    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->createSchema();
        $this->seedBills();
    }

    public function test_outstanding_bills_are_filterable_by_financial_category(): void
    {
        $processor = app(BillPaymentProcessor::class);

        $school = $processor->outstandingBillsForStudent($this->student, BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES);
        $feeding = $processor->outstandingBillsForStudent($this->student, BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE);

        $this->assertSame([$this->schoolBill->id], $school->pluck('id')->all());
        $this->assertSame([$this->feedingBill->id], $feeding->pluck('id')->all());
        $this->assertSame(500.0, $school->sum('balance'));
        $this->assertSame(100.0, $feeding->sum('balance'));
    }

    public function test_mixed_category_and_manipulated_student_bill_ids_are_rejected(): void
    {
        $processor = app(BillPaymentProcessor::class);

        $this->expectException(InvalidArgumentException::class);
        $processor->resolveAllocations($this->student->id, BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES, 600, 0, collect([
            ['student_bill_id' => $this->schoolBill->id, 'amount' => 500],
            ['student_bill_id' => $this->feedingBill->id, 'amount' => 100],
        ]));
    }

    public function test_bill_from_another_student_is_rejected(): void
    {
        $foreignBill = StudentBill::create([
            'student_id' => $this->otherStudent->id,
            'billing_item_id' => 1,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            'amount_due' => 50,
            'amount_paid' => 0,
            'balance' => 50,
            'status' => 'Pending',
        ]);

        $this->expectException(InvalidArgumentException::class);
        app(BillPaymentProcessor::class)->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            50,
            0,
            collect([['student_bill_id' => $foreignBill->id, 'amount' => 50]])
        );
    }

    public function test_feeding_fee_instalment_is_classified_and_reduces_only_feeding_balance(): void
    {
        $processor = app(BillPaymentProcessor::class);
        $allocations = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            40,
            0,
            collect([['student_bill_id' => $this->feedingBill->id, 'amount' => 40]])
        );

        $payment = DB::transaction(fn () => $processor->finalizePayment(
            student: $this->student,
            financialCategory: BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            cashAmount: 40,
            creditApplied: 0,
            paymentMethod: 'Cash',
            paidAt: '2026-11-26 12:00:00',
            allocations: $allocations,
        ));

        $this->assertSame(BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $payment->financial_category);
        $this->assertSame('60.00', $this->feedingBill->fresh()->balance);
        $this->assertSame('500.00', $this->schoolBill->fresh()->balance);
        $this->assertSame('0.00', $payment->credit_generated);
        $this->assertDatabaseHas('school_vault_transactions', [
            'source_type' => \App\Models\BillPayment::class,
            'source_id' => $payment->id,
            'direction' => SchoolVaultTransaction::DIRECTION_CREDIT,
            'transaction_type' => SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            'amount' => 40,
        ]);

        $remaining = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            60,
            0,
            collect([['student_bill_id' => $this->feedingBill->id, 'amount' => 60]])
        );
        DB::transaction(fn () => $processor->finalizePayment(
            student: $this->student,
            financialCategory: BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            cashAmount: 60,
            creditApplied: 0,
            paymentMethod: 'Cash',
            paidAt: '2026-11-26 12:30:00',
            allocations: $remaining,
        ));

        $this->assertSame('0.00', $this->feedingBill->fresh()->balance);
        $this->assertSame('Paid', $this->feedingBill->fresh()->status);
        $this->assertSame(2, SchoolVaultTransaction::count());
        $this->assertSame('100.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_feeding_fee_paystack_is_rejected_but_school_fees_paystack_remains_available(): void
    {
        $processor = app(BillPaymentProcessor::class);
        $feedingAllocations = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            '20.00',
            0,
            collect([['student_bill_id' => $this->feedingBill->id, 'amount' => '20.00']])
        );

        try {
            DB::transaction(fn () => $processor->finalizePayment(
                student: $this->student,
                financialCategory: BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
                cashAmount: '20.00',
                creditApplied: 0,
                paymentMethod: 'Paystack',
                paidAt: now()->toDateTimeString(),
                allocations: $feedingAllocations,
            ));
            $this->fail('A new Feeding Fee Paystack payment was accepted.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(PaymentMethodEligibility::FEEDING_FEE_METHOD_MESSAGE, $exception->getMessage());
        }

        $schoolAllocations = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            '20.00',
            0,
            collect([['student_bill_id' => $this->schoolBill->id, 'amount' => '20.00']])
        );
        $payment = DB::transaction(fn () => $processor->finalizePayment(
            student: $this->student,
            financialCategory: BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            cashAmount: '20.00',
            creditApplied: 0,
            paymentMethod: 'Paystack',
            paidAt: now()->toDateTimeString(),
            allocations: $schoolAllocations,
        ));

        $this->assertSame('Paystack', $payment->payment_method);
        $this->assertSame('480.00', $this->schoolBill->fresh()->balance);
        $this->assertSame(0, SchoolVaultTransaction::count());
    }

    public function test_paystack_initialization_rejects_feeding_and_mixed_allocations_before_gateway_checkout(): void
    {
        config(['paystack.public_key' => 'pk_test_local', 'paystack.secret_key' => 'sk_test_local']);
        $controller = app(BillPaymentController::class);

        $feeding = Request::create('/paystack/bill-payment/initialize', 'POST', [
            'student_id' => $this->student->id,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'amount' => '20.00',
            'credit_applied' => '0.00',
            'allocations' => [['student_bill_id' => $this->feedingBill->id, 'amount' => '20.00']],
        ], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $feedingResponse = $controller->initializePaystack($feeding);

        $this->assertSame(422, $feedingResponse->getStatusCode());
        $this->assertSame(
            PaymentMethodEligibility::FEEDING_FEE_METHOD_MESSAGE,
            $feedingResponse->getData(true)['message'],
        );
        $this->assertSame(0, DB::table('bill_payment_transactions')->count());

        $mixed = Request::create('/paystack/bill-payment/initialize', 'POST', [
            'student_id' => $this->student->id,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            'amount' => '40.00',
            'credit_applied' => '0.00',
            'allocations' => [
                ['student_bill_id' => $this->schoolBill->id, 'amount' => '20.00'],
                ['student_bill_id' => $this->feedingBill->id, 'amount' => '20.00'],
            ],
        ], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $mixedResponse = $controller->initializePaystack($mixed);

        $this->assertSame(422, $mixedResponse->getStatusCode());
        $this->assertSame(0, DB::table('bill_payment_transactions')->count());
    }

    public function test_feeding_fee_rejects_shared_credit_and_overpayment(): void
    {
        $this->student->update(['credit_balance' => 30]);
        $processor = app(BillPaymentProcessor::class);

        try {
            $processor->resolveAllocations(
                $this->student->id,
                BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
                20,
                10,
                collect([['student_bill_id' => $this->feedingBill->id, 'amount' => 30]])
            );
            $this->fail('Feeding Fee accepted shared student credit.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('credit', strtolower($exception->getMessage()));
        }

        $this->expectException(InvalidArgumentException::class);
        $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            110,
            0,
            collect([['student_bill_id' => $this->feedingBill->id, 'amount' => 100]])
        );
    }

    public function test_pending_bank_approval_retains_its_financial_category(): void
    {
        $pending = BillPendingPayment::create([
            'student_id' => $this->student->id,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'amount' => 25,
            'credit_applied' => 0,
            'allocations' => [['student_bill_id' => $this->feedingBill->id, 'amount' => 25]],
            'payment_method' => 'Bank',
            'bank_instrument' => 'Cheque',
            'reference' => 'TEST-CHEQUE',
            'paid_at' => '2026-11-26 13:00:00',
            'status' => BillPendingPayment::STATUS_PENDING,
        ]);

        $this->assertSame(0, SchoolVaultTransaction::count());

        $processor = app(BillPaymentProcessor::class);
        $allocations = $processor->resolveAllocations(
            $pending->student_id,
            $pending->financial_category,
            (float) $pending->amount,
            (float) $pending->credit_applied,
            collect($pending->allocations)
        );
        $payment = DB::transaction(fn () => $processor->finalizePayment(
            student: $this->student,
            financialCategory: $pending->financial_category,
            cashAmount: (float) $pending->amount,
            creditApplied: 0,
            paymentMethod: 'Bank',
            paidAt: $pending->paid_at->toDateTimeString(),
            allocations: $allocations,
            reference: $pending->reference,
        ));

        $this->assertSame(BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $payment->financial_category);
        $this->assertSame('75.00', $this->feedingBill->fresh()->balance);
        $this->assertSame(1, SchoolVaultTransaction::count());
        $this->assertSame('25.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_rejected_pending_bank_payment_does_not_post_to_vault(): void
    {
        BillPendingPayment::create([
            'student_id' => $this->student->id,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'amount' => 25,
            'credit_applied' => 0,
            'allocations' => [['student_bill_id' => $this->feedingBill->id, 'amount' => 25]],
            'payment_method' => 'Bank',
            'bank_instrument' => 'Bankers Draft',
            'reference' => 'REJECTED-DRAFT',
            'paid_at' => '2026-11-26 13:00:00',
            'status' => BillPendingPayment::STATUS_REJECTED,
        ]);

        $this->assertSame(0, BillPayment::count());
        $this->assertSame(0, SchoolVaultTransaction::count());
    }

    public function test_school_fees_payment_does_not_post_to_school_vault(): void
    {
        $processor = app(BillPaymentProcessor::class);
        $allocations = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            100,
            0,
            collect([['student_bill_id' => $this->schoolBill->id, 'amount' => 100]])
        );

        DB::transaction(fn () => $processor->finalizePayment(
            student: $this->student,
            financialCategory: BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            cashAmount: 100,
            creditApplied: 0,
            paymentMethod: 'Cash',
            paidAt: '2026-11-26 14:00:00',
            allocations: $allocations,
        ));

        $this->assertSame(0, SchoolVaultTransaction::count());
        $this->assertSame('0.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_complete_school_fees_feeding_and_expense_lifecycle_reconciles(): void
    {
        $processor = app(BillPaymentProcessor::class);
        $expenses = app(ExpenseVaultService::class);
        $vault = app(SchoolVaultService::class);

        $this->schoolBill->update(['amount_due' => 2000, 'amount_paid' => 0, 'balance' => 2000, 'status' => 'Pending']);
        $this->feedingBill->update(['amount_due' => 1200, 'amount_paid' => 0, 'balance' => 1200, 'status' => 'Pending']);

        $pay = function (string $category, float $amount, int $billId, string $method = 'Cash') use ($processor): BillPayment {
            $allocations = $processor->resolveAllocations(
                $this->student->id,
                $category,
                $amount,
                0,
                collect([['student_bill_id' => $billId, 'amount' => $amount]])
            );

            return DB::transaction(fn () => $processor->finalizePayment(
                student: $this->student,
                financialCategory: $category,
                cashAmount: $amount,
                creditApplied: 0,
                paymentMethod: $method,
                paidAt: now()->toDateTimeString(),
                allocations: $allocations,
            ));
        };

        $pay(BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES, 1000, $this->schoolBill->id);
        $this->assertSame('0.00', $vault->availableBalance());

        foreach ([[300, '300.00'], [500, '800.00'], [400, '1200.00']] as [$amount, $expected]) {
            $pay(BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $amount, $this->feedingBill->id);
            $this->assertSame($expected, $vault->availableBalance());
        }

        $this->assertSame('0.00', $this->feedingBill->fresh()->balance);
        $this->assertSame('1000.00', $this->schoolBill->fresh()->balance);

        DB::table('expense_categories')->insert(['id' => 1, 'name' => 'Operations', 'status' => 'Active']);
        $expenseData = fn (string $amount, string $reference) => [
            'expense_category_id' => 1,
            'expense_date' => now()->toDateString(),
            'amount' => $amount,
            'payee' => 'Isolated test supplier',
            'payment_method' => 'Cash',
            'reference' => $reference,
            'notes' => 'Task 009 isolated workflow',
            'academic_year_id' => null,
        ];

        $first = $expenses->create($expenseData('350.00', 'E2E-350'), 1, '00000000-0000-4000-8000-000000000001');
        $this->assertSame('850.00', $vault->availableBalance());
        $second = $expenses->create($expenseData('200.00', 'E2E-200'), 1, '00000000-0000-4000-8000-000000000002');
        $this->assertSame('650.00', $vault->availableBalance());

        $first = $expenses->update(
            $first->fresh(),
            $expenseData('250.00', 'E2E-350'),
            1,
            '00000000-0000-4000-8000-000000000003',
            'Corrected isolated test invoice.'
        );
        $this->assertSame('750.00', $vault->availableBalance());

        $expenses->cancel($second->fresh(), 'Cancelled isolated test invoice.', 1);
        $this->assertSame('950.00', $vault->availableBalance());

        try {
            $expenses->create($expenseData('1000.00', 'E2E-REJECT'), 1, '00000000-0000-4000-8000-000000000004');
            $this->fail('An expense exceeding the final Vault balance was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertSame('950.00', $vault->availableBalance());
        }

        $retry = $expenses->update(
            $first->fresh(),
            $expenseData('250.00', 'E2E-350'),
            1,
            '00000000-0000-4000-8000-000000000003',
            'Corrected isolated test invoice.'
        );

        $this->assertSame($first->id, $retry->id);
        $this->assertSame(3, SchoolVaultTransaction::where('transaction_type', SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION)->count());
        $this->assertSame(2, Expense::count());
        $this->assertSame('950.00', $vault->availableBalance());
    }

    public function test_historical_verified_feeding_paystack_finalization_is_preserved_and_idempotent(): void
    {
        $processor = app(BillPaymentProcessor::class);
        $allocations = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            30,
            0,
            collect([['student_bill_id' => $this->feedingBill->id, 'amount' => 30]])
        );
        $payment = DB::transaction(fn () => $processor->finalizePayment(
            student: $this->student,
            financialCategory: BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            cashAmount: 30,
            creditApplied: 0,
            paymentMethod: 'Paystack',
            paidAt: '2026-11-26 15:00:00',
            allocations: $allocations,
            reference: 'PAYSTACK-TEST',
            paymentChannel: 'mobile_money',
            gatewayTransactionId: 'TEST-GATEWAY-ID',
            historicalGatewayCompletion: true,
        ));

        $entry = $payment->schoolVaultTransactions->firstOrFail();
        $retry = app(SchoolVaultService::class)->postCredit(
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
            '30.00',
            \App\Models\BillPayment::class,
            $payment->id,
            'Feeding Fee collection '.$payment->receipt_no.' · PAYSTACK-TEST',
            $payment->paid_at,
            $payment->created_by,
        );

        $this->assertSame($entry->id, $retry->id);
        $this->assertSame(1, SchoolVaultTransaction::count());
        $this->assertSame('30.00', app(SchoolVaultService::class)->availableBalance());
    }

    public function test_unposted_development_payment_correction_is_auditable_atomic_and_idempotent(): void
    {
        $this->feedingBill->update(['amount_paid' => '100.00', 'balance' => '0.00', 'status' => 'Paid']);
        $payment = $this->manualPayment(
            'RCP-DEV-VOID',
            '100.00',
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            $this->feedingBill->id,
        );

        $service = app(DevelopmentPaymentCorrectionService::class);
        $corrected = $service->voidUnpostedPayment(
            'RCP-DEV-VOID',
            '100.00',
            'Authorized development test correction.',
            'Automated test',
            1,
        );

        $this->assertTrue($corrected->isVoided());
        $this->assertSame('RCP-DEV-VOID', $corrected->receipt_no);
        $this->assertSame('100.00', $corrected->allocations->first()->amount);
        $this->assertSame('0.00', $this->feedingBill->fresh()->amount_paid);
        $this->assertSame('100.00', $this->feedingBill->fresh()->balance);
        $this->assertSame('Pending', $this->feedingBill->fresh()->status);
        $this->assertSame('0.00', app(SchoolVaultService::class)->availableBalance());
        $this->assertSame(0, app(SchoolVaultReconciliationService::class)->report()['issue_count']);

        $again = $service->voidUnpostedPayment(
            'RCP-DEV-VOID',
            '100.00',
            'Authorized development test correction.',
            'Automated test',
            1,
        );

        $this->assertSame($corrected->id, $again->id);
        $this->assertSame('100.00', $this->feedingBill->fresh()->balance);
        $this->assertSame(1, DB::table('bill_payment_allocations')->count());
    }

    public function test_vault_failure_rolls_back_payment_allocations_and_bill_update(): void
    {
        $vault = app(SchoolVaultService::class)->vault();
        $vault->status = SchoolVault::STATUS_INACTIVE;
        $vault->save();
        $processor = app(BillPaymentProcessor::class);
        $allocations = $processor->resolveAllocations(
            $this->student->id,
            BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            20,
            0,
            collect([['student_bill_id' => $this->feedingBill->id, 'amount' => 20]])
        );

        try {
            DB::transaction(fn () => $processor->finalizePayment(
                student: $this->student,
                financialCategory: BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
                cashAmount: 20,
                creditApplied: 0,
                paymentMethod: 'Cash',
                paidAt: '2026-11-26 16:00:00',
                allocations: $allocations,
            ));
            $this->fail('Inactive vault accepted a Feeding Fee payment.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, \App\Models\BillPayment::count());
        $this->assertSame(0, DB::table('bill_payment_allocations')->count());
        $this->assertSame('100.00', $this->feedingBill->fresh()->balance);
        $this->assertSame(0, SchoolVaultTransaction::count());
    }

    public function test_reconciliation_reports_missing_mismatched_orphan_and_school_fee_links(): void
    {
        $vault = app(SchoolVaultService::class)->vault();
        $missing = $this->manualPayment('RECON-MISSING', '10.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $this->feedingBill->id);
        $mismatch = $this->manualPayment('RECON-MISMATCH', '15.00', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $this->feedingBill->id);
        $school = $this->manualPayment('RECON-SCHOOL', '20.00', BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES, $this->schoolBill->id);

        foreach ([
            ['number' => 'SVT-RECON-MISMATCH', 'amount' => '14.00', 'source_id' => $mismatch->id],
            ['number' => 'SVT-RECON-ORPHAN', 'amount' => '5.00', 'source_id' => 99999],
            ['number' => 'SVT-RECON-SCHOOL', 'amount' => '20.00', 'source_id' => $school->id],
        ] as $row) {
            DB::table('school_vault_transactions')->insert([
                'school_vault_id' => $vault->id,
                'transaction_number' => $row['number'],
                'direction' => SchoolVaultTransaction::DIRECTION_CREDIT,
                'transaction_type' => SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
                'amount' => $row['amount'],
                'source_type' => BillPayment::class,
                'source_id' => $row['source_id'],
                'description' => 'Reconciliation fixture',
                'transaction_date' => '2026-11-26 17:00:00',
                'created_at' => now(),
            ]);
        }

        $report = app(SchoolVaultReconciliationService::class)->report();

        $this->assertTrue($report['missing_payment_credits']->contains('payment_id', $missing->id));
        $this->assertTrue($report['amount_mismatches']->contains('payment_id', $mismatch->id));
        $this->assertTrue($report['orphan_collection_entries']->contains('source_id', 99999));
        $this->assertTrue($report['school_fees_links']->contains('payment_id', $school->id));
        $this->assertSame(4, $report['issue_count']);
    }

    private function manualPayment(string $receipt, string $amount, string $category, int $studentBillId): BillPayment
    {
        $payment = BillPayment::create([
            'student_id' => $this->student->id,
            'financial_category' => $category,
            'receipt_no' => $receipt,
            'amount' => $amount,
            'credit_applied' => 0,
            'credit_generated' => 0,
            'payment_method' => 'Cash',
            'paid_at' => '2026-11-26 17:00:00',
        ]);
        DB::table('bill_payment_allocations')->insert([
            'bill_payment_id' => $payment->id,
            'student_bill_id' => $studentBillId,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $payment;
    }

    public function test_decimal_pesewas_allocate_exactly_across_repeated_instalments(): void
    {
        $this->feedingBill->update([
            'amount_due' => '0.30',
            'amount_paid' => '0.00',
            'balance' => '0.30',
            'status' => 'Pending',
        ]);

        $processor = app(BillPaymentProcessor::class);

        foreach (['0.10', '0.10', '0.10'] as $amount) {
            $allocations = $processor->resolveAllocations(
                $this->student->id,
                BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
                $amount,
                '0.00',
                collect([['student_bill_id' => $this->feedingBill->id, 'amount' => $amount]])
            );

            DB::transaction(fn () => $processor->finalizePayment(
                student: $this->student,
                financialCategory: BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
                cashAmount: $amount,
                creditApplied: '0.00',
                paymentMethod: 'Cash',
                paidAt: now()->toDateTimeString(),
                allocations: $allocations,
            ));
        }

        $this->assertSame('0.30', $this->feedingBill->fresh()->amount_paid);
        $this->assertSame('0.00', $this->feedingBill->fresh()->balance);
        $this->assertSame('Paid', $this->feedingBill->fresh()->status);
        $this->assertSame('0.30', app(SchoolVaultService::class)->availableBalance());
        $this->assertSame('0.30', Money::fromMinor(Money::toMinor((string) BillPayment::query()->sum('amount'))));
    }

    private function seedBills(): void
    {
        DB::table('billing_items')->insert([
            ['id' => 1, 'name' => 'Tuition', 'is_compulsory' => 1, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES],
            ['id' => 2, 'name' => 'Feeding Fee', 'is_compulsory' => 0, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE],
        ]);
        DB::table('students')->insert([
            ['id' => 1, 'student_id' => 'TEST-1', 'firstname' => 'Test', 'surname' => 'One', 'credit_balance' => 0],
            ['id' => 2, 'student_id' => 'TEST-2', 'firstname' => 'Test', 'surname' => 'Two', 'credit_balance' => 0],
        ]);

        $this->student = Student::findOrFail(1);
        $this->otherStudent = Student::findOrFail(2);
        $this->schoolBill = StudentBill::create([
            'student_id' => 1, 'billing_item_id' => 1, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            'amount_due' => 500, 'amount_paid' => 0, 'balance' => 500, 'status' => 'Pending',
        ]);
        $this->feedingBill = StudentBill::create([
            'student_id' => 1, 'billing_item_id' => 2, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'amount_due' => 100, 'amount_paid' => 0, 'balance' => 100, 'status' => 'Pending',
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); $table->string('student_id'); $table->string('firstname'); $table->string('surname');
            $table->decimal('credit_balance', 12, 2)->default(0); $table->timestamps();
        });
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->boolean('is_compulsory')->default(true);
            $table->string('financial_category', 30); $table->timestamps();
        });
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->unsignedBigInteger('billing_item_id');
            $table->unsignedBigInteger('category_bill_setup_id')->nullable(); $table->string('financial_category', 30);
            $table->unsignedBigInteger('academic_year_id')->nullable(); $table->unsignedBigInteger('academic_term_id')->nullable();
            $table->decimal('amount_due', 12, 2); $table->decimal('amount_paid', 12, 2); $table->decimal('balance', 12, 2);
            $table->string('status'); $table->timestamps();
        });
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->string('financial_category', 30)->nullable();
            $table->string('receipt_no')->unique(); $table->decimal('amount', 12, 2); $table->decimal('credit_applied', 12, 2)->default(0);
            $table->decimal('credit_generated', 12, 2)->default(0); $table->string('payment_method'); $table->string('reference')->nullable();
            $table->string('payment_channel')->nullable(); $table->string('gateway_transaction_id')->nullable(); $table->dateTime('paid_at');
            $table->text('notes')->nullable(); $table->string('status')->default(BillPayment::STATUS_ACTIVE); $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable(); $table->string('voided_actor')->nullable(); $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable(); $table->timestamps();
        });
        Schema::create('bill_payment_receipt_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary(); $table->unsignedBigInteger('last_number')->default(0); $table->timestamps();
        });
        Schema::create('bill_payment_allocations', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('bill_payment_id'); $table->unsignedBigInteger('student_bill_id');
            $table->decimal('amount', 12, 2); $table->timestamps();
        });
        Schema::create('bill_payment_transactions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->string('financial_category')->nullable();
            $table->string('reference')->unique(); $table->decimal('amount', 12, 2); $table->decimal('credit_applied', 12, 2)->default(0);
            $table->string('currency', 3); $table->string('status'); $table->json('allocations')->nullable();
            $table->string('paystack_transaction_id')->nullable(); $table->string('paystack_channel')->nullable(); $table->json('gateway_response')->nullable();
            $table->unsignedBigInteger('bill_payment_id')->nullable(); $table->unsignedBigInteger('created_by')->nullable();
            $table->string('initiated_by')->nullable(); $table->unsignedBigInteger('parent_account_id')->nullable(); $table->timestamps();
        });
        Schema::create('student_bill_credit_transactions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->string('type'); $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2); $table->string('source'); $table->unsignedBigInteger('bill_payment_id')->nullable();
            $table->text('notes')->nullable(); $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps();
        });
        Schema::create('bill_pending_payments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->string('financial_category', 30)->nullable();
            $table->decimal('amount', 12, 2); $table->decimal('credit_applied', 12, 2)->default(0); $table->json('allocations');
            $table->string('payment_method'); $table->string('bank_instrument'); $table->string('reference'); $table->string('receipt_path')->nullable();
            $table->dateTime('paid_at'); $table->text('notes')->nullable(); $table->string('status'); $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); $table->dateTime('approved_at')->nullable(); $table->string('rejected_reason')->nullable();
            $table->unsignedBigInteger('bill_payment_id')->nullable(); $table->timestamps();
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
    }
}
