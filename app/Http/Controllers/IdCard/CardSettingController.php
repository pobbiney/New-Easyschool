<?php

namespace App\Http\Controllers\IdCard;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Support\IdCardEmployeeFields;
use App\Support\IdCardStudentFields;
use App\Support\LogoPalette;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CardSettingController extends Controller
{
    public function index()
    {
        $settings = $this->current();
        $school = SchoolSetting::current();
        $palette = LogoPalette::fromSchool($school);
        $audience = request('tab') === 'employee' ? 'employee' : 'student';

        return view('id-card.settings', [
            'school' => $school,
            'settings' => $settings,
            'palette' => $palette,
            'audience' => $audience,
            'fieldGroups' => IdCardStudentFields::groups(),
            'selectedFields' => IdCardStudentFields::selected($settings->student_fields ?? null, $settings),
            'employeeFieldGroups' => IdCardEmployeeFields::groups(),
            'selectedEmployeeFields' => IdCardEmployeeFields::selected($settings->employee_fields ?? null),
            'signatureUrl' => MediaUrl::resolve($settings->director_signature_path ?? null),
        ]);
    }

    public function update(Request $request)
    {
        $studentKeys = IdCardStudentFields::keys();
        $employeeKeys = IdCardEmployeeFields::keys();

        $validated = $request->validate([
            'card_title' => 'nullable|string|max:120',
            'employee_card_title' => 'nullable|string|max:120',
            'header_text' => 'nullable|string|max:180',
            'footer_text' => 'nullable|string|max:180',
            'director_name' => 'nullable|string|max:120',
            'student_fields' => 'nullable|array',
            'student_fields.*' => ['string', Rule::in($studentKeys)],
            'employee_fields' => 'nullable|array',
            'employee_fields.*' => ['string', Rule::in($employeeKeys)],
            'director_signature' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $selected = array_values(array_unique($validated['student_fields'] ?? []));
        $selectedEmployee = array_values(array_unique($validated['employee_fields'] ?? []));
        $row = $this->current();
        $palette = LogoPalette::fromSchool(SchoolSetting::current());
        $payload = [
            'card_title' => trim((string) ($validated['card_title'] ?? '')) ?: 'Student Identity Card',
            'header_text' => trim((string) ($validated['header_text'] ?? '')) ?: null,
            'footer_text' => trim((string) ($validated['footer_text'] ?? '')) ?: null,
            'accent_color' => $palette['accent'],
            'show_photo' => in_array('picture', $selected, true),
            'show_id_number' => in_array('student_id', $selected, true),
            'show_class' => in_array('class', $selected, true),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('id_card_settings', 'director_name')) {
            $payload['director_name'] = trim((string) ($validated['director_name'] ?? '')) ?: null;
        }

        if (Schema::hasColumn('id_card_settings', 'student_fields')) {
            $payload['student_fields'] = json_encode($selected);
        }

        if (Schema::hasColumn('id_card_settings', 'employee_fields')) {
            $payload['employee_fields'] = json_encode($selectedEmployee);
        }

        if (Schema::hasColumn('id_card_settings', 'employee_card_title')) {
            $payload['employee_card_title'] = trim((string) ($validated['employee_card_title'] ?? '')) ?: 'Employee Identity Card';
        }

        $oldSignature = $row->director_signature_path ?? null;

        if ($request->boolean('remove_director_signature') && Schema::hasColumn('id_card_settings', 'director_signature_path')) {
            $this->deletePublicFile($oldSignature);
            $payload['director_signature_path'] = null;
            $oldSignature = null;
        }

        if ($request->hasFile('director_signature') && Schema::hasColumn('id_card_settings', 'director_signature_path')) {
            $payload['director_signature_path'] = $this->storeSignature($request->file('director_signature'), $oldSignature);
        }

        if ($row) {
            DB::table('id_card_settings')->where('id', $row->id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('id_card_settings')->insert($payload);
        }

        $tab = $request->input('tab') === 'employee' ? 'employee' : 'student';

        return redirect()
            ->route('id-card-settings', ['tab' => $tab])
            ->with('message_success', 'ID card settings saved.');
    }

    private function current(): ?object
    {
        if (! Schema::hasTable('id_card_settings')) {
            return null;
        }

        return DB::table('id_card_settings')->orderBy('id')->first();
    }

    private function storeSignature($file, ?string $oldPath = null): string
    {
        $this->deletePublicFile($oldPath);

        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $folder = 'uploads/id-card';

        if (! is_dir(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        $file->move(public_path($folder), $filename);

        return $folder.'/'.$filename;
    }

    private function deletePublicFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        $full = public_path(ltrim(str_replace('\\', '/', $path), '/'));
        if (is_file($full)) {
            unlink($full);
        }
    }
}
