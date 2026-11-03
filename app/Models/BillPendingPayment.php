<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPendingPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const INSTRUMENTS = ['Cheque', 'Bankers Draft'];

    protected $fillable = [
        'student_id',
        'amount',
        'credit_applied',
        'allocations',
        'payment_method',
        'bank_instrument',
        'reference',
        'receipt_path',
        'paid_at',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'bill_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credit_applied' => 'decimal:2',
        'allocations' => 'array',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function billPayment()
    {
        return $this->belongsTo(BillPayment::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function receiptUrl(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        return asset(ltrim(str_replace('\\', '/', $this->receipt_path), '/'));
    }
}
