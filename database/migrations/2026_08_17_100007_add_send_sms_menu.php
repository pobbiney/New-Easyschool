<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('link_url', 'send-sms')->exists()) {
            return;
        }

        $parentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'Send SMS',
            'link_target' => null,
            'link_image' => 'ri-message-2-line',
            'link_parent' => 0,
            'page_id' => 'sms',
            'page_id_sub' => 'sms',
            'status' => 'Active',
        ]);

        $childId = DB::table('user_links')->insertGetId([
            'link_url' => 'send-sms',
            'link_name' => 'Compose SMS',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $parentId,
            'page_id' => 'sms',
            'page_id_sub' => 'send-sms',
            'status' => 'Active',
        ]);

        $staffLink = DB::table('user_links')->where('link_url', 'list-staff')->first();
        $categoryIds = DB::table('user_cat_links')
            ->when($staffLink, fn ($query) => $query->where('link_id', $staffLink->link_id))
            ->pluck('cat_id')
            ->unique();

        if ($categoryIds->isEmpty()) {
            $categoryIds = collect([1]);
        }

        foreach ([$parentId, $childId] as $linkId) {
            foreach ($categoryIds as $categoryId) {
                $exists = DB::table('user_cat_links')
                    ->where('cat_id', $categoryId)
                    ->where('link_id', $linkId)
                    ->exists();

                if (! $exists) {
                    DB::table('user_cat_links')->insert([
                        'cat_id' => $categoryId,
                        'link_id' => $linkId,
                    ]);
                }
            }
        }

        $userIds = DB::table('user_access_links')
            ->when($staffLink, fn ($query) => $query->where('link_id', $staffLink->link_id))
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            foreach ([$parentId, $childId] as $linkId) {
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
        }
    }

    public function down(): void
    {
        $links = DB::table('user_links')->whereIn('page_id', ['sms'])->get();
        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }
    }
};
