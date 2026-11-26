<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('link_url', 'school-vault-dashboard')->exists()) {
            return;
        }

        $nextLinkId = static fn (): int => ((int) DB::table('user_links')->max('link_id')) + 1;
        $expenseLink = DB::table('user_links')->where('link_url', 'expenses')->first();
        $categoryIds = $expenseLink
            ? DB::table('user_cat_links')->where('link_id', $expenseLink->link_id)->pluck('cat_id')->unique()
            : collect();
        $userIds = $expenseLink
            ? DB::table('user_access_links')->where('link_id', $expenseLink->link_id)->pluck('user_id')->unique()
            : collect();

        $parentId = $nextLinkId();
        DB::table('user_links')->insert([
            'link_id' => $parentId,
            'link_url' => '#',
            'link_name' => 'School Vault',
            'link_target' => null,
            'link_image' => 'ri-safe-2-line',
            'link_parent' => 0,
            'page_id' => 'school-vault',
            'page_id_sub' => 'school-vault',
            'status' => 'Active',
        ]);

        $dashboardId = $nextLinkId();
        DB::table('user_links')->insert([
            'link_id' => $dashboardId,
            'link_url' => 'school-vault-dashboard',
            'link_name' => 'Dashboard',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $parentId,
            'page_id' => 'school-vault',
            'page_id_sub' => 'school-vault-dashboard',
            'status' => 'Active',
        ]);

        foreach ($categoryIds as $categoryId) {
            foreach ([$parentId, $dashboardId] as $linkId) {
                DB::table('user_cat_links')->insertOrIgnore(['cat_id' => $categoryId, 'link_id' => $linkId]);
            }
        }

        foreach ($userIds as $userId) {
            foreach ([$parentId, $dashboardId] as $linkId) {
                DB::table('user_access_links')->insertOrIgnore([
                    'user_id' => $userId,
                    'link_id' => $linkId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $links = DB::table('user_links')->where('page_id', 'school-vault')->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
