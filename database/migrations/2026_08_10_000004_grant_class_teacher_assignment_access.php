<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classTeacherLink = DB::table('user_links')->where('link_url', 'class-teacher-assignment')->first();
        $schoolClassLink = DB::table('user_links')->where('link_url', 'school-classes')->first();

        if (! $classTeacherLink) {
            return;
        }

        $categoryIds = DB::table('user_cat_links')
            ->when($schoolClassLink, function ($query) use ($schoolClassLink) {
                $query->where('link_id', $schoolClassLink->link_id);
            })
            ->pluck('cat_id')
            ->unique();

        foreach ($categoryIds as $categoryId) {
            $exists = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->where('link_id', $classTeacherLink->link_id)
                ->exists();

            if (! $exists) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $classTeacherLink->link_id,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->where(function ($query) use ($classTeacherLink, $schoolClassLink) {
                $query->where('link_id', $classTeacherLink->link_parent);

                if ($schoolClassLink) {
                    $query->orWhere('link_id', $schoolClassLink->link_id);
                }
            })
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            $exists = DB::table('user_access_links')
                ->where('user_id', $userId)
                ->where('link_id', $classTeacherLink->link_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $classTeacherLink->link_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $classTeacherLink = DB::table('user_links')->where('link_url', 'class-teacher-assignment')->first();

        if (! $classTeacherLink) {
            return;
        }

        DB::table('user_access_links')->where('link_id', $classTeacherLink->link_id)->delete();
    }
};
