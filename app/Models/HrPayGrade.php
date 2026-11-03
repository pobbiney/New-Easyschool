<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayGrade extends Model
{

    protected $fillable = ['name', 'basic_salary', 'status', 'created_by', 'updated_by'];

    protected $casts = [
        'basic_salary' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'pay_grade_id');
    }
}
