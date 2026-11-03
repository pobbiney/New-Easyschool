<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBillCreditTransaction extends Model
{

    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    public const SOURCE_OVERPAYMENT = 'overpayment';

    public const SOURCE_PAYMENT_APPLIED = 'payment_applied';

    public const SOURCE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'balance_after',
        'source',
        'bill_payment_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
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
