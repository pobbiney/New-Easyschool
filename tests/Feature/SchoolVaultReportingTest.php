<?php

namespace Tests\Feature;

use App\Models\BillPayment;
use App\Models\Expense\Expense;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use App\Models\VaultCashReconciliation;
use App\Services\Finance\SchoolVaultReportingService;
use App\Services\Finance\SchoolVaultService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class SchoolVaultReportingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        Carbon::setTestNow('2026-11-26 12:00:00');
        $this->schema();
        DB::table('academic_years')->insert(['id'=>1,'name'=>'2026/2027','status'=>'Active']);
        DB::table('academic_terms')->insert(['id'=>1,'name'=>'First Term','sort_order'=>1,'status'=>'Active']);
        DB::table('class_categories')->insert(['id'=>1,'name'=>'Primary','status'=>'Active']);
        DB::table('school_classes')->insert(['id'=>1,'name'=>'Class 1','class_category_id'=>1,'status'=>'Active']);
        DB::table('expense_categories')->insert(['id'=>1,'name'=>'Utilities','status'=>'Active']);
        DB::table('school_vaults')->insert(['id'=>1,'account_code'=>SchoolVault::ACCOUNT_CODE,'name'=>'School Vault','currency'=>'GHS','status'=>'Active']);
        DB::table('users')->insert(['id'=>1,'name'=>'Accountant']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_all_reporting_and_export_routes_use_existing_authorization(): void
    {
        $names = ['school-vault-collections','school-vault-transactions','school-vault-reconciliation','school-vault-reconciliation-store','school-vault-statement'];
        foreach (['collections','transactions','reconciliation','statement'] as $report) {
            foreach (['print','pdf','excel'] as $format) $names[] = 'school-vault-'.$report.'-'.$format;
        }

        foreach ($names as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertContains('staff.auth', $route->gatherMiddleware(), $name);
            $this->assertContains('menu.access', $route->gatherMiddleware(), $name);
        }
    }

    public function test_collections_keep_billing_confirmed_payments_and_vault_postings_distinct(): void
    {
        $this->seedStudentsAndPayments();
        $data = $this->reports()->collections($this->request('/school-vault-collections', ['academic_year_id'=>1,'academic_term_id'=>1]), false);

        $this->assertSame('150.00', $data['summary']['billed']);
        $this->assertSame('110.00', $data['summary']['confirmed']);
        $this->assertSame('60.00', $data['summary']['posted']);
        $this->assertSame('40.00', $data['summary']['outstanding']);
        $this->assertCount(2, $data['students']);
        $this->assertFalse($data['payments']->firstWhere('receipt', 'RCP-UNPOSTED')['posted']);
    }

    public function test_running_balance_is_full_ledger_balance_before_filtering_and_pagination(): void
    {
        for ($id = 1; $id <= 30; $id++) {
            $this->transaction($id, 'SVT-'.$id, 'credit', 'authorized_adjustment', 1, SchoolVault::class, 1, '2026-11-'.str_pad((string) min($id, 30), 2, '0', STR_PAD_LEFT).' 09:00:00');
        }
        $request = $this->request('/school-vault-transactions', ['page'=>2]);
        $data = $this->reports()->transactions($request);

        $this->assertSame(2, $data['transactions']->currentPage());
        $this->assertSame('5.00', $data['transactions']->first()['running_balance']);

        $filtered = $this->reports()->transactions($this->request('/school-vault-transactions', ['from_date'=>'2026-11-20']), false);
        $this->assertSame('30.00', $filtered['transactions']->first()['running_balance']);
        $this->assertSame('11.00', $filtered['totals']['credits']);
        $this->assertSame('30.00', $filtered['currentBalance']);
    }

    public function test_statement_reconciles_and_cross_period_expense_reversal_is_not_income(): void
    {
        $this->seedStudentsAndPayments();
        DB::table('expenses')->insert(['id'=>1,'expense_category_id'=>1,'expense_date'=>'2026-11-10','amount'=>20,'payee'=>'ECG','payment_method'=>'Cash','status'=>'Cancelled']);
        $this->transaction(2,'SVT-EXP','debit','school_operational_expense',20,Expense::class,1,'2026-11-10 10:00:00');
        $this->transaction(3,'SVT-REV','credit','reversal',20,SchoolVaultTransaction::class,2,'2026-12-02 10:00:00',2);

        $november = $this->reports()->statement($this->request('/school-vault-statement',['period'=>'custom','from_date'=>'2026-11-01','to_date'=>'2026-11-30','academic_year_id'=>1,'academic_term_id'=>1]));
        $this->assertSame('60.00', $november['summary']['collections']);
        $this->assertSame('20.00', $november['summary']['expenses']);
        $this->assertSame('40.00', $november['summary']['closing']);
        $this->assertSame('40.00', $november['summary']['net_movement']);

        $december = $this->reports()->statement($this->request('/school-vault-statement',['period'=>'custom','from_date'=>'2026-12-01','to_date'=>'2026-12-31','academic_year_id'=>1,'academic_term_id'=>1]));
        $this->assertSame('-20.00', $december['summary']['expenses']);
        $this->assertSame('0.00', $december['summary']['collections']);
        $this->assertSame('60.00', $december['summary']['closing']);
    }

    public function test_daily_cash_movements_exclude_bank_and_reconciliation_record_does_not_change_ledger(): void
    {
        $this->seedStudentsAndPayments();
        DB::table('expenses')->insert([
            ['id'=>1,'expense_category_id'=>1,'expense_date'=>'2026-11-26','amount'=>10,'payee'=>'Cash vendor','payment_method'=>'Cash','status'=>'Active'],
            ['id'=>2,'expense_category_id'=>1,'expense_date'=>'2026-11-26','amount'=>5,'payee'=>'Bank vendor','payment_method'=>'Bank Transfer','status'=>'Active'],
        ]);
        $this->transaction(2,'SVT-CASH-EXP','debit','school_operational_expense',10,Expense::class,1,'2026-11-26 10:00:00');
        $this->transaction(3,'SVT-BANK-EXP','debit','school_operational_expense',5,Expense::class,2,'2026-11-26 11:00:00');
        $before = app(SchoolVaultService::class)->availableBalance();
        $daily = $this->reports()->dailyMovements(Carbon::parse('2026-11-26'));

        $this->assertSame('60.00', $daily['cash_collections']);
        $this->assertSame('10.00', $daily['cash_expenditure']);
        $this->assertSame('50.00', $daily['cash_net_movement']);

        $record = VaultCashReconciliation::create(['school_vault_id'=>1,'reconciliation_date'=>'2026-11-26','cash_collections'=>'60.00','cash_expenditure'=>'10.00','actual_cash_counted'=>'48.00','recorded_by'=>1,'reconciled_at'=>now()]);
        $this->assertSame($before, app(SchoolVaultService::class)->availableBalance());
        $this->expectException(LogicException::class);
        $record->update(['actual_cash_counted'=>'50.00']);
    }

    private function seedStudentsAndPayments(): void
    {
        DB::table('students')->insert([
            ['id'=>1,'student_id'=>'ST001','firstname'=>'Ama','surname'=>'Mensah','school_class_id'=>1,'class_name'=>'Class 1','status'=>'Active'],
            ['id'=>2,'student_id'=>'ST002','firstname'=>'Kojo','surname'=>'Owusu','school_class_id'=>1,'class_name'=>'Class 1','status'=>'Active'],
            ['id'=>3,'student_id'=>'ST003','firstname'=>'School','surname'=>'Fees','school_class_id'=>1,'class_name'=>'Class 1','status'=>'Active'],
        ]);
        DB::table('student_bills')->insert([
            ['id'=>1,'student_id'=>1,'financial_category'=>'feeding_fee','academic_year_id'=>1,'academic_term_id'=>1,'amount_due'=>100,'amount_paid'=>60,'balance'=>40,'status'=>'Partial'],
            ['id'=>2,'student_id'=>2,'financial_category'=>'feeding_fee','academic_year_id'=>1,'academic_term_id'=>1,'amount_due'=>50,'amount_paid'=>50,'balance'=>0,'status'=>'Paid'],
            ['id'=>3,'student_id'=>3,'financial_category'=>'school_fees','academic_year_id'=>1,'academic_term_id'=>1,'amount_due'=>500,'amount_paid'=>500,'balance'=>0,'status'=>'Paid'],
        ]);
        DB::table('bill_payments')->insert([
            ['id'=>1,'student_id'=>1,'financial_category'=>'feeding_fee','receipt_no'=>'RCP-POSTED','amount'=>60,'payment_method'=>'Cash','status'=>BillPayment::STATUS_ACTIVE,'paid_at'=>'2026-11-26 08:00:00'],
            ['id'=>2,'student_id'=>2,'financial_category'=>'feeding_fee','receipt_no'=>'RCP-UNPOSTED','amount'=>50,'payment_method'=>'Bank','status'=>BillPayment::STATUS_ACTIVE,'paid_at'=>'2026-11-26 08:30:00'],
            ['id'=>3,'student_id'=>3,'financial_category'=>'school_fees','receipt_no'=>'RCP-SCHOOL','amount'=>500,'payment_method'=>'Cash','status'=>BillPayment::STATUS_ACTIVE,'paid_at'=>'2026-11-26 09:00:00'],
        ]);
        DB::table('bill_payment_allocations')->insert([
            ['bill_payment_id'=>1,'student_bill_id'=>1,'amount'=>60],['bill_payment_id'=>2,'student_bill_id'=>2,'amount'=>50],['bill_payment_id'=>3,'student_bill_id'=>3,'amount'=>500],
        ]);
        $this->transaction(1,'SVT-FEED','credit','feeding_fee_collection',60,BillPayment::class,1,'2026-11-26 08:00:00');
    }

    private function transaction(int $id,string $number,string $direction,string $type,float $amount,string $sourceType,int $sourceId,string $date,?int $reversalOf=null): void
    {
        DB::table('school_vault_transactions')->insert(['id'=>$id,'school_vault_id'=>1,'transaction_number'=>$number,'direction'=>$direction,'transaction_type'=>$type,'amount'=>$amount,'source_type'=>$sourceType,'source_id'=>$sourceId,'description'=>$number,'transaction_date'=>$date,'reversal_of_id'=>$reversalOf]);
    }

    private function request(string $path,array $query=[]): Request
    {
        return Request::create($path,'GET',$query);
    }

    private function reports(): SchoolVaultReportingService
    {
        return app(SchoolVaultReportingService::class);
    }

    private function schema(): void
    {
        Schema::create('users',fn(Blueprint $t)=>[$t->id(),$t->string('name')]);
        Schema::create('academic_years',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('status'),$t->timestamps()]);
        Schema::create('academic_terms',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->unsignedInteger('sort_order'),$t->string('status'),$t->timestamps()]);
        Schema::create('class_categories',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('status'),$t->timestamps()]);
        Schema::create('school_classes',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->unsignedBigInteger('class_category_id'),$t->string('status'),$t->timestamps()]);
        Schema::create('students',fn(Blueprint $t)=>[$t->id(),$t->string('student_id'),$t->string('firstname'),$t->string('othername')->nullable(),$t->string('surname'),$t->unsignedBigInteger('school_class_id')->nullable(),$t->string('class_name')->nullable(),$t->string('status'),$t->decimal('credit_balance',12,2)->default(0),$t->timestamps()]);
        Schema::create('expense_categories',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('status'),$t->timestamps()]);
        Schema::create('student_bills',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('student_id'),$t->string('financial_category'),$t->unsignedBigInteger('academic_year_id')->nullable(),$t->unsignedBigInteger('academic_term_id')->nullable(),$t->decimal('amount_due',12,2),$t->decimal('amount_paid',12,2),$t->decimal('balance',12,2),$t->string('status'),$t->timestamps()]);
        Schema::create('bill_payments',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('student_id'),$t->string('financial_category')->nullable(),$t->string('receipt_no'),$t->decimal('amount',12,2),$t->decimal('credit_applied',12,2)->default(0),$t->decimal('credit_generated',12,2)->default(0),$t->string('payment_method'),$t->string('reference')->nullable(),$t->string('payment_channel')->nullable(),$t->string('gateway_transaction_id')->nullable(),$t->dateTime('paid_at'),$t->text('notes')->nullable(),$t->string('status')->default(BillPayment::STATUS_ACTIVE),$t->unsignedBigInteger('created_by')->nullable(),$t->unsignedBigInteger('updated_by')->nullable(),$t->timestamps()]);
        Schema::create('bill_payment_allocations',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('bill_payment_id'),$t->unsignedBigInteger('student_bill_id'),$t->decimal('amount',12,2),$t->timestamps()]);
        Schema::create('expenses',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('expense_category_id'),$t->date('expense_date'),$t->decimal('amount',12,2),$t->string('payee'),$t->string('payment_method'),$t->string('reference')->nullable(),$t->text('notes')->nullable(),$t->unsignedBigInteger('academic_year_id')->nullable(),$t->string('status'),$t->timestamp('cancelled_at')->nullable(),$t->unsignedBigInteger('cancelled_by')->nullable(),$t->text('cancellation_reason')->nullable(),$t->unsignedBigInteger('created_by')->nullable(),$t->unsignedBigInteger('updated_by')->nullable(),$t->timestamps()]);
        Schema::create('expense_vault_postings',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('expense_id'),$t->unsignedInteger('version'),$t->decimal('amount',12,2),$t->date('expense_date'),$t->unsignedBigInteger('expense_category_id'),$t->string('payee'),$t->string('payment_method'),$t->string('reference')->nullable(),$t->unsignedBigInteger('academic_year_id')->nullable(),$t->string('status'),$t->uuid('request_token'),$t->text('change_reason')->nullable(),$t->timestamp('ended_at')->nullable(),$t->unsignedBigInteger('ended_by')->nullable(),$t->unsignedBigInteger('created_by')->nullable(),$t->timestamp('created_at')->nullable()]);
        Schema::create('school_vaults',fn(Blueprint $t)=>[$t->id(),$t->string('account_code'),$t->string('name'),$t->char('currency',3),$t->string('status'),$t->timestamps()]);
        Schema::create('school_vault_transactions',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('school_vault_id'),$t->string('transaction_number'),$t->string('direction'),$t->string('transaction_type'),$t->decimal('amount',12,2),$t->string('source_type'),$t->unsignedBigInteger('source_id'),$t->string('description'),$t->dateTime('transaction_date'),$t->unsignedBigInteger('recorded_by')->nullable(),$t->unsignedBigInteger('reversal_of_id')->nullable(),$t->timestamp('created_at')->nullable()]);
        Schema::create('vault_cash_reconciliations',fn(Blueprint $t)=>[$t->id(),$t->unsignedBigInteger('school_vault_id'),$t->date('reconciliation_date'),$t->decimal('cash_collections',14,2),$t->decimal('cash_expenditure',14,2),$t->decimal('actual_cash_counted',14,2),$t->text('notes')->nullable(),$t->unsignedBigInteger('recorded_by')->nullable(),$t->timestamp('reconciled_at'),$t->timestamps()]);
    }
}
