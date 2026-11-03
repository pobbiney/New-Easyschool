<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRemark extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'academic_term_id',
        'attitude',
        'conduct',
        'interest',
        'class_teacher_remark',
        'headteacher_signature',
        'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }
}
