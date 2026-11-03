<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\ClassAttendance;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\GradebookService;
use App\Services\ParentPortal\ParentStudentService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentAcademicsController extends Controller
{
    public function __construct(
        private ParentStudentService $parentStudentService,
        private GradebookService $gradebook,
    ) {}

    public function show(Request $request, Student $student)
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
        $attendanceSummary = null;

        if ($yearId && $termId) {
            $report = $this->gradebook->studentReportCard($child, $yearId, $termId);

            $attendance = ClassAttendance::query()
                ->where('student_id', $child->id)
                ->where('academic_year_id', $yearId)
                ->where('academic_term_id', $termId)
                ->get();

            $attendanceSummary = [
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
                'late' => $attendance->where('status', 'late')->count(),
                'excused' => $attendance->where('status', 'excused')->count(),
                'total_days' => $attendance->count(),
            ];
        }

        return view('parent.academics', [
            'parent' => $parent,
            'student' => $child,
            'children' => $this->parentStudentService->childrenFor($parent),
            'report' => $report,
            'attendanceSummary' => $attendanceSummary,
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
            'school' => SchoolSetting::current(),
        ]);
    }
}
