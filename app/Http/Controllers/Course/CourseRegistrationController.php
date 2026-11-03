<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\CourseTeachingAssignment;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CourseRegistrationController extends Controller
{
    public function index()
    {
        return view('course-setup.course-registration', [
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'totalSavedRegistrations' => CourseRegistration::count(),
        ]);
    }

    public function courses(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $this->validateActiveFilters($validated);

        return response()->json($this->buildCoursesPayload(
            (int) $validated['school_class_id'],
            (int) $validated['academic_term_id'],
            (int) $validated['academic_year_id']
        ));
    }

    public function registered(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'nullable|exists:school_classes,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        if (empty(array_filter($validated))) {
            return response()->json([
                'message' => 'Select at least one filter to view registrations.',
                'registrations' => [],
                'stats' => ['total' => 0],
            ], 422);
        }

        if (! empty($validated['school_class_id'])) {
            SchoolClass::where('id', $validated['school_class_id'])->where('status', 'Active')->firstOrFail();
        }

        if (! empty($validated['academic_term_id'])) {
            AcademicTerm::where('id', $validated['academic_term_id'])->where('status', 'Active')->firstOrFail();
        }

        if (! empty($validated['academic_year_id'])) {
            AcademicYear::where('id', $validated['academic_year_id'])->where('status', 'Active')->firstOrFail();
        }

        return response()->json($this->buildRegisteredPayload($validated));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $this->validateActiveFilters($validated);

        $course = Course::where('id', $validated['course_id'])->where('status', 'Active')->firstOrFail();

        if (! $this->isAssignableCourse($course)) {
            $message = 'This course cannot be registered.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('message_error', $message);
        }

        if (! $this->courseIsEligibleForClass($course, (int) $validated['school_class_id'])) {
            $message = 'This subject is not linked to the selected class category.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('message_error', $message);
        }

        $existing = CourseRegistration::query()
            ->where('course_id', $course->id)
            ->where('school_class_id', $validated['school_class_id'])
            ->where('academic_term_id', $validated['academic_term_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->first();

        if ($existing) {
            $message = 'This course is already registered for the selected class, term, and year.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'registration_id' => $existing->id,
                ], 422);
            }

            return back()->with('message_error', $message);
        }

        $registration = CourseRegistration::create([
            'course_id' => $course->id,
            'school_class_id' => $validated['school_class_id'],
            'academic_term_id' => $validated['academic_term_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $message = 'Course registered successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'registration_id' => $registration->id,
                'course_id' => $course->id,
            ]);
        }

        return back()->with('message_success', $message);
    }

    public function unregister(Request $request)
    {
        $validated = $request->validate([
            'registration_id' => 'required|exists:course_registrations,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $registration = CourseRegistration::query()
            ->where('id', $validated['registration_id'])
            ->where('school_class_id', $validated['school_class_id'])
            ->where('academic_term_id', $validated['academic_term_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->firstOrFail();

        $courseId = $registration->course_id;
        $registration->delete();

        $message = 'Course registration removed successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'registration_id' => (int) $validated['registration_id'],
                'course_id' => $courseId,
            ]);
        }

        return back()->with('message_success', $message);
    }

    private function validateActiveFilters(array $validated): void
    {
        SchoolClass::where('id', $validated['school_class_id'])->where('status', 'Active')->firstOrFail();
        AcademicTerm::where('id', $validated['academic_term_id'])->where('status', 'Active')->firstOrFail();
        AcademicYear::where('id', $validated['academic_year_id'])->where('status', 'Active')->firstOrFail();
    }

    private function buildCoursesPayload(int $schoolClassId, int $academicTermId, int $academicYearId): array
    {
        $schoolClass = SchoolClass::with('category')->findOrFail($schoolClassId);
        $eligibleCourseIds = $this->eligibleCourseIdsForClass($schoolClassId);

        $assignableCourses = $this->assignableCourses(
            Course::topLevel()
                ->with(['subCourses.parent', 'parent'])
                ->where('status', 'Active')
                ->orderBy('name')
                ->get()
        )->filter(fn (Course $course) => $eligibleCourseIds->contains($course->id));

        $registrations = CourseRegistration::query()
            ->where('school_class_id', $schoolClassId)
            ->where('academic_term_id', $academicTermId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('course_id');

        $courses = $assignableCourses->map(function (Course $course) use ($registrations) {
            $registration = $registrations->get($course->id);

            return [
                'id' => $course->id,
                'name' => $course->name,
                'parent_name' => $course->parent?->name,
                'category' => $course->category,
                'is_sub_course' => $course->isSubCourse(),
                'is_registered' => $registration !== null,
                'registration_id' => $registration?->id,
                'is_eligible' => true,
            ];
        })->values();

        $registeredCount = $courses->where('is_registered', true)->count();

        $payload = [
            'courses' => $courses,
            'stats' => [
                'total' => $courses->count(),
                'registered' => $registeredCount,
                'unregistered' => max($courses->count() - $registeredCount, 0),
            ],
            'has_registrations' => $registeredCount > 0,
            'class_category' => $schoolClass->category?->name,
        ];

        if ($courses->isEmpty()) {
            $payload['message'] = 'No active subjects are available to register for this class.';
        }

        return $payload;
    }

    private function buildRegisteredPayload(array $filters): array
    {
        $registrationModels = CourseRegistration::query()
            ->with(['course.parent', 'schoolClass', 'academicTerm', 'academicYear'])
            ->when($filters['school_class_id'] ?? null, function ($query, $classId) {
                $query->where('school_class_id', $classId);
            })
            ->when($filters['academic_term_id'] ?? null, function ($query, $termId) {
                $query->where('academic_term_id', $termId);
            })
            ->when($filters['academic_year_id'] ?? null, function ($query, $yearId) {
                $query->where('academic_year_id', $yearId);
            })
            ->orderByDesc('created_at')
            ->get();

        $courseIds = $registrationModels->pluck('course_id')->unique()->filter()->values();
        $classIds = $registrationModels->pluck('school_class_id')->unique()->filter()->values();

        $assignments = CourseTeachingAssignment::query()
            ->with('teacher')
            ->whereIn('course_id', $courseIds)
            ->whereIn('school_class_id', $classIds)
            ->get()
            ->keyBy(fn (CourseTeachingAssignment $assignment) => "{$assignment->course_id}:{$assignment->school_class_id}");

        $registrations = $registrationModels->map(function (CourseRegistration $registration) use ($assignments) {
            $course = $registration->course;
            $assignment = $assignments->get("{$registration->course_id}:{$registration->school_class_id}");
            $teacher = $assignment?->teacher;

            return [
                'id' => $registration->id,
                'course_id' => $course?->id,
                'course_name' => $course?->name,
                'parent_name' => $course?->parent?->name,
                'category' => $course?->category,
                'is_sub_course' => $course?->isSubCourse() ?? false,
                'class_name' => $registration->schoolClass?->name,
                'teacher_name' => $teacher?->full_name,
                'teacher_picture' => $teacher?->picture ? asset($teacher->picture) : null,
                'has_teacher' => $assignment !== null,
                'term_name' => $registration->academicTerm?->name,
                'year_name' => $registration->academicYear?->name,
                'registered_at' => $registration->created_at?->format('M j, Y'),
            ];
        })
            ->values();

        return [
            'registrations' => $registrations,
            'stats' => [
                'total' => $registrations->count(),
            ],
        ];
    }

    private function assignableCourses($topLevelCourses)
    {
        return $topLevelCourses->flatMap(function ($course) {
            if ($course->subCourses->where('status', 'Active')->isNotEmpty()) {
                return $course->subCourses->where('status', 'Active');
            }

            return collect([$course]);
        });
    }

    private function isAssignableCourse(Course $course): bool
    {
        if ($course->isSubCourse()) {
            return true;
        }

        return $course->subCourses()->where('status', 'Active')->doesntExist();
    }

    private function eligibleCourseIdsForClass(int $schoolClassId): Collection
    {
        $schoolClass = SchoolClass::query()->find($schoolClassId);

        $linkedIds = collect();

        if ($schoolClass?->class_category_id) {
            $linkedIds = Course::query()
                ->where('status', 'Active')
                ->whereHas('classCategories', fn ($query) => $query->where('class_categories.id', $schoolClass->class_category_id))
                ->pluck('id');
        }

        if ($linkedIds->isNotEmpty()) {
            return $linkedIds;
        }

        return Course::query()
            ->where('status', 'Active')
            ->pluck('id');
    }

    private function courseIsEligibleForClass(Course $course, int $schoolClassId): bool
    {
        return $this->eligibleCourseIdsForClass($schoolClassId)->contains($course->id);
    }
}
