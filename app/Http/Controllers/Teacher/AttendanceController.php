<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassAttendance;
use App\Models\SchoolClass;
use App\Services\TeacherAccessService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller implements HasMiddleware
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public static function middleware(): array
    {
        return ['staff.auth', 'teacher'];
    }

    public function hub(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();
        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId);

        return view('teacher.attendance-hub', [
            'homeroomClasses' => $homeroomClasses,
            'period' => AcademicPeriodDefaults::forFrontend(),
        ]);
    }

    public function index(Request $request, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertHomeroomTeacher($staffId, $class->id);

        $date = $request->input('date', now()->toDateString());
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $students = $this->teacherAccess->studentsForClass($class->id);
        $records = ClassAttendance::query()
            ->where('school_class_id', $class->id)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        $monthStart = now()->parse($date)->startOfMonth()->toDateString();
        $monthEnd = now()->parse($date)->endOfMonth()->toDateString();
        $monthSummary = ClassAttendance::query()
            ->where('school_class_id', $class->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get()
            ->groupBy('student_id');

        return view('teacher.attendance', [
            'schoolClass' => $class,
            'students' => $students,
            'date' => $date,
            'records' => $records,
            'monthSummary' => $monthSummary,
            'period' => AcademicPeriodDefaults::forFrontend(),
        ]);
    }

    public function store(Request $request, SchoolClass $class)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertHomeroomTeacher($staffId, $class->id);

        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:'.implode(',', ClassAttendance::STATUSES),
            'attendance.*.notes' => 'nullable|string|max:255',
        ]);

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        DB::transaction(function () use ($request, $class, $yearId, $termId) {
            foreach ($request->attendance as $row) {
                ClassAttendance::updateOrCreate(
                    [
                        'student_id' => $row['student_id'],
                        'school_class_id' => $class->id,
                        'date' => $request->date,
                    ],
                    [
                        'status' => $row['status'],
                        'notes' => trim($row['notes'] ?? '') ?: null,
                        'academic_year_id' => $yearId,
                        'academic_term_id' => $termId,
                        'recorded_by' => Auth::id(),
                    ]
                );
            }
        });

        return back()->with('message_success', 'Attendance saved for '.$request->date.'.');
    }
}
