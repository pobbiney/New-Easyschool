<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $printLinks = DB::table('user_links')
            ->whereIn('link_url', ['print-student-bill', 'print-class-bills'])
            ->get();

        if ($printLinks->isEmpty()) {
            return;
        }

        $studentBillsLink = DB::table('user_links')->where('link_url', 'student-bills')->first();

        $categoryIds = DB::table('user_cat_links')
            ->when($studentBillsLink, fn ($query) => $query->where('link_id', $studentBillsLink->link_id))
            ->pluck('cat_id')
            ->unique();

        foreach ($printLinks as $printLink) {
            foreach ($categoryIds as $categoryId) {
                $exists = DB::table('user_cat_links')
                    ->where('cat_id', $categoryId)
                    ->where('link_id', $printLink->link_id)
                    ->exists();

                if (! $exists) {
                    DB::table('user_cat_links')->insert([
                        'cat_id' => $categoryId,
                        'link_id' => $printLink->link_id,
                    ]);
                }
            }
        }

        $userIds = DB::table('user_access_links')
            ->where(function ($query) use ($printLinks, $studentBillsLink) {
                $query->whereIn('link_id', $printLinks->pluck('link_id'));

                if ($studentBillsLink) {
                    $query->orWhere('link_id', $studentBillsLink->link_id);
                }

                $query->orWhere('link_id', $printLinks->first()->link_parent);
            })
            ->pluck('user_id')
            ->unique();

        foreach ($printLinks as $printLink) {
            foreach ($userIds as $userId) {
                $exists = DB::table('user_access_links')
                    ->where('user_id', $userId)
                    ->where('link_id', $printLink->link_id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('user_access_links')->insert([
                    'user_id' => $userId,
                    'link_id' => $printLink->link_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $linkIds = DB::table('user_links')
            ->whereIn('link_url', ['print-student-bill', 'print-class-bills'])
            ->pluck('link_id');

        if ($linkIds->isNotEmpty()) {
            DB::table('user_access_links')->whereIn('link_id', $linkIds)->delete();
        }
    }
};
