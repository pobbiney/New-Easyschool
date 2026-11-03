<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $school = SchoolSetting::current();

        return view('settings.school-settings', [
            'school' => $school,
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'focusAcademicSession' => request()->routeIs('academic-session'),
        ]);
    }

    public function update(Request $request)
    {
        $request->merge([
            'email' => trim($request->email ?? '') ?: null,
            'website' => trim($request->website ?? '') ?: null,
            'motto' => trim($request->motto ?? '') ?: null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'motto' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'default_academic_year_id' => [
                'required',
                'exists:academic_years,id',
                function ($attribute, $value, $fail) {
                    if (! AcademicYear::where('id', $value)->where('status', 'Active')->exists()) {
                        $fail('The selected academic year must be active.');
                    }
                },
            ],
            'default_academic_term_id' => [
                'required',
                'exists:academic_terms,id',
                function ($attribute, $value, $fail) {
                    if (! AcademicTerm::where('id', $value)->where('status', 'Active')->exists()) {
                        $fail('The selected academic term must be active.');
                    }
                },
            ],
        ]);

        $school = SchoolSetting::current();
        $school->name = trim($request->name);
        $school->motto = $request->motto;
        $school->address = trim($request->address);
        $school->phone = trim($request->phone);
        $school->email = $request->email;
        $school->website = $request->website;
        $school->default_academic_year_id = $request->default_academic_year_id;
        $school->default_academic_term_id = $request->default_academic_term_id;
        $school->updated_by = Auth::id();

        if ($request->hasFile('logo')) {
            $school->logo_path = $this->uploadLogo($request->file('logo'), $school->logo_path);
        }

        $school->save();

        return redirect()
            ->route('school-settings')
            ->with('message_success', 'School information saved successfully.');
    }

    private function uploadLogo($file, $oldPath = null)
    {
        if (!empty($oldPath) && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $folder = 'uploads/school';

        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        $file->move(public_path($folder), $filename);

        return $folder . '/' . $filename;
    }
}
