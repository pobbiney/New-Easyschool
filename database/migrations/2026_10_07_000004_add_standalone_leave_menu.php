<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('link_url', 'leave-application')->exists()) {
            return;
        }

        $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

        if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
        }

        $linkId = ((int) DB::table('user_links')->max('link_id')) + 1;

        DB::table('user_links')->insert([
            'link_id' => $linkId,
            'link_url' => 'leave-application',
            'link_name' => 'Leave',
            'link_target' => null,
            'link_image' => 'ri-calendar-event-line',
            'link_parent' => 0,
            'page_id' => 'leave',
            'page_id_sub' => 'leave-application',
            'status' => 'Active',
        ]);

        $old = DB::table('user_links')->where('link_url', 'teacher-leave')->first();
        $sourceIds = collect([$old?->link_id])
            ->merge(
                DB::table('user_links')
                    ->whereIn('page_id', ['teacher-management', 'teacher-portal'])
                    ->pluck('link_id')
            )
            ->filter()
            ->unique()
            ->values();

        $categoryIds = DB::table('user_cat_links')
            ->whereIn('link_id', $sourceIds)
            ->pluck('cat_id')
            ->unique();

        if ($categoryIds->isEmpty()) {
            $categoryIds = collect([1]);
        }

        foreach ($categoryIds as $categoryId) {
            if (! DB::table('user_cat_links')->where('cat_id', $categoryId)->where('link_id', $linkId)->exists()) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $linkId,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->whereIn('link_id', $sourceIds)
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            if (DB::table('user_access_links')->where('user_id', $userId)->where('link_id', $linkId)->exists()) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $linkId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($old) {
            DB::table('user_cat_links')->where('link_id', $old->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $old->link_id)->delete();
            DB::table('user_links')->where('link_id', $old->link_id)->delete();
        }
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'leave-application')->first();

        if (! $link) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
