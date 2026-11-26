<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

class StudentBill extends Model
{
    protected $fillable = [
        'student_id',
        'category_bill_setup_id',
        'billing_item_id',
        'financial_category',
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
        $paidMinor = Money::toMinor((string) $this->amount_paid);
        $balanceMinor = max(Money::toMinor((string) $this->amount_due) - $paidMinor, 0);
        $this->balance = Money::fromMinor($balanceMinor);

        if ($balanceMinor === 0 && $paidMinor > 0) {
            $this->status = 'Paid';
        } elseif ($paidMinor > 0) {
            $this->status = 'Partial';
        } else {
            $this->status = 'Pending';
        }
    }
}
