<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> table => primary key column */
    protected array $tables = [
        'user_cat' => 'cat_id',
        'users' => 'id',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $extra = DB::selectOne("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]);

            if ($extra && str_contains((string) ($extra->Extra ?? ''), 'auto_increment')) {
                continue;
            }

            $maxId = (int) DB::table($table)->max($column);
            $next = max(1, $maxId + 1);

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL");
        }
    }
};
