<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    protected array $dropUniques = [
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

    protected function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))
            ->pluck('name')
            ->contains($indexName);
    }

    public function up(): void
    {
        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('school_id')->nullable();
                $blueprint->index('school_id');
            });
        }

        foreach ($this->dropUniques as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $indexName = $table.'_'.implode('_', $columns).'_unique';

            if (! $this->indexExists($table, $indexName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropUnique($indexName);
            });
        }

        foreach ($this->compositeUniques as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $indexName = $table.'_'.implode('_', $columns).'_unique';

            if ($this->indexExists($table, $indexName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->unique($columns, $indexName);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->compositeUniques as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $indexName = $table.'_'.implode('_', $columns).'_unique';

            if (! $this->indexExists($table, $indexName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropUnique($indexName);
            });
        }

        foreach ($this->dropUniques as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $indexName = $table.'_'.implode('_', $columns).'_unique';

            if ($this->indexExists($table, $indexName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->unique($columns);
            });
        }

        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['school_id']);
                $blueprint->dropColumn('school_id');
            });
        }
    }
};