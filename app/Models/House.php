<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function dormitories()
    {
        return $this->hasMany(Dormitory::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
