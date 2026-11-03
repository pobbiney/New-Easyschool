<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $teacherPortal = DB::table('user_links')
            ->where('page_id', 'teacher-portal')
            ->where('link_parent', 0)
            ->first();

        if (! $teacherPortal) {
            return;
        }

        if (DB::table('user_links')->where('link_url', 'teacher-assessment-records')->exists()) {
            return;
        }

        $assessmentsLink = DB::table('user_links')
            ->where('link_url', 'teacher-assessments')
            ->where('link_parent', $teacherPortal->link_id)
            ->first();

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'teacher-assessment-records',
            'link_name' => 'Assessment Records',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $teacherPortal->link_id,
            'page_id' => 'teacher-portal',
            'page_id_sub' => 'teacher-assessment-records',
            'status' => 'Active',
        ]);

        $categoryIds = DB::table('user_cat_links')
            ->where('link_id', $teacherPortal->link_id)
            ->pluck('cat_id');

        foreach ($categoryIds as $catId) {
            DB::table('user_cat_links')->insert([
                'cat_id' => $catId,
                'link_id' => $linkId,
            ]);
        }

        $sourceLinkId = $assessmentsLink?->link_id ?? $teacherPortal->link_id;

        $userIds = DB::table('user_access_links')
            ->where('link_id', $sourceLinkId)
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            if (! DB::table('user_access_links')->where('user_id', $userId)->where('link_id', $linkId)->exists()) {
                DB::table('user_access_links')->insert([
                    'user_id' => $userId,
                    'link_id' => $linkId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'teacher-assessment-records')->first();

        if ($link) {
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
