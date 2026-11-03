<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $current = DB::table('user_links')
            ->where('link_url', 'student-remarks')
            ->where('link_parent', 0)
            ->first();

        if ($current) {
            DB::table('user_links')->where('link_id', $current->link_id)->update([
                'link_url' => '#',
                'link_name' => 'Student Remarks',
                'link_image' => 'ri-chat-quote-line',
                'link_parent' => 0,
                'page_id' => 'student-remarks',
                'page_id_sub' => 'student-remarks',
                'status' => 'Active',
            ]);
            $parentId = $current->link_id;
        } else {
            $parent = DB::table('user_links')
                ->where('page_id', 'student-remarks')
                ->where('link_parent', 0)
                ->first();

            if (! $parent) {
                return;
            }

            $parentId = $parent->link_id;
        }

        $addId = $this->ensureChild($parentId, 'student-remarks', 'Add Remarks', 'add-remarks');
        $listId = $this->ensureChild($parentId, 'list-student-remarks', 'List Remarks', 'list-remarks');

        $this->copyGrants($parentId, [$addId, $listId]);
    }

    public function down(): void
    {
        $parent = DB::table('user_links')
            ->where('page_id', 'student-remarks')
            ->where('link_parent', 0)
            ->first();

        if (! $parent) {
            return;
        }

        $childIds = DB::table('user_links')
            ->where('link_parent', $parent->link_id)
            ->whereIn('link_url', ['student-remarks', 'list-student-remarks'])
            ->pluck('link_id');

        DB::table('user_cat_links')->whereIn('link_id', $childIds)->delete();
        DB::table('user_access_links')->whereIn('link_id', $childIds)->delete();
        DB::table('user_links')->whereIn('link_id', $childIds)->delete();

        DB::table('user_links')->where('link_id', $parent->link_id)->update([
            'link_url' => 'student-remarks',
            'link_name' => 'Student Remarks',
            'page_id_sub' => 'student-remarks',
        ]);
    }

    private function ensureChild(int $parentId, string $url, string $name, string $pageIdSub): int
    {
        $child = DB::table('user_links')
            ->where('link_url', $url)
            ->where('link_parent', $parentId)
            ->first();

        if ($child) {
            DB::table('user_links')->where('link_id', $child->link_id)->update([
                'link_name' => $name,
                'page_id' => 'student-remarks',
                'page_id_sub' => $pageIdSub,
                'status' => 'Active',
            ]);

            return (int) $child->link_id;
        }

        $userLinksCreate = DB::select('SHOW CREATE TABLE user_links')[0]->{'Create Table'} ?? '';

        if ($userLinksCreate !== '' && ! str_contains($userLinksCreate, 'AUTO_INCREMENT')) {
            DB::statement('ALTER TABLE user_links MODIFY link_id INT NOT NULL AUTO_INCREMENT');
        }

        $childId = ((int) DB::table('user_links')->max('link_id')) + 1;

        DB::table('user_links')->insert([
            'link_id' => $childId,
            'link_url' => $url,
            'link_name' => $name,
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $parentId,
            'page_id' => 'student-remarks',
            'page_id_sub' => $pageIdSub,
            'status' => 'Active',
        ]);

        return $childId;
    }

    private function copyGrants(int $parentId, array $childIds): void
    {
        $categoryIds = DB::table('user_cat_links')->where('link_id', $parentId)->pluck('cat_id');
        $userIds = DB::table('user_access_links')->where('link_id', $parentId)->pluck('user_id');

        foreach ($childIds as $childId) {
            foreach ($categoryIds as $categoryId) {
                if (! DB::table('user_cat_links')->where('cat_id', $categoryId)->where('link_id', $childId)->exists()) {
                    DB::table('user_cat_links')->insert([
                        'cat_id' => $categoryId,
                        'link_id' => $childId,
                    ]);
                }
            }

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
    }
};
