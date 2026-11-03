<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingsParent = DB::table('user_links')
            ->where('link_name', 'Settings')
            ->where('link_parent', 0)
            ->first();

        if (! $settingsParent) {
            return;
        }

        DB::table('user_links')
            ->where('link_url', 'academic-years')
            ->update([
                'link_parent' => $settingsParent->link_id,
                'page_id' => 'settings',
            ]);
    }

    public function down(): void
    {
        $studentParent = DB::table('user_links')
            ->where('link_name', 'Student Management')
            ->where('link_parent', 0)
            ->first();

        if (! $studentParent) {
            return;
        }

        DB::table('user_links')
            ->where('link_url', 'academic-years')
            ->update([
                'link_parent' => $studentParent->link_id,
                'page_id' => 'student',
            ]);
    }
};
