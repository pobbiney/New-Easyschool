<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('link_url', 'parent-messages')->exists()) {
            return;
        }

        $smsParent = DB::table('user_links')
            ->where('page_id', 'sms')
            ->where('link_parent', 0)
            ->first();

        if (! $smsParent) {
            return;
        }

        $childId = DB::table('user_links')->insertGetId([
            'link_url' => 'parent-messages',
            'link_name' => 'Parent Messages',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $smsParent->link_id,
            'page_id' => 'sms',
            'page_id_sub' => 'parent-messages',
            'status' => 'Active',
        ]);

        $sendSmsLink = DB::table('user_links')->where('link_url', 'send-sms')->first();

        $categoryIds = DB::table('user_cat_links')
            ->when($sendSmsLink, fn ($query) => $query->where('link_id', $sendSmsLink->link_id))
            ->pluck('cat_id')
            ->unique();

        if ($categoryIds->isEmpty()) {
            $categoryIds = collect([1]);
        }

        foreach ($categoryIds as $categoryId) {
            $exists = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->where('link_id', $childId)
                ->exists();

            if (! $exists) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $childId,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->when($sendSmsLink, fn ($query) => $query->where('link_id', $sendSmsLink->link_id))
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
        $link = DB::table('user_links')->where('link_url', 'parent-messages')->first();

        if (! $link) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
