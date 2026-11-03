<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Teacher Portal',
            'link_target' => null,
            'link_image' => 'ri-book-open-line',
            'link_parent' => 0,
            'page_id' => 'teacher-portal',
            'page_id_sub' => 'teacher-portal',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 2,
            'link_id' => $parentId,
        ]);

        $children = [
            ['link_url' => 'teacher-dashboard', 'link_name' => 'Dashboard', 'page_id_sub' => 'teacher-dashboard'],
            ['link_url' => 'teacher-attendance', 'link_name' => 'Attendance', 'page_id_sub' => 'teacher-attendance'],
            ['link_url' => 'teacher-assessments', 'link_name' => 'Assessments', 'page_id_sub' => 'teacher-assessments'],
            ['link_url' => 'teacher-gradebook', 'link_name' => 'Gradebook', 'page_id_sub' => 'teacher-gradebook'],
        ];

        foreach ($children as $child) {
            $linkId = DB::table('user_links')->insertGetId([
                'link_url' => $child['link_url'],
                'link_name' => $child['link_name'],
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parentId,
                'page_id' => 'teacher-portal',
                'page_id_sub' => $child['page_id_sub'],
                'status' => 'Active',
            ]);

            DB::table('user_cat_links')->insert([
                'cat_id' => 2,
                'link_id' => $linkId,
            ]);
        }
    }

    public function down(): void
    {
        $links = DB::table('user_links')->where('page_id', 'teacher-portal')->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
