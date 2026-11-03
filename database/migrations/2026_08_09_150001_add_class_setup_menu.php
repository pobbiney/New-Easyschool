<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classSetupParentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Class Setup',
            'link_target' => null,
            'link_image' => 'ri-book-open-line',
            'link_parent' => 0,
            'page_id' => 'class-setup',
            'page_id_sub' => 'class-setup',
            'status' => 'Active',
        ]);

        DB::table('user_links')
            ->where('link_url', 'school-classes')
            ->update([
                'link_parent' => $classSetupParentId,
                'page_id' => 'class-setup',
            ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $classSetupParentId,
        ]);
    }

    public function down(): void
    {
        $classSetupParent = DB::table('user_links')
            ->where('link_name', 'Class Setup')
            ->where('link_parent', 0)
            ->first();

        if (!$classSetupParent) {
            return;
        }

        DB::table('user_links')
            ->where('link_url', 'school-classes')
            ->update([
                'link_parent' => 3,
                'page_id' => 'student',
            ]);

        DB::table('user_cat_links')->where('link_id', $classSetupParent->link_id)->delete();
        DB::table('user_links')->where('link_id', $classSetupParent->link_id)->delete();
    }
};
