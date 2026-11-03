<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->decimal('credit_applied', 12, 2)->default(0)->after('amount');
            $table->decimal('credit_generated', 12, 2)->default(0)->after('credit_applied');
        });
    }

    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropColumn(['credit_applied', 'credit_generated']);
        });
    }
};
