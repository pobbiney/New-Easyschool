<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $portalLinks = DB::table('user_links')->where('page_id', 'teacher-portal')->pluck('link_id');

        if ($portalLinks->isEmpty()) {
            return;
        }

        $teacherUserIds = DB::table('users')
            ->where('user_cat', 2)
            ->where('status', 'Active')
            ->whereNotNull('staff_id')
            ->pluck('id');

        foreach ($teacherUserIds as $userId) {
            foreach ($portalLinks as $linkId) {
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
        $portalLinks = DB::table('user_links')->where('page_id', 'teacher-portal')->pluck('link_id');
        DB::table('user_access_links')->whereIn('link_id', $portalLinks)->delete();
    }
};
