<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = DB::select("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND ENGINE = 'MyISAM'
        ");

        foreach ($tables as $table) {
            $name = $table->TABLE_NAME ?? $table->table_name ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            DB::statement('ALTER TABLE `'.str_replace('`', '``', $name).'` ENGINE=InnoDB');
        }
    }

    public function down(): void
    {
        // Conversion is one-way: MyISAM cannot participate in GTID-safe mixed transactions.
    }
};
