<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeaveRequest extends Model
{
    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'approved_by',
        'reviewed_at',
        'review_note',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(HrLeaveType::class, 'leave_type_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
