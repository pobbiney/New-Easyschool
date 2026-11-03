<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    protected array $tenantTables = [
        'school_settings',
        'users',
        'staff',
        'students',
        'academic_years',
        'academic_terms',
        'school_classes',
        'class_categories',
        'courses',
        'grading_schemes',
        'assessment_types',
        'timetable_periods',
        'houses',
        'dormitories',
        'billing_items',
        'category_bill_setups',
        'student_bills',
        'bill_payments',
        'bill_payment_transactions',
        'student_bill_credit_transactions',
        'hr_departments',
        'hr_positions',
        'hr_pay_grades',
        'hr_earning_types',
        'hr_deduction_types',
        'hr_leave_types',
        'hr_payroll_settings',
        'hr_payroll_runs',
        'pos_categories',
        'pos_products',
        'pos_sales',
        'pos_sale_transactions',
        'expense_categories',
        'expenses',
        'parent_accounts',
        'parent_messages',
        'parent_communication_logs',
        'sms_messages',
        'user_cat',
        'academic_assessments',
        'class_timetables',
    ];

    /** @var array<string, list<string>> */
    protected array $compositeUniques = [
        'users' => ['school_id', 'email'],
        'students' => ['school_id', 'student_id'],
        'houses' => ['school_id', 'name'],
        'class_categories' => ['school_id', 'name'],
        'school_classes' => ['school_id', 'class_teacher_id'],
        'billing_items' => ['school_id', 'name'],
        'bill_payments' => ['school_id', 'receipt_no'],
        'bill_payment_transactions' => ['school_id', 'reference'],
        'pos_categories' => ['school_id', 'name'],
        'pos_products' => ['school_id', 'sku'],
        'pos_sales' => ['school_id', 'receipt_no'],
        'pos_sale_transactions' => ['school_id', 'reference'],
        'expense_categories' => ['school_id', 'name'],
        'assessment_types' => ['school_id', 'slug'],
        'parent_accounts' => ['school_id', 'phone'],
        'hr_payroll_runs' => ['school_id', 'period_year', 'period_month'],
    ];

    /** @var array<string, list<string>> */
    protected array $restoreUniques = [
        'users' => ['email'],
        'students' => ['student_id'],
        'houses' => ['name'],
        'class_categories' => ['name'],
        'school_classes' => ['class_teacher_id'],
        'billing_items' => ['name'],
        'bill_payments' => ['receipt_no'],
        'bill_payment_transactions' => ['reference'],
        'pos_categories' => ['name'],
        'pos_products' => ['sku'],
        'pos_sales' => ['receipt_no'],
        'pos_sale_transactions' => ['reference'],
        'expense_categories' => ['name'],
        'assessment_types' => ['slug'],
        'parent_accounts' => ['phone'],
        'hr_payroll_runs' => ['period_year', 'period_month'],
    ];

    public function up(): void
    {
        $keepSchoolId = $this->resolveKeepSchoolId();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if ($keepSchoolId) {
            foreach ($this->tenantTables as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                    continue;
                }

                DB::table($table)
                    ->whereNotNull('school_id')
                    ->where('school_id', '!=', $keepSchoolId)
                    ->delete();
            }

            if (Schema::hasTable('academic_term_calendars') && Schema::hasColumn('academic_term_calendars', 'school_id')) {
                DB::table('academic_term_calendars')
                    ->whereNotNull('school_id')
                    ->where('school_id', '!=', $keepSchoolId)
                    ->delete();
            }

            if (Schema::hasTable('school_settings') && Schema::hasColumn('school_settings', 'school_id')) {
                $keptSettings = DB::table('school_settings')->where('school_id', $keepSchoolId)->exists();
                if ($keptSettings) {
                    DB::table('school_settings')->whereNull('school_id')->delete();
                }
            }
        }

        Schema::dropIfExists('school_subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('academic_term_calendars');
        Schema::dropIfExists('school_activity_logs');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('super_admins');

        foreach ($this->compositeUniques as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $indexName = $table.'_'.implode('_', $columns).'_unique';

            try {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropUnique($indexName);
                });
            } catch (\Throwable) {
            }
        }

        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $blueprint) {
                    try {
                        $blueprint->dropIndex(['school_id']);
                    } catch (\Throwable) {
                    }

                    $blueprint->dropColumn('school_id');
                });
            } catch (\Throwable) {
            }
        }

        foreach ($this->restoreUniques as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                    $blueprint->unique($columns);
                });
            } catch (\Throwable) {
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Irreversible: tenancy tables and school_id columns are not recreated here.
    }

    private function resolveKeepSchoolId(): ?int
    {
        if (! Schema::hasTable('schools')) {
            return null;
        }

        foreach (['students', 'users', 'school_settings'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            $row = DB::table($table)
                ->select('school_id', DB::raw('COUNT(*) as aggregate_count'))
                ->whereNotNull('school_id')
                ->groupBy('school_id')
                ->orderByDesc('aggregate_count')
                ->orderBy('school_id')
                ->first();

            if ($row?->school_id) {
                return (int) $row->school_id;
            }
        }

        $id = DB::table('schools')->orderBy('id')->value('id');

        return $id ? (int) $id : null;
    }
};
