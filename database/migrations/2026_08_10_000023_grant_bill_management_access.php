<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $billLinks = DB::table('user_links')->where('page_id', 'bill-management')->pluck('link_id');
        $studentLink = DB::table('user_links')->where('link_url', 'list-students')->first();

        if ($billLinks->isEmpty()) {
            return;
        }

        $categoryIds = DB::table('user_cat_links')
            ->when($studentLink, fn ($query) => $query->where('link_id', $studentLink->link_id))
            ->pluck('cat_id')
            ->unique();

        foreach ($billLinks as $linkId) {
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
            ->when($studentLink, function ($query) use ($studentLink) {
                $query->where('link_id', $studentLink->link_id);
            })
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            foreach ($billLinks as $linkId) {
                $exists = DB::table('user_access_links')
                    ->where('user_id', $userId)
                    ->where('link_id', $linkId)
                    ->exists();

                if ($exists) {
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
        $billLinks = DB::table('user_links')->where('page_id', 'bill-management')->pluck('link_id');
        DB::table('user_access_links')->whereIn('link_id', $billLinks)->delete();
    }
};
