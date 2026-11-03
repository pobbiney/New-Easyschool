<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryBillSetup extends Model
{

    protected $fillable = [
        'class_category_id',
        'academic_year_id',
        'academic_term_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public function classCategory()
    {
        return $this->belongsTo(ClassCategory::class, 'class_category_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function items()
    {
        return $this->hasMany(CategoryBillSetupItem::class, 'category_bill_setup_id');
    }

    public function studentBills()
    {
        return $this->hasMany(StudentBill::class, 'category_bill_setup_id');
    }
}
