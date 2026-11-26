<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class SchoolVaultTransaction extends Model
{
    public const UPDATED_AT = null;

    public const DIRECTION_CREDIT = 'credit';

    public const DIRECTION_DEBIT = 'debit';

    public const TYPE_FEEDING_FEE_COLLECTION = 'feeding_fee_collection';

    public const TYPE_SCHOOL_OPERATIONAL_EXPENSE = 'school_operational_expense';

    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_AUTHORIZED_ADJUSTMENT = 'authorized_adjustment';

    public const TYPE_REVERSAL = 'reversal';

    protected $guarded = ['*'];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Posted School Vault transactions are immutable.'));
        static::deleting(fn () => throw new LogicException('Posted School Vault transactions cannot be deleted.'));
    }

    public function vault()
    {
        return $this->belongsTo(SchoolVault::class, 'school_vault_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function reversedTransaction()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reversal_of_id');
    }
}
