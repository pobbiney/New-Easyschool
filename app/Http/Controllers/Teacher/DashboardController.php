<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassAttendance;
use App\Models\SchoolClass;
use App\Services\TeacherAccessService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Routing\Controllers\HasMiddleware;

class DashboardController extends Controller implements HasMiddleware
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public static function middleware(): array
    {
        return ['staff.auth', 'teacher'];
    }

    public function index()
    {
        $staffId = $this->teacherAccess->staffId();
        $staff = $this->teacherAccess->staff();
        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId);
        $subjectAssignments = $this->teacherAccess->subjectAssignments(
            $staffId,
            AcademicPeriodDefaults::yearId(request()),
            AcademicPeriodDefaults::termId(request())
        );

        $today = now()->toDateString();
        $presentToday = 0;

        foreach ($homeroomClasses as $class) {
            $presentToday += ClassAttendance::query()
                ->where('school_class_id', $class->id)
                ->where('date', $today)
                ->where('status', 'present')
                ->count();
        }

        return view('teacher.dashboard', [
            'staff' => $staff,
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'stats' => [
                'homeroom_classes' => $homeroomClasses->count(),
                'subject_slots' => $subjectAssignments->count(),
                'present_today' => $presentToday,
            ],
            'period' => AcademicPeriodDefaults::forFrontend(),
        ]);
    }
}
