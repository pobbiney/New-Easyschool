<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        return view('student.academic-years', [
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'status' => 'required',
        ]);

        if (AcademicYear::where('name', trim($request->name))->count() > 0) {
            return back()->with('message_error', 'This academic year already exists.');
        }

        $year = new AcademicYear();
        $year->name = trim($request->name);
        $year->status = trim($request->status);
        $year->created_by = Auth::id();
        $year->save();

        return back()->with('message_success', 'Academic year added successfully.');
    }

    public function show($id)
    {
        $year = AcademicYear::findOrFail($id);

        return response()->json($year);
    }

    public function update(Request $request)
    {
        $request->validate([
            'year_id' => 'required',
            'name' => 'required|string|max:100',
            'status' => 'required',
        ]);

        $year = AcademicYear::findOrFail($request->year_id);

        $nameExists = AcademicYear::where('name', trim($request->name))
            ->where('id', '!=', $request->year_id)
            ->count();

        if ($nameExists > 0) {
            return back()->with('message_error', 'This academic year already exists.');
        }

        $year->name = trim($request->name);
        $year->status = trim($request->status);
        $year->updated_by = Auth::id();
        $year->save();

        return back()->with('message_success', 'Academic year updated successfully.');
    }
}
