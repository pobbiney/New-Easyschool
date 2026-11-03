<?php

namespace App\Models\Expense;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{

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
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
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
}
