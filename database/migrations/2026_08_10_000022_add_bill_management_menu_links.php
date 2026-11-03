<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Bill Management',
            'link_target' => null,
            'link_image' => 'ri-bill-line',
            'link_parent' => 0,
            'page_id' => 'bill-management',
            'page_id_sub' => 'bill-management',
            'status' => 'Active',
        ]);

        DB::table('user_cat_links')->insert([
            'cat_id' => 1,
            'link_id' => $parentId,
        ]);

        $children = [
            ['link_url' => 'billing-items', 'link_name' => 'Billing Items', 'page_id_sub' => 'billing-items'],
            ['link_url' => 'category-bill-setup', 'link_name' => 'Category Bill Setup', 'page_id_sub' => 'category-bill-setup'],
            ['link_url' => 'student-bills', 'link_name' => 'Student Bills', 'page_id_sub' => 'student-bills'],
        ];

        foreach ($children as $child) {
            $linkId = DB::table('user_links')->insertGetId([
                'link_url' => $child['link_url'],
                'link_name' => $child['link_name'],
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parentId,
                'page_id' => 'bill-management',
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
        $urls = ['billing-items', 'category-bill-setup', 'student-bills', '#'];
        $links = DB::table('user_links')->whereIn('link_url', $urls)->where('page_id', 'bill-management')->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
