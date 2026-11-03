<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicAssessment;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\ClassCourseAssessmentMark;
use App\Models\Course;
use App\Models\CourseTeachingAssignment;
use App\Models\GradingScheme;
use App\Models\SchoolClass;
use App\Services\AssessmentMarksService;
use App\Services\GradingService;
use App\Services\TeacherAccessService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller implements HasMiddleware
{
    public function __construct(
        private TeacherAccessService $teacherAccess,
        private GradingService $grading,
        private AssessmentMarksService $assessmentMarks
    ) {}

    public static function middleware(): array
    {
        return ['staff.auth', 'teacher'];
    }

    public function hub(Request $request)
    {
        return $this->renderHub($request, 'pending');
    }

    public function records(Request $request)
    {
        return $this->renderHub($request, 'records');
    }

    public function classIndex(Request $request, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, null, 'pending');
    }

    public function courseIndex(Request $request, Course $course, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, $course, 'pending');
    }

    public function classRecords(Request $request, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, null, 'records');
    }

    public function courseRecords(Request $request, Course $course, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, $course, 'records');
    }

    private function renderHub(Request $request, string $scope)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId)->load('category');
        $subjectAssignments = $this->teacherAccess->subjectAssignments($staffId, $yearId, $termId);
        $subjectAssignments->loadMissing(['schoolClass.category', 'course']);

        $assessments = $this->teacherAssessmentsQuery($request, $scope)->get();

        $publishedCount = $assessments->where('status', 'published')->count();
        $draftCount = $assessments->where('status', 'draft')->count();
        $scoredEntries = $assessments->sum(fn ($a) => $a->scores->whereNotNull('score')->count());

        $pendingCount = $scope === 'pending'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'pending')->count();

        $recordsCount = $scope === 'records'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'records')->count();

        $view = $scope === 'records' ? 'teacher.assessment-records' : 'teacher.assessments-hub';

        return view($view, [
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'assessments' => $assessments,
            'assessmentTypes' => $this->legendAssessmentTypes(),
            ...$this->assessmentPageData($request),
            'stats' => [
                'total' => $assessments->count(),
                'published' => $publishedCount,
                'draft' => $draftCount,
                'homeroom_slots' => $homeroomClasses->count(),
                'subject_slots' => $subjectAssignments->count(),
                'scores_entered' => $scoredEntries,
                'pending' => $pendingCount,
                'records' => $recordsCount,
            ],
        ]);
    }

    private function renderIndex(Request $request, SchoolClass $class, ?Course $course = null, string $scope = 'pending')
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $this->teacherAccess->assertCanAccessClass($staffId, $class->id, $course?->id, $request);
        $class->loadMissing('category');

        $assessments = $this->teacherAssessmentsQuery($request, $scope, $class->id, $course?->id)
            ->orderByDesc('assessment_date')
            ->get();

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId)->load('category');
        $subjectAssignments = $this->teacherAccess->subjectAssignments($staffId, $yearId, $termId);
        $subjectAssignments->loadMissing(['schoolClass.category', 'course']);

        $pendingCount = $scope === 'pending'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'pending', $class->id, $course?->id)->count();

        $recordsCount = $scope === 'records'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'records', $class->id, $course?->id)->count();

        return view('teacher.assessments', [
            'assessments' => $assessments,
            'schoolClass' => $class,
            'course' => $course,
            ...$this->assessmentPageData($request),
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'assessmentTypes' => $this->assessmentMarks->typesForClass($class),
            'defaultClassId' => $class->id,
            'defaultCourseId' => $course?->id,
            'lockClass' => true,
            'scope' => $scope,
            'stats' => [
                'pending' => $pendingCount,
                'records' => $recordsCount,
            ],
        ]);
    }

    private function teacherAssessmentsQuery(Request $request, string $scope, ?int $classId = null, ?int $courseId = null)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $query = AcademicAssessment::query()
            ->with(['schoolClass', 'course', 'scores', 'assessmentType', 'academicYear', 'academicTerm'])
            ->where('staff_id', $staffId)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
            ->when($classId, fn ($q) => $q->where('school_class_id', $classId))
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId));

        if ($scope === 'records') {
            $query->withRecordedScores();
        } else {
            $query->withoutRecordedScores();
        }

        return $query->orderByDesc('created_at');
    }

    public function store(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();

        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'assessment_date' => 'nullable|date',
            'school_class_id' => 'required|exists:school_classes,id',
            'course_id' => 'required|exists:courses,id',
            'status' => 'required|in:'.implode(',', AcademicAssessment::STATUSES),
        ]);

        $courseId = (int) $validated['course_id'];
        $classId = (int) $validated['school_class_id'];
        $this->teacherAccess->assertCanAccessClass($staffId, $classId, $courseId, $request);

        if (! $this->teacherAccess->ownsSubjectAssignment($staffId, $courseId, $classId, AcademicPeriodDefaults::yearId($request), AcademicPeriodDefaults::termId($request))
            && ! $this->teacherAccess->ownsHomeroomClass($staffId, $classId)) {
            return back()->with('message_error', 'You are not assigned to teach this course in this class.');
        }

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $class = SchoolClass::findOrFail($classId);
        $type = $this->assessmentMarks->resolveTypeForClass($class, $validated['type']);

        if (! $type) {
            return back()->with('message_error', 'That assessment type is not available for this class category.');
        }

        $mark = $this->assessmentMarks->markForType($classId, $courseId, $type->id, $yearId, $termId);

        if (! $mark) {
            return back()->with(
                'message_error',
                'Set the total marks for '.$type->name.' in '.$class->name.' before creating this assessment.'
            );
        }

        $used = $this->assessmentMarks->usedCount($classId, $courseId, $type->slug, $yearId, $termId);

        if ($used >= (int) $type->max_number) {
            return back()->with(
                'message_error',
                'You can only create '.$type->max_number.' '.$type->name.' assessment'.((int) $type->max_number === 1 ? '' : 's').' for this subject this term.'
            );
        }

        AcademicAssessment::create([
            'type' => $type->slug,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'assessment_date' => $validated['assessment_date'] ?? null,
            'school_class_id' => $classId,
            'course_id' => $courseId,
            'max_score' => $mark->total_score,
            'status' => $validated['status'],
            'academic_year_id' => $yearId,
            'academic_term_id' => $termId,
            'staff_id' => $staffId,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Assessment created successfully.');
    }

    public function setupOptions(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();

        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $classId = (int) $validated['school_class_id'];
        $courseId = (int) $validated['course_id'];
        $this->teacherAccess->assertCanAccessClass($staffId, $classId, $courseId, $request);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return response()->json([
                'types' => [],
                'message' => 'Set a default academic year and term in school settings.',
            ]);
        }

        $class = SchoolClass::findOrFail($classId);

        return response()->json([
            'types' => $this->assessmentMarks->setupOptions($class, $courseId, $yearId, $termId),
            'marks_url' => route('teacher-course-assessment-marks', [
                'course' => $courseId,
                'class' => $classId,
                'academic_year_id' => $yearId,
                'academic_term_id' => $termId,
            ]),
        ]);
    }

    public function classMarks(Request $request, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass($staffId, $class->id, null, $request);
        $class->loadMissing(['category.courses']);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $courses = $this->markableCourses($staffId, $class, $yearId, $termId);
        $types = $this->assessmentMarks->typesForClass($class);

        $courseRows = $courses->map(function (Course $course) use ($class, $yearId, $termId, $types) {
            $marks = $this->assessmentMarks->marksFor($class->id, $course->id, $yearId, $termId);
            $setCount = $types->filter(fn (AssessmentType $type) => $marks->has($type->id))->count();

            return [
                'course' => $course,
                'set_count' => $setCount,
                'type_count' => $types->count(),
                'complete' => $types->isNotEmpty() && $setCount === $types->count(),
            ];
        });

        return view('teacher.assessment-marks-class', [
            'schoolClass' => $class,
            'courseRows' => $courseRows,
            ...$this->assessmentPageData($request),
        ]);
    }

    public function courseMarks(Request $request, Course $course, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass($staffId, $class->id, $course->id, $request);
        $class->loadMissing('category');

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $types = $this->assessmentMarks->typesForClass($class);
        $marks = $this->assessmentMarks->marksFor($class->id, $course->id, $yearId, $termId);

        $rows = $types->map(function (AssessmentType $type) use ($class, $course, $yearId, $termId, $marks) {
            $mark = $marks->get($type->id);

            return [
                'type' => $type,
                'total_score' => $mark?->total_score,
                'used_count' => $this->assessmentMarks->usedCount($class->id, $course->id, $type->slug, $yearId, $termId),
            ];
        });

        return view('teacher.assessment-marks', [
            'schoolClass' => $class,
            'course' => $course,
            'rows' => $rows,
            ...$this->assessmentPageData($request),
        ]);
    }

    public function saveCourseMarks(Request $request, Course $course, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass($staffId, $class->id, $course->id, $request);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        $validated = $request->validate([
            'marks' => 'required|array',
            'marks.*.assessment_type_id' => 'required|exists:assessment_types,id',
            'marks.*.total_score' => 'nullable|numeric|min:1|max:9999',
        ]);

        $allowedTypeIds = $this->assessmentMarks->typesForClass($class)->pluck('id')->all();

        DB::transaction(function () use ($validated, $allowedTypeIds, $class, $course, $yearId, $termId, $staffId) {
            foreach ($validated['marks'] as $row) {
                $typeId = (int) $row['assessment_type_id'];

                if (! in_array($typeId, $allowedTypeIds, true)) {
                    continue;
                }

                $score = $row['total_score'] ?? null;

                if ($score === null || $score === '') {
                    ClassCourseAssessmentMark::query()
                        ->where('school_class_id', $class->id)
                        ->where('course_id', $course->id)
                        ->where('assessment_type_id', $typeId)
                        ->where('academic_year_id', $yearId)
                        ->where('academic_term_id', $termId)
                        ->delete();

                    continue;
                }

                $mark = ClassCourseAssessmentMark::query()->firstOrNew([
                    'school_class_id' => $class->id,
                    'course_id' => $course->id,
                    'assessment_type_id' => $typeId,
                    'academic_year_id' => $yearId,
                    'academic_term_id' => $termId,
                ]);

                if (! $mark->exists) {
                    $mark->created_by = Auth::id();
                }

                $mark->total_score = round((float) $score, 2);
                $mark->staff_id = $staffId;
                $mark->updated_by = Auth::id();
                $mark->save();
            }
        });

        return back()->with('message_success', 'Assessment marks saved for this subject and term.');
    }

    public function scores(AcademicAssessment $assessment)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass(
            $staffId,
            $assessment->school_class_id,
            $assessment->course_id,
            request()
        );

        $students = $this->teacherAccess->studentsForClass($assessment->school_class_id);
        $existingScores = $assessment->scores()->get()->keyBy('student_id');

        return view('teacher.assessment-scores', [
            'assessment' => $assessment->load(['schoolClass', 'course', 'assessmentType', 'academicYear', 'academicTerm']),
            'students' => $students,
            'existingScores' => $existingScores,
            'gradingSchemes' => GradingScheme::orderByDesc('min_percentage')->get(),
            'period' => AcademicPeriodDefaults::forFrontend(request()),
            'stats' => [
                'total' => $students->count(),
                'scored' => $existingScores->filter(fn ($s) => $s->score !== null)->count(),
                'pending' => $students->count() - $existingScores->filter(fn ($s) => $s->score !== null)->count(),
            ],
        ]);
    }

    public function saveScores(Request $request, AcademicAssessment $assessment)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass(
            $staffId,
            $assessment->school_class_id,
            $assessment->course_id,
            $request
        );

        $request->validate([
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
            'scores.*.score' => 'nullable|numeric|min:0|max:'.$assessment->max_score,
            'scores.*.remarks' => 'nullable|string|max:500',
        ], [
            'scores.*.score.min' => 'Score cannot be negative.',
            'scores.*.score.max' => 'Score cannot exceed the maximum of '.number_format((float) $assessment->max_score, 2).'.',
            'scores.*.score.numeric' => 'Please enter a valid score.',
        ]);

        DB::transaction(function () use ($request, $assessment) {
            foreach ($request->scores as $row) {
                $scoreValue = ($row['score'] === '' || $row['score'] === null) ? null : (float) $row['score'];
                $graded = $this->grading->gradeScore($scoreValue, (float) $assessment->max_score);

                AssessmentScore::updateOrCreate(
                    [
                        'academic_assessment_id' => $assessment->id,
                        'student_id' => $row['student_id'],
                    ],
                    [
                        'score' => $scoreValue,
                        'letter_grade' => $graded['letter_grade'],
                        'remarks' => trim($row['remarks'] ?? '') ?: null,
                        'graded_by' => Auth::id(),
                        'graded_at' => $scoreValue !== null ? now() : null,
                    ]
                );
            }
        });

        return back()->with('message_success', 'Scores saved successfully.');
    }

    public function destroy(AcademicAssessment $assessment)
    {
        $staffId = $this->teacherAccess->staffId();

        if ((int) $assessment->staff_id !== (int) $staffId) {
            abort(403, 'You can only delete assessments you created.');
        }

        $this->teacherAccess->assertCanAccessClass(
            $staffId,
            $assessment->school_class_id,
            $assessment->course_id,
            request()
        );

        if ($assessment->hasRecordedScores()) {
            return back()->with('message_error', 'This assessment cannot be deleted because marks have already been entered.');
        }

        $assessment->delete();

        return back()->with('message_success', 'Assessment deleted successfully.');
    }

    private function legendAssessmentTypes()
    {
        return AssessmentType::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->unique('slug')
            ->values();
    }

    private function markableCourses(int $staffId, SchoolClass $class, int $yearId, int $termId)
    {
        if ($this->teacherAccess->ownsHomeroomClass($staffId, $class->id)) {
            $fromAssignments = CourseTeachingAssignment::query()
                ->with('course')
                ->where('school_class_id', $class->id)
                ->when($yearId, fn ($query) => $query->where(function ($inner) use ($yearId) {
                    $inner->where('academic_year_id', $yearId)->orWhereNull('academic_year_id');
                }))
                ->when($termId, fn ($query) => $query->where(function ($inner) use ($termId) {
                    $inner->where('academic_term_id', $termId)->orWhereNull('academic_term_id');
                }))
                ->get()
                ->pluck('course')
                ->filter();

            $fromCategory = $class->category
                ? $class->category->courses()->orderBy('name')->get()
                : collect();

            return $fromAssignments
                ->concat($fromCategory)
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        return $this->teacherAccess
            ->subjectAssignments($staffId, $yearId, $termId)
            ->where('school_class_id', $class->id)
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    private function assessmentPageData(Request $request): array
    {
        return [
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
        ];
    }
}
