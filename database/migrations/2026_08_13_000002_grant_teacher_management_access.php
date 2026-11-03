<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $teacherLinks = DB::table('user_links')->where('page_id', 'teacher-management')->pluck('link_id');
        $staffLink = DB::table('user_links')->where('link_url', 'list-staff')->first();

        if ($teacherLinks->isEmpty()) {
            return;
        }

        $categoryIds = DB::table('user_cat_links')
            ->when($staffLink, fn ($query) => $query->where('link_id', $staffLink->link_id))
            ->pluck('cat_id')
            ->unique();

        foreach ($teacherLinks as $linkId) {
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
            foreach ($teacherLinks as $linkId) {
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
        $teacherLinks = DB::table('user_links')->where('page_id', 'teacher-management')->pluck('link_id');
        DB::table('user_access_links')->whereIn('link_id', $teacherLinks)->delete();
    }
};
