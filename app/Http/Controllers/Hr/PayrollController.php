<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrPayrollRun;
use App\Models\HrPayslip;
use App\Models\SchoolSetting;
use App\Services\Hr\PayrollService;
use Illuminate\Http\Request;
use RuntimeException;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    public function index()
    {
        return view('hr.payroll', [
            'runs' => HrPayrollRun::orderByDesc('period_year')->orderByDesc('period_month')->get(),
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'period_year' => 'required|integer|min:2020|max:2100',
            'period_month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $run = $this->payroll->generate((int) $request->period_year, (int) $request->period_month);
        } catch (RuntimeException $exception) {
            return back()->with('message_error', $exception->getMessage());
        }

        if ($run->employee_count < 1) {
            return redirect()->route('hr-payroll-show', $run->id)
                ->with('message_error', 'No active staff with a salary were found. Assign a pay grade or basic salary first.');
        }

        return redirect()->route('hr-payroll-show', $run->id)
            ->with('message_success', 'Draft payroll generated for '.$run->periodLabel().'.');
    }

    public function show($id)
    {
        $run = HrPayrollRun::with(['payslips.staff.department', 'payslips.staff.hrPosition'])->findOrFail($id);
        $run->setRelation(
            'payslips',
            $run->payslips->sortBy(fn ($slip) => strtolower($slip->staff?->full_name ?? ''))->values()
        );

        return view('hr.payroll-show', [
            'run' => $run,
        ]);
    }

    public function approve($id)
    {
        $run = HrPayrollRun::findOrFail($id);

        try {
            $this->payroll->approve($run);
        } catch (RuntimeException $exception) {
            return back()->with('message_error', $exception->getMessage());
        }

        return back()->with('message_success', 'Payroll approved.');
    }

    public function markPaid($id)
    {
        $run = HrPayrollRun::findOrFail($id);

        try {
            $this->payroll->markPaid($run);
        } catch (RuntimeException $exception) {
            return back()->with('message_error', $exception->getMessage());
        }

        return back()->with('message_success', 'Payroll marked as paid.');
    }

    public function payslips(Request $request)
    {
        $runId = $request->input('run_id');
        $runs = HrPayrollRun::orderByDesc('period_year')->orderByDesc('period_month')->get();
        $selected = $runId ? $runs->firstWhere('id', (int) $runId) : $runs->first();

        return view('hr.payslips', [
            'runs' => $runs,
            'selected' => $selected,
            'payslips' => $selected
                ? HrPayslip::with('staff')->where('payroll_run_id', $selected->id)->get()
                : collect(),
        ]);
    }

    public function printPayslip($id)
    {
        $payslip = HrPayslip::with(['staff.department', 'staff.hrPosition', 'payrollRun'])->findOrFail($id);

        return view('hr.print-payslip', [
            'payslip' => $payslip,
            'school' => SchoolSetting::current(),
            'title' => 'Payslip',
        ]);
    }
}
