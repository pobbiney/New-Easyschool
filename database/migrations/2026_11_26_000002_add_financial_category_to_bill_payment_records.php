<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->string('financial_category', 30)->nullable()->after('student_id')->index();
        });

        Schema::table('bill_pending_payments', function (Blueprint $table) {
            $table->string('financial_category', 30)->nullable()->after('student_id')->index();
        });

        Schema::table('bill_payment_transactions', function (Blueprint $table) {
            $table->string('financial_category', 30)->nullable()->after('student_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('bill_payment_transactions', fn (Blueprint $table) => $table->dropColumn('financial_category'));
        Schema::table('bill_pending_payments', fn (Blueprint $table) => $table->dropColumn('financial_category'));
        Schema::table('bill_payments', fn (Blueprint $table) => $table->dropColumn('financial_category'));
    }
};
