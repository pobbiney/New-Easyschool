<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosCategoryController extends Controller
{
    public function index()
    {
        $categories = PosCategory::withCount('products')
            ->orderBy('name')
            ->get();

        return view('pos.categories', [
            'categories' => $categories,
            'stats' => [
                'total' => $categories->count(),
                'active' => $categories->where('status', 'Active')->count(),
                'inactive' => $categories->where('status', 'Inactive')->count(),
                'products' => $categories->sum('products_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (PosCategory::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This category already exists.');
        }

        PosCategory::create([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Category added successfully.');
    }

    public function show($id)
    {
        return response()->json(PosCategory::findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:pos_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category = PosCategory::findOrFail($request->category_id);

        if (PosCategory::where('name', trim($request->name))->where('id', '!=', $category->id)->exists()) {
            return back()->with('message_error', 'This category already exists.');
        }

        $category->update([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Category updated successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:pos_categories,id',
        ]);

        $category = PosCategory::query()
            ->withCount('products')
            ->findOrFail($request->category_id);

        if ($category->isInUse()) {
            return back()->with(
                'message_error',
                'This category cannot be deleted because it is used by '.$category->products_count.' product'.($category->products_count === 1 ? '' : 's').'.'
            );
        }

        $category->delete();

        return back()->with('message_success', 'Category deleted successfully.');
    }
}
