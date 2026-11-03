<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->string('payment_channel')->nullable()->after('reference');
            $table->string('gateway_transaction_id')->nullable()->after('payment_channel');

            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropIndex(['gateway_transaction_id']);
            $table->dropColumn(['payment_channel', 'gateway_transaction_id']);
        });
    }
};
