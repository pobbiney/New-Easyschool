<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE bill_payment_transactions MODIFY created_by BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE bill_payments MODIFY created_by BIGINT UNSIGNED NULL');

        Schema::table('bill_payment_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('bill_payment_transactions', 'parent_account_id')) {
                $table->foreignId('parent_account_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('parent_accounts')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bill_payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('bill_payment_transactions', 'parent_account_id')) {
                $table->dropConstrainedForeignId('parent_account_id');
            }
        });

        DB::statement('ALTER TABLE bill_payment_transactions MODIFY created_by BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE bill_payments MODIFY created_by BIGINT UNSIGNED NOT NULL');
    }
};
