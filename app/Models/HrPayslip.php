<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayslip extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'staff_id',
        'basic',
        'gross',
        'ssnit_employee',
        'ssnit_employer',
        'paye',
        'other_deductions',
        'net',
        'unpaid_leave_days',
        'lines',
    ];

    protected $casts = [
        'basic' => 'decimal:2',
        'gross' => 'decimal:2',
        'ssnit_employee' => 'decimal:2',
        'ssnit_employer' => 'decimal:2',
        'paye' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net' => 'decimal:2',
        'lines' => 'array',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(HrPayrollRun::class, 'payroll_run_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
