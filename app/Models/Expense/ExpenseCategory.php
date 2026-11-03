<?php

namespace App\Models\Expense;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
