<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schools') && ! Schema::hasColumn('schools', 'suspension_reason')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->string('suspension_reason', 32)->nullable()->after('status');
            });
        }

        if (! Schema::hasTable('school_subscription_payments')) {
            Schema::create('school_subscription_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('payer_full_name');
                $table->string('payer_phone', 30);
                $table->string('payer_email');
                $table->string('paystack_reference')->unique();
                $table->string('status', 20)->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('sms_sent_at')->nullable();
                $table->string('paystack_transaction_id')->nullable();
                $table->string('paystack_channel')->nullable();
                $table->json('gateway_response')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_subscription_payments');

        if (Schema::hasTable('schools') && Schema::hasColumn('schools', 'suspension_reason')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('suspension_reason');
            });
        }
    }
};
