<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    protected $fillable = [
        'academic_assessment_id',
        'student_id',
        'score',
        'letter_grade',
        'remarks',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(AcademicAssessment::class, 'academic_assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function percentage(): ?float
    {
        $max = (float) ($this->assessment?->max_score ?? 0);

        if ($max <= 0 || $this->score === null) {
            return null;
        }

        return round(((float) $this->score / $max) * 100, 2);
    }
}
