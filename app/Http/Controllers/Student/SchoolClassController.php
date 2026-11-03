<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassCategory;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolClassController extends Controller
{
    public function index()
    {
        $schoolClasses = SchoolClass::with('category')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $schoolClasses->count(),
            'active' => $schoolClasses->where('status', 'Active')->count(),
            'inactive' => $schoolClasses->where('status', 'Inactive')->count(),
            'categories' => ClassCategory::where('status', 'Active')->count(),
        ];

        return view('student.school-classes', [
            'schoolClasses' => $schoolClasses,
            'classCategories' => ClassCategory::orderBy('name')->get(),
            'activeClassCategories' => ClassCategory::where('status', 'Active')->orderBy('name')->get(),
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'class_category_id' => 'required|exists:class_categories,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (SchoolClass::where('name', trim($request->name))->count() > 0) {
            return back()->with('message_error', 'This class already exists.');
        }

        SchoolClass::create([
            'name' => trim($request->name),
            'class_category_id' => $request->class_category_id,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Class added successfully.');
    }

    public function show($id)
    {
        $class = SchoolClass::with('category')->findOrFail($id);

        return response()->json($class);
    }

    public function update(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'name' => 'required|string|max:100',
            'class_category_id' => 'required|exists:class_categories,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);

        $nameExists = SchoolClass::where('name', trim($request->name))
            ->where('id', '!=', $request->class_id)
            ->count();

        if ($nameExists > 0) {
            return back()->with('message_error', 'This class already exists.');
        }

        $class->update([
            'name' => trim($request->name),
            'class_category_id' => $request->class_category_id,
            'status' => trim($request->status),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Class updated successfully.');
    }
}
