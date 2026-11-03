<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeaveBalance extends Model
{
    protected $fillable = ['staff_id', 'leave_type_id', 'year', 'entitled', 'taken'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(HrLeaveType::class, 'leave_type_id');
    }

    public function remaining(): int
    {
        return max(0, (int) $this->entitled - (int) $this->taken);
    }
}
