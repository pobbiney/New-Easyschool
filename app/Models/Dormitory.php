<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dormitory extends Model
{

    protected $fillable = [
        'house_id',
        'name',
        'bed_count',
        'status',
        'created_by',
        'updated_by',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function beds()
    {
        return $this->hasMany(DormitoryBed::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
