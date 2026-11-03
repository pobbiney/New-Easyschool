<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDoc extends Model
{
    protected $fillable = [
        'staff_id',
        'level',
        'year',
        'qualification',
        'institution',
        'document_path',
        'created_by',
    ];

    public static function educationLevels(): array
    {
        return [
            'Basic Education',
            'Junior High',
            'Senior High',
            'Vocational',
            'Tertiary',
            'Certificate',
            'Diploma',
            'Higher National Diploma (HND)',
            "Bachelor's Degree",
            'Postgraduate Diploma',
            "Master's Degree",
            'Doctorate (PhD)',
            'Professional Qualification',
            'Other',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
