<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $addStaff = DB::table('user_links')->where('link_url', 'add-staff')->first();
        $listStaff = DB::table('user_links')->where('link_url', 'list-staff')->first();
        $parentId = $addStaff->link_parent ?? $listStaff->link_parent ?? null;

        if (! $parentId) {
            $parentId = DB::table('user_links')->insertGetId([
                'link_url' => '#',
                'link_name' => 'HR',
                'link_target' => null,
                'link_image' => 'ri-id-card-line',
                'link_parent' => 0,
                'page_id' => 'hr',
                'page_id_sub' => 'hr',
                'status' => 'Active',
            ]);

            DB::table('user_cat_links')->insert([
                'cat_id' => 1,
                'link_id' => $parentId,
            ]);
        } else {
            DB::table('user_links')->where('link_id', $parentId)->update([
                'link_name' => 'HR',
                'link_image' => 'ri-id-card-line',
                'page_id' => 'hr',
                'page_id_sub' => 'hr',
            ]);
        }

        if ($addStaff) {
            DB::table('user_links')->where('link_id', $addStaff->link_id)->update([
                'link_name' => 'Add Employee',
                'link_parent' => $parentId,
                'page_id' => 'hr',
                'page_id_sub' => 'add-staff',
            ]);
        }

        if ($listStaff) {
            DB::table('user_links')->where('link_id', $listStaff->link_id)->update([
                'link_name' => 'Employees',
                'link_parent' => $parentId,
                'page_id' => 'hr',
                'page_id_sub' => 'list-staff',
            ]);
        }

        $children = [
            ['link_url' => 'hr-dashboard', 'link_name' => 'Dashboard', 'page_id_sub' => 'hr-dashboard'],
            ['link_url' => 'hr-departments', 'link_name' => 'Departments', 'page_id_sub' => 'hr-departments'],
            ['link_url' => 'hr-positions', 'link_name' => 'Positions', 'page_id_sub' => 'hr-positions'],
            ['link_url' => 'hr-leave', 'link_name' => 'Leave', 'page_id_sub' => 'hr-leave'],
            ['link_url' => 'hr-attendance', 'link_name' => 'Attendance', 'page_id_sub' => 'hr-attendance'],
            ['link_url' => 'hr-salary-structures', 'link_name' => 'Salary Structures', 'page_id_sub' => 'hr-salary-structures'],
            ['link_url' => 'hr-payroll', 'link_name' => 'Payroll', 'page_id_sub' => 'hr-payroll'],
            ['link_url' => 'hr-payslips', 'link_name' => 'Payslips', 'page_id_sub' => 'hr-payslips'],
            ['link_url' => 'hr-appraisals', 'link_name' => 'Appraisals', 'page_id_sub' => 'hr-appraisals'],
            ['link_url' => 'hr-settings', 'link_name' => 'Statutory Settings', 'page_id_sub' => 'hr-settings'],
        ];

        foreach ($children as $child) {
            $existing = DB::table('user_links')->where('link_url', $child['link_url'])->first();

            if ($existing) {
                DB::table('user_links')->where('link_id', $existing->link_id)->update([
                    'link_name' => $child['link_name'],
                    'link_parent' => $parentId,
                    'page_id' => 'hr',
                    'page_id_sub' => $child['page_id_sub'],
                    'status' => 'Active',
                ]);
                continue;
            }

            $linkId = DB::table('user_links')->insertGetId([
                'link_url' => $child['link_url'],
                'link_name' => $child['link_name'],
                'link_target' => null,
                'link_image' => null,
                'link_parent' => $parentId,
                'page_id' => 'hr',
                'page_id_sub' => $child['page_id_sub'],
                'status' => 'Active',
            ]);

            DB::table('user_cat_links')->insert([
                'cat_id' => 1,
                'link_id' => $linkId,
            ]);
        }
    }

    public function down(): void
    {
        $parent = DB::table('user_links')->where('page_id', 'hr')->where('link_parent', 0)->first();

        $newUrls = [
            'hr-dashboard',
            'hr-departments',
            'hr-positions',
            'hr-leave',
            'hr-attendance',
            'hr-salary-structures',
            'hr-payroll',
            'hr-payslips',
            'hr-appraisals',
            'hr-settings',
        ];

        $links = DB::table('user_links')->whereIn('link_url', $newUrls)->where('page_id', 'hr')->get();

        foreach ($links as $link) {
            DB::table('user_cat_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_access_links')->where('link_id', $link->link_id)->delete();
            DB::table('user_links')->where('link_id', $link->link_id)->delete();
        }

        if ($parent) {
            DB::table('user_links')->where('link_id', $parent->link_id)->update([
                'link_name' => 'Staff Management',
                'link_image' => 'ri-user-settings-line',
                'page_id' => 'staff',
                'page_id_sub' => 'staff',
            ]);
        }

        DB::table('user_links')->where('link_url', 'add-staff')->update([
            'link_name' => 'Add New Staff',
            'page_id' => 'staff',
            'page_id_sub' => 'add-staff',
        ]);

        DB::table('user_links')->where('link_url', 'list-staff')->update([
            'link_name' => 'Staff List',
            'page_id' => 'staff',
            'page_id_sub' => 'list-staff',
        ]);
    }
};
