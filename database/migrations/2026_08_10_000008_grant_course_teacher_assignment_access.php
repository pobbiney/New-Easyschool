<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $courseTeacherLink = DB::table('user_links')->where('link_url', 'course-teacher-assignment')->first();
        $addCourseLink = DB::table('user_links')->where('link_url', 'add-course')->first();

        if (! $courseTeacherLink) {
            return;
        }

        $categoryIds = DB::table('user_cat_links')
            ->when($addCourseLink, function ($query) use ($addCourseLink) {
                $query->where('link_id', $addCourseLink->link_id);
            })
            ->pluck('cat_id')
            ->unique();

        foreach ($categoryIds as $categoryId) {
            $exists = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->where('link_id', $courseTeacherLink->link_id)
                ->exists();

            if (! $exists) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $courseTeacherLink->link_id,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->where(function ($query) use ($courseTeacherLink, $addCourseLink) {
                $query->where('link_id', $courseTeacherLink->link_parent);

                if ($addCourseLink) {
                    $query->orWhere('link_id', $addCourseLink->link_id);
                }
            })
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            $exists = DB::table('user_access_links')
                ->where('user_id', $userId)
                ->where('link_id', $courseTeacherLink->link_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $courseTeacherLink->link_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $courseTeacherLink = DB::table('user_links')->where('link_url', 'course-teacher-assignment')->first();

        if (! $courseTeacherLink) {
            return;
        }

        DB::table('user_access_links')->where('link_id', $courseTeacherLink->link_id)->delete();
    }
};
