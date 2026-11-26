<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{
    public const FINANCIAL_CATEGORY_SCHOOL_FEES = 'school_fees';

    public const FINANCIAL_CATEGORY_FEEDING_FEE = 'feeding_fee';

    protected $fillable = [
        'name',
        'description',
        'status',
        'is_compulsory',
        'financial_category',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_compulsory' => 'boolean',
    ];

    public function setupItems()
    {
        return $this->hasMany(CategoryBillSetupItem::class, 'billing_item_id');
    }

    public function studentBills()
    {
        return $this->hasMany(StudentBill::class, 'billing_item_id');
    }

    public static function financialCategoryOptions(): array
    {
        return [
            self::FINANCIAL_CATEGORY_SCHOOL_FEES => 'School Fees',
            self::FINANCIAL_CATEGORY_FEEDING_FEE => 'Feeding Fee',
        ];
    }

    public function getFinancialCategoryLabelAttribute(): string
    {
        return self::financialCategoryOptions()[$this->financial_category] ?? 'Unknown';
    }
}
