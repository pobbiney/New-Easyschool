<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{

    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'class_category_id',
        'status',
        'promotion_minimum_mark',
        'created_by',
        'updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(ClassCategory::class, 'class_category_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_class_id');
    }

    public function classTeacherAssignments()
    {
        return $this->hasMany(ClassTeacherAssignment::class, 'school_class_id');
    }

    public function classTeachers()
    {
        return $this->belongsToMany(Staff::class, 'class_teacher_assignments', 'school_class_id', 'staff_id')
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    public function classTeacherNames(): string
    {
        $names = $this->relationLoaded('classTeachers')
            ? $this->classTeachers->map(fn (Staff $teacher) => $teacher->full_name)->filter()->values()
            : $this->classTeachers()->get()->map(fn (Staff $teacher) => $teacher->full_name)->filter()->values();

        return $names->implode(', ');
    }

    /**
     * Next active class in the same category (natural name order), or null if this is the highest.
     */
    public function nextActiveClass(): ?self
    {
        if (! $this->class_category_id) {
            return null;
        }

        $classes = static::query()
            ->where('status', 'Active')
            ->where('class_category_id', $this->class_category_id)
            ->get()
            ->sort(fn (self $a, self $b) => strnatcasecmp($a->name, $b->name))
            ->values();

        $index = $classes->search(fn (self $class) => $class->id === $this->id);

        if ($index === false) {
            return null;
        }

        return $classes->get($index + 1);
    }

    public function courseTeachingAssignments()
    {
        return $this->hasMany(CourseTeachingAssignment::class, 'school_class_id');
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class, 'school_class_id');
    }

    public function timetables()
    {
        return $this->hasMany(ClassTimetable::class, 'school_class_id');
    }
}
