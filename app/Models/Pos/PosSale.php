<?php

namespace App\Models\Pos;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{

    protected $fillable = [
        'receipt_no',
        'student_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'payment_reference',
        'paystack_transaction_id',
        'paystack_channel',
        'notes',
        'sold_at',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function items()
    {
        return $this->hasMany(PosSaleItem::class, 'pos_sale_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function buyerLabel(): string
    {
        if ($this->student) {
            return $this->student->full_name.' ('.$this->student->student_id.')';
        }

        return $this->customer_name ?: 'Walk-in customer';
    }
}
