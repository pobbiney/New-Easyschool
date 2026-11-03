<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{

    protected $fillable = [
        'name',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class, 'academic_term_id');
    }
}
