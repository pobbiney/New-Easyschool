<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{

    protected $fillable = [
        'student_id',
        'receipt_no',
        'amount',
        'credit_applied',
        'credit_generated',
        'payment_method',
        'reference',
        'payment_channel',
        'gateway_transaction_id',
        'paid_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credit_applied' => 'decimal:2',
        'credit_generated' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function allocations()
    {
        return $this->hasMany(BillPaymentAllocation::class, 'bill_payment_id');
    }
}
