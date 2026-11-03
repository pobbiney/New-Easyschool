<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->string('status', 20)->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->string('name');
            $table->string('status', 20)->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_pay_grades', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->string('status', 20)->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_earning_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->nullable();
            $table->string('method', 20)->default('fixed');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        Schema::create('hr_deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->nullable();
            $table->string('method', 20)->default('fixed');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_statutory')->default(false);
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        Schema::create('hr_staff_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('earning_type_id')->constrained('hr_earning_types')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['staff_id', 'earning_type_id']);
        });

        Schema::create('hr_staff_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->constrained('hr_deduction_types')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['staff_id', 'deduction_type_id']);
        });

        Schema::create('hr_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('days_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->string('gender_limit', 20)->nullable();
            $table->string('status', 20)->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('entitled')->default(0);
            $table->unsignedInteger('taken')->default(0);
            $table->timestamps();
            $table->unique(['staff_id', 'leave_type_id', 'year']);
        });

        Schema::create('hr_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('days');
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->unique(['staff_id', 'date']);
        });

        Schema::create('hr_payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('ssnit_employee_rate', 8, 4)->default(5.5);
            $table->decimal('ssnit_employer_rate', 8, 4)->default(13);
            $table->decimal('ssnit_ceiling', 12, 2)->nullable();
            $table->decimal('personal_relief', 12, 2)->default(0);
            $table->json('paye_bands')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->string('status', 20)->default('draft');
            $table->date('run_date')->nullable();
            $table->decimal('total_gross', 14, 2)->default(0);
            $table->decimal('total_ssnit_employee', 14, 2)->default(0);
            $table->decimal('total_ssnit_employer', 14, 2)->default(0);
            $table->decimal('total_paye', 14, 2)->default(0);
            $table->decimal('total_other_deductions', 14, 2)->default(0);
            $table->decimal('total_net', 14, 2)->default(0);
            $table->unsignedInteger('employee_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['period_year', 'period_month']);
        });

        Schema::create('hr_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('hr_payroll_runs')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('ssnit_employee', 12, 2)->default(0);
            $table->decimal('ssnit_employer', 12, 2)->default(0);
            $table->decimal('paye', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->unsignedInteger('unpaid_leave_days')->default(0);
            $table->json('lines')->nullable();
            $table->timestamps();
            $table->unique(['payroll_run_id', 'staff_id']);
        });

        Schema::create('hr_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('period_label');
            $table->json('scores')->nullable();
            $table->decimal('overall', 4, 2)->nullable();
            $table->text('comments')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('appraised_by')->nullable();
            $table->timestamps();
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('position')->constrained('hr_departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained('hr_positions')->nullOnDelete();
            $table->foreignId('pay_grade_id')->nullable()->after('position_id')->constrained('hr_pay_grades')->nullOnDelete();
            $table->decimal('basic_salary', 12, 2)->nullable()->after('pay_grade_id');
            $table->string('employment_type', 40)->nullable()->after('basic_salary');
            $table->date('appointment_date')->nullable()->after('employment_type');
            $table->date('confirmation_date')->nullable()->after('appointment_date');
            $table->date('contract_end_date')->nullable()->after('confirmation_date');
            $table->string('ssnit_number', 40)->nullable()->after('contract_end_date');
            $table->string('tin', 40)->nullable()->after('ssnit_number');
            $table->string('bank_name')->nullable()->after('tin');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('account_name')->nullable()->after('bank_branch');
            $table->string('account_number', 40)->nullable()->after('account_name');
            $table->string('next_of_kin_name')->nullable()->after('account_number');
            $table->string('next_of_kin_phone', 40)->nullable()->after('next_of_kin_name');
            $table->string('next_of_kin_relationship', 40)->nullable()->after('next_of_kin_phone');
        });

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('pay_grade_id');
            $table->dropColumn([
                'basic_salary',
                'employment_type',
                'appointment_date',
                'confirmation_date',
                'contract_end_date',
                'ssnit_number',
                'tin',
                'bank_name',
                'bank_branch',
                'account_name',
                'account_number',
                'next_of_kin_name',
                'next_of_kin_phone',
                'next_of_kin_relationship',
            ]);
        });

        Schema::dropIfExists('hr_appraisals');
        Schema::dropIfExists('hr_payslips');
        Schema::dropIfExists('hr_payroll_runs');
        Schema::dropIfExists('hr_payroll_settings');
        Schema::dropIfExists('hr_staff_attendance');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_leave_balances');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_staff_deductions');
        Schema::dropIfExists('hr_staff_earnings');
        Schema::dropIfExists('hr_deduction_types');
        Schema::dropIfExists('hr_earning_types');
        Schema::dropIfExists('hr_pay_grades');
        Schema::dropIfExists('hr_positions');
        Schema::dropIfExists('hr_departments');
    }

    private function seedDefaults(): void
    {
        $now = now();

        $departments = [
            ['name' => 'Teaching', 'code' => 'TEACH'],
            ['name' => 'Administration', 'code' => 'ADMIN'],
            ['name' => 'Accounts', 'code' => 'ACCT'],
            ['name' => 'Support', 'code' => 'SUPP'],
        ];

        $departmentIds = [];
        foreach ($departments as $department) {
            $departmentIds[$department['code']] = DB::table('hr_departments')->insertGetId([
                'name' => $department['name'],
                'code' => $department['code'],
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $positions = [
            ['name' => 'Head Teacher', 'code' => 'ADMIN'],
            ['name' => 'Deputy Head Teacher', 'code' => 'ADMIN'],
            ['name' => 'Administrative Officer', 'code' => 'ADMIN'],
            ['name' => 'Teacher', 'code' => 'TEACH'],
            ['name' => 'Bursar', 'code' => 'ACCT'],
            ['name' => 'Accountant', 'code' => 'ACCT'],
            ['name' => 'Security Officer', 'code' => 'SUPP'],
        ];

        $positionIds = [];
        foreach ($positions as $position) {
            $positionIds[$position['name']] = DB::table('hr_positions')->insertGetId([
                'department_id' => $departmentIds[$position['code']],
                'name' => $position['name'],
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (DB::table('staff')->get() as $staff) {
            $positionId = $positionIds[$staff->position] ?? null;
            if (! $positionId) {
                continue;
            }

            $position = DB::table('hr_positions')->where('id', $positionId)->first();
            DB::table('staff')->where('id', $staff->id)->update([
                'position_id' => $positionId,
                'department_id' => $position->department_id,
            ]);
        }

        DB::table('hr_pay_grades')->insert([
            ['name' => 'Grade A — Leadership', 'basic_salary' => 4500, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grade B — Senior Teacher', 'basic_salary' => 3200, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grade C — Teacher', 'basic_salary' => 2500, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grade D — Support', 'basic_salary' => 1500, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('hr_earning_types')->insert([
            ['name' => 'Responsibility Allowance', 'code' => 'RESP', 'method' => 'fixed', 'default_amount' => 200, 'is_taxable' => true, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rent Allowance', 'code' => 'RENT', 'method' => 'percent_basic', 'default_amount' => 10, 'is_taxable' => true, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Transport Allowance', 'code' => 'TRANS', 'method' => 'fixed', 'default_amount' => 150, 'is_taxable' => false, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('hr_deduction_types')->insert([
            ['name' => 'SSNIT (Employee)', 'code' => 'SSNIT', 'method' => 'percent_basic', 'default_amount' => 5.5, 'is_statutory' => true, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PAYE', 'code' => 'PAYE', 'method' => 'percent_basic', 'default_amount' => 0, 'is_statutory' => true, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Welfare', 'code' => 'WELF', 'method' => 'fixed', 'default_amount' => 20, 'is_statutory' => false, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Staff Loan', 'code' => 'LOAN', 'method' => 'fixed', 'default_amount' => 0, 'is_statutory' => false, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('hr_leave_types')->insert([
            ['name' => 'Annual Leave', 'days_per_year' => 15, 'is_paid' => true, 'gender_limit' => null, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sick Leave', 'days_per_year' => 10, 'is_paid' => true, 'gender_limit' => null, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maternity Leave', 'days_per_year' => 90, 'is_paid' => true, 'gender_limit' => 'Female', 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Casual Leave', 'days_per_year' => 5, 'is_paid' => true, 'gender_limit' => null, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Study Leave', 'days_per_year' => 10, 'is_paid' => false, 'gender_limit' => null, 'status' => 'Active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('hr_payroll_settings')->insert([
            'ssnit_employee_rate' => 5.5,
            'ssnit_employer_rate' => 13,
            'ssnit_ceiling' => 50000,
            'personal_relief' => 0,
            'paye_bands' => json_encode([
                ['up_to' => 490, 'rate' => 0],
                ['up_to' => 600, 'rate' => 5],
                ['up_to' => 730, 'rate' => 10],
                ['up_to' => 3896.67, 'rate' => 17.5],
                ['up_to' => 19896.67, 'rate' => 25],
                ['up_to' => 50416.67, 'rate' => 30],
                ['up_to' => null, 'rate' => 35],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
