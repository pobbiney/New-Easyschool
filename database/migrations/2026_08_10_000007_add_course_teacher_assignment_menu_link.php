<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $courseSetupParent = DB::table('user_links')
            ->where('link_name', 'Course Setup')
            ->where('link_parent', 0)
            ->first();

        if (! $courseSetupParent) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'course-teacher-assignment',
            'link_name' => 'Course Teachers',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $courseSetupParent->link_id,
            'page_id' => 'course',
            'page_id_sub' => 'course-teacher-assignment',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $linkId,
        ]);
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'course-teacher-assignment')->first();

        if (! $link) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
