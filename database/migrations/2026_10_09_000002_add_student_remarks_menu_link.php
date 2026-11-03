<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('user_links')->where('link_url', 'student-remarks')->exists()) {
            return;
        }

        $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

        if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
        }

        $linkId = ((int) DB::table('user_links')->max('link_id')) + 1;

        DB::table('user_links')->insert([
            'link_id' => $linkId,
            'link_url' => 'student-remarks',
            'link_name' => 'Student Remarks',
            'link_target' => null,
            'link_image' => 'ri-chat-quote-line',
            'link_parent' => 0,
            'page_id' => 'student-remarks',
            'page_id_sub' => 'student-remarks',
            'status' => 'Active',
        ]);

        $studentLinkIds = DB::table('user_links')
            ->where('page_id', 'student')
            ->pluck('link_id');

        $categoryIds = DB::table('user_cat_links')
            ->whereIn('link_id', $studentLinkIds)
            ->pluck('cat_id')
            ->unique();

        if ($categoryIds->isEmpty()) {
            $categoryIds = DB::table('user_cat')->pluck('cat_id');
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
            ->whereIn('link_id', $studentLinkIds)
            ->pluck('user_id')
            ->unique();

        if ($userIds->isEmpty()) {
            $userIds = DB::table('users')->pluck('id');
        }

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
        $linkId = DB::table('user_links')->where('link_url', 'student-remarks')->value('link_id');

        if (! $linkId) {
            return;
        }

        DB::table('user_cat_links')->where('link_id', $linkId)->delete();
        DB::table('user_access_links')->where('link_id', $linkId)->delete();
        DB::table('user_links')->where('link_id', $linkId)->delete();
    }
};
