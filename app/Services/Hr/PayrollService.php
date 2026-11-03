<?php

namespace App\Services\Hr;

use App\Models\HrDeductionType;
use App\Models\HrLeaveRequest;
use App\Models\HrPayrollRun;
use App\Models\HrPayrollSetting;
use App\Models\HrPayslip;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollService
{
    public function generate(int $year, int $month, bool $replaceDraft = true): HrPayrollRun
    {
        $existing = HrPayrollRun::where('period_year', $year)->where('period_month', $month)->first();

        if ($existing && $existing->isLocked()) {
            throw new RuntimeException('This payroll period is already '.$existing->status.' and cannot be regenerated.');
        }

        if ($existing && ! $replaceDraft) {
            return $existing->load('payslips.staff');
        }

        $settings = HrPayrollSetting::current();
        $staffMembers = Staff::query()
            ->with(['payGrade', 'staffEarnings.earningType', 'staffDeductions.deductionType'])
            ->where('status', 'Active')
            ->orderBy('surname')
            ->get()
            ->filter(fn (Staff $staff) => $staff->resolvedBasicSalary() > 0)
            ->values();

        return DB::transaction(function () use ($existing, $year, $month, $settings, $staffMembers) {
            if ($existing) {
                $existing->payslips()->delete();
                $run = $existing;
            } else {
                $run = new HrPayrollRun();
                $run->period_year = $year;
                $run->period_month = $month;
                $run->created_by = Auth::id();
            }

            $run->status = 'draft';
            $run->run_date = now()->toDateString();
            $run->approved_by = null;
            $run->approved_at = null;
            $run->paid_at = null;
            $run->save();

            $totals = [
                'gross' => 0,
                'ssnit_employee' => 0,
                'ssnit_employer' => 0,
                'paye' => 0,
                'other' => 0,
                'net' => 0,
            ];

            foreach ($staffMembers as $staff) {
                $slip = $this->buildPayslip($run, $staff, $settings, $year, $month);
                $totals['gross'] += $slip->gross;
                $totals['ssnit_employee'] += $slip->ssnit_employee;
                $totals['ssnit_employer'] += $slip->ssnit_employer;
                $totals['paye'] += $slip->paye;
                $totals['other'] += $slip->other_deductions;
                $totals['net'] += $slip->net;
            }

            $run->total_gross = round($totals['gross'], 2);
            $run->total_ssnit_employee = round($totals['ssnit_employee'], 2);
            $run->total_ssnit_employer = round($totals['ssnit_employer'], 2);
            $run->total_paye = round($totals['paye'], 2);
            $run->total_other_deductions = round($totals['other'], 2);
            $run->total_net = round($totals['net'], 2);
            $run->employee_count = $staffMembers->count();
            $run->save();

            return $run->load('payslips.staff');
        });
    }

    public function approve(HrPayrollRun $run): HrPayrollRun
    {
        if ($run->status !== 'draft') {
            throw new RuntimeException('Only a draft payroll can be approved.');
        }

        $run->status = 'approved';
        $run->approved_by = Auth::id();
        $run->approved_at = now();
        $run->save();

        return $run;
    }

    public function markPaid(HrPayrollRun $run): HrPayrollRun
    {
        if ($run->status !== 'approved') {
            throw new RuntimeException('Payroll must be approved before it can be marked as paid.');
        }

        $run->status = 'paid';
        $run->paid_at = now();
        $run->save();

        return $run;
    }

    private function buildPayslip(HrPayrollRun $run, Staff $staff, HrPayrollSetting $settings, int $year, int $month): HrPayslip
    {
        $unpaidDays = $this->unpaidLeaveDays($staff->id, $year, $month);
        $basic = $staff->resolvedBasicSalary();

        if ($unpaidDays > 0) {
            $basic = max(0, $basic - (($basic / 22) * $unpaidDays));
        }

        $basic = round($basic, 2);
        $earningLines = [];
        $taxableEarnings = 0;
        $nonTaxableEarnings = 0;

        foreach ($staff->staffEarnings as $earning) {
            $type = $earning->earningType;
            if (! $type || $type->status !== 'Active') {
                continue;
            }

            $amount = $this->resolveAmount($earning->amount, $type->method, $type->default_amount, $basic);
            if ($amount <= 0) {
                continue;
            }

            $earningLines[] = [
                'type' => 'earning',
                'name' => $type->name,
                'amount' => $amount,
                'taxable' => (bool) $type->is_taxable,
            ];

            if ($type->is_taxable) {
                $taxableEarnings += $amount;
            } else {
                $nonTaxableEarnings += $amount;
            }
        }

        $gross = round($basic + $taxableEarnings + $nonTaxableEarnings, 2);
        $ssnitBase = $settings->ssnit_ceiling ? min($basic, (float) $settings->ssnit_ceiling) : $basic;
        $ssnitEmployee = round($ssnitBase * ((float) $settings->ssnit_employee_rate / 100), 2);
        $ssnitEmployer = round($ssnitBase * ((float) $settings->ssnit_employer_rate / 100), 2);

        $taxable = max(0, $gross - $nonTaxableEarnings - $ssnitEmployee - (float) $settings->personal_relief);
        $paye = $this->calculatePaye($taxable, $settings->paye_bands ?? []);

        $otherDeductions = 0;
        $deductionLines = [];
        $statutoryCodes = ['SSNIT', 'PAYE'];

        foreach ($staff->staffDeductions as $deduction) {
            $type = $deduction->deductionType;
            if (! $type || $type->status !== 'Active') {
                continue;
            }

            if ($type->is_statutory || in_array(strtoupper((string) $type->code), $statutoryCodes, true)) {
                continue;
            }

            $amount = $this->resolveAmount($deduction->amount, $type->method, $type->default_amount, $basic);
            if ($amount <= 0) {
                continue;
            }

            $otherDeductions += $amount;
            $deductionLines[] = [
                'type' => 'deduction',
                'name' => $type->name,
                'amount' => $amount,
            ];
        }

        $otherDeductions = round($otherDeductions, 2);
        $net = round($gross - $ssnitEmployee - $paye - $otherDeductions, 2);

        $lines = array_merge(
            [['type' => 'earning', 'name' => 'Basic salary', 'amount' => $basic, 'taxable' => true]],
            $earningLines,
            [
                ['type' => 'deduction', 'name' => 'SSNIT (Employee)', 'amount' => $ssnitEmployee],
                ['type' => 'deduction', 'name' => 'PAYE', 'amount' => $paye],
            ],
            $deductionLines
        );

        return HrPayslip::create([
            'payroll_run_id' => $run->id,
            'staff_id' => $staff->id,
            'basic' => $basic,
            'gross' => $gross,
            'ssnit_employee' => $ssnitEmployee,
            'ssnit_employer' => $ssnitEmployer,
            'paye' => $paye,
            'other_deductions' => $otherDeductions,
            'net' => max(0, $net),
            'unpaid_leave_days' => $unpaidDays,
            'lines' => $lines,
        ]);
    }

    private function resolveAmount($override, string $method, $defaultAmount, float $basic): float
    {
        $value = $override !== null && $override !== '' ? (float) $override : (float) $defaultAmount;

        if ($method === 'percent_basic') {
            return round($basic * ($value / 100), 2);
        }

        return round($value, 2);
    }

    private function calculatePaye(float $taxable, array $bands): float
    {
        $tax = 0;
        $previous = 0;

        foreach ($bands as $band) {
            $limit = $band['up_to'] === null || $band['up_to'] === '' ? INF : (float) $band['up_to'];
            $slice = min($taxable, $limit) - $previous;

            if ($slice > 0) {
                $tax += $slice * (((float) ($band['rate'] ?? 0)) / 100);
            }

            $previous = $limit;

            if ($taxable <= $limit) {
                break;
            }
        }

        return round($tax, 2);
    }

    private function unpaidLeaveDays(int $staffId, int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $requests = HrLeaveRequest::query()
            ->with('leaveType')
            ->where('staff_id', $staffId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get();

        $days = 0;
        foreach ($requests as $request) {
            if ($request->leaveType?->is_paid) {
                continue;
            }

            $from = Carbon::parse($request->start_date)->max($start);
            $to = Carbon::parse($request->end_date)->min($end);
            $days += $from->diffInDays($to) + 1;
        }

        return $days;
    }
}
