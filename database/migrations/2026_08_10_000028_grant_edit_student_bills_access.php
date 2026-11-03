<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $editLink = DB::table('user_links')->where('link_url', 'edit-student-bills')->first();
        $studentBillsLink = DB::table('user_links')->where('link_url', 'student-bills')->first();

        if (! $editLink) {
            return;
        }

        $categoryIds = DB::table('user_cat_links')
            ->when($studentBillsLink, fn ($query) => $query->where('link_id', $studentBillsLink->link_id))
            ->pluck('cat_id')
            ->unique();

        foreach ($categoryIds as $categoryId) {
            $exists = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->where('link_id', $editLink->link_id)
                ->exists();

            if (! $exists) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $categoryId,
                    'link_id' => $editLink->link_id,
                ]);
            }
        }

        $userIds = DB::table('user_access_links')
            ->where(function ($query) use ($editLink, $studentBillsLink) {
                $query->where('link_id', $editLink->link_parent);

                if ($studentBillsLink) {
                    $query->orWhere('link_id', $studentBillsLink->link_id);
                }
            })
            ->pluck('user_id')
            ->unique();

        foreach ($userIds as $userId) {
            $exists = DB::table('user_access_links')
                ->where('user_id', $userId)
                ->where('link_id', $editLink->link_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('user_access_links')->insert([
                'user_id' => $userId,
                'link_id' => $editLink->link_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $editLink = DB::table('user_links')->where('link_url', 'edit-student-bills')->first();

        if (! $editLink) {
            return;
        }

        DB::table('user_access_links')->where('link_id', $editLink->link_id)->delete();
    }
};
