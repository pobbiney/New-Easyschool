<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classSetupParent = DB::table('user_links')
            ->where('link_name', 'Class Setup')
            ->where('link_parent', 0)
            ->first();

        if (! $classSetupParent) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'class-teacher-assignment',
            'link_name' => 'Class Teachers',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $classSetupParent->link_id,
            'page_id' => 'class-setup',
            'page_id_sub' => 'class-teacher-assignment',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $linkId,
        ]);
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'class-teacher-assignment')->first();

        if (! $link) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
