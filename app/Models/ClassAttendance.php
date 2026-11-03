<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassAttendance extends Model
{
    public const STATUSES = ['present', 'absent', 'late', 'excused'];

    protected $table = 'class_attendance';

    protected $fillable = [
        'student_id',
        'school_class_id',
        'date',
        'status',
        'academic_year_id',
        'academic_term_id',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }
}
