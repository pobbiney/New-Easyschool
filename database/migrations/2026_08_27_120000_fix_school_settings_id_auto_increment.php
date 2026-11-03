<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_settings')) {
            return;
        }

        $maxId = (int) DB::table('school_settings')->max('id');

        DB::statement('ALTER TABLE `school_settings` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `school_settings` AUTO_INCREMENT = '.max(1, $maxId + 1));
    }

    public function down(): void
    {
        if (! Schema::hasTable('school_settings')) {
            return;
        }

        DB::statement('ALTER TABLE `school_settings` MODIFY `id` BIGINT UNSIGNED NOT NULL');
    }
};
