<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DormitoryBed extends Model
{
    protected $fillable = [
        'dormitory_id',
        'bed_label',
        'student_id',
    ];

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
