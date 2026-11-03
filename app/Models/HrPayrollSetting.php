<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrPayrollSetting extends Model
{

    protected $fillable = [
        'ssnit_employee_rate',
        'ssnit_employer_rate',
        'ssnit_ceiling',
        'personal_relief',
        'paye_bands',
        'updated_by',
    ];

    protected $casts = [
        'ssnit_employee_rate' => 'decimal:4',
        'ssnit_employer_rate' => 'decimal:4',
        'ssnit_ceiling' => 'decimal:2',
        'personal_relief' => 'decimal:2',
        'paye_bands' => 'array',
    ];

    public static function current(): self
    {
        $settings = static::query()->first();

        if ($settings) {
            return $settings;
        }

        return static::create([
            'ssnit_employee_rate' => 5.5,
            'ssnit_employer_rate' => 13,
            'ssnit_ceiling' => 50000,
            'personal_relief' => 0,
            'paye_bands' => [
                ['up_to' => 490, 'rate' => 0],
                ['up_to' => 600, 'rate' => 5],
                ['up_to' => 730, 'rate' => 10],
                ['up_to' => 3896.67, 'rate' => 17.5],
                ['up_to' => 19896.67, 'rate' => 25],
                ['up_to' => 50416.67, 'rate' => 30],
                ['up_to' => null, 'rate' => 35],
            ],
        ]);
    }
}
