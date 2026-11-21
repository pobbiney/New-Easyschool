<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreschoolAssessment extends Model
{
    protected $fillable = [
        'student_id',
        'school_class_id',
        'academic_year_id',
        'academic_term_id',
        'staff_id',
        'ratings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ratings' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
