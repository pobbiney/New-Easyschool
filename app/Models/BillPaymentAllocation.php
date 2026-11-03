<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPaymentAllocation extends Model
{
    protected $fillable = [
        'bill_payment_id',
        'student_bill_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(BillPayment::class, 'bill_payment_id');
    }

    public function studentBill()
    {
        return $this->belongsTo(StudentBill::class, 'student_bill_id');
    }
}
