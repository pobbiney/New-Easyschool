<?php

namespace Database\Seeders;

use App\Models\Pos\PosCategory;
use App\Models\Pos\PosProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class PosDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (PosCategory::query()->exists()) {
            return;
        }

        $userId = Auth::id() ?: 1;

        $categories = [
            'Uniforms' => [
                ['name' => 'JHS Uniform Shirt', 'sku' => 'UNI-JHS-SHT', 'price' => 85.00, 'stock_qty' => 40, 'low_stock_threshold' => 10],
                ['name' => 'JHS Uniform Trousers', 'sku' => 'UNI-JHS-TRS', 'price' => 95.00, 'stock_qty' => 35, 'low_stock_threshold' => 10],
            ],
            'Books' => [
                ['name' => 'Mathematics Textbook', 'sku' => 'BK-MATH-01', 'price' => 45.00, 'stock_qty' => 60, 'low_stock_threshold' => 15],
                ['name' => 'English Reader', 'sku' => 'BK-ENG-01', 'price' => 35.00, 'stock_qty' => 55, 'low_stock_threshold' => 15],
            ],
            'Stationery' => [
                ['name' => 'Exercise Book (Pack of 5)', 'sku' => 'ST-EXBK-5', 'price' => 12.00, 'stock_qty' => 120, 'low_stock_threshold' => 25],
                ['name' => 'Ballpoint Pen (Pack of 10)', 'sku' => 'ST-PEN-10', 'price' => 8.00, 'stock_qty' => 3, 'low_stock_threshold' => 10],
            ],
            'Souvenirs' => [
                ['name' => 'School Branded Cap', 'sku' => 'SV-CAP-01', 'price' => 25.00, 'stock_qty' => 20, 'low_stock_threshold' => 5],
                ['name' => 'School Branded Mug', 'sku' => 'SV-MUG-01', 'price' => 30.00, 'stock_qty' => 0, 'low_stock_threshold' => 5],
            ],
        ];

        foreach ($categories as $categoryName => $products) {
            $category = PosCategory::create([
                'name' => $categoryName,
                'description' => $categoryName.' sold at the school shop.',
                'status' => 'Active',
                'created_by' => $userId,
            ]);

            foreach ($products as $product) {
                PosProduct::create([
                    'pos_category_id' => $category->id,
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'price' => $product['price'],
                    'cost_price' => round($product['price'] * 0.65, 2),
                    'stock_qty' => $product['stock_qty'],
                    'low_stock_threshold' => $product['low_stock_threshold'],
                    'description' => null,
                    'status' => 'Active',
                    'created_by' => $userId,
                ]);
            }
        }
    }
}
