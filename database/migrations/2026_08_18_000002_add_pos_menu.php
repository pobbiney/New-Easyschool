<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('page_id', 'pos')->exists()) {
            return;
        }

        $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

        if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
        }

        $nextLinkId = static function (): int {
            return ((int) DB::table('user_links')->max('link_id')) + 1;
        };

        $parentId = $nextLinkId();
        DB::table('user_links')->insert([
            'link_id' => $parentId,
            'link_url' => '#',
            'link_name' => 'POS',
            'link_target' => null,
            'link_image' => 'ri-store-2-line',
            'link_parent' => 0,
            'page_id' => 'pos',
            'page_id_sub' => 'pos',
            'status' => 'Active',
        ]);

        $children = [
            ['link_url' => 'pos-sale', 'link_name' => 'New Sale', 'page_id_sub' => 'pos-sale'],
            ['link_url' => 'pos-sales', 'link_name' => 'Sales History', 'page_id_sub' => 'pos-sales'],
            ['link_url' => 'pos-products', 'link_name' => 'Products', 'page_id_sub' => 'pos-products'],
            ['link_url' => 'pos-categories', 'link_name' => 'Categories', 'page_id_sub' => 'pos-categories'],
            ['link_url' => 'pos-stock', 'link_name' => 'Stock Adjustments', 'page_id_sub' => 'pos-stock'],
        ];

        $linkIds = [$parentId];

        foreach ($children as $child) {
            $linkId = $nextLinkId();
            DB::table('user_links')->insert([
                'link_id' => $linkId,
                'link_url' => $child['link_url'],
                'link_name' => $child['link_name'],
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parentId,
                'page_id' => 'pos',
                'page_id_sub' => $child['page_id_sub'],
                'status' => 'Active',
            ]);
            $linkIds[] = $linkId;
        }

        $staffLink = DB::table('user_links')->where('link_url', 'list-staff')->first();
        $categoryIds = DB::table('user_cat_links')
            ->when($staffLink, fn ($query) => $query->where('link_id', $staffLink->link_id))
            ->pluck('cat_id')
            ->unique();

        if ($categoryIds->isEmpty()) {
            $categoryIds = collect([1]);
        }

        foreach ($linkIds as $linkId) {
            foreach ($categoryIds as $categoryId) {
                DB::table('user_cat_links')->insertOrIgnore([
                    'cat_id' => $categoryId,
                    'link_id' => $linkId,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->when($staffLink, fn ($query) => $query->where('link_id', $staffLink->link_id))
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            foreach ($linkIds as $linkId) {
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
        $links = DB::table('user_links')->where('page_id', 'pos')->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
