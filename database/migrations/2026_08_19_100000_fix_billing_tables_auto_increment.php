<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'student_bills',
        'billing_items',
        'category_bill_setups',
        'category_bill_setup_items',
        'bill_payments',
        'bill_payment_allocations',
        'bill_payment_transactions',
        'student_bill_credit_transactions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            $this->ensureAutoIncrement($table);
        }
    }

    public function down(): void
    {
        // Intentionally left blank — reverting would break inserts.
    }

    private function ensureAutoIncrement(string $table): void
    {
        try {
            $create = DB::select('SHOW CREATE TABLE '.$table)[0]->{'Create Table'} ?? '';
        } catch (\Throwable) {
            return;
        }

        if ($create === '' || str_contains($create, 'AUTO_INCREMENT')) {
            return;
        }

        if (! preg_match('/`id`\s+(bigint\(20\) unsigned|int\(10\) unsigned|bigint unsigned)/i', $create, $matches)) {
            return;
        }

        $type = stripos($matches[1], 'bigint') !== false
            ? 'BIGINT UNSIGNED'
            : 'INT UNSIGNED';

        DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
    }
};
