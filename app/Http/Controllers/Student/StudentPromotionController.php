<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Support\AcademicPeriodDefaults;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class StudentPromotionController extends Controller
{
    public function __construct(private PromotionService $promotion) {}

    public function index(Request $request)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $classes = SchoolClass::query()
            ->with('category')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $summaries = $this->promotion->hubSummaries($classes, $yearId, $termId);

        $totals = [
            'classes' => $summaries->count(),
            'students' => $summaries->sum(fn (array $item) => $item['counts']['total']),
            'eligible' => $summaries->sum(fn (array $item) => $item['counts']['eligible']),
            'below' => $summaries->sum(fn (array $item) => $item['counts']['below']),
        ];

        return view('student.student-promotion', [
            'summaries' => $summaries,
            'totals' => $totals,
            ...$this->pageData($request),
        ]);
    }

    public function show(Request $request, SchoolClass $class)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $summary = $this->promotion->classSummary($class, $yearId, $termId);

        return view('student.student-promotion-class', [
            'summary' => $summary,
            ...$this->pageData($request),
        ]);
    }

    public function promote(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'promotion_type' => 'nullable|in:eligible,override',
        ]);

        $result = $this->promotion->promoteStudents(
            $class,
            $validated['student_ids'],
            (int) $validated['academic_year_id'],
            (int) $validated['academic_term_id'],
            ($validated['promotion_type'] ?? 'eligible') === 'override'
        );

        $message = $result['message'];

        if (! empty($result['skipped'])) {
            $message .= ' '.count($result['skipped']).' skipped.';
        }

        if ($request->expectsJson()) {
            return response()->json($result + ['message' => $message]);
        }

        return redirect()
            ->route('student-promotion-class', [
                'class' => $class->id,
                'academic_year_id' => $validated['academic_year_id'],
                'academic_term_id' => $validated['academic_term_id'],
            ])
            ->with($result['promoted'] > 0 ? 'message_success' : 'message_error', $message);
    }

    private function pageData(Request $request): array
    {
        return [
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
        ];
    }
}
