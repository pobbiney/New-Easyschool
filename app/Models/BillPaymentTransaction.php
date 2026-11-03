<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPaymentTransaction extends Model
{

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'student_id',
        'reference',
        'amount',
        'credit_applied',
        'currency',
        'status',
        'allocations',
        'paystack_transaction_id',
        'paystack_channel',
        'gateway_response',
        'bill_payment_id',
        'created_by',
        'initiated_by',
        'parent_account_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credit_applied' => 'decimal:2',
        'allocations' => 'array',
        'gateway_response' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function billPayment()
    {
        return $this->belongsTo(BillPayment::class);
    }
}
