<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $migrationsCreate = DB::select('SHOW CREATE TABLE migrations')[0]->{'Create Table'} ?? '';

        if ($migrationsCreate !== '' && ! str_contains($migrationsCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE migrations MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $oldLinks = DB::table('user_links')->where('link_url', 'teacher-leave')->get();

        if ($oldLinks->isEmpty()) {
            return;
        }

        $replacement = DB::table('user_links')
            ->where('link_url', 'leave-application')
            ->orderByDesc('link_id')
            ->first();

        foreach ($oldLinks as $old) {
            if ($replacement && (int) $replacement->link_id !== (int) $old->link_id) {
                $this->moveGrants((int) $old->link_id, (int) $replacement->link_id);
                DB::table('user_links')->where('link_id', $old->link_id)->delete();

                continue;
            }

            DB::table('user_links')->where('link_id', $old->link_id)->update([
                'link_url' => 'leave-application',
                'page_id_sub' => 'leave-application',
            ]);
        }
    }

    public function down(): void
    {
        // The old teacher-leave menu now points at the leave application page.
    }

    private function moveGrants(int $fromLinkId, int $toLinkId): void
    {
        $categoryIds = DB::table('user_cat_links')->where('link_id', $fromLinkId)->pluck('cat_id');

        foreach ($categoryIds as $categoryId) {
            $exists = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->where('link_id', $toLinkId)
                ->exists();

            if (! $exists) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $toLinkId,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')->where('link_id', $fromLinkId)->pluck('user_id');

        foreach ($userIds as $userId) {
            $exists = DB::table('user_access_links')
                ->where('user_id', $userId)
                ->where('link_id', $toLinkId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $toLinkId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('user_cat_links')->where('link_id', $fromLinkId)->delete();
        DB::table('user_access_links')->where('link_id', $fromLinkId)->delete();
    }
};
