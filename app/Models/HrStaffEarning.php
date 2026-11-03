<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrStaffEarning extends Model
{
    protected $fillable = ['staff_id', 'earning_type_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function earningType()
    {
        return $this->belongsTo(HrEarningType::class, 'earning_type_id');
    }
}
