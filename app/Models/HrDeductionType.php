<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrDeductionType extends Model
{

    protected $fillable = ['name', 'code', 'method', 'default_amount', 'is_statutory', 'status'];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_statutory' => 'boolean',
    ];
}
