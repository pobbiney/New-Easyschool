<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPosition extends Model
{

    protected $fillable = ['department_id', 'name', 'status', 'created_by', 'updated_by'];

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'position_id');
    }

    public function isInUse(): bool
    {
        if (array_key_exists('staff_count', $this->attributes)) {
            return (int) $this->attributes['staff_count'] > 0;
        }

        return $this->staff()->exists();
    }

    public static function teacher(): self
    {
        $existing = static::query()->where('name', 'Teacher')->first();
        if ($existing) {
            return $existing;
        }

        $departmentId = HrDepartment::query()
            ->where('code', 'TEACH')
            ->orWhere('name', 'Teaching')
            ->value('id');

        return static::query()->create([
            'name' => 'Teacher',
            'department_id' => $departmentId,
            'status' => 'Active',
        ]);
    }
}
