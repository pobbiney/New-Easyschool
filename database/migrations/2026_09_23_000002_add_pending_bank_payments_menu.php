<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('link_url', 'pending-bank-payments')->exists()) {
            return;
        }

        $parent = DB::table('user_links')
            ->where('page_id', 'bill-management')
            ->where('link_parent', 0)
            ->first();

        if (! $parent) {
            return;
        }

        $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

        if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
        }

        $childId = ((int) DB::table('user_links')->max('link_id')) + 1;

        DB::table('user_links')->insert([
            'link_id' => $childId,
            'link_url' => 'pending-bank-payments',
            'link_name' => 'Pending Bank Payments',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $parent->link_id,
            'page_id' => 'bill-management',
            'page_id_sub' => 'pending-bank-payments',
            'status' => 'Active',
        ]);

        $billsLink = DB::table('user_links')->where('link_url', 'student-bills')->first();

        $categoryIds = DB::table('user_cat_links')
            ->when($billsLink, fn ($query) => $query->where('link_id', $billsLink->link_id))
            ->pluck('cat_id')
            ->unique();

        if ($categoryIds->isEmpty()) {
            $categoryIds = collect([1]);
        }

        foreach ($categoryIds as $categoryId) {
            if (! DB::table('user_cat_links')->where('cat_id', $categoryId)->where('link_id', $childId)->exists()) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $childId,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->when($billsLink, fn ($query) => $query->where('link_id', $billsLink->link_id))
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            if (DB::table('user_access_links')->where('user_id', $userId)->where('link_id', $childId)->exists()) {
                continue;
            }
            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $childId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $links = DB::table('user_links')->where('link_url', 'pending-bank-payments')->get();
        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
