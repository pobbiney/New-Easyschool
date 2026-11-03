<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\GradebookService;
use App\Services\TeacherAccessService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class GradebookController extends Controller implements HasMiddleware
{
    public function __construct(
        private TeacherAccessService $teacherAccess,
        private GradebookService $gradebook
    ) {}

    public static function middleware(): array
    {
        return ['staff.auth', 'teacher'];
    }

    public function hub(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId)
            ->loadCount(['students' => fn ($query) => $query->where('status', 'Active')]);

        if ($yearId && $termId) {
            $homeroomClasses->each(function (SchoolClass $class) use ($yearId, $termId) {
                $gradebook = $this->gradebook->classGradebook($class->id, $yearId, $termId);
                $gradedRows = $gradebook['term_averages']->filter(fn ($row) => $row['average_percentage'] !== null);

                $class->gradebook_preview = [
                    'subjects' => $gradebook['course_summaries']->count(),
                    'types' => $gradebook['course_summaries']->sum(fn ($summary) => $summary['type_columns']->count()),
                    'tests' => $gradebook['assessments']->count(),
                    'graded' => $gradedRows->count(),
                    'students' => $gradebook['term_averages']->count(),
                    'class_average' => $gradedRows->isNotEmpty()
                        ? round($gradedRows->avg('average_percentage'), 0)
                        : null,
                ];
            });
        }

        return view('teacher.gradebook-hub', [
            'homeroomClasses' => $homeroomClasses,
            ...$this->gradebookPageData($request),
            'stats' => [
                'classes' => $homeroomClasses->count(),
                'students' => $homeroomClasses->sum('students_count'),
            ],
        ]);
    }

    public function index(Request $request, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertHomeroomTeacher($staffId, $class->id);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $data = $this->gradebook->classGradebook($class->id, $yearId, $termId);

        $gradedRows = $data['term_averages']->filter(fn ($row) => $row['average_percentage'] !== null);

        return view('teacher.gradebook', [
            'schoolClass' => $class,
            'gradebook' => $data,
            ...$this->gradebookPageData($request),
            'stats' => [
                'students' => $data['term_averages']->count(),
                'subjects' => $data['course_summaries']->count(),
                'assessments' => $data['assessments']->count(),
                'graded_students' => $gradedRows->count(),
                'class_average' => $gradedRows->isNotEmpty()
                    ? round($gradedRows->avg('average_percentage'), 0)
                    : null,
            ],
        ]);
    }

    public function printReportCard(Request $request, Student $student)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertHomeroomTeacher($staffId, (int) $student->school_class_id);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $report = $this->gradebook->studentReportCard($student->load('schoolClass'), $yearId, $termId);

        return view('teacher.print-report-card', [
            'report' => $report,
            'school' => SchoolSetting::current()->fresh(),
            'printedAt' => now(),
            'period' => AcademicPeriodDefaults::forFrontend($request),
        ]);
    }

    public function printClassReportCards(Request $request, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertHomeroomTeacher($staffId, $class->id);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $students = $this->teacherAccess->studentsForClass($class->id);
        $reports = $students->map(fn (Student $student) => $this->gradebook->studentReportCard($student, $yearId, $termId));

        return view('teacher.print-class-report-cards', [
            'reports' => $reports,
            'className' => $class->name,
            'school' => SchoolSetting::current()->fresh(),
            'printedAt' => now(),
            'period' => AcademicPeriodDefaults::forFrontend($request),
        ]);
    }

    private function gradebookPageData(Request $request): array
    {
        return [
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
        ];
    }
}
