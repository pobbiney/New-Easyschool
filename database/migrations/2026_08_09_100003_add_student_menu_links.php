<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_links')
            ->where('link_id', 4)
            ->update([
                'link_url' => 'add-student',
                'link_name' => 'Register New Student',
                'page_id_sub' => 'add-student',
            ]);

        $listStudentLinkId = DB::table('user_links')->insertGetId([
            'link_url' => 'list-students',
            'link_name' => 'Student List',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => 3,
            'page_id' => 'student',
            'page_id_sub' => 'list-students',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $listStudentLinkId,
        ]);
    }

    public function down(): void
    {
        $listLink = DB::table('user_links')->where('link_url', 'list-students')->first();

        if ($listLink) {
            DB::table('user_cat_links')->where('link_id', $listLink->link_id)->delete();
            DB::table('user_links')->where('link_id', $listLink->link_id)->delete();
        }

        DB::table('user_links')
            ->where('link_id', 4)
            ->update([
                'link_url' => 'student',
                'page_id_sub' => 'register-student',
            ]);
    }
};
