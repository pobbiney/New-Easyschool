<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $database = DB::getDatabaseName();

        $columns = DB::select(
            'SELECT TABLE_NAME, COLUMN_TYPE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND COLUMN_NAME = ?
               AND COLUMN_KEY = ?
               AND DATA_TYPE IN (?, ?)
               AND EXTRA NOT LIKE ?',
            [$database, 'id', 'PRI', 'bigint', 'int', '%auto_increment%']
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($columns as $column) {
                $table = $column->TABLE_NAME;

                if (! Schema::hasTable($table)) {
                    continue;
                }

                $quoted = str_replace('`', '``', $table);
                $type = $column->COLUMN_TYPE;

                DB::statement("ALTER TABLE `{$quoted}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // AUTO_INCREMENT is required for inserts; do not reverse.
    }
};
