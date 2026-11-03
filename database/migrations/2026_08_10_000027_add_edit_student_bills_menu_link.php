<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parent = DB::table('user_links')
            ->where('link_name', 'Bill Management')
            ->where('link_parent', 0)
            ->first();

        if (! $parent) {
            return;
        }

        if (DB::table('user_links')->where('link_url', 'edit-student-bills')->exists()) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'edit-student-bills',
            'link_name' => 'Edit Student Bills',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $parent->link_id,
            'page_id' => 'bill-management',
            'page_id_sub' => 'edit-student-bills',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $linkId,
        ]);
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'edit-student-bills')->first();

        if (! $link) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
