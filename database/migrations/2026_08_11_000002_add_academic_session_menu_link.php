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

        $existing = DB::table('user_links')->where('link_url', 'academic-session')->first();
        if ($existing) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'academic-session',
            'link_name' => 'Academic Session',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $settingsParent->link_id,
            'page_id' => 'settings',
            'page_id_sub' => 'academic-session',
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
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'academic-session')->first();

        if ($link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
