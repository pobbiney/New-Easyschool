<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $linkId = DB::table('user_links')->where('link_url', 'leave-application')->value('link_id');

        if (! $linkId) {
            return;
        }

        $categoryIds = DB::table('user_cat')->pluck('cat_id');

        foreach ($categoryIds as $categoryId) {
            if (! DB::table('user_cat_links')->where('cat_id', $categoryId)->where('link_id', $linkId)->exists()) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $linkId,
                ]);
            }
        }

        $userIds = DB::table('users')->pluck('id');

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
    }

    public function down(): void
    {
        $linkId = DB::table('user_links')->where('link_url', 'leave-application')->value('link_id');

        if (! $linkId) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $linkId)->delete();
        DB::table('user_access_links')->where('link_id', $linkId)->delete();
    }
};
