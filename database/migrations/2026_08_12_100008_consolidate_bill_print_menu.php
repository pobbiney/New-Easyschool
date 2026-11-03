<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $studentLink = DB::table('user_links')->where('link_url', 'print-student-bill')->first();
        $classLink = DB::table('user_links')->where('link_url', 'print-class-bills')->first();

        if ($studentLink) {
            DB::table('user_links')->where('link_id', $studentLink->link_id)->update([
                'link_url' => 'print-bills',
                'link_name' => 'Print Bills',
                'page_id_sub' => 'print-bills',
            ]);
        } elseif (! DB::table('user_links')->where('link_url', 'print-bills')->exists()) {
            $parent = DB::table('user_links')
                ->where('link_name', 'Bill Management')
                ->where('link_parent', 0)
                ->first();

            if ($parent) {
                $linkId = DB::table('user_links')->insertGetId([
                    'link_url' => 'print-bills',
                    'link_name' => 'Print Bills',
                    'link_target' => null,
                    'link_image' => null,
                    'link_parent' => $parent->link_id,
                    'page_id' => 'bill-management',
                    'page_id_sub' => 'print-bills',
                    'status' => 'Active',
                ]);

                DB::table('user_cat_links')->insert([
                    'cat_id' => 1,
                    'link_id' => $linkId,
                ]);
            }
        }

        if ($classLink) {
            DB::table('user_access_links')->where('link_id', $classLink->link_id)->delete();
            DB::table('user_cat_links')->where('link_id', $classLink->link_id)->delete();
            DB::table('user_links')->where('link_id', $classLink->link_id)->delete();
        }
    }

    public function down(): void
    {
        $link = DB::table('user_links')->where('link_url', 'print-bills')->first();

        if ($link) {
            DB::table('user_links')->where('link_id', $link->link_id)->update([
                'link_url' => 'print-student-bill',
                'link_name' => 'Print Student Bill',
                'page_id_sub' => 'print-student-bill',
            ]);
        }
    }
};
