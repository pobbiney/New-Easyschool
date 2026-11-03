<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPromotionLog extends Model
{
    public const TYPE_STANDARD = 'standard';

    public const TYPE_CONDITIONAL = 'conditional';

    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'academic_term_id',
        'promotion_type',
        'aggregate_total_score',
        'promotion_minimum_mark',
        'promoted_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass()
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function promotedBy()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }

    public function typeLabel(): string
    {
        return match ($this->promotion_type) {
            self::TYPE_CONDITIONAL => 'Conditional Promotion',
            default => 'Standard Promotion',
        };
    }

    public function isConditional(): bool
    {
        return $this->promotion_type === self::TYPE_CONDITIONAL;
    }
}
