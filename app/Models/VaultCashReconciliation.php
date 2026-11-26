<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class VaultCashReconciliation extends Model
{
    protected $fillable = [
        'school_vault_id',
        'reconciliation_date',
        'cash_collections',
        'cash_expenditure',
        'actual_cash_counted',
        'notes',
        'recorded_by',
        'reconciled_at',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'cash_collections' => 'decimal:2',
        'cash_expenditure' => 'decimal:2',
        'actual_cash_counted' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Completed cash reconciliations are immutable.'));
        static::deleting(fn () => throw new LogicException('Completed cash reconciliations cannot be deleted.'));
    }

    public function vault()
    {
        return $this->belongsTo(SchoolVault::class, 'school_vault_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
