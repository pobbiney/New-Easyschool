<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseTeachingAssignment;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Support\AcademicPeriodDefaults;
use App\Support\TeacherCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseTeacherController extends Controller
{

    public function index()
    {
        $topLevelCourses = Course::topLevel()
            ->with([
                'subCourses.parent',
                'subCourses.teachingAssignments.teacher',
                'subCourses.teachingAssignments.schoolClass',
                'teachingAssignments.teacher',
                'teachingAssignments.schoolClass',
            ])
            ->orderBy('name')
            ->get();

        $assignableCourses = $this->assignableCourses($topLevelCourses);
        $schoolClasses = SchoolClass::where('status', 'Active')->orderBy('name')->get();
        $teachers = $this->teacherStaffQuery()->get();

        $assignmentCount = CourseTeachingAssignment::count();
        $assignedClassPairs = CourseTeachingAssignment::query()
            ->select('course_id', 'school_class_id')
            ->distinct()
            ->count();
        $totalSlots = $this->countAssignableSlots($topLevelCourses, $schoolClasses->count());

        return view('course-setup.course-teacher-assignment', [
            'assignableCourses' => $assignableCourses,
            'schoolClasses' => $schoolClasses,
            'teachers' => $teachers,
            'stats' => [
                'total_courses' => $assignableCourses->count(),
                'assignments' => $assignmentCount,
                'unassigned' => max($totalSlots - $assignedClassPairs, 0),
                'teachers' => $teachers->count(),
            ],
        ]);
    }

    public function show($id)
    {
        $course = Course::with(['parent', 'teachingAssignments.teacher', 'teachingAssignments.schoolClass'])
            ->findOrFail($id);

        return response()->json([
            'id' => $course->id,
            'name' => $course->name,
            'parent_name' => $course->parent?->name,
            'is_sub_course' => $course->isSubCourse(),
            'assignments' => $course->teachingAssignments->map(function ($assignment) {
                return $this->formatAssignment($assignment);
            })->values(),
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $schoolClass = SchoolClass::where('id', $request->school_class_id)->where('status', 'Active')->firstOrFail();
        $teacher = $this->teacherStaffQuery()->where('staff.id', $request->staff_id)->firstOrFail();

        $assignment = CourseTeachingAssignment::firstOrNew([
            'course_id' => $course->id,
            'school_class_id' => $schoolClass->id,
        ]);

        if ($assignment->exists && (int) $assignment->staff_id === (int) $teacher->id) {
            $message = 'This teacher is already assigned to this course and class.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('message_error', $message);
        }

        if (! $assignment->exists) {
            $assignment->created_by = Auth::id();
        }

        $assignment->staff_id = $teacher->id;
        $assignment->academic_year_id = AcademicPeriodDefaults::yearId($request);
        $assignment->academic_term_id = AcademicPeriodDefaults::termId($request);
        $assignment->updated_by = Auth::id();
        $assignment->save();

        $assignment->load(['teacher', 'schoolClass']);

        $message = $assignment->wasRecentlyCreated
            ? 'Course teacher assigned successfully.'
            : 'Course teacher updated for this class.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'assignment' => $this->formatAssignment($assignment),
            ]);
        }

        return back()->with('message_success', $message);
    }

    public function unassign(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:course_teaching_assignments,id',
        ]);

        CourseTeachingAssignment::where('id', $request->assignment_id)->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Course teacher assignment removed successfully.',
                'assignment_id' => (int) $request->assignment_id,
            ]);
        }

        return back()->with('message_success', 'Course teacher assignment removed successfully.');
    }

    private function teacherStaffQuery()
    {
        return Staff::query()
            ->where('staff.status', 'Active')
            ->whereHas('user', function ($query) {
                $query->where('user_cat', TeacherCategory::id())
                    ->where('status', 'Active');
            })
            ->orderBy('staff.surname')
            ->orderBy('staff.firstname');
    }

    private function assignableCourses($topLevelCourses)
    {
        return $topLevelCourses->flatMap(function ($course) {
            if ($course->subCourses->isNotEmpty()) {
                return $course->subCourses;
            }

            return collect([$course]);
        });
    }

    private function countAssignableSlots($topLevelCourses, int $classCount): int
    {
        if ($classCount === 0) {
            return 0;
        }

        $courseCount = $topLevelCourses->sum(function ($course) {
            return $course->subCourses->isNotEmpty()
                ? $course->subCourses->count()
                : 1;
        });

        return $courseCount * $classCount;
    }

    private function formatAssignment(CourseTeachingAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'school_class_id' => $assignment->school_class_id,
            'class_name' => $assignment->schoolClass?->name,
            'staff_id' => $assignment->staff_id,
            'teacher_name' => $assignment->teacher?->full_name,
            'teacher_picture' => $assignment->teacher?->picture ? asset($assignment->teacher->picture) : null,
        ];
    }
}
