<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEarningType extends Model
{

    protected $fillable = ['name', 'code', 'method', 'default_amount', 'is_taxable', 'status'];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];
}
