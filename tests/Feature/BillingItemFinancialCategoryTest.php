<?php

namespace Tests\Feature;

use App\Http\Controllers\Billing\BillingItemController;
use App\Models\BillingItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BillingItemFinancialCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

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

        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_item_id');
        });

        Auth::shouldReceive('id')->andReturn(1);
    }

    public function test_school_fees_item_can_be_created(): void
    {
        $response = $this->controller()->store($this->request($this->payload()));

        $this->assertSame(201, $response->getStatusCode());

        $this->assertDatabaseHas('billing_items', [
            'name' => 'Tuition',
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            'is_compulsory' => true,
        ]);
    }

    public function test_optional_feeding_fee_can_be_created(): void
    {
        $response = $this->controller()->store($this->request($this->payload([
            'name' => 'Feeding Fee',
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'is_compulsory' => '0',
        ])));

        $this->assertSame(201, $response->getStatusCode());

        $this->assertDatabaseHas('billing_items', [
            'name' => 'Feeding Fee',
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'is_compulsory' => false,
        ]);
    }

    public function test_feeding_fee_cannot_be_compulsory(): void
    {
        try {
            $this->controller()->store($this->request($this->payload([
                'name' => 'Feeding Fee',
                'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
                'is_compulsory' => '1',
            ])));
            $this->fail('Expected Feeding Fee validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('is_compulsory', $exception->errors());
        }

        $this->assertDatabaseMissing('billing_items', ['name' => 'Feeding Fee']);
    }

    public function test_unsupported_financial_category_is_rejected(): void
    {
        try {
            $this->controller()->store($this->request($this->payload([
                'financial_category' => 'other_income',
            ])));
            $this->fail('Expected financial category validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('financial_category', $exception->errors());
        }

        $this->assertDatabaseCount('billing_items', 0);
    }

    public function test_financial_category_cannot_change_after_student_bills_exist(): void
    {
        $item = BillingItem::create(array_merge($this->payload(), ['created_by' => 1]));
        DB::table('student_bills')->insert(['billing_item_id' => $item->id]);

        $response = $this->controller()->update($this->request($this->payload([
            'billing_item_id' => $item->id,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
            'is_compulsory' => '0',
        ])));

        $this->assertSame(422, $response->getStatusCode());

        $this->assertDatabaseHas('billing_items', [
            'id' => $item->id,
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tuition',
            'description' => 'Core tuition fee.',
            'status' => 'Active',
            'financial_category' => BillingItem::FINANCIAL_CATEGORY_SCHOOL_FEES,
            'is_compulsory' => '1',
        ], $overrides);
    }

    private function controller(): BillingItemController
    {
        return app(BillingItemController::class);
    }

    private function request(array $payload): Request
    {
        return Request::create('/billing-items', 'POST', $payload, [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }
}
