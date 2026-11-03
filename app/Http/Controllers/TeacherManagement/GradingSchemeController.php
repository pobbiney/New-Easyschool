<?php

namespace App\Http\Controllers\TeacherManagement;

use App\Http\Controllers\Controller;
use App\Models\GradingScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradingSchemeController extends Controller
{
    public function index()
    {
        $schemes = GradingScheme::orderByDesc('min_percentage')->get();

        return view('teacher-management.grading-scheme', [
            'schemes' => $schemes,
            'stats' => [
                'total' => $schemes->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'letter_grade' => 'required|string|max:5',
            'remark' => 'nullable|string|max:100',
        ]);

        GradingScheme::create([
            'min_percentage' => $request->min_percentage,
            'max_percentage' => $request->max_percentage,
            'letter_grade' => strtoupper(trim($request->letter_grade)),
            'remark' => trim($request->remark ?? '') ?: null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Grading scheme row added.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'grading_scheme_id' => 'required|exists:grading_schemes,id',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'letter_grade' => 'required|string|max:5',
            'remark' => 'nullable|string|max:100',
        ]);

        $scheme = GradingScheme::findOrFail($request->grading_scheme_id);
        $scheme->update([
            'min_percentage' => $request->min_percentage,
            'max_percentage' => $request->max_percentage,
            'letter_grade' => strtoupper(trim($request->letter_grade)),
            'remark' => trim($request->remark ?? '') ?: null,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Grading scheme updated.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'grading_scheme_id' => 'required|exists:grading_schemes,id',
        ]);

        GradingScheme::where('id', $request->grading_scheme_id)->delete();

        return back()->with('message_success', 'Grading scheme row removed.');
    }
}
