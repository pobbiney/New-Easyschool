<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use App\Models\HrPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    public function index()
    {
        $positions = HrPosition::with('department')->withCount('staff')->orderBy('name')->get();

        return view('hr.positions', [
            'positions' => $positions,
            'departments' => HrDepartment::where('status', 'Active')->orderBy('name')->get(),
            'stats' => [
                'total' => $positions->count(),
                'active' => $positions->where('status', 'Active')->count(),
                'staff' => $positions->sum('staff_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'department_id' => 'nullable|exists:hr_departments,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrPosition::create([
            'name' => trim($request->name),
            'department_id' => $request->department_id ?: null,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Position added successfully.');
    }

    public function show($id)
    {
        return response()->json(HrPosition::findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:hr_positions,id',
            'name' => 'required|string|max:120',
            'department_id' => 'nullable|exists:hr_departments,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $position = HrPosition::findOrFail($request->position_id);
        $position->update([
            'name' => trim($request->name),
            'department_id' => $request->department_id ?: null,
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Position updated successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:hr_positions,id',
        ]);

        $position = HrPosition::withCount('staff')->findOrFail($request->position_id);

        if ($position->isInUse()) {
            return back()->with('message_error', 'Cannot delete '.$position->name.' — it is assigned to '.$position->staff_count.' staff member'.($position->staff_count === 1 ? '' : 's').'.');
        }

        $position->delete();

        return back()->with('message_success', 'Position deleted.');
    }
}
