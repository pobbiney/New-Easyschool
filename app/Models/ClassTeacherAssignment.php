<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTeacherAssignment extends Model
{
    protected $fillable = [
        'school_class_id',
        'staff_id',
        'created_by',
        'updated_by',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
