<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ClassCourseAssessmentMark extends Model
{
    protected $fillable = [
        'school_class_id',
        'course_id',
        'assessment_type_id',
        'academic_year_id',
        'academic_term_id',
        'total_score',
        'staff_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class, 'assessment_type_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public static function mapFor(int $classId, int $courseId, int $yearId, int $termId): Collection
    {
        return static::query()
            ->where('school_class_id', $classId)
            ->where('course_id', $courseId)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->get()
            ->keyBy('assessment_type_id');
    }
}
