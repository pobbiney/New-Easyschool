<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('GHS');
            $table->string('status')->default('pending');
            $table->json('allocations');
            $table->string('paystack_transaction_id')->nullable();
            $table->string('paystack_channel')->nullable();
            $table->json('gateway_response')->nullable();
            $table->foreignId('bill_payment_id')->nullable()->constrained('bill_payments')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('status');
            $table->index('paystack_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payment_transactions');
    }
};
