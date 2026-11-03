<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\Staff;
use App\Services\Hr\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct(private LeaveBalanceService $balances) {}

    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $this->balances->ensureBalances($year);

        return view('hr.leave', [
            'tab' => $request->input('tab', 'requests'),
            'year' => $year,
            'leaveTypes' => HrLeaveType::query()
                ->withCount([
                    'requests',
                    'balances as used_balances_count' => fn ($query) => $query->where('taken', '>', 0),
                ])
                ->orderBy('name')
                ->get(),
            'requests' => HrLeaveRequest::with(['staff', 'leaveType', 'reviewer'])->latest()->get(),
            'balances' => $this->balancesForYear($year),
        ]);
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'days_per_year' => 'required|integer|min:0|max:365',
            'is_paid' => 'nullable|boolean',
            'gender_limit' => 'nullable|in:Male,Female',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrLeaveType::create([
            'name' => trim($request->name),
            'days_per_year' => (int) $request->days_per_year,
            'is_paid' => $request->boolean('is_paid'),
            'gender_limit' => $request->gender_limit ?: null,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Leave type added.')->with('active_tab', 'types');
    }

    public function updateType(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'name' => 'required|string|max:100',
            'days_per_year' => 'required|integer|min:0|max:365',
            'is_paid' => 'nullable|boolean',
            'gender_limit' => 'nullable|in:Male,Female',
            'status' => 'required|in:Active,Inactive',
        ]);

        $type = HrLeaveType::findOrFail($request->leave_type_id);
        $type->update([
            'name' => trim($request->name),
            'days_per_year' => (int) $request->days_per_year,
            'is_paid' => $request->boolean('is_paid'),
            'gender_limit' => $request->gender_limit ?: null,
            'status' => $request->status,
        ]);

        $this->balances->syncTypeEntitlement($type);

        return back()->with('message_success', 'Leave type updated. Staff balances now use '.$type->days_per_year.' day(s) per year.')->with('active_tab', 'types');
    }

    public function destroyType(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_types,id',
        ]);

        $type = HrLeaveType::query()
            ->withCount([
                'requests',
                'balances as used_balances_count' => fn ($query) => $query->where('taken', '>', 0),
            ])
            ->findOrFail($request->leave_type_id);

        if ($type->isInUse()) {
            return back()
                ->with('message_error', 'Cannot delete '.$type->name.' — it is already in use on leave records.')
                ->with('active_tab', 'types');
        }

        $type->delete();

        return back()->with('message_success', $type->name.' deleted.')->with('active_tab', 'types');
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $staff = Staff::findOrFail($validated['staff_id']);
        $type = HrLeaveType::findOrFail($validated['leave_type_id']);

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

        return back()->with('message_success', 'Leave request submitted.')->with('active_tab', 'requests');
    }

    public function review(Request $request)
    {
        $request->validate([
            'leave_request_id' => 'required|exists:hr_leave_requests,id',
            'decision' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string|max:500',
        ]);

        $leave = HrLeaveRequest::with(['staff', 'leaveType'])->findOrFail($request->leave_request_id);

        if ($leave->status !== 'pending') {
            return back()->with('message_error', 'This request has already been reviewed.');
        }

        if ($request->decision === 'approved') {
            $balance = $this->balances->balanceFor($leave->staff, $leave->leaveType, (int) $leave->start_date->year);
            if ($leave->days > $balance->remaining()) {
                return back()->with('message_error', 'Cannot approve: remaining balance is '.$balance->remaining().' day(s).');
            }
            $balance->increment('taken', $leave->days);
        }

        $leave->update([
            'status' => $request->decision,
            'approved_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => trim((string) $request->review_note) ?: null,
        ]);

        return back()->with('message_success', 'Leave request '.$request->decision.'.')->with('active_tab', 'requests');
    }

    private function balancesForYear(int $year)
    {
        return \App\Models\HrLeaveBalance::with(['staff', 'leaveType'])
            ->where('year', $year)
            ->get()
            ->sortBy(fn ($row) => $row->staff?->surname.' '.$row->leaveType?->name)
            ->values();
    }
}
