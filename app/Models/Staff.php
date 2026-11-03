<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{

    protected $casts = [
        'appointment_date' => 'date',
        'confirmation_date' => 'date',
        'contract_end_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->title, $this->firstname, $this->othername, $this->surname])->filter()->implode(' '));
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'nationality');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'staff_id', 'id');
    }

    public function assignedClass()
    {
        return $this->hasOne(SchoolClass::class, 'class_teacher_id');
    }

    public function courseTeachingAssignments()
    {
        return $this->hasMany(CourseTeachingAssignment::class, 'staff_id');
    }

    public function qualifications()
    {
        return $this->hasMany(StaffDoc::class, 'staff_id')->orderBy('year');
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function hrPosition()
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }

    public function payGrade()
    {
        return $this->belongsTo(HrPayGrade::class, 'pay_grade_id');
    }

    public function staffEarnings()
    {
        return $this->hasMany(HrStaffEarning::class, 'staff_id');
    }

    public function staffDeductions()
    {
        return $this->hasMany(HrStaffDeduction::class, 'staff_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(HrLeaveRequest::class, 'staff_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(HrStaffAttendance::class, 'staff_id');
    }

    public function payslips()
    {
        return $this->hasMany(HrPayslip::class, 'staff_id');
    }

    public function appraisals()
    {
        return $this->hasMany(HrAppraisal::class, 'staff_id');
    }

    public function resolvedBasicSalary(): float
    {
        if ($this->basic_salary !== null && $this->basic_salary !== '') {
            return (float) $this->basic_salary;
        }

        return (float) ($this->payGrade?->basic_salary ?? 0);
    }
}
