<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTimetableEntry extends Model
{
    protected $fillable = [
        'class_timetable_id',
        'day',
        'timetable_period_id',
        'course_id',
        'staff_id',
    ];

    protected $casts = [
        'day' => 'integer',
    ];

    public function timetable()
    {
        return $this->belongsTo(ClassTimetable::class, 'class_timetable_id');
    }

    public function period()
    {
        return $this->belongsTo(TimetablePeriod::class, 'timetable_period_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
