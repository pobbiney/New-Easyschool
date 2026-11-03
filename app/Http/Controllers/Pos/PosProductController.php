<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosCategory;
use App\Models\Pos\PosProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosProductController extends Controller
{
    public function index()
    {
        $products = PosProduct::with('category')->orderBy('name')->get();

        return view('pos.products', [
            'products' => $products,
            'categories' => PosCategory::where('status', 'Active')->orderBy('name')->get(),
            'stats' => [
                'total' => $products->count(),
                'active' => $products->where('status', 'Active')->count(),
                'low_stock' => $products->filter(fn (PosProduct $p) => $p->isLowStock() && ! $p->isOutOfStock())->count(),
                'out_of_stock' => $products->filter(fn (PosProduct $p) => $p->isOutOfStock())->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pos_category_id' => 'required|exists:pos_categories,id',
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        $sku = trim($request->sku ?? '') ?: $this->generateSku(trim($request->name));

        if (PosProduct::where('sku', $sku)->exists()) {
            return back()->with('message_error', 'This SKU already exists.');
        }

        $imagePath = $request->hasFile('image') ? $this->uploadImage($request->file('image')) : null;

        PosProduct::create([
            'pos_category_id' => $request->pos_category_id,
            'name' => trim($request->name),
            'sku' => $sku,
            'price' => $request->price,
            'cost_price' => $request->cost_price ?: null,
            'stock_qty' => (int) $request->stock_qty,
            'low_stock_threshold' => (int) $request->low_stock_threshold,
            'description' => trim($request->description ?? '') ?: null,
            'image_path' => $imagePath,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Product added successfully.');
    }

    public function show($id)
    {
        $product = PosProduct::with('category')->findOrFail($id);
        $data = $product->toArray();
        $data['image_url'] = $product->imageUrl();

        return response()->json($data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:pos_products,id',
            'pos_category_id' => 'required|exists:pos_categories,id',
            'name' => 'required|string|max:150',
            'sku' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        $product = PosProduct::findOrFail($request->product_id);

        if (PosProduct::where('sku', trim($request->sku))->where('id', '!=', $product->id)->exists()) {
            return back()->with('message_error', 'This SKU already exists.');
        }

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'), $product->image_path);
        }

        $product->update([
            'pos_category_id' => $request->pos_category_id,
            'name' => trim($request->name),
            'sku' => trim($request->sku),
            'price' => $request->price,
            'cost_price' => $request->cost_price ?: null,
            'stock_qty' => (int) $request->stock_qty,
            'low_stock_threshold' => (int) $request->low_stock_threshold,
            'description' => trim($request->description ?? '') ?: null,
            'image_path' => $imagePath,
            'status' => trim($request->status),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Product updated successfully.');
    }

    private function generateSku(string $name): string
    {
        $base = Str::upper(Str::slug($name, '-'));
        $base = Str::limit($base, 20, '');
        $sku = $base ?: 'ITEM';
        $counter = 1;

        while (PosProduct::where('sku', $sku)->exists()) {
            $sku = $base.'-'.$counter;
            $counter++;
        }

        return $sku;
    }

    private function uploadImage($file, ?string $oldPath = null): string
    {
        if (! empty($oldPath) && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $folder = public_path('uploads/pos-products');
        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($folder, $filename);

        return 'uploads/pos-products/'.$filename;
    }
}
