<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Models\Expense\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->get();

        return view('expenses.categories', [
            'categories' => $categories,
            'stats' => [
                'total' => $categories->count(),
                'active' => $categories->where('status', 'Active')->count(),
                'inactive' => $categories->where('status', 'Inactive')->count(),
                'records' => $categories->sum('expenses_count'),
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

        if (ExpenseCategory::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This category already exists.');
        }

        ExpenseCategory::create([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Category added successfully.');
    }

    public function show($id)
    {
        $category = ExpenseCategory::withCount('expenses')->findOrFail($id);

        return response()->json($category);
    }

    public function update(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category = ExpenseCategory::findOrFail($request->category_id);

        if (ExpenseCategory::where('name', trim($request->name))->where('id', '!=', $category->id)->exists()) {
            return back()->with('message_error', 'This category already exists.');
        }

        if ($request->status === 'Inactive' && $category->expenses()->exists()) {
            return back()->with('message_error', 'This category has recorded expenses. Keep it Active, or move those records first.');
        }

        $category->update([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Category updated successfully.');
    }
}
