<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $create = DB::select('SHOW CREATE TABLE migrations')[0]->{'Create Table'} ?? '';

        if ($create !== '' && ! str_contains($create, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE migrations MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

        if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        // Intentionally left blank — reverting would break future migration inserts.
    }
};
