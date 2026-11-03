<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\Staff;
use App\Services\Hr\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller implements HasMiddleware
{
    public function __construct(private LeaveBalanceService $balances) {}

    public static function middleware(): array
    {
        return ['staff.auth'];
    }

    public function index()
    {
        $staff = Auth::user()?->staff;
        $year = (int) date('Y');

        if (! $staff) {
            return view('teacher.leave', [
                'staff' => null,
                'year' => $year,
                'leaveTypes' => collect(),
                'balances' => collect(),
                'requests' => collect(),
            ]);
        }

        $this->balances->ensureBalances($year);
        $leaveTypes = $this->typesFor($staff);

        $balances = $leaveTypes->map(function (HrLeaveType $type) use ($staff, $year) {
            return $this->balances->balanceFor($staff, $type, $year);
        });

        return view('teacher.leave', [
            'staff' => $staff,
            'year' => $year,
            'leaveTypes' => $leaveTypes,
            'balances' => $balances,
            'requests' => HrLeaveRequest::with('leaveType')
                ->where('staff_id', $staff->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $staff = Auth::user()?->staff;

        if (! $staff) {
            return back()->with('message_error', 'Your login is not linked to a staff record, so leave cannot be submitted.');
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $type = HrLeaveType::where('status', 'Active')->findOrFail($validated['leave_type_id']);

        if ($type->gender_limit && strcasecmp((string) $staff->gender, $type->gender_limit) !== 0) {
            return back()->with('message_error', $type->name.' is only available for '.$type->gender_limit.' staff.');
        }

        $days = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        $balance = $this->balances->balanceFor($staff, $type, (int) Carbon::parse($validated['start_date'])->year);

        if ($days > $balance->remaining()) {
            return back()->with('message_error', 'This request exceeds the remaining '.$type->name.' balance ('.$balance->remaining().' day(s)).');
        }

        HrLeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $type->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => trim((string) ($validated['reason'] ?? '')) ?: null,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Leave request submitted. An administrator or HR will review it.');
    }

    private function typesFor(Staff $staff)
    {
        return HrLeaveType::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get()
            ->filter(function (HrLeaveType $type) use ($staff) {
                return ! $type->gender_limit
                    || strcasecmp((string) $staff->gender, $type->gender_limit) === 0;
            })
            ->values();
    }
}
