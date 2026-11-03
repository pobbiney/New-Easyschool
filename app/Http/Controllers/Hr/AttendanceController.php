<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrLeaveRequest;
use App\Models\HrStaffAttendance;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $staffMembers = Staff::with(['department', 'hrPosition'])
            ->where('status', 'Active')
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();
        $records = HrStaffAttendance::whereDate('date', $date)->get()->keyBy('staff_id');

        $onLeaveIds = HrLeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->pluck('staff_id')
            ->unique()
            ->all();

        $summary = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'on_leave' => 0,
            'unmarked' => 0,
        ];

        foreach ($staffMembers as $member) {
            if (in_array($member->id, $onLeaveIds, true)) {
                $summary['on_leave']++;
                continue;
            }

            $record = $records->get($member->id);
            if (! $record) {
                $summary['unmarked']++;
                continue;
            }

            $status = $record->status;
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        return view('hr.attendance', [
            'date' => $date,
            'dateCarbon' => Carbon::parse($date),
            'staffMembers' => $staffMembers,
            'records' => $records,
            'onLeaveIds' => $onLeaveIds,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late,on_leave',
            'attendance.*.check_in' => 'nullable',
            'attendance.*.check_out' => 'nullable',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $date = $validated['date'];
        $onLeaveIds = HrLeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->pluck('staff_id')
            ->unique()
            ->all();

        foreach ($validated['attendance'] as $staffId => $row) {
            $status = in_array((int) $staffId, $onLeaveIds, true) ? 'on_leave' : $row['status'];

            HrStaffAttendance::updateOrCreate(
                [
                    'staff_id' => (int) $staffId,
                    'date' => $date,
                ],
                [
                    'status' => $status,
                    'check_in' => $row['check_in'] ?? null,
                    'check_out' => $row['check_out'] ?? null,
                    'remarks' => trim((string) ($row['remarks'] ?? '')) ?: null,
                    'recorded_by' => Auth::id(),
                ]
            );
        }

        return back()->with('message_success', 'Attendance saved for '.Carbon::parse($date)->format('d M Y').'.');
    }
}
