<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use App\Models\HrLeaveRequest;
use App\Models\HrPayrollRun;
use App\Models\HrStaffAttendance;
use App\Models\Staff;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $staff = Staff::query()->get();
        $attendanceToday = HrStaffAttendance::whereDate('date', $today)->get()->keyBy('staff_id');
        $onLeaveIds = HrLeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->pluck('staff_id');

        $lastPayroll = HrPayrollRun::orderByDesc('period_year')->orderByDesc('period_month')->first();

        return view('hr.dashboard', [
            'stats' => [
                'headcount' => $staff->where('status', 'Active')->count(),
                'inactive' => $staff->where('status', 'Inactive')->count(),
                'departments' => HrDepartment::where('status', 'Active')->count(),
                'on_leave' => $onLeaveIds->unique()->count(),
                'pending_leave' => HrLeaveRequest::where('status', 'pending')->count(),
                'present_today' => $attendanceToday->where('status', 'present')->count(),
                'absent_today' => $attendanceToday->where('status', 'absent')->count(),
                'last_payroll_net' => $lastPayroll?->total_net,
                'last_payroll_label' => $lastPayroll?->periodLabel(),
                'last_payroll_status' => $lastPayroll?->status,
            ],
            'pendingLeave' => HrLeaveRequest::with(['staff', 'leaveType'])->where('status', 'pending')->latest()->limit(8)->get(),
            'recentPayrolls' => HrPayrollRun::orderByDesc('period_year')->orderByDesc('period_month')->limit(5)->get(),
        ]);
    }
}
