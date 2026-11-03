<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class PosProduct extends Model
{

    protected $fillable = [
        'pos_category_id',
        'name',
        'sku',
        'price',
        'cost_price',
        'stock_qty',
        'low_stock_threshold',
        'description',
        'image_path',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_qty' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(PosCategory::class, 'pos_category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(PosStockMovement::class, 'pos_product_id');
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_qty <= 0;
    }

    public function imageUrl(): string
    {
        if (! empty($this->image_path) && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }

        return asset('assets/images/pos-product-placeholder.svg');
    }
}
