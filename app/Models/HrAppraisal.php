<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrAppraisal extends Model
{
    protected $fillable = [
        'staff_id',
        'academic_year_id',
        'academic_term_id',
        'period_label',
        'scores',
        'overall',
        'comments',
        'status',
        'appraised_by',
    ];

    protected $casts = [
        'scores' => 'array',
        'overall' => 'decimal:2',
    ];

    public static function criteria(): array
    {
        return [
            'punctuality' => 'Punctuality',
            'professionalism' => 'Professionalism',
            'teamwork' => 'Teamwork',
            'job_knowledge' => 'Job knowledge',
            'communication' => 'Communication',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function appraiser()
    {
        return $this->belongsTo(User::class, 'appraised_by');
    }

    public function periodLabel(): string
    {
        $year = $this->academicYear?->name;
        $term = $this->academicTerm?->name;

        if ($year && $term) {
            return $term.' · '.$year;
        }

        return (string) ($this->period_label ?: '—');
    }
}
