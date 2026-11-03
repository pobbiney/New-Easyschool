<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrStaffDeduction extends Model
{
    protected $fillable = ['staff_id', 'deduction_type_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function deductionType()
    {
        return $this->belongsTo(HrDeductionType::class, 'deduction_type_id');
    }
}
