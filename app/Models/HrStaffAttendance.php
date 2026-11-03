<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrStaffAttendance extends Model
{
    protected $table = 'hr_staff_attendance';

    protected $fillable = [
        'staff_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
