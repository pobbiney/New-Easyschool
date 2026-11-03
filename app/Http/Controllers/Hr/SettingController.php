<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrPayrollSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        return view('hr.settings', [
            'settings' => HrPayrollSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'ssnit_employee_rate' => 'required|numeric|min:0|max:100',
            'ssnit_employer_rate' => 'required|numeric|min:0|max:100',
            'ssnit_ceiling' => 'nullable|numeric|min:0',
            'personal_relief' => 'required|numeric|min:0',
            'bands' => 'required|array|min:1',
            'bands.*.up_to' => 'nullable|numeric|min:0',
            'bands.*.rate' => 'required|numeric|min:0|max:100',
        ]);

        $bands = collect($request->input('bands', []))
            ->map(fn ($band) => [
                'up_to' => $band['up_to'] === null || $band['up_to'] === '' ? null : (float) $band['up_to'],
                'rate' => (float) $band['rate'],
            ])
            ->values()
            ->all();

        $settings = HrPayrollSetting::current();
        $settings->update([
            'ssnit_employee_rate' => $request->ssnit_employee_rate,
            'ssnit_employer_rate' => $request->ssnit_employer_rate,
            'ssnit_ceiling' => $request->ssnit_ceiling ?: null,
            'personal_relief' => $request->personal_relief,
            'paye_bands' => $bands,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Statutory settings saved. Confirm these rates against current GRA and SSNIT notices.');
    }
}
