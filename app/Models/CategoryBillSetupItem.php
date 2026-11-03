<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryBillSetupItem extends Model
{
    protected $fillable = [
        'category_bill_setup_id',
        'billing_item_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function setup()
    {
        return $this->belongsTo(CategoryBillSetup::class, 'category_bill_setup_id');
    }

    public function billingItem()
    {
        return $this->belongsTo(BillingItem::class, 'billing_item_id');
    }
}
