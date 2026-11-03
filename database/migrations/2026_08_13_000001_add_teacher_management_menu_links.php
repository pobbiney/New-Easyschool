<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Teacher Management',
            'link_target' => null,
            'link_image' => 'ri-user-star-line',
            'link_parent' => 0,
            'page_id' => 'teacher-management',
            'page_id_sub' => 'teacher-management',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $parentId,
        ]);

        $children = [
            ['link_url' => 'teacher-directory', 'link_name' => 'Teacher Directory', 'page_id_sub' => 'teacher-directory'],
            ['link_url' => 'class-teacher-assignment', 'link_name' => 'Class Teachers', 'page_id_sub' => 'class-teacher-assignment'],
            ['link_url' => 'course-teacher-assignment', 'link_name' => 'Course Teachers', 'page_id_sub' => 'course-teacher-assignment'],
            ['link_url' => 'grading-scheme', 'link_name' => 'Grading Scheme', 'page_id_sub' => 'grading-scheme'],
        ];

        foreach ($children as $child) {
            if ($child['link_url'] === 'class-teacher-assignment' || $child['link_url'] === 'course-teacher-assignment') {
                DB::table('user_links')
                    ->where('link_url', $child['link_url'])
                    ->update([
                        'link_parent' => $parentId,
                        'page_id' => 'teacher-management',
                        'page_id_sub' => $child['page_id_sub'],
                    ]);

                continue;
            }

            $linkId = DB::table('user_links')->insertGetId([
                'link_url' => $child['link_url'],
                'link_name' => $child['link_name'],
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parentId,
                'page_id' => 'teacher-management',
                'page_id_sub' => $child['page_id_sub'],
                'status' => 'Active',
            ]);

            DB::table('user_cat_links')->insert([
                'cat_id' => 1,
                'link_id' => $linkId,
            ]);
        }
    }

    public function down(): void
    {
        $classSetupParent = DB::table('user_links')
            ->where('link_name', 'Class Setup')
            ->where('link_parent', 0)
            ->first();

        $courseSetupParent = DB::table('user_links')
            ->where('link_name', 'Course Setup')
            ->where('link_parent', 0)
            ->first();

        if ($classSetupParent) {
            DB::table('user_links')
                ->where('link_url', 'class-teacher-assignment')
                ->update([
                    'link_parent' => $classSetupParent->link_id,
                    'page_id' => 'class-setup',
                    'page_id_sub' => 'class-teacher-assignment',
                ]);
        }

        if ($courseSetupParent) {
            DB::table('user_links')
                ->where('link_url', 'course-teacher-assignment')
                ->update([
                    'link_parent' => $courseSetupParent->link_id,
                    'page_id' => 'course-setup',
                    'page_id_sub' => 'course-teacher-assignment',
                ]);
        }

        $newUrls = ['teacher-directory', 'grading-scheme', '#'];
        $links = DB::table('user_links')
            ->whereIn('link_url', $newUrls)
            ->where('page_id', 'teacher-management')
            ->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
