<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classSetupParent = DB::table('user_links')
            ->where('link_name', 'Class Setup')
            ->where('link_parent', 0)
            ->first();

        if (! $classSetupParent) {
            return;
        }

        if (DB::table('user_links')->where('link_url', 'student-promotion')->exists()) {
            return;
        }

        $linkId = DB::table('user_links')->insertGetId([
            'link_url' => 'student-promotion',
            'link_name' => 'Student Promotion',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $classSetupParent->link_id,
            'page_id' => 'class-setup',
            'page_id_sub' => 'student-promotion',
            'status' => 'Active',
        ]);

        $referenceLink = DB::table('user_links')->where('link_url', 'student-class-assignment')->first()
            ?? DB::table('user_links')->where('link_url', 'school-classes')->first();

        if ($referenceLink) {
            $categoryIds = DB::table('user_cat_links')
                ->where('link_id', $referenceLink->link_id)
                ->pluck('cat_id');

            foreach ($categoryIds as $catId) {
                DB::table('user_cat_links')->insert([
                    'cat_id' => $catId,
                    'link_id' => $linkId,
                ]);
            }

            $userIds = DB::table('user_access_links')
                ->where('link_id', $referenceLink->link_id)
                ->pluck('user_id')
                ->unique();

            foreach ($userIds as $userId) {
                if (! DB::table('user_access_links')->where('user_id', $userId)->where('link_id', $linkId)->exists()) {
                    DB::table('user_access_links')->insert([
                        'user_id' => $userId,
                        'link_id' => $linkId,
                    ]);
                }
            }
        } else {
            DB::table('user_cat_links')->insert([
                'cat_id' => 1,
                'link_id' => $linkId,
            ]);
        }
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'student-promotion')->first();

        if (! $link) {
            return;
        }

        DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
        DB::table('user_links')->where('link_id', $link->link_id)->delete();
    }
};
