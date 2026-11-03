<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

    protected $fillable = [
        'parent_id',
        'name',
        'category',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function parent()
    {
        return $this->belongsTo(Course::class, 'parent_id');
    }

    public function subCourses()
    {
        return $this->hasMany(Course::class, 'parent_id')->orderBy('name');
    }

    public function teachingAssignments()
    {
        return $this->hasMany(CourseTeachingAssignment::class, 'course_id');
    }

    public function registrations()
    {
        return $this->hasMany(CourseRegistration::class, 'course_id');
    }

    public function classCategories()
    {
        return $this->belongsToMany(ClassCategory::class, 'class_category_course', 'course_id', 'class_category_id');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isSubCourse(): bool
    {
        return ! is_null($this->parent_id);
    }
}
