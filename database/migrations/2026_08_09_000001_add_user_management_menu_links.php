<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix staff list sub-page name so active menu highlight works
        DB::table('user_links')
            ->where('link_id', 5)
            ->update(['page_id_sub' => 'list-staff']);

        // Add User Management menu
        $userManagementParentId = DB::table('user_links')->insertGetId([
            'link_url' => '#',
            'link_name' => 'User Management',
            'link_target' => null,
            'link_image' => 'ri-shield-user-line',
            'link_parent' => 0,
            'page_id' => 'user-management',
            'page_id_sub' => 'user-management',
            'status' => 'Active',
        ]);

        $userCategoriesLinkId = DB::table('user_links')->insertGetId([
            'link_url' => 'user-categories',
            'link_name' => 'User Categories',
            'link_target' => null,
            'link_image' => null,
            'link_parent' => $userManagementParentId,
            'page_id' => 'user-management',
            'page_id_sub' => 'user-categories',
            'status' => 'Active',
        ]);

        // Give Administrator full access to the new menu
        DB::table('user_cat_links')->insert([
            ['cat_id' => 1, 'link_id' => $userManagementParentId],
            ['cat_id' => 1, 'link_id' => $userCategoriesLinkId],
        ]);
    }

    public function down(): void
    {
        $userCategoriesLink = DB::table('user_links')->where('link_url', 'user-categories')->first();
        $userManagementParent = DB::table('user_links')->where('link_name', 'User Management')->where('link_parent', 0)->first();

        if ($userCategoriesLink) {
            DB::table('user_cat_links')->where('link_id', $userCategoriesLink->link_id)->delete();
            DB::table('user_links')->where('link_id', $userCategoriesLink->link_id)->delete();
        }

        if ($userManagementParent) {
            DB::table('user_cat_links')->where('link_id', $userManagementParent->link_id)->delete();
            DB::table('user_links')->where('link_id', $userManagementParent->link_id)->delete();
        }

        DB::table('user_links')
            ->where('link_id', 5)
            ->update(['page_id_sub' => 'staff-list']);
    }
};
