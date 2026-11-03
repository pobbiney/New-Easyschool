<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingsParent = DB::table('user_links')
            ->where('link_name', 'Settings')
            ->where('link_parent', 0)
            ->first();

        if (! $settingsParent) {
            return;
        }

        if (DB::table('user_links')->where('link_url', 'assessment-types')->exists()) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'assessment-types',
            'link_name' => 'Assessment Types',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $settingsParent->link_id,
            'page_id' => 'settings',
            'page_id_sub' => 'assessment-types',
            'status' => 'Active',
        ]);

        $categoryIds = DB::table('user_cat_links')
            ->where('link_id', $settingsParent->link_id)
            ->pluck('cat_id');

        foreach ($categoryIds as $catId) {
            DB::table('user_cat_links')->insert([
                'cat_id' => $catId,
                'link_id' => $linkId,
            ]);
        }

        $userIds = DB::table('user_access_links')
            ->where('link_id', $settingsParent->link_id)
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
        $link = DB::table('user_links')->where('link_url', 'assessment-types')->first();

        if ($link) {
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
