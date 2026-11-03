<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayrollRun extends Model
{

    protected $fillable = [
        'period_year',
        'period_month',
        'status',
        'run_date',
        'total_gross',
        'total_ssnit_employee',
        'total_ssnit_employer',
        'total_paye',
        'total_other_deductions',
        'total_net',
        'employee_count',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'run_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_ssnit_employee' => 'decimal:2',
        'total_ssnit_employer' => 'decimal:2',
        'total_paye' => 'decimal:2',
        'total_other_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function payslips()
    {
        return $this->hasMany(HrPayslip::class, 'payroll_run_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function periodLabel(): string
    {
        return date('F Y', mktime(0, 0, 0, (int) $this->period_month, 1, (int) $this->period_year));
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['approved', 'paid'], true);
    }
}
