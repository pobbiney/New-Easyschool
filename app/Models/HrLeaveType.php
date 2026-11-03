<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeaveType extends Model
{

    protected $fillable = ['name', 'days_per_year', 'is_paid', 'gender_limit', 'status', 'created_by'];

    protected $casts = [
        'is_paid' => 'boolean',
        'days_per_year' => 'integer',
    ];

    public function requests()
    {
        return $this->hasMany(HrLeaveRequest::class, 'leave_type_id');
    }

    public function balances()
    {
        return $this->hasMany(HrLeaveBalance::class, 'leave_type_id');
    }

    public function isInUse(): bool
    {
        if (array_key_exists('requests_count', $this->attributes) && (int) $this->attributes['requests_count'] > 0) {
            return true;
        }

        if (array_key_exists('used_balances_count', $this->attributes) && (int) $this->attributes['used_balances_count'] > 0) {
            return true;
        }

        return $this->requests()->exists()
            || $this->balances()->where('taken', '>', 0)->exists();
    }
}
