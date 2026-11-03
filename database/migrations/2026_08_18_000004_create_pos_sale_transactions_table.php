<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos_sale_transactions')) {
            return;
        }

        Schema::create('pos_sale_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('GHS');
            $table->string('status')->default('pending');
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->json('cart_items');
            $table->string('paystack_transaction_id')->nullable();
            $table->string('paystack_channel')->nullable();
            $table->json('gateway_response')->nullable();
            $table->foreignId('pos_sale_id')->nullable()->constrained('pos_sales')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('status');
            $table->index('paystack_transaction_id');
        });

        if (! Schema::hasColumn('pos_sales', 'payment_reference')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->string('payment_reference')->nullable()->after('payment_method');
                $table->string('paystack_transaction_id')->nullable()->after('payment_reference');
                $table->string('paystack_channel')->nullable()->after('paystack_transaction_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pos_sales', 'payment_reference')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->dropColumn(['payment_reference', 'paystack_transaction_id', 'paystack_channel']);
            });
        }

        Schema::dropIfExists('pos_sale_transactions');
    }
};
