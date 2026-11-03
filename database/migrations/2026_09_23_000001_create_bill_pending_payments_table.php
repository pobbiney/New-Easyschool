<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bill_pending_payments')) {
            return;
        }

        Schema::create('bill_pending_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('credit_applied', 12, 2)->default(0);
            $table->json('allocations');
            $table->string('payment_method', 40)->default('Bank');
            $table->string('bank_instrument', 40);
            $table->string('reference', 100);
            $table->string('receipt_path')->nullable();
            $table->dateTime('paid_at');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('rejected_reason', 500)->nullable();
            $table->foreignId('bill_payment_id')->nullable()->constrained('bill_payments')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_pending_payments');
    }
};
