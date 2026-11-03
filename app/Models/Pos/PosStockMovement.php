<?php

namespace App\Models\Pos;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PosStockMovement extends Model
{
    public const TYPE_SALE = 'sale';

    public const TYPE_RESTOCK = 'restock';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_RETURN = 'return';

    protected $fillable = [
        'pos_product_id',
        'movement_type',
        'quantity_change',
        'qty_before',
        'qty_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'qty_before' => 'integer',
        'qty_after' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(PosProduct::class, 'pos_product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
