<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $current = DB::table('user_links')->where('link_url', 'leave-application')->first();

        if ($current && (int) $current->link_parent === 0) {
            DB::table('user_links')->where('link_id', $current->link_id)->update([
                'link_url' => '#',
                'link_name' => 'Leave',
                'link_image' => 'ri-calendar-event-line',
                'link_parent' => 0,
                'page_id' => 'leave',
                'page_id_sub' => 'leave',
                'status' => 'Active',
            ]);
            $parentId = $current->link_id;
        } else {
            $parent = DB::table('user_links')
                ->where('page_id', 'leave')
                ->where('link_parent', 0)
                ->first();

            if (! $parent) {
                return;
            }

            $parentId = $parent->link_id;
        }

        $child = DB::table('user_links')
            ->where('link_url', 'leave-application')
            ->where('link_parent', $parentId)
            ->first();

        if (! $child) {
            $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

            if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
                DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
            }

            $childId = ((int) DB::table('user_links')->max('link_id')) + 1;

            DB::table('user_links')->insert([
                'link_id' => $childId,
                'link_url' => 'leave-application',
                'link_name' => 'Request leave',
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parentId,
                'page_id' => 'leave',
                'page_id_sub' => 'leave-application',
                'status' => 'Active',
            ]);
        } else {
            $childId = $child->link_id;
            DB::table('user_links')->where('link_id', $childId)->update([
                'link_name' => 'Request leave',
                'page_id' => 'leave',
                'page_id_sub' => 'leave-application',
                'status' => 'Active',
            ]);
        }

        $categoryIds = DB::table('user_cat')->pluck('cat_id');

        foreach ($categoryIds as $categoryId) {
            foreach ([$parentId, $childId] as $linkId) {
                if (! DB::table('user_cat_links')->where('cat_id', $categoryId)->where('link_id', $linkId)->exists()) {
                    DB::table('user_cat_links')->insert([
                        'cat_id' => $categoryId,
                        'link_id' => $linkId,
                    ]);
                }
            }
        }

        $userIds = DB::table('users')->pluck('id');

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
        $child = DB::table('user_links')
            ->where('link_url', 'leave-application')
            ->where('link_parent', '>', 0)
            ->first();

        if (! $child) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $child->link_id)->delete();
        DB::table('user_access_links')->where('link_id', $child->link_id)->delete();
        DB::table('user_links')->where('link_id', $child->link_id)->delete();

        DB::table('user_links')->where('link_id', $child->link_parent)->update([
            'link_url' => 'leave-application',
            'link_name' => 'Leave',
            'page_id_sub' => 'leave-application',
        ]);
    }
};
