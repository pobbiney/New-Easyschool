<?php

namespace Tests\Feature;

use App\Models\BillPayment;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use App\Services\Finance\SchoolVaultDashboardService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchoolVaultDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        Carbon::setTestNow('2026-11-26 12:00:00');
        $this->createSchema();
        DB::table('academic_years')->insert(['id' => 1, 'name' => '2026/2027', 'status' => 'Active']);
        DB::table('academic_terms')->insert(['id' => 1, 'name' => 'First Term', 'sort_order' => 1, 'status' => 'Active']);
        DB::table('expense_categories')->insert(['id' => 1, 'name' => 'Utilities', 'status' => 'Active']);
        DB::table('school_vaults')->insert([
            'id' => 1, 'account_code' => SchoolVault::ACCOUNT_CODE, 'name' => 'School Vault',
            'currency' => SchoolVault::CURRENCY, 'status' => SchoolVault::STATUS_ACTIVE,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_route_uses_existing_auth_and_menu_authorization(): void
    {
        $route = Route::getRoutes()->getByName('school-vault-dashboard');

        $this->assertNotNull($route);
        $this->assertSame('school-vault-dashboard', $route->uri());
        $this->assertContains('staff.auth', $route->gatherMiddleware());
        $this->assertContains('menu.access', $route->gatherMiddleware());
    }

    public function test_empty_vault_has_safe_zero_balances_and_empty_charts(): void
    {
        $payload = $this->payload('2026-11-01', '2026-11-30');

        $this->assertSame('0.00', $payload['currentBalance']);
        $this->assertSame('0.00', $payload['summary']['opening']);
        $this->assertSame('0.00', $payload['summary']['closing']);
        $this->assertSame(0, $payload['feedingPerformance']['percentage']);
        $this->assertSame([], array_values(array_filter($payload['trendChart']['collections'])));
        $this->assertTrue($payload['recentTransactions']->isEmpty());
    }

    public function test_ledger_summaries_filters_charts_and_current_balance_reconcile(): void
    {
        $this->seedPopulatedLedger();
        $payload = $this->payload('2026-11-10', '2026-11-30');

        $this->assertSame('130.00', $payload['currentBalance']);
        $this->assertSame('100.00', $payload['summary']['opening']);
        $this->assertSame('40.00', $payload['summary']['collections']);
        $this->assertSame('10.00', $payload['summary']['expenses']);
        $this->assertSame('30.00', $payload['summary']['net_movement']);
        $this->assertSame('130.00', $payload['summary']['closing']);
        $this->assertSame(40.0, array_sum($payload['trendChart']['collections']));
        $this->assertSame(10.0, array_sum($payload['trendChart']['expenses']));

        $narrow = $this->payload('2026-11-25', '2026-11-26');
        $this->assertSame('130.00', $narrow['currentBalance']);
        $this->assertSame('0.00', $narrow['summary']['net_movement']);
        $this->assertSame('130.00', $narrow['summary']['opening']);
        $this->assertSame('130.00', $narrow['summary']['closing']);
    }

    public function test_feeding_bill_performance_includes_unposted_payment_effect_but_vault_does_not(): void
    {
        $this->seedPopulatedLedger();
        $payload = $this->payload('2026-11-01', '2026-11-30');
        $performance = $payload['feedingPerformance'];

        $this->assertSame('180.00', $performance['billed']);
        $this->assertSame('110.00', $performance['paid']);
        $this->assertSame('70.00', $performance['outstanding']);
        $this->assertSame(61.1, $performance['percentage']);
        $this->assertSame(3, $performance['students']);
        $this->assertSame(1, $performance['fully_paid']);
        $this->assertSame(1, $performance['partially_paid']);
        $this->assertSame(1, $performance['unpaid']);
        $this->assertSame('40.00', $payload['summary']['collections']);
        $this->assertSame(1, $payload['reconciliation']['count']);
        $this->assertSame('20.00', $payload['reconciliation']['types'][0]['amount']);
    }

    public function test_cancelled_and_corrected_expenses_have_zero_net_effect(): void
    {
        $this->seedPopulatedLedger();
        $payload = $this->payload('2026-11-01', '2026-11-30');

        $this->assertSame('10.00', $payload['summary']['expenses']);
        $this->assertSame([10.0], array_values(array_filter($payload['expenseChart']['series'])));
        $this->assertSame(0, collect($payload['reconciliation']['types'])->where('key', 'incorrect_expense_net_expenditure')->count());
    }

    public function test_payment_method_breakdown_uses_only_posted_collections(): void
    {
        $this->seedPopulatedLedger();
        $payload = $this->payload('2026-11-01', '2026-11-30');
        $methods = collect($payload['paymentMethods'])->keyBy('label');

        $this->assertSame('40.00', $methods['Cash']['amount']);
        $this->assertSame(100.0, $methods['Cash']['percentage']);
        $this->assertSame('0.00', $methods['Paystack']['amount']);
        $this->assertSame('0.00', $methods['Bank']['amount']);
    }

    private function payload(string $from, string $to): array
    {
        $request = Request::create('/school-vault-dashboard', 'GET', [
            'period' => 'custom',
            'from_date' => $from,
            'to_date' => $to,
            'academic_year_id' => 1,
            'academic_term_id' => 1,
        ]);

        return app(SchoolVaultDashboardService::class)->payload($request, $request->query());
    }

    private function seedPopulatedLedger(): void
    {
        DB::table('student_bills')->insert([
            ['id' => 1, 'student_id' => 1, 'financial_category' => 'feeding_fee', 'academic_year_id' => 1, 'academic_term_id' => 1, 'amount_due' => 100, 'amount_paid' => 60, 'balance' => 40, 'status' => 'Partial'],
            ['id' => 2, 'student_id' => 2, 'financial_category' => 'feeding_fee', 'academic_year_id' => 1, 'academic_term_id' => 1, 'amount_due' => 50, 'amount_paid' => 50, 'balance' => 0, 'status' => 'Paid'],
            ['id' => 3, 'student_id' => 3, 'financial_category' => 'feeding_fee', 'academic_year_id' => 1, 'academic_term_id' => 1, 'amount_due' => 30, 'amount_paid' => 0, 'balance' => 30, 'status' => 'Pending'],
            ['id' => 4, 'student_id' => 4, 'financial_category' => 'school_fees', 'academic_year_id' => 1, 'academic_term_id' => 1, 'amount_due' => 500, 'amount_paid' => 500, 'balance' => 0, 'status' => 'Paid'],
        ]);
        DB::table('bill_payments')->insert([
            ['id' => 1, 'student_id' => 1, 'financial_category' => 'feeding_fee', 'receipt_no' => 'RCP-POSTED', 'amount' => 40, 'payment_method' => 'Cash', 'status' => BillPayment::STATUS_ACTIVE, 'paid_at' => '2026-11-11 09:00:00'],
            ['id' => 2, 'student_id' => 1, 'financial_category' => 'feeding_fee', 'receipt_no' => 'RCP-UNPOSTED', 'amount' => 20, 'payment_method' => 'Cash', 'status' => BillPayment::STATUS_ACTIVE, 'paid_at' => '2026-11-12 09:00:00'],
            ['id' => 3, 'student_id' => 4, 'financial_category' => 'school_fees', 'receipt_no' => 'RCP-SCHOOL', 'amount' => 500, 'payment_method' => 'Cash', 'status' => BillPayment::STATUS_ACTIVE, 'paid_at' => '2026-11-12 10:00:00'],
        ]);
        DB::table('bill_payment_allocations')->insert([
            ['bill_payment_id' => 1, 'student_bill_id' => 1, 'amount' => 40],
            ['bill_payment_id' => 2, 'student_bill_id' => 1, 'amount' => 20],
            ['bill_payment_id' => 3, 'student_bill_id' => 4, 'amount' => 500],
        ]);
        DB::table('expenses')->insert([
            ['id' => 1, 'expense_category_id' => 1, 'expense_date' => '2026-11-13', 'amount' => 10, 'payee' => 'Power Company', 'payment_method' => 'Cash', 'status' => Expense::STATUS_ACTIVE],
            ['id' => 2, 'expense_category_id' => 1, 'expense_date' => '2026-11-14', 'amount' => 15, 'payee' => 'Supplier', 'payment_method' => 'Cash', 'status' => Expense::STATUS_CANCELLED],
        ]);
        DB::table('expense_vault_postings')->insert([
            ['id' => 1, 'expense_id' => 2, 'version' => 1, 'amount' => 20, 'expense_date' => '2026-11-14', 'expense_category_id' => 1, 'payee' => 'Supplier', 'payment_method' => 'Cash', 'status' => ExpenseVaultPosting::STATUS_SUPERSEDED, 'request_token' => '00000000-0000-4000-8000-000000000001'],
            ['id' => 2, 'expense_id' => 2, 'version' => 2, 'amount' => 15, 'expense_date' => '2026-11-16', 'expense_category_id' => 1, 'payee' => 'Supplier', 'payment_method' => 'Cash', 'status' => ExpenseVaultPosting::STATUS_CANCELLED, 'request_token' => '00000000-0000-4000-8000-000000000002'],
        ]);

        $this->insertTransaction(1, 'SVT-OPEN', 'credit', 'opening_balance', 100, SchoolVault::class, 1, '2026-11-01 00:00:00');
        $this->insertTransaction(2, 'SVT-FEED', 'credit', 'feeding_fee_collection', 40, BillPayment::class, 1, '2026-11-11 09:00:00');
        $this->insertTransaction(3, 'SVT-EXP-ACTIVE', 'debit', 'school_operational_expense', 10, Expense::class, 1, '2026-11-13 00:00:00');
        $this->insertTransaction(4, 'SVT-EXP-V1', 'debit', 'school_operational_expense', 20, ExpenseVaultPosting::class, 1, '2026-11-14 00:00:00');
        $this->insertTransaction(5, 'SVT-REV-V1', 'credit', 'reversal', 20, SchoolVaultTransaction::class, 4, '2026-11-15 00:00:00', 4);
        $this->insertTransaction(6, 'SVT-EXP-V2', 'debit', 'school_operational_expense', 15, ExpenseVaultPosting::class, 2, '2026-11-16 00:00:00');
        $this->insertTransaction(7, 'SVT-REV-V2', 'credit', 'reversal', 15, SchoolVaultTransaction::class, 6, '2026-11-17 00:00:00', 6);
    }

    private function insertTransaction(int $id, string $number, string $direction, string $type, float $amount, string $sourceType, int $sourceId, string $date, ?int $reversalOf = null): void
    {
        DB::table('school_vault_transactions')->insert([
            'id' => $id, 'school_vault_id' => 1, 'transaction_number' => $number, 'direction' => $direction,
            'transaction_type' => $type, 'amount' => $amount, 'source_type' => $sourceType, 'source_id' => $sourceId,
            'description' => $number, 'transaction_date' => $date, 'reversal_of_id' => $reversalOf,
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('academic_years', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('status'); $t->timestamps(); });
        Schema::create('academic_terms', function (Blueprint $t) { $t->id(); $t->string('name'); $t->unsignedInteger('sort_order'); $t->string('status'); $t->timestamps(); });
        Schema::create('expense_categories', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('status'); $t->timestamps(); });
        Schema::create('student_bills', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('student_id'); $t->string('financial_category'); $t->unsignedBigInteger('academic_year_id')->nullable();
            $t->unsignedBigInteger('academic_term_id')->nullable(); $t->decimal('amount_due', 12, 2); $t->decimal('amount_paid', 12, 2); $t->decimal('balance', 12, 2); $t->string('status'); $t->timestamps();
        });
        Schema::create('bill_payments', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('student_id'); $t->string('financial_category')->nullable(); $t->string('receipt_no');
            $t->decimal('amount', 12, 2); $t->decimal('credit_applied', 12, 2)->default(0); $t->decimal('credit_generated', 12, 2)->default(0);
            $t->string('payment_method'); $t->string('reference')->nullable(); $t->string('payment_channel')->nullable(); $t->string('gateway_transaction_id')->nullable();
            $t->dateTime('paid_at'); $t->text('notes')->nullable(); $t->string('status')->default(BillPayment::STATUS_ACTIVE); $t->unsignedBigInteger('created_by')->nullable(); $t->unsignedBigInteger('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('bill_payment_allocations', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('bill_payment_id'); $t->unsignedBigInteger('student_bill_id'); $t->decimal('amount', 12, 2); $t->timestamps(); });
        Schema::create('expenses', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('expense_category_id'); $t->date('expense_date'); $t->decimal('amount', 12, 2); $t->string('payee');
            $t->string('payment_method'); $t->string('reference')->nullable(); $t->text('notes')->nullable(); $t->unsignedBigInteger('academic_year_id')->nullable();
            $t->string('status'); $t->timestamp('cancelled_at')->nullable(); $t->unsignedBigInteger('cancelled_by')->nullable(); $t->text('cancellation_reason')->nullable();
            $t->unsignedBigInteger('created_by')->nullable(); $t->unsignedBigInteger('updated_by')->nullable(); $t->timestamps();
        });
        Schema::create('expense_vault_postings', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('expense_id'); $t->unsignedInteger('version'); $t->decimal('amount', 12, 2); $t->date('expense_date');
            $t->unsignedBigInteger('expense_category_id'); $t->string('payee'); $t->string('payment_method'); $t->string('reference')->nullable();
            $t->unsignedBigInteger('academic_year_id')->nullable(); $t->string('status'); $t->uuid('request_token'); $t->text('change_reason')->nullable();
            $t->timestamp('ended_at')->nullable(); $t->unsignedBigInteger('ended_by')->nullable(); $t->unsignedBigInteger('created_by')->nullable(); $t->timestamp('created_at')->nullable();
        });
        Schema::create('school_vaults', function (Blueprint $t) { $t->id(); $t->string('account_code'); $t->string('name'); $t->char('currency', 3); $t->string('status'); $t->timestamps(); });
        Schema::create('school_vault_transactions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('school_vault_id'); $t->string('transaction_number'); $t->string('direction'); $t->string('transaction_type');
            $t->decimal('amount', 12, 2); $t->string('source_type'); $t->unsignedBigInteger('source_id'); $t->string('description'); $t->dateTime('transaction_date');
            $t->unsignedBigInteger('recorded_by')->nullable(); $t->unsignedBigInteger('reversal_of_id')->nullable(); $t->timestamp('created_at')->nullable();
        });
    }
}
