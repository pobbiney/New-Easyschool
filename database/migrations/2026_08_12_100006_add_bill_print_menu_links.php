<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parent = DB::table('user_links')
            ->where('link_name', 'Bill Management')
            ->where('link_parent', 0)
            ->first();

        if (! $parent) {
            return;
        }

        $links = [
            [
                'link_url' => 'print-student-bill',
                'link_name' => 'Print Student Bill',
                'page_id_sub' => 'print-student-bill',
            ],
            [
                'link_url' => 'print-class-bills',
                'link_name' => 'Print Class Bills',
                'page_id_sub' => 'print-class-bills',
            ],
        ];

        foreach ($links as $link) {
            if (DB::table('user_links')->where('link_url', $link['link_url'])->exists()) {
                continue;
            }

            $linkId = DB::table('user_links')->insertGetId([
                'link_url' => $link['link_url'],
                'link_name' => $link['link_name'],
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parent->link_id,
                'page_id' => 'bill-management',
                'page_id_sub' => $link['page_id_sub'],
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
        $urls = ['print-student-bill', 'print-class-bills'];

        foreach ($urls as $url) {
            $link = DB::table('user_links')->where('link_url', $url)->first();

            if (! $link) {
                continue;
            }

            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
