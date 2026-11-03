<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Services\TeacherAccessService;
use Illuminate\Routing\Controllers\HasMiddleware;

class CourseWorkspaceController extends Controller implements HasMiddleware
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public static function middleware(): array
    {
        return ['staff.auth', 'teacher', 'teacher.owns.class'];
    }

    public function show(Course $course, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $students = $this->teacherAccess->studentsForClass($class->id);

        return view('teacher.course-workspace', [
            'course' => $course->load('parent'),
            'schoolClass' => $class,
            'students' => $students,
            'stats' => [
                'headcount' => $students->count(),
            ],
        ]);
    }
}
