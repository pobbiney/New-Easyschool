<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    protected $fillable = [
        'student_id',
        'academic_year',
        'academic_year_id',
        'academic_term_id',
        'class_name',
        'school_class_id',
        'last_promotion_from_class_id',
        'last_promotion_type',
        'last_promoted_at',
        'section',
        'roll_number',
        'firstname',
        'othername',
        'surname',
        'category',
        'gender',
        'dob',
        'phone',
        'email',
        'picture',
        'father_name',
        'father_phone',
        'father_occupation',
        'father_photo',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'mother_photo',
        'guardian_type',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'guardian_occupation',
        'guardian_address',
        'guardian_photo',
        'blood_group',
        'height',
        'weight',
        'has_nhis',
        'nhis_number',
        'nhis_card_name',
        'current_address',
        'previous_school_name',
        'notes',
        'house_id',
        'dormitory_id',
        'bed_id',
        'status',
        'credit_balance',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_balance' => 'decimal:2',
        'last_promoted_at' => 'datetime',
    ];

    public function docs()
    {
        return $this->hasMany(StudentDoc::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function lastPromotionFromClass()
    {
        return $this->belongsTo(SchoolClass::class, 'last_promotion_from_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function studentBills()
    {
        return $this->hasMany(StudentBill::class);
    }

    public function billPayments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(StudentBillCreditTransaction::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    public function bed()
    {
        return $this->belongsTo(DormitoryBed::class, 'bed_id');
    }

    public function getFullNameAttribute()
    {
        $name = trim($this->firstname . ' ' . $this->othername . ' ' . $this->surname);
        return preg_replace('/\s+/', ' ', $name);
    }
}
