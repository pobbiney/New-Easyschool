<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosProduct;
use App\Models\Pos\PosStockMovement;
use App\Services\Pos\PosStockService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PosStockController extends Controller
{
    public function __construct(
        private PosStockService $stockService,
    ) {}

    public function index()
    {
        $products = PosProduct::with('category')->where('status', 'Active')->orderBy('name')->get();
        $lowStockProducts = $this->stockService->getLowStockProducts();
        $movements = PosStockMovement::with(['product.category', 'creator'])
            ->latest('id')
            ->limit(100)
            ->get();

        $outOfStockCount = $products->where('stock_qty', 0)->count();

        return view('pos.stock', [
            'lowStockProducts' => $lowStockProducts,
            'products' => $products,
            'movements' => $movements,
            'stats' => [
                'active_products' => $products->count(),
                'total_units' => (int) $products->sum('stock_qty'),
                'low_stock' => $lowStockProducts->count(),
                'out_of_stock' => $outOfStockCount,
                'movements_today' => PosStockMovement::whereDate('created_at', today())->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pos_product_id' => 'required|exists:pos_products,id',
            'movement_type' => 'required|in:restock,adjustment,return',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = PosProduct::findOrFail($request->pos_product_id);

        try {
            $this->stockService->adjustStock(
                product: $product,
                movementType: $request->movement_type,
                quantity: (int) $request->quantity,
                notes: trim($request->notes ?? '') ?: null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('message_error', $e->getMessage());
        }

        return back()->with('message_success', 'Stock updated successfully.');
    }
}
