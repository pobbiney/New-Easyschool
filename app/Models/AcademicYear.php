<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{

    protected $fillable = [
        'name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class, 'academic_year_id');
    }
}
