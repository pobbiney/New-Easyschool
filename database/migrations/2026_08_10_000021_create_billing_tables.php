<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('category_bill_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_category_id')->constrained('class_categories')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['class_category_id', 'academic_year_id', 'academic_term_id'], 'category_bill_setups_unique');
        });

        Schema::create('category_bill_setup_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_bill_setup_id')->constrained('category_bill_setups')->cascadeOnDelete();
            $table->foreignId('billing_item_id')->constrained('billing_items')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['category_bill_setup_id', 'billing_item_id'], 'category_bill_setup_items_unique');
        });

        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('category_bill_setup_id')->constrained('category_bill_setups')->cascadeOnDelete();
            $table->foreignId('billing_item_id')->constrained('billing_items')->cascadeOnDelete();
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->unique(['student_id', 'category_bill_setup_id', 'billing_item_id'], 'student_bills_unique');
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('receipt_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->dateTime('paid_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_payment_id')->constrained('bill_payments')->cascadeOnDelete();
            $table->foreignId('student_bill_id')->constrained('student_bills')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payment_allocations');
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('student_bills');
        Schema::dropIfExists('category_bill_setup_items');
        Schema::dropIfExists('category_bill_setups');
        Schema::dropIfExists('billing_items');
    }
};
