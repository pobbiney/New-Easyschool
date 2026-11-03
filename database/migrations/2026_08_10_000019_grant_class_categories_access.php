<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classCategoriesLink = DB::table('user_links')->where('link_url', 'class-categories')->first();
        $schoolClassLink = DB::table('user_links')->where('link_url', 'school-classes')->first();

        if (! $classCategoriesLink) {
            return;
        }

        $categoryIds = DB::table('user_cat_links')
            ->when($schoolClassLink, function ($query) use ($schoolClassLink) {
                $query->where('link_id', $schoolClassLink->link_id);
            })
            ->pluck('cat_id')
            ->unique();

        foreach ($categoryIds as $categoryId) {
            $exists = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->where('link_id', $classCategoriesLink->link_id)
                ->exists();

            if (! $exists) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $classCategoriesLink->link_id,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->where(function ($query) use ($classCategoriesLink, $schoolClassLink) {
                $query->where('link_id', $classCategoriesLink->link_parent);

                if ($schoolClassLink) {
                    $query->orWhere('link_id', $schoolClassLink->link_id);
                }
            })
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            $exists = DB::table('user_access_links')
                ->where('user_id', $userId)
                ->where('link_id', $classCategoriesLink->link_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $classCategoriesLink->link_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $classCategoriesLink = DB::table('user_links')->where('link_url', 'class-categories')->first();

        if (! $classCategoriesLink) {
            return;
        }

        DB::table('user_access_links')->where('link_id', $classCategoriesLink->link_id)->delete();
    }
};
