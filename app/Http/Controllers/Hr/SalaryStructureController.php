<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDeductionType;
use App\Models\HrEarningType;
use App\Models\HrPayGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryStructureController extends Controller
{
    public function index()
    {
        return view('hr.salary-structures', [
            'grades' => HrPayGrade::withCount('staff')->orderBy('name')->get(),
            'earnings' => HrEarningType::orderBy('name')->get(),
            'deductions' => HrDeductionType::orderBy('name')->get(),
        ]);
    }

    public function storeGrade(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrPayGrade::create([
            'name' => trim($request->name),
            'basic_salary' => $request->basic_salary,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Pay grade added.');
    }

    public function updateGrade(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:hr_pay_grades,id',
            'name' => 'required|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrPayGrade::findOrFail($request->grade_id)->update([
            'name' => trim($request->name),
            'basic_salary' => $request->basic_salary,
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Pay grade updated.');
    }

    public function storeEarning(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:40',
            'method' => 'required|in:fixed,percent_basic',
            'default_amount' => 'required|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrEarningType::create([
            'name' => trim($request->name),
            'code' => strtoupper(trim((string) $request->code)) ?: null,
            'method' => $request->method,
            'default_amount' => $request->default_amount,
            'is_taxable' => $request->boolean('is_taxable'),
            'status' => $request->status,
        ]);

        return back()->with('message_success', 'Earning type added.');
    }

    public function updateEarning(Request $request)
    {
        $request->validate([
            'earning_id' => 'required|exists:hr_earning_types,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:40',
            'method' => 'required|in:fixed,percent_basic',
            'default_amount' => 'required|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrEarningType::findOrFail($request->earning_id)->update([
            'name' => trim($request->name),
            'code' => strtoupper(trim((string) $request->code)) ?: null,
            'method' => $request->method,
            'default_amount' => $request->default_amount,
            'is_taxable' => $request->boolean('is_taxable'),
            'status' => $request->status,
        ]);

        return back()->with('message_success', 'Earning type updated.');
    }

    public function storeDeduction(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:40',
            'method' => 'required|in:fixed,percent_basic',
            'default_amount' => 'required|numeric|min:0',
            'is_statutory' => 'nullable|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrDeductionType::create([
            'name' => trim($request->name),
            'code' => strtoupper(trim((string) $request->code)) ?: null,
            'method' => $request->method,
            'default_amount' => $request->default_amount,
            'is_statutory' => $request->boolean('is_statutory'),
            'status' => $request->status,
        ]);

        return back()->with('message_success', 'Deduction type added.');
    }

    public function updateDeduction(Request $request)
    {
        $request->validate([
            'deduction_id' => 'required|exists:hr_deduction_types,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:40',
            'method' => 'required|in:fixed,percent_basic',
            'default_amount' => 'required|numeric|min:0',
            'is_statutory' => 'nullable|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        HrDeductionType::findOrFail($request->deduction_id)->update([
            'name' => trim($request->name),
            'code' => strtoupper(trim((string) $request->code)) ?: null,
            'method' => $request->method,
            'default_amount' => $request->default_amount,
            'is_statutory' => $request->boolean('is_statutory'),
            'status' => $request->status,
        ]);

        return back()->with('message_success', 'Deduction type updated.');
    }
}
