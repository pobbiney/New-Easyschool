<?php

namespace App\Http\Controllers\IdCard;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Staff;
use App\Models\Student;
use App\Support\IdCardEmployeeFields;
use App\Support\IdCardStudentFields;
use App\Support\LogoPalette;
use App\Support\MediaUrl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintIdCardController extends Controller
{
    public function index(Request $request)
    {
        $audience = $request->query('tab') === 'employee' ? 'employee' : 'student';
        $classId = $request->query('class_id');
        $search = trim((string) $request->query('q', ''));
        $school = SchoolSetting::current();

        $students = collect();
        $staff = collect();

        if ($audience === 'student') {
            $query = Student::query()
                ->with('schoolClass')
                ->where('status', 'Active')
                ->orderBy('surname')
                ->orderBy('firstname');

            if ($classId) {
                $query->where('school_class_id', $classId);
            }

            if ($search !== '') {
                $query->where(function ($inner) use ($search) {
                    $inner->where('student_id', 'like', '%'.$search.'%')
                        ->orWhere('firstname', 'like', '%'.$search.'%')
                        ->orWhere('surname', 'like', '%'.$search.'%')
                        ->orWhere('othername', 'like', '%'.$search.'%');
                });
            }

            $students = $query->get();
        } else {
            $staffQuery = Staff::query()
                ->with('hrPosition')
                ->orderBy('surname')
                ->orderBy('firstname');

            if ($search !== '') {
                $staffQuery->where(function ($inner) use ($search) {
                    $inner->where('employee_id', 'like', '%'.$search.'%')
                        ->orWhere('firstname', 'like', '%'.$search.'%')
                        ->orWhere('surname', 'like', '%'.$search.'%')
                        ->orWhere('othername', 'like', '%'.$search.'%');
                });
            }

            $staff = $staffQuery->get();
        }

        return view('id-card.print', [
            'audience' => $audience,
            'classId' => $classId,
            'search' => $search,
            'classes' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
            'students' => $students,
            'staff' => $staff,
            'school' => $school,
            'palette' => LogoPalette::fromSchool($school),
            'logoUrl' => $school->logoUrl(),
        ]);
    }

    public function sheet(Request $request)
    {
        $audience = $request->query('tab') === 'employee' ? 'employee' : 'student';
        $ids = collect($request->query('ids', []))->filter()->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return redirect()
                ->route('print-id-cards', ['tab' => $audience])
                ->with('message_error', 'Select at least one person.');
        }

        $school = SchoolSetting::current();
        $settings = $this->cardSettings();
        $palette = LogoPalette::fromSchool($school);

        if (empty($settings->header_text) && $school) {
            $settings->header_text = $school->name;
        }
        if (empty($settings->card_title)) {
            $settings->card_title = $school->motto ?? 'Student Identity Card';
        }
        if (empty($settings->employee_card_title)) {
            $settings->employee_card_title = 'Employee Identity Card';
        }

        if ($audience === 'employee') {
            $people = Staff::with('hrPosition')->whereIn('id', $ids)->orderBy('surname')->get()
                ->map(fn (Staff $staff) => $this->employeeCard($staff, $settings));
            $cardTitle = $settings->employee_card_title ?? 'Employee Identity Card';
        } else {
            $people = Student::with('schoolClass')->whereIn('id', $ids)->orderBy('surname')->get()
                ->map(fn (Student $student) => $this->studentCard($student, $settings));
            $cardTitle = $settings->card_title ?? ($school->motto ?? 'Student Identity Card');
        }

        if ($people->isEmpty()) {
            return redirect()
                ->route('print-id-cards', ['tab' => $audience])
                ->with('message_error', 'No matching records to print.');
        }

        return view('id-card.print-sheet', [
            'people' => $people,
            'school' => $school,
            'settings' => $settings,
            'palette' => $palette,
            'logoUrl' => $school->logoUrl(),
            'cardTitle' => $cardTitle,
            'signatureUrl' => MediaUrl::resolve($settings->director_signature_path ?? null),
        ]);
    }

    private function cardSettings(): object
    {
        if (Schema::hasTable('id_card_settings')) {
            $row = DB::table('id_card_settings')->orderBy('id')->first();
            if ($row) {
                return $row;
            }
        }

        return (object) [];
    }

    private function studentCard(Student $student, object $settings): array
    {
        $fields = IdCardStudentFields::selected($settings->student_fields ?? null, $settings);
        $labels = collect(IdCardStudentFields::groups())->flatMap(fn ($group) => $group)->all();
        $values = [
            'student_id' => $student->student_id,
            'gender' => $student->gender,
            'dob' => $this->formatDate($student->dob),
            'category' => $student->category,
            'status' => $student->status,
            'nhis_number' => $student->nhis_number,
            'firstname' => $student->firstname,
            'othername' => $student->othername,
            'surname' => $student->surname,
        ];
        $short = [
            'student_id' => 'ID',
            'dob' => 'D.O.B',
            'nhis_number' => 'NHIA',
        ];
        $skip = ['picture', 'full_name', 'firstname', 'othername', 'surname', 'category'];

        return [
            'name' => strtoupper($this->displayName($student, $fields)),
            'role' => in_array('category', $fields, true) && $student->category ? $student->category : 'Student',
            'photo' => $this->photoUrl($student->picture, asset('assets/images/id-card-school-girl.png')),
            'show_photo' => in_array('picture', $fields, true),
            'rows' => $this->rows($fields, $skip, $values, $labels, $short, ['student_id', 'gender', 'nhis_number']),
            'qr' => $this->qrSvg($student->student_id
                ? route('id-card-scan-student', ['code' => $student->student_id])
                : (string) $student->id),
        ];
    }

    private function employeeCard(Staff $staff, object $settings): array
    {
        $fields = IdCardEmployeeFields::selected($settings->employee_fields ?? null);
        $labels = collect(IdCardEmployeeFields::groups())->flatMap(fn ($group) => $group)->all();
        $position = $staff->hrPosition->name ?? $staff->position;
        $values = [
            'employee_id' => $staff->employee_id,
            'title' => $staff->title,
            'gender' => $staff->gender,
            'dob' => $this->formatDate($staff->dob),
            'position' => $position,
            'status' => $staff->status,
            'firstname' => $staff->firstname,
            'othername' => $staff->othername,
            'surname' => $staff->surname,
        ];
        $short = [
            'employee_id' => 'ID',
            'dob' => 'D.O.B',
        ];
        $skip = ['picture', 'full_name', 'firstname', 'othername', 'surname', 'title', 'position'];

        return [
            'name' => strtoupper($this->displayName($staff, $fields)),
            'role' => in_array('position', $fields, true) && $position ? $position : 'Employee',
            'photo' => $this->photoUrl($staff->picture, asset('assets/images/id-card-employee.png')),
            'show_photo' => in_array('picture', $fields, true),
            'rows' => $this->rows($fields, $skip, $values, $labels, $short, ['employee_id', 'gender', 'position']),
            'qr' => $this->qrSvg($staff->employee_id
                ? route('id-card-scan-staff', ['code' => $staff->employee_id])
                : (string) $staff->id),
        ];
    }

    /**
     * @param  list<string>  $fields
     * @param  list<string>  $skip
     * @param  array<string, mixed>  $values
     * @param  array<string, string>  $labels
     * @param  array<string, string>  $short
     * @param  list<string>  $large
     * @return list<array{label:string,value:string,large:bool}>
     */
    private function rows(array $fields, array $skip, array $values, array $labels, array $short, array $large): array
    {
        $out = [];
        foreach ($fields as $key) {
            if (in_array($key, $skip, true)) {
                continue;
            }
            $value = trim((string) ($values[$key] ?? ''));
            $out[] = [
                'label' => $short[$key] ?? ($labels[$key] ?? $key),
                'value' => $value !== '' ? $value : '—',
                'large' => in_array($key, $large, true),
            ];
        }

        return array_slice($out, 0, 5);
    }

    private function displayName(object $person, array $fields): string
    {
        if (in_array('full_name', $fields, true) && ! empty($person->full_name)) {
            return (string) $person->full_name;
        }

        $parts = [];
        if (in_array('title', $fields, true) && ! empty($person->title)) {
            $parts[] = $person->title;
        }
        foreach (['firstname', 'othername', 'surname'] as $key) {
            if (in_array($key, $fields, true) && ! empty($person->{$key})) {
                $parts[] = $person->{$key};
            }
        }

        return trim(implode(' ', $parts)) ?: (string) ($person->full_name ?? 'Name');
    }

    private function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function photoUrl(?string $path, string $fallback): string
    {
        $resolved = MediaUrl::resolve($path);
        if (! $resolved) {
            return $fallback;
        }

        $relative = ltrim($resolved, '/');
        if (is_file(public_path($relative))) {
            return asset($relative);
        }

        return $fallback;
    }

    private function qrSvg(string $payload): string
    {
        $payload = trim($payload) !== '' ? $payload : 'ID';

        return QrCode::size(96)->margin(0)->errorCorrection('H')->generate($payload);
    }
}
