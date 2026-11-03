<?php

namespace App\Services\Hr;

use App\Models\HrLeaveBalance;
use App\Models\HrLeaveType;
use App\Models\Staff;

class LeaveBalanceService
{
    public function ensureBalances(?int $year = null): void
    {
        $year = $year ?: (int) date('Y');
        $types = HrLeaveType::where('status', 'Active')->get();
        $staffMembers = Staff::where('status', 'Active')->get();

        foreach ($staffMembers as $staff) {
            foreach ($types as $type) {
                if ($type->gender_limit && strcasecmp((string) $staff->gender, $type->gender_limit) !== 0) {
                    continue;
                }

                $this->balanceFor($staff, $type, $year);
            }
        }
    }

    public function balanceFor(Staff $staff, HrLeaveType $type, ?int $year = null): HrLeaveBalance
    {
        $year = $year ?: (int) date('Y');

        $balance = HrLeaveBalance::firstOrCreate(
            [
                'staff_id' => $staff->id,
                'leave_type_id' => $type->id,
                'year' => $year,
            ],
            [
                'entitled' => $type->days_per_year,
                'taken' => 0,
            ]
        );

        if ((int) $balance->entitled !== (int) $type->days_per_year) {
            $balance->entitled = $type->days_per_year;
            $balance->save();
        }

        return $balance;
    }

    public function syncTypeEntitlement(HrLeaveType $type, ?int $year = null): void
    {
        $query = HrLeaveBalance::where('leave_type_id', $type->id);

        if ($year) {
            $query->where('year', $year);
        }

        $query->update(['entitled' => $type->days_per_year]);
    }
}
