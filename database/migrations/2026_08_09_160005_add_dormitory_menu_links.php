<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dormitoryParentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Dormitory',
            'link_target' => null,
            'link_image' => 'ri-hotel-bed-line',
            'link_parent' => 0,
            'page_id' => 'dormitory',
            'page_id_sub' => 'dormitory',
            'status' => 'Active',
        ]);

        $setupLinkId = DB::table('user_links')->insertGetId([
            'link_url' => 'dormitory-setup',
            'link_name' => 'Dormitory Setup',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $dormitoryParentId,
            'page_id' => 'dormitory',
            'page_id_sub' => 'dormitory-setup',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            ['cat_id' => 1, 'link_id' => $dormitoryParentId],
            ['cat_id' => 1, 'link_id' => $setupLinkId],
        ]);
    }

    public function down(): void
    {
        $setupLink = DB::table('user_links')->where('link_url', 'dormitory-setup')->first();
        $parent = DB::table('user_links')->where('link_name', 'Dormitory')->where('link_parent', 0)->first();

        if ($setupLink) {
            DB::table('user_cat_links')->where('link_id', $setupLink->link_id)->delete();
            DB::table('user_links')->where('link_id', $setupLink->link_id)->delete();
        }

        if ($parent) {
            DB::table('user_cat_links')->where('link_id', $parent->link_id)->delete();
            DB::table('user_links')->where('link_id', $parent->link_id)->delete();
        }
    }
};
