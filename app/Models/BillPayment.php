<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'student_id',
        'financial_category',
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
        'status',
        'voided_at',
        'voided_by',
        'voided_actor',
        'void_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credit_applied' => 'decimal:2',
        'credit_generated' => 'decimal:2',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function allocations()
    {
        return $this->hasMany(BillPaymentAllocation::class, 'bill_payment_id');
    }

    public function schoolVaultTransactions()
    {
        return $this->morphMany(SchoolVaultTransaction::class, 'source');
    }

    public function gatewayTransactions()
    {
        return $this->hasMany(BillPaymentTransaction::class);
    }

    public function pendingBankPayments()
    {
        return $this->hasMany(BillPendingPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }
}
