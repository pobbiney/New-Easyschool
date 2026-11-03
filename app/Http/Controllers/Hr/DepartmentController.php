<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = HrDepartment::withCount(['positions', 'staff'])->orderBy('name')->get();

        return view('hr.departments', [
            'departments' => $departments,
            'stats' => [
                'total' => $departments->count(),
                'active' => $departments->where('status', 'Active')->count(),
                'staff' => $departments->sum('staff_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:30',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (HrDepartment::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This department already exists.');
        }

        HrDepartment::create([
            'name' => trim($request->name),
            'code' => trim((string) $request->code) ?: null,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Department added successfully.');
    }

    public function show($id)
    {
        return response()->json(HrDepartment::findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:hr_departments,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:30',
            'status' => 'required|in:Active,Inactive',
        ]);

        $department = HrDepartment::findOrFail($request->department_id);

        if (HrDepartment::where('name', trim($request->name))->where('id', '!=', $department->id)->exists()) {
            return back()->with('message_error', 'This department already exists.');
        }

        $department->update([
            'name' => trim($request->name),
            'code' => trim((string) $request->code) ?: null,
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Department updated successfully.');
    }
}
