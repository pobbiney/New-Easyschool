<?php

namespace App\Services\Pos;

use App\Models\Pos\PosProduct;
use App\Models\Pos\PosStockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class PosStockService
{
    public function recordMovement(
        PosProduct $product,
        string $movementType,
        int $quantityChange,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
    ): PosStockMovement {
        $qtyBefore = (int) $product->stock_qty;
        $qtyAfter = $qtyBefore + $quantityChange;

        if ($qtyAfter < 0) {
            throw new InvalidArgumentException('Stock cannot go below zero for '.$product->name.'.');
        }

        $product->update(['stock_qty' => $qtyAfter]);

        return PosStockMovement::create([
            'pos_product_id' => $product->id,
            'movement_type' => $movementType,
            'quantity_change' => $quantityChange,
            'qty_before' => $qtyBefore,
            'qty_after' => $qtyAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    public function adjustStock(
        PosProduct $product,
        string $movementType,
        int $quantity,
        ?string $notes = null,
    ): PosStockMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        $change = match ($movementType) {
            PosStockMovement::TYPE_RESTOCK, PosStockMovement::TYPE_RETURN => $quantity,
            PosStockMovement::TYPE_ADJUSTMENT => -$quantity,
            default => throw new InvalidArgumentException('Invalid movement type.'),
        };

        return $this->recordMovement(
            product: $product,
            movementType: $movementType,
            quantityChange: $change,
            notes: $notes,
        );
    }

    /**
     * @return Collection<int, PosProduct>
     */
    public function getLowStockProducts(int $limit = 50): Collection
    {
        return PosProduct::query()
            ->with('category')
            ->where('status', 'Active')
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->orderBy('stock_qty')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
