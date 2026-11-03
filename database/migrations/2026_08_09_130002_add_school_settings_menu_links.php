<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingsParentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Settings',
            'link_target' => null,
            'link_image' => 'ri-settings-3-line',
            'link_parent' => 0,
            'page_id' => 'settings',
            'page_id_sub' => 'settings',
            'status' => 'Active',
        ]);

        $schoolSettingsLinkId = DB::table('user_links')->insertGetId([
            'link_url' => 'school-settings',
            'link_name' => 'School Firm Setup',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $settingsParentId,
            'page_id' => 'settings',
            'page_id_sub' => 'school-settings',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            ['cat_id' => 1, 'link_id' => $settingsParentId],
            ['cat_id' => 1, 'link_id' => $schoolSettingsLinkId],
        ]);
    }

    public function down(): void
    {
        $schoolSettingsLink = DB::table('user_links')->where('link_url', 'school-settings')->first();
        $settingsParent = DB::table('user_links')->where('link_name', 'Settings')->where('link_parent', 0)->first();

        if ($schoolSettingsLink) {
            DB::table('user_cat_links')->where('link_id', $schoolSettingsLink->link_id)->delete();
            DB::table('user_links')->where('link_id', $schoolSettingsLink->link_id)->delete();
        }

        if ($settingsParent) {
            DB::table('user_cat_links')->where('link_id', $settingsParent->link_id)->delete();
            DB::table('user_links')->where('link_id', $settingsParent->link_id)->delete();
        }
    }
};
