<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrDepartment extends Model
{

    protected $fillable = ['name', 'code', 'status', 'created_by', 'updated_by'];

    public function positions()
    {
        return $this->hasMany(HrPosition::class, 'department_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'department_id');
    }
}
