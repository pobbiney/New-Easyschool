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

        if (DB::table('user_links')->where('link_url', 'student-class-assignment')->exists()) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'student-class-assignment',
            'link_name' => 'Student Class Assignment',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $classSetupParent->link_id,
            'page_id' => 'class-setup',
            'page_id_sub' => 'student-class-assignment',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $linkId,
        ]);
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'student-class-assignment')->first();

        if (! $link) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
