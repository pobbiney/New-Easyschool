<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\GradebookService;
use App\Services\ParentPortal\ParentStudentService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentReportCardController extends Controller
{
    public function __construct(
        private ParentStudentService $parentStudentService,
        private GradebookService $gradebook,
    ) {}

    public function print(Request $request, Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            abort(422, 'Select an academic year and term.');
        }

        $report = $this->gradebook->studentReportCard($child->load('schoolClass'), $yearId, $termId);

        return view('teacher.print-report-card', [
            'report' => $report,
            'school' => SchoolSetting::current()->fresh(),
            'printedAt' => now(),
            'period' => AcademicPeriodDefaults::forFrontend($request),
        ]);
    }

    public function index(Request $request, Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $child->load('schoolClass');
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $report = null;

        if ($yearId && $termId) {
            $report = $this->gradebook->studentReportCard($child, $yearId, $termId);
        }

        return view('parent.report-card', [
            'parent' => $parent,
            'student' => $child,
            'children' => $this->parentStudentService->childrenFor($parent),
            'report' => $report,
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
            'school' => SchoolSetting::current(),
        ]);
    }
}
