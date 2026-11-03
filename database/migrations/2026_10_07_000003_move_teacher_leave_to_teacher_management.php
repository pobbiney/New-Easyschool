<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parent = DB::table('user_links')
            ->where('page_id', 'teacher-management')
            ->where('link_parent', 0)
            ->first();

        if (! $parent) {
            return;
        }

        $link = DB::table('user_links')->where('link_url', 'teacher-leave')->first();

        if (! $link) {
            $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

            if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
                DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
            }

            $linkId = DB::table('user_links')->insertGetId([
                'link_url' => 'teacher-leave',
                'link_name' => 'Leave',
                'link_target' => null,
                'link_image' => 'ri-calendar-event-line',
                'link_parent' => $parent->link_id,
                'page_id' => 'teacher-management',
                'page_id_sub' => 'teacher-leave',
                'status' => 'Active',
            ]);
        } else {
            $linkId = $link->link_id;
            DB::table('user_links')->where('link_id', $linkId)->update([
                'link_name' => 'Leave',
                'link_image' => 'ri-calendar-event-line',
                'link_parent' => $parent->link_id,
                'page_id' => 'teacher-management',
                'page_id_sub' => 'teacher-leave',
                'status' => 'Active',
            ]);
        }

        $managementLinkIds = DB::table('user_links')
            ->where('page_id', 'teacher-management')
            ->pluck('link_id');

        $categoryIds = DB::table('user_cat_links')
            ->whereIn('link_id', $managementLinkIds)
            ->pluck('cat_id')
            ->unique();

        foreach ($categoryIds as $catId) {
            if (! DB::table('user_cat_links')->where('cat_id', $catId)->where('link_id', $linkId)->exists()) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $catId,
                    'link_id' => $linkId,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->whereIn('link_id', $managementLinkIds)
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            if (DB::table('user_access_links')->where('user_id', $userId)->where('link_id', $linkId)->exists()) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $linkId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $teacherPortal = DB::table('user_links')
            ->where('page_id', 'teacher-portal')
            ->where('link_parent', 0)
            ->first();

        $link = DB::table('user_links')->where('link_url', 'teacher-leave')->first();

        if ($link && $teacherPortal) {
            DB::table('user_links')->where('link_id', $link->link_id)->update([
                'link_parent' => $teacherPortal->link_id,
                'page_id' => 'teacher-portal',
            ]);
        }
    }
};
