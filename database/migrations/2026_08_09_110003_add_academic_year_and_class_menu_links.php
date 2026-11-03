<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $academicYearLinkId = DB::table('user_links')->insertGetId([
            'link_url' => 'academic-years',
            'link_name' => 'Academic Years',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => 3,
            'page_id' => 'student',
            'page_id_sub' => 'academic-years',
            'status' => 'Active',
        ]);

        $classLinkId = DB::table('user_links')->insertGetId([
            'link_url' => 'school-classes',
            'link_name' => 'Classes',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => 3,
            'page_id' => 'student',
            'page_id_sub' => 'school-classes',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            ['cat_id' => 1, 'link_id' => $academicYearLinkId],
            ['cat_id' => 1, 'link_id' => $classLinkId],
        ]);
    }

    public function down(): void
    {
        $links = DB::table('user_links')->whereIn('link_url', ['academic-years', 'school-classes'])->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
