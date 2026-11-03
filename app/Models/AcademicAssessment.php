<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicAssessment extends Model
{

    public const STATUSES = ['draft', 'published'];

    protected $fillable = [
        'type',
        'title',
        'description',
        'due_date',
        'assessment_date',
        'school_class_id',
        'course_id',
        'academic_year_id',
        'academic_term_id',
        'staff_id',
        'max_score',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assessment_date' => 'date',
        'max_score' => 'decimal:2',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
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

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class, 'academic_assessment_id');
    }

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class, 'type', 'slug');
    }

    public function typeLabel(): string
    {
        $type = $this->resolvedAssessmentType();

        if ($type) {
            return $type->name;
        }

        return ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function resolvedAssessmentType(): ?AssessmentType
    {
        $this->loadMissing('schoolClass');

        $categoryId = $this->schoolClass?->class_category_id;

        if ($this->relationLoaded('assessmentType') && $this->assessmentType) {
            if (! $categoryId || (int) $this->assessmentType->class_category_id === (int) $categoryId) {
                return $this->assessmentType;
            }
        }

        return AssessmentType::query()
            ->where('slug', $this->type)
            ->when($categoryId, fn ($query) => $query->where('class_category_id', $categoryId))
            ->first();
    }

    public function hasRecordedScores(): bool
    {
        if ($this->relationLoaded('scores')) {
            return $this->scores->contains(fn (AssessmentScore $score) => $score->score !== null);
        }

        return $this->scores()->whereNotNull('score')->exists();
    }

    public function scopeWithRecordedScores($query)
    {
        return $query->whereHas('scores', fn ($q) => $q->whereNotNull('score'));
    }

    public function scopeWithoutRecordedScores($query)
    {
        return $query->whereDoesntHave('scores', fn ($q) => $q->whereNotNull('score'));
    }
}
