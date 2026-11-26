<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SCREENS = [
        ['school-vault-collections', 'collections', 'Feeding Fee Collections', 'ri-hand-coin-line'],
        ['school-vault-transactions', 'transactions', 'Transaction History', 'ri-file-list-3-line'],
        ['school-vault-reconciliation', 'reconciliation', 'Daily Reconciliation', 'ri-scales-3-line'],
        ['school-vault-statement', 'statement', 'Vault Statement', 'ri-file-chart-line'],
    ];

    public function up(): void
    {
        $parent = DB::table('user_links')->where('page_id', 'school-vault')->where('link_parent', 0)->first();
        $dashboard = DB::table('user_links')->where('link_url', 'school-vault-dashboard')->first();

        if (! $parent || ! $dashboard) {
            return;
        }

        foreach (self::SCREENS as [$url, $sub, $name, $icon]) {
            $linkId = DB::table('user_links')->where('link_url', $url)->value('link_id');

            if (! $linkId) {
                $linkId = ((int) DB::table('user_links')->max('link_id')) + 1;
                DB::table('user_links')->insert([
                    'link_id' => $linkId,
                    'page_id' => 'school-vault',
                    'page_id_sub' => $sub,
                    'link_url' => $url,
                    'link_name' => $name,
                    'link_image' => $icon,
                    'link_target' => null,
                    'link_parent' => $parent->link_id,
                    'status' => 'Active',
                ]);
            }

            foreach (DB::table('user_cat_links')->where('link_id', $dashboard->link_id)->pluck('cat_id') as $catId) {
                DB::table('user_cat_links')->insertOrIgnore(['cat_id' => $catId, 'link_id' => $linkId]);
            }

            foreach (DB::table('user_access_links')->where('link_id', $dashboard->link_id)->pluck('user_id') as $userId) {
                DB::table('user_access_links')->updateOrInsert(
                    ['user_id' => $userId, 'link_id' => $linkId],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('user_links')->whereIn('link_url', array_column(self::SCREENS, 0))->pluck('link_id');
        DB::table('user_access_links')->whereIn('link_id', $ids)->delete();
        DB::table('user_cat_links')->whereIn('link_id', $ids)->delete();
        DB::table('user_links')->whereIn('link_id', $ids)->delete();
    }
};
