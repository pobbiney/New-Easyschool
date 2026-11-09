<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usr_user_log')) {
            return;
        }

        $create = DB::select('SHOW CREATE TABLE usr_user_log')[0]->{'Create Table'} ?? '';

        if ($create !== '' && ! str_contains($create, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE usr_user_log MODIFY id INT NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        // Leaving AUTO_INCREMENT in place keeps login inserts working.
    }
};
