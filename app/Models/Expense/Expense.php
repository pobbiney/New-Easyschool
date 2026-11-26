<?php

namespace App\Models\Expense;

use App\Models\AcademicYear;
use App\Models\SchoolVaultTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_CANCELLED = 'Cancelled';

    public const PAYMENT_METHODS = [
        'Cash',
        'Bank Transfer',
        'Mobile Money',
        'Cheque',
    ];

    protected $fillable = [
        'expense_category_id',
        'expense_date',
        'amount',
        'payee',
        'payment_method',
        'reference',
        'notes',
        'academic_year_id',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schoolVaultTransactions()
    {
        return $this->morphMany(SchoolVaultTransaction::class, 'source');
    }

    public function vaultPostings()
    {
        return $this->hasMany(ExpenseVaultPosting::class);
    }

    public function currentVaultPosting()
    {
        return $this->hasOne(ExpenseVaultPosting::class)
            ->where('status', ExpenseVaultPosting::STATUS_ACTIVE)
            ->latestOfMany('version');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getExpenseNumberAttribute(): string
    {
        return 'EXP-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
