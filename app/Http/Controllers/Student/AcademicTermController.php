<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicTermController extends Controller
{
    public function index()
    {
        $academicTerms = AcademicTerm::orderBy('sort_order')->orderBy('name')->get();

        return view('student.academic-terms', [
            'academicTerms' => $academicTerms,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1|max:99',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (AcademicTerm::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This academic term already exists.');
        }

        $term = new AcademicTerm();
        $term->name = trim($request->name);
        $term->sort_order = (int) $request->sort_order;
        $term->status = trim($request->status);
        $term->created_by = Auth::id();
        $term->save();

        return back()->with('message_success', 'Academic term added successfully.');
    }

    public function show($id)
    {
        $term = AcademicTerm::findOrFail($id);

        return response()->json($term);
    }

    public function update(Request $request)
    {
        $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'name' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1|max:99',
            'status' => 'required|in:Active,Inactive',
        ]);

        $term = AcademicTerm::findOrFail($request->term_id);

        $nameExists = AcademicTerm::where('name', trim($request->name))
            ->where('id', '!=', $request->term_id)
            ->exists();

        if ($nameExists) {
            return back()->with('message_error', 'This academic term already exists.');
        }

        $term->name = trim($request->name);
        $term->sort_order = (int) $request->sort_order;
        $term->status = trim($request->status);
        $term->updated_by = Auth::id();
        $term->save();

        return back()->with('message_success', 'Academic term updated successfully.');
    }
}
