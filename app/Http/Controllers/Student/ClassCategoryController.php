<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassCategoryController extends Controller
{
    public function index()
    {
        $categories = ClassCategory::withCount('schoolClasses')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $categories->count(),
            'active' => $categories->where('status', 'Active')->count(),
            'inactive' => $categories->where('status', 'Inactive')->count(),
            'classes' => $categories->sum('school_classes_count'),
        ];

        return view('student.class-categories', [
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (ClassCategory::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This class category already exists.');
        }

        ClassCategory::create([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Class category added successfully.');
    }

    public function show($id)
    {
        return response()->json(ClassCategory::findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:class_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category = ClassCategory::findOrFail($request->category_id);

        $nameExists = ClassCategory::where('name', trim($request->name))
            ->where('id', '!=', $category->id)
            ->exists();

        if ($nameExists) {
            return back()->with('message_error', 'This class category already exists.');
        }

        $category->update([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Class category updated successfully.');
    }
}
