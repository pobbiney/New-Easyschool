<?php

namespace Tests\Feature;

use App\Http\Controllers\Billing\CategoryBillSetupController;
use App\Models\BillingItem;
use App\Models\CategoryBillSetup;
use App\Models\CategoryBillSetupItem;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\Billing\StudentBillSyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TermlyFeedingFeeSetupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->createSchema();
        $this->seedReferenceData();
    }

    public function test_load_separates_financial_categories_and_calculates_subtotals(): void
    {
        [$schoolFees, $feedingFee] = $this->billingItems();
        $setup = $this->makeSetup(1, 1);
        $this->setupItem($setup, $schoolFees, 500);
        $this->setupItem($setup, $feedingFee, 100);

        $response = app(CategoryBillSetupController::class)->load($this->request([
            'class_category_id' => 1,
            'academic_year_id' => 1,
            'academic_term_id' => 1,
        ]));
        $data = json_decode($response->getContent(), true);

        $this->assertSame(500, $data['subtotals'][BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES]);
        $this->assertSame(100, $data['subtotals'][BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE]);
        $this->assertSame(600, $data['combined_total']);
        $this->assertFalse(collect($data['items'])->firstWhere('id', $feedingFee->id)['is_compulsory']);
    }

    public function test_termly_feeding_bills_are_unique_and_preserve_paid_history(): void
    {
        [$schoolFees, $feedingFee] = $this->billingItems();
        $termOneSetup = $this->makeSetup(1, 1);
        $schoolSetupItem = $this->setupItem($termOneSetup, $schoolFees, 500);
        $feedingSetupItem = $this->setupItem($termOneSetup, $feedingFee, 100);
        $student = Student::create([
            'student_id' => 'TEST-001',
            'firstname' => 'Test',
            'surname' => 'Student',
            'status' => 'Active',
            'school_class_id' => 1,
            'academic_year_id' => 1,
            'academic_term_id' => 1,
        ]);

        $service = app(StudentBillSyncService::class);
        $firstSync = $service->syncForSetup($termOneSetup);
        $secondSync = $service->syncForSetup($termOneSetup);

        $this->assertSame(2, $firstSync['bills_created']);
        $this->assertSame(0, $secondSync['bills_created']);
        $this->assertSame(2, $secondSync['bills_preserved']);
        $this->assertSame(2, StudentBill::count());

        $feedingBill = StudentBill::query()
            ->where('student_id', $student->id)
            ->where('billing_item_id', $feedingFee->id)
            ->where('academic_term_id', 1)
            ->firstOrFail();

        $this->assertSame(BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, $feedingBill->financial_category);
        $feedingBill->amount_paid = 40;
        $feedingBill->refreshTotals();
        $feedingBill->save();
        DB::table('bill_payment_allocations')->insert([
            'bill_payment_id' => 1,
            'student_bill_id' => $feedingBill->id,
            'amount' => 40,
        ]);

        $feedingSetupItem->update(['amount' => 150]);
        $schoolSetupItem->update(['amount' => 550]);
        $adjustedSync = $service->syncForSetup($termOneSetup->fresh());
        $feedingBill->refresh();

        $this->assertSame(2, $adjustedSync['bills_preserved']);
        $this->assertSame('100.00', $feedingBill->amount_due);
        $this->assertSame('40.00', $feedingBill->amount_paid);
        $this->assertSame('60.00', $feedingBill->balance);
        $this->assertDatabaseHas('bill_payment_allocations', [
            'student_bill_id' => $feedingBill->id,
            'amount' => 40,
        ]);

        $termTwoSetup = $this->makeSetup(1, 2);
        $this->setupItem($termTwoSetup, $schoolFees, 525);
        $this->setupItem($termTwoSetup, $feedingFee, 175);
        $student->update(['academic_term_id' => 2]);
        $termTwoSync = $service->syncForSetup($termTwoSetup);

        $this->assertSame(2, $termTwoSync['bills_created']);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'billing_item_id' => $feedingFee->id,
            'academic_year_id' => 1,
            'academic_term_id' => 1,
            'amount_due' => 100,
            'amount_paid' => 40,
        ]);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'billing_item_id' => $feedingFee->id,
            'academic_year_id' => 1,
            'academic_term_id' => 2,
            'amount_due' => 175,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
        ]);
    }

    private function billingItems(): array
    {
        $schoolFees = BillingItem::create([
            'name' => 'Tuition',
            'status' => 'Active',
            'is_compulsory' => true,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
        ]);
        $feedingFee = BillingItem::create([
            'name' => 'Feeding Fee',
            'status' => 'Active',
            'is_compulsory' => false,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
        ]);

        return [$schoolFees, $feedingFee];
    }

    private function makeSetup(int $yearId, int $termId): CategoryBillSetup
    {
        return CategoryBillSetup::create([
            'class_category_id' => 1,
            'academic_year_id' => $yearId,
            'academic_term_id' => $termId,
            'status' => 'Active',
        ]);
    }

    private function setupItem(CategoryBillSetup $setup, BillingItem $item, float $amount): CategoryBillSetupItem
    {
        return CategoryBillSetupItem::create([
            'category_bill_setup_id' => $setup->id,
            'billing_item_id' => $item->id,
            'amount' => $amount,
        ]);
    }

    private function request(array $query): Request
    {
        return Request::create('/category-bill-setup-load', 'GET', $query, [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }

    private function seedReferenceData(): void
    {
        DB::table('class_categories')->insert(['id' => 1, 'name' => 'Primary', 'status' => 'Active']);
        DB::table('school_classes')->insert(['id' => 1, 'name' => 'Primary 1', 'class_category_id' => 1, 'status' => 'Active']);
        DB::table('academic_years')->insert(['id' => 1, 'name' => '2026/2027', 'status' => 'Active']);
        DB::table('academic_terms')->insert([
            ['id' => 1, 'name' => 'First', 'sort_order' => 1, 'status' => 'Active'],
            ['id' => 2, 'name' => 'Second', 'sort_order' => 2, 'status' => 'Active'],
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('Active');
            $table->boolean('is_compulsory')->default(true);
            $table->string('financial_category', 30)->default(BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('class_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status');
        });
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('class_category_id');
            $table->string('status');
        });
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status');
        });
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order');
            $table->string('status');
        });
        Schema::create('category_bill_setups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_category_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('academic_term_id');
            $table->string('status');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['class_category_id', 'academic_year_id', 'academic_term_id']);
        });
        Schema::create('category_bill_setup_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_bill_setup_id');
            $table->unsignedBigInteger('billing_item_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['category_bill_setup_id', 'billing_item_id']);
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('firstname');
            $table->string('surname');
            $table->string('status');
            $table->unsignedBigInteger('school_class_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('academic_term_id');
            $table->timestamps();
        });
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('category_bill_setup_id');
            $table->unsignedBigInteger('billing_item_id');
            $table->string('financial_category', 30);
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('academic_term_id');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->string('status');
            $table->timestamps();
            $table->unique(['student_id', 'billing_item_id', 'academic_year_id', 'academic_term_id']);
        });
        Schema::create('bill_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_payment_id');
            $table->unsignedBigInteger('student_bill_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }
}
