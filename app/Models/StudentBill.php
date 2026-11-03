<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBill extends Model
{

    protected $fillable = [
        'student_id',
        'category_bill_setup_id',
        'billing_item_id',
        'academic_year_id',
        'academic_term_id',
        'amount_due',
        'amount_paid',
        'balance',
        'status',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function setup()
    {
        return $this->belongsTo(CategoryBillSetup::class, 'category_bill_setup_id');
    }

    public function billingItem()
    {
        return $this->belongsTo(BillingItem::class, 'billing_item_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function allocations()
    {
        return $this->hasMany(BillPaymentAllocation::class, 'student_bill_id');
    }

    public function refreshTotals(): void
    {
        $this->balance = max((float) $this->amount_due - (float) $this->amount_paid, 0);

        if ($this->balance <= 0 && (float) $this->amount_paid > 0) {
            $this->status = 'Paid';
        } elseif ((float) $this->amount_paid > 0) {
            $this->status = 'Partial';
        } else {
            $this->status = 'Pending';
        }
    }
}
