<?php

namespace App\Services;

use App\Models\BillPayment;
use App\Models\ClassAttendance;
use App\Models\HrLeaveRequest;
use App\Models\Pos\PosSale;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class DashboardHomeService
{
    public function __construct(
        private UserAccessService $access,
        private TeacherAccessService $teacherAccess,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function payload(?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $staff = $user?->staff_id ? Staff::query()->find($user->staff_id) : null;
        $school = SchoolSetting::current();
        $period = AcademicPeriodDefaults::forFrontend();
        $isTeacher = $this->teacherAccess->isTeacher($user);
        $name = $staff?->full_name ?: ($user?->name ?: 'there');

        $homeroomClasses = collect();
        $subjectAssignments = collect();
        $teacherStats = [
            'homeroom_classes' => 0,
            'subject_slots' => 0,
            'present_today' => 0,
        ];

        if ($isTeacher && $user?->staff_id) {
            $staffId = (int) $user->staff_id;
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
            $teacherStats = [
                'homeroom_classes' => $homeroomClasses->count(),
                'subject_slots' => $subjectAssignments->count(),
                'present_today' => $presentToday,
            ];
        }

        return [
            'staff' => $staff,
            'displayName' => $name,
            'roleName' => $user?->getUserCategory() ?: 'Staff',
            'greeting' => $this->greeting(),
            'schoolName' => $school->name ?: 'EasySchool',
            'logoUrl' => $school->logoUrl(),
            'period' => $period,
            'isTeacher' => $isTeacher,
            'kpis' => $this->kpis($isTeacher, $teacherStats),
            'charts' => $this->charts($isTeacher, $homeroomClasses),
            'shortcuts' => $this->shortcuts(),
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'pendingLeave' => $this->pendingLeave(),
            'showPendingLeave' => $this->access->can('hr-leave'),
            'heroLine' => $isTeacher
                ? 'Your classes, attendance, and subjects for this term.'
                : 'Open only the modules your role can use.',
        ];
    }

    /**
     * @param  array{homeroom_classes: int, subject_slots: int, present_today: int}  $teacherStats
     * @return list<array{label: string, value: int, icon: string, tone: string}>
     */
    private function kpis(bool $isTeacher, array $teacherStats): array
    {
        $cards = [];

        if ($this->access->can('list-students')) {
            $cards[] = [
                'label' => 'Students',
                'value' => Student::query()->where('status', 'Active')->count(),
                'icon' => 'ri-graduation-cap-line',
                'tone' => 'teal',
            ];
        }

        if ($this->access->can('school-classes')) {
            $cards[] = [
                'label' => 'Classes',
                'value' => SchoolClass::query()->where('status', 'Active')->count(),
                'icon' => 'ri-layout-grid-line',
                'tone' => 'indigo',
            ];
        }

        if ($this->access->canAny(['list-staff', 'hr-dashboard'])) {
            $cards[] = [
                'label' => 'Staff',
                'value' => Staff::query()->where('status', 'Active')->count(),
                'icon' => 'ri-team-line',
                'tone' => 'sky',
            ];
        }

        if ($this->access->can('student-bills')) {
            $cards[] = [
                'label' => 'Unpaid bills',
                'value' => StudentBill::query()->where('balance', '>', 0)->count(),
                'icon' => 'ri-wallet-3-line',
                'tone' => 'amber',
            ];
        }

        if ($this->access->can('hr-leave')) {
            $cards[] = [
                'label' => 'Pending leave',
                'value' => HrLeaveRequest::query()->where('status', 'pending')->count(),
                'icon' => 'ri-time-line',
                'tone' => 'violet',
            ];
        }

        if ($isTeacher) {
            $cards[] = [
                'label' => 'Homeroom classes',
                'value' => $teacherStats['homeroom_classes'],
                'icon' => 'ri-home-smile-line',
                'tone' => 'teal',
            ];
            $cards[] = [
                'label' => 'Subject slots',
                'value' => $teacherStats['subject_slots'],
                'icon' => 'ri-book-open-line',
                'tone' => 'indigo',
            ];
            $cards[] = [
                'label' => 'Present today',
                'value' => $teacherStats['present_today'],
                'icon' => 'ri-user-follow-line',
                'tone' => 'emerald',
            ];
        }

        return $cards;
    }

    /**
     * @return list<array{url: string, label: string, help: string, icon: string, tone: string}>
     */
    private function shortcuts(): array
    {
        $catalog = [
            ['url' => 'list-students', 'label' => 'Students', 'help' => 'List and add learners', 'icon' => 'ri-graduation-cap-line', 'tone' => 'teal'],
            ['url' => 'student-remarks', 'label' => 'Student Remarks', 'help' => 'Term remarks for a class', 'icon' => 'ri-chat-quote-line', 'tone' => 'violet'],
            ['url' => 'school-classes', 'label' => 'Classes', 'help' => 'Class setup and assignment', 'icon' => 'ri-layout-grid-line', 'tone' => 'indigo'],
            ['url' => 'student-bills', 'label' => 'Student bills', 'help' => 'Fees and balances', 'icon' => 'ri-wallet-3-line', 'tone' => 'amber'],
            ['url' => 'hr-dashboard', 'label' => 'HR', 'help' => 'People, leave, and payroll', 'icon' => 'ri-team-line', 'tone' => 'sky'],
            ['url' => 'list-staff', 'label' => 'Staff', 'help' => 'Staff directory', 'icon' => 'ri-user-star-line', 'tone' => 'sky'],
            ['url' => 'timetable', 'label' => 'Timetable', 'help' => 'Class weeks and period times', 'icon' => 'ri-calendar-schedule-line', 'tone' => 'violet'],
            ['url' => 'send-sms', 'label' => 'Send SMS', 'help' => 'Message parents and staff', 'icon' => 'ri-message-3-line', 'tone' => 'rose'],
            ['url' => 'teacher-dashboard', 'label' => 'Teacher portal', 'help' => 'Attendance, assessments, gradebook', 'icon' => 'ri-book-open-line', 'tone' => 'teal'],
            ['url' => 'pos-sale', 'label' => 'POS sale', 'help' => 'Record a shop sale', 'icon' => 'ri-store-2-line', 'tone' => 'orange'],
            ['url' => 'expenses', 'label' => 'Record expense', 'help' => 'Track school spending', 'icon' => 'ri-wallet-3-line', 'tone' => 'rose'],
            ['url' => 'report-students', 'label' => 'Reports', 'help' => 'Print, PDF, and Excel', 'icon' => 'ri-file-chart-line', 'tone' => 'slate'],
            ['url' => 'course-registration', 'label' => 'Courses', 'help' => 'Register class subjects', 'icon' => 'ri-book-2-line', 'tone' => 'indigo'],
            ['url' => 'add-course', 'label' => 'Add course', 'help' => 'Subject catalogue', 'icon' => 'ri-book-marked-line', 'tone' => 'indigo'],
            ['url' => 'school-settings', 'label' => 'School settings', 'help' => 'School profile and defaults', 'icon' => 'ri-settings-3-line', 'tone' => 'slate'],
        ];

        $seen = [];
        $items = [];

        foreach ($catalog as $item) {
            if (isset($seen[$item['url']])) {
                continue;
            }
            if (! $this->access->can($item['url']) || ! Route::has($item['url'])) {
                continue;
            }
            if ($item['url'] === 'list-staff' && $this->access->can('hr-dashboard')) {
                continue;
            }
            if ($item['url'] === 'add-course' && $this->access->can('course-registration')) {
                continue;
            }
            $seen[$item['url']] = true;
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SchoolClass>  $homeroomClasses
     * @return list<array<string, mixed>>
     */
    private function charts(bool $isTeacher, $homeroomClasses): array
    {
        $charts = [];

        if ($this->access->canAny(['list-students', 'school-classes'])) {
            $rows = Student::query()
                ->select('school_classes.name as class_name', DB::raw('COUNT(students.id) as total'))
                ->join('school_classes', 'school_classes.id', '=', 'students.school_class_id')
                ->where('students.status', 'Active')
                ->groupBy('school_classes.id', 'school_classes.name')
                ->orderByDesc('total')
                ->limit(12)
                ->get();

            $charts[] = [
                'id' => 'dashStudentsByClass',
                'title' => 'Students by class',
                'help' => 'Active enrolment',
                'type' => 'bar',
                'col' => 'col-lg-8',
                'categories' => $rows->pluck('class_name')->values()->all(),
                'series' => [[
                    'name' => 'Students',
                    'data' => $rows->pluck('total')->map(fn ($n) => (int) $n)->values()->all(),
                ]],
                'colors' => ['#25A194'],
            ];
        }

        if ($this->access->can('list-students')) {
            $gender = Student::query()
                ->where('status', 'Active')
                ->select('gender', DB::raw('COUNT(*) as total'))
                ->groupBy('gender')
                ->get()
                ->groupBy(function ($row) {
                    $label = trim((string) $row->gender);

                    return $label !== '' ? ucfirst(strtolower($label)) : 'Unspecified';
                })
                ->map(fn ($rows) => (int) $rows->sum('total'))
                ->sortDesc();

            $charts[] = [
                'id' => 'dashStudentGender',
                'title' => 'Students by gender',
                'help' => 'Active learners',
                'type' => 'donut',
                'col' => 'col-lg-4',
                'labels' => $gender->keys()->values()->all(),
                'series' => $gender->values()->all(),
                'colors' => ['#25A194', '#487FFF', '#FF7A2C', '#8252E9'],
            ];
        }

        if ($this->access->can('student-bills')) {
            $paid = (float) StudentBill::query()->sum('amount_paid');
            $outstanding = (float) StudentBill::query()->sum('balance');

            $charts[] = [
                'id' => 'dashFeeSplit',
                'title' => 'Fee collection',
                'help' => 'Paid vs outstanding',
                'type' => 'donut',
                'col' => 'col-lg-4',
                'labels' => ['Paid', 'Outstanding'],
                'series' => [round($paid, 2), round($outstanding, 2)],
                'colors' => ['#25A194', '#FF7A2C'],
            ];
        }

        if ($this->access->can('student-bills')) {
            $days = $this->lastDayMap(14);
            $totals = BillPayment::query()
                ->where('paid_at', '>=', now()->subDays(13)->startOfDay())
                ->selectRaw('DATE(paid_at) as d, SUM(amount) as total')
                ->groupByRaw('DATE(paid_at)')
                ->pluck('total', 'd');

            $charts[] = [
                'id' => 'dashPayments',
                'title' => 'Bill payments',
                'help' => 'Last 14 days',
                'type' => 'area',
                'col' => 'col-lg-8',
                'categories' => array_values($days),
                'series' => [[
                    'name' => 'Collected (GHS)',
                    'data' => collect($days)->keys()->map(fn ($iso) => round((float) ($totals[$iso] ?? 0), 2))->values()->all(),
                ]],
                'colors' => ['#487FFF'],
            ];
        }

        if ($this->access->can('pos-sale')) {
            $days = $this->lastDayMap(14);
            $totals = PosSale::query()
                ->where('sold_at', '>=', now()->subDays(13)->startOfDay())
                ->selectRaw('DATE(sold_at) as d, SUM(total) as total')
                ->groupByRaw('DATE(sold_at)')
                ->pluck('total', 'd');

            $charts[] = [
                'id' => 'dashPosSales',
                'title' => 'POS sales',
                'help' => 'Last 14 days',
                'type' => 'area',
                'col' => 'col-lg-8',
                'categories' => array_values($days),
                'series' => [[
                    'name' => 'Sales (GHS)',
                    'data' => collect($days)->keys()->map(fn ($iso) => round((float) ($totals[$iso] ?? 0), 2))->values()->all(),
                ]],
                'colors' => ['#ea580c'],
            ];
        }

        if ($isTeacher && $homeroomClasses->isNotEmpty()) {
            $days = $this->lastDayMap(7);
            $rows = ClassAttendance::query()
                ->whereIn('school_class_id', $homeroomClasses->pluck('id'))
                ->whereBetween('date', [array_key_first($days), array_key_last($days)])
                ->selectRaw('date, status, COUNT(*) as total')
                ->groupBy('date', 'status')
                ->get();

            $lookup = [];
            foreach ($rows as $row) {
                $iso = substr((string) $row->date, 0, 10);
                $lookup[$iso][$row->status] = (int) $row->total;
            }

            $present = [];
            $absent = [];
            foreach (array_keys($days) as $iso) {
                $present[] = $lookup[$iso]['present'] ?? 0;
                $absent[] = $lookup[$iso]['absent'] ?? 0;
            }

            $charts[] = [
                'id' => 'dashClassAttendance',
                'title' => 'Homeroom attendance',
                'help' => 'Last 7 days',
                'type' => 'bar',
                'stacked' => true,
                'col' => 'col-lg-12',
                'categories' => array_values($days),
                'series' => [
                    ['name' => 'Present', 'data' => $present],
                    ['name' => 'Absent', 'data' => $absent],
                ],
                'colors' => ['#25A194', '#e11d48'],
            ];
        }

        return $charts;
    }

    /**
     * @return array<string, string>
     */
    private function lastDayMap(int $days): array
    {
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $out[$date->toDateString()] = $date->format('d M');
        }

        return $out;
    }

    private function pendingLeave()
    {
        if (! $this->access->can('hr-leave')) {
            return collect();
        }

        return HrLeaveRequest::query()
            ->with(['staff', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->limit(6)
            ->get();
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        if ($hour < 12) {
            return 'Good morning';
        }

        if ($hour < 17) {
            return 'Good afternoon';
        }

        return 'Good evening';
    }
}
