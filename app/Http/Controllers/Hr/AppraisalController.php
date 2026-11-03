<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\HrAppraisal;
use App\Models\Staff;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppraisalController extends Controller
{
    public function index()
    {
        $defaults = AcademicPeriodDefaults::forFrontend();

        return view('hr.appraisals', [
            'appraisals' => HrAppraisal::with(['staff', 'appraiser', 'academicYear', 'academicTerm'])->latest()->get(),
            'staffMembers' => Staff::where('status', 'Active')->orderBy('surname')->get(),
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->orderBy('name')->get(),
            'selectedYearId' => $defaults['year_id'],
            'selectedTermId' => $defaults['term_id'],
            'criteria' => HrAppraisal::criteria(),
        ]);
    }

    public function store(Request $request)
    {
        $criteria = array_keys(HrAppraisal::criteria());
        $scoreRules = [];
        foreach ($criteria as $key) {
            $scoreRules['scores.'.$key] = 'required|integer|min:0|max:5';
        }

        $request->validate(array_merge([
            'staff_id' => 'required|exists:staff,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'comments' => 'nullable|string|max:2000',
            'status' => 'required|in:draft,final',
        ], $scoreRules));

        $year = AcademicYear::findOrFail($request->academic_year_id);
        $term = AcademicTerm::findOrFail($request->academic_term_id);

        if ($year->status !== 'Active' || $term->status !== 'Active') {
            return back()->withInput()->with('message_error', 'Choose an active academic year and term.');
        }

        $exists = HrAppraisal::query()
            ->where('staff_id', $request->staff_id)
            ->where('academic_year_id', $year->id)
            ->where('academic_term_id', $term->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('message_error', 'An appraisal already exists for this staff member in '.$term->name.' · '.$year->name.'.');
        }

        $scores = $request->input('scores', []);
        $overall = round(collect($scores)->avg(), 2);

        HrAppraisal::create([
            'staff_id' => $request->staff_id,
            'academic_year_id' => $year->id,
            'academic_term_id' => $term->id,
            'period_label' => $term->name.' · '.$year->name,
            'scores' => $scores,
            'overall' => $overall,
            'comments' => trim((string) $request->comments) ?: null,
            'status' => $request->status,
            'appraised_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Appraisal saved.');
    }

    public function show($id)
    {
        return view('hr.appraisal-show', [
            'appraisal' => HrAppraisal::with(['staff.department', 'appraiser', 'academicYear', 'academicTerm'])->findOrFail($id),
            'criteria' => HrAppraisal::criteria(),
        ]);
    }
}
