<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{

    protected $fillable = [
        'name',
        'description',
        'status',
        'is_compulsory',
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
}
