<?php

namespace App\Services\Pos;

use App\Models\Pos\PosProduct;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleItem;
use App\Models\Pos\PosStockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PosSaleService
{
    public function __construct(
        private PosStockService $stockService,
    ) {}

    /**
     * @param  array<int, array{product_id:int, quantity:int}>  $cartItems
     */
    public function processSale(array $payload, array $cartItems): PosSale
    {
        if (empty($cartItems)) {
            throw new InvalidArgumentException('Add at least one product to the cart.');
        }

        $subtotal = round((float) ($payload['subtotal'] ?? 0), 2);
        $discount = round(max((float) ($payload['discount'] ?? 0), 0), 2);
        $total = round(max($subtotal - $discount, 0), 2);

        if ($total <= 0) {
            throw new InvalidArgumentException('Sale total must be greater than zero.');
        }

        return DB::transaction(function () use ($payload, $cartItems, $subtotal, $discount, $total) {
            $sale = PosSale::create([
                'receipt_no' => $this->generateReceiptNumber(),
                'student_id' => $payload['student_id'] ?? null,
                'customer_name' => $payload['customer_name'] ?? null,
                'customer_phone' => $payload['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $payload['payment_method'],
                'payment_reference' => $payload['payment_reference'] ?? null,
                'paystack_transaction_id' => $payload['paystack_transaction_id'] ?? null,
                'paystack_channel' => $payload['paystack_channel'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'sold_at' => $payload['sold_at'] ?? now(),
                'created_by' => Auth::id(),
            ]);

            foreach ($cartItems as $line) {
                $product = PosProduct::query()
                    ->where('id', $line['product_id'])
                    ->where('status', 'Active')
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = (int) $line['quantity'];

                if ($quantity <= 0) {
                    throw new InvalidArgumentException('Invalid quantity for '.$product->name.'.');
                }

                if ($product->stock_qty < $quantity) {
                    throw new InvalidArgumentException('Insufficient stock for '.$product->name.'. Available: '.$product->stock_qty);
                }

                $unitPrice = round((float) $product->price, 2);
                $lineTotal = round($unitPrice * $quantity, 2);

                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'pos_product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $this->stockService->recordMovement(
                    product: $product,
                    movementType: PosStockMovement::TYPE_SALE,
                    quantityChange: -$quantity,
                    referenceType: PosSale::class,
                    referenceId: $sale->id,
                    notes: 'Sale '.$sale->receipt_no,
                );
            }

            return $sale->load(['items', 'student.schoolClass.category', 'cashier']);
        });
    }

    public function generateReceiptNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'POS-'.$year.'-';
        $last = PosSale::query()
            ->where('receipt_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('receipt_no');

        $sequence = 1;

        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param  Collection<int, PosProduct>  $products
     * @param  array<int, array{product_id:int, quantity:int}>  $cartItems
     */
    public function calculateTotals(Collection $products, array $cartItems, float $discount = 0): array
    {
        $subtotal = 0;

        foreach ($cartItems as $line) {
            $product = $products->firstWhere('id', $line['product_id']);

            if (! $product) {
                continue;
            }

            $subtotal += round((float) $product->price * (int) $line['quantity'], 2);
        }

        $discount = round(max($discount, 0), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'total' => round(max($subtotal - $discount, 0), 2),
        ];
    }
}
