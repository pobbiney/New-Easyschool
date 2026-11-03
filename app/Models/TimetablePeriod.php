<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetablePeriod extends Model
{

    protected $fillable = [
        'sort_order',
        'label',
        'start_time',
        'end_time',
        'kind',
        'course_id',
        'duration_minutes',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function isLesson(): bool
    {
        return $this->kind === 'lesson';
    }

    public function minutes(): int
    {
        return max(1, (int) ($this->duration_minutes ?: 50));
    }

    public function timeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5).' – '.substr((string) $this->end_time, 0, 5);
    }
}
