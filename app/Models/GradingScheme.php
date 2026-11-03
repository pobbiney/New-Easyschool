<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScheme extends Model
{

    protected $fillable = [
        'min_percentage',
        'max_percentage',
        'letter_grade',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
    ];
}
