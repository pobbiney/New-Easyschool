<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassAttendance;
use App\Models\SchoolClass;
use App\Services\TeacherAccessService;
use Illuminate\Routing\Controllers\HasMiddleware;

class ClassWorkspaceController extends Controller implements HasMiddleware
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public static function middleware(): array
    {
        return ['staff.auth', 'teacher', 'teacher.owns.class'];
    }

    public function show(SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $isHomeroom = $this->teacherAccess->ownsHomeroomClass($staffId, $class->id);
        $students = $this->teacherAccess->studentsForClass($class->id);

        $today = now()->toDateString();
        $presentToday = ClassAttendance::query()
            ->where('school_class_id', $class->id)
            ->where('date', $today)
            ->where('status', 'present')
            ->count();

        return view('teacher.class-workspace', [
            'schoolClass' => $class->load('classTeacher'),
            'students' => $students,
            'isHomeroom' => $isHomeroom,
            'stats' => [
                'headcount' => $students->count(),
                'present_today' => $presentToday,
            ],
        ]);
    }
}
