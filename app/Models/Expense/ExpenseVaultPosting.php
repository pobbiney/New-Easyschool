<?php

namespace App\Models\Expense;

use App\Models\SchoolVaultTransaction;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class ExpenseVaultPosting extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_SUPERSEDED = 'Superseded';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'expense_id',
        'version',
        'amount',
        'expense_date',
        'expense_category_id',
        'payee',
        'payment_method',
        'reference',
        'academic_year_id',
        'status',
        'request_token',
        'change_reason',
        'ended_at',
        'ended_by',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $posting) {
            if ($posting->isDirty([
                'expense_id', 'version', 'amount', 'expense_date', 'expense_category_id',
                'payee', 'payment_method', 'reference', 'academic_year_id', 'request_token',
                'created_by', 'created_at',
            ])) {
                throw new LogicException('Posted expense financial snapshots are immutable.');
            }
        });
        static::deleting(fn () => throw new LogicException('Posted expense financial snapshots cannot be deleted.'));
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function vaultTransactions()
    {
        return $this->morphMany(SchoolVaultTransaction::class, 'source');
    }

    public function debit()
    {
        return $this->morphOne(SchoolVaultTransaction::class, 'source')
            ->where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE);
    }
}
