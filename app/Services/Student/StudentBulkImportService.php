<?php

namespace App\Services\Student;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\Billing\StudentBillSyncService;
use App\Services\ParentPortal\ParentAccountService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentBulkImportService
{
    public const HEADERS = [
        'student_id',
        'firstname',
        'othername',
        'surname',
        'gender',
        'dob',
        'phone',
        'email',
        'class_name',
        'category',
        'status',
        'father_name',
        'father_phone',
        'father_occupation',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'guardian_type',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_occupation',
        'guardian_address',
        'blood_group',
        'height',
        'weight',
        'has_nhis',
        'nhis_number',
        'nhis_card_name',
        'current_address',
        'previous_school_name',
        'notes',
    ];

    private const MAX_ROWS = 500;

    public function __construct(
        private StudentBillSyncService $billSync,
        private ParentAccountService $parentAccounts,
    ) {}

    public function downloadTemplate(array $classNames = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $header);
        }

        $sample = [
            'student_id' => '',
            'firstname' => 'Ama',
            'othername' => 'Serwaa',
            'surname' => 'Mensah',
            'gender' => 'Female',
            'dob' => '2015-03-12',
            'phone' => '',
            'email' => '',
            'class_name' => $classNames[0] ?? 'GRADE 1A',
            'category' => 'Day Student',
            'status' => 'Active',
            'father_name' => 'Kwame Mensah',
            'father_phone' => '0244123456',
            'father_occupation' => 'Trader',
            'mother_name' => 'Akosua Mensah',
            'mother_phone' => '0244987654',
            'mother_occupation' => 'Teacher',
            'guardian_type' => 'Mother',
            'guardian_name' => 'Akosua Mensah',
            'guardian_phone' => '0244987654',
            'guardian_email' => '',
            'guardian_occupation' => 'Teacher',
            'guardian_address' => 'Accra',
            'blood_group' => '',
            'height' => '',
            'weight' => '',
            'has_nhis' => 'No',
            'nhis_number' => '',
            'nhis_card_name' => '',
            'current_address' => 'Accra',
            'previous_school_name' => '',
            'notes' => '',
        ];

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'2', $sample[$header] ?? '');
        }

        $help = $spreadsheet->createSheet();
        $help->setTitle('Instructions');
        $help->setCellValue('A1', 'EasySchool student bulk upload');
        $help->setCellValue('A3', '1. Keep the header row exactly as provided.');
        $help->setCellValue('A4', '2. Leave student_id blank to auto-generate an ID.');
        $help->setCellValue('A5', '3. class_name must match an existing Active class name exactly (see Classes sheet).');
        $help->setCellValue('A6', '4. gender must be Male or Female.');
        $help->setCellValue('A7', '5. guardian_type must be Father, Mother, or Others.');
        $help->setCellValue('A8', '6. Academic year and term are selected on the upload form and applied to every row.');
        $help->setCellValue('A9', '7. Required columns: firstname, surname, gender, class_name, father_name, father_phone, mother_name, mother_phone, guardian_type, guardian_name, guardian_phone.');
        $help->setCellValue('A10', '8. Maximum '.self::MAX_ROWS.' students per file.');

        $classesSheet = $spreadsheet->createSheet();
        $classesSheet->setTitle('Classes');
        $classesSheet->setCellValue('A1', 'class_name');
        foreach (array_values($classNames) as $i => $name) {
            $classesSheet->setCellValue('A'.($i + 2), $name);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'easyschool-students-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{imported:int, failed:int, errors:list<string>}
     */
    public function import(UploadedFile $file, int $academicYearId, int $academicTermId): array
    {
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $classes = SchoolClass::query()
            ->where('status', 'Active')
            ->get()
            ->keyBy(fn (SchoolClass $class) => $this->normalizeKey($class->name));

        $rows = $this->readRows($file);
        $imported = 0;
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            try {
                $this->importRow($row, $rowNumber, $academicYear, $academicTermId, $classes);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = 'Row '.$rowNumber.': '.$e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'failed' => count($errors),
            'errors' => array_slice($errors, 0, 50),
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @param  \Illuminate\Support\Collection<string, SchoolClass>  $classes
     */
    private function importRow(array $row, int $rowNumber, AcademicYear $academicYear, int $academicTermId, $classes): void
    {
        $required = [
            'firstname', 'surname', 'gender', 'class_name',
            'father_name', 'father_phone', 'mother_name', 'mother_phone',
            'guardian_type', 'guardian_name', 'guardian_phone',
        ];

        foreach ($required as $field) {
            if ($this->blank($row[$field] ?? null)) {
                throw new \RuntimeException($field.' is required.');
            }
        }

        $gender = $this->normalizeGender($row['gender']);
        if (! $gender) {
            throw new \RuntimeException('gender must be Male or Female.');
        }

        $guardianType = $this->normalizeGuardianType($row['guardian_type']);
        if (! $guardianType) {
            throw new \RuntimeException('guardian_type must be Father, Mother, or Others.');
        }

        $classKey = $this->normalizeKey($row['class_name']);
        $schoolClass = $classes->get($classKey);
        if (! $schoolClass) {
            throw new \RuntimeException('class_name "'.$row['class_name'].'" was not found among Active classes.');
        }

        $studentId = trim((string) ($row['student_id'] ?? ''));
        if ($studentId === '') {
            $studentId = $this->generateStudentCode();
        } elseif (Student::where('student_id', $studentId)->where('status', '!=', 'Draft')->exists()) {
            throw new \RuntimeException('student_id "'.$studentId.'" already exists.');
        }

        $status = $this->normalizeStatus($row['status'] ?? 'Active');

        $student = new Student();
        $student->student_id = $studentId;
        $student->school_class_id = $schoolClass->id;
        $student->class_name = $schoolClass->name;
        $student->academic_year_id = $academicYear->id;
        $student->academic_year = $academicYear->name;
        $student->academic_term_id = $academicTermId;
        $student->section = '';
        $student->roll_number = '';
        $student->firstname = trim($row['firstname']);
        $student->othername = $this->nullable($row['othername'] ?? null);
        $student->surname = trim($row['surname']);
        $student->category = $this->nullable($row['category'] ?? null) ?? 'Day Student';
        $student->gender = $gender;
        $student->dob = $this->nullable($row['dob'] ?? null) ?? '';
        $student->phone = $this->nullable($row['phone'] ?? null) ?? '';
        $student->email = $this->nullable($row['email'] ?? null);
        $student->father_name = trim($row['father_name']);
        $student->father_phone = trim($row['father_phone']);
        $student->father_occupation = $this->nullable($row['father_occupation'] ?? null);
        $student->mother_name = trim($row['mother_name']);
        $student->mother_phone = trim($row['mother_phone']);
        $student->mother_occupation = $this->nullable($row['mother_occupation'] ?? null);
        $student->guardian_type = $guardianType;
        $student->guardian_name = trim($row['guardian_name']);
        $student->guardian_phone = trim($row['guardian_phone']);
        $student->guardian_email = $this->nullable($row['guardian_email'] ?? null);
        $student->guardian_occupation = $this->nullable($row['guardian_occupation'] ?? null);
        $student->guardian_address = $this->nullable($row['guardian_address'] ?? null);
        $student->blood_group = $this->nullable($row['blood_group'] ?? null);
        $student->height = $this->nullable($row['height'] ?? null);
        $student->weight = $this->nullable($row['weight'] ?? null);
        $student->has_nhis = $this->nullable($row['has_nhis'] ?? null);
        $student->nhis_number = ($student->has_nhis === 'Yes') ? $this->nullable($row['nhis_number'] ?? null) : null;
        $student->nhis_card_name = ($student->has_nhis === 'Yes') ? $this->nullable($row['nhis_card_name'] ?? null) : null;
        $student->current_address = $this->nullable($row['current_address'] ?? null);
        $student->previous_school_name = $this->nullable($row['previous_school_name'] ?? null);
        $student->notes = $this->nullable($row['notes'] ?? null);
        $student->status = $status;
        $student->created_by = Auth::id();
        $student->updated_by = Auth::id();
        $student->save();

        if ($student->status === 'Active') {
            $this->billSync->syncForStudent($student->fresh(['schoolClass.category']));
            $this->parentAccounts->syncFromStudent($student->fresh());
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        if ($highestRow < 2) {
            throw new \RuntimeException('The file has no data rows. Download the template and add students.');
        }

        if (($highestRow - 1) > self::MAX_ROWS) {
            throw new \RuntimeException('Too many rows. Upload at most '.self::MAX_ROWS.' students at a time.');
        }

        $headerMap = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $raw = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col).'1')->getValue());
            $key = $this->normalizeHeader($raw);
            if ($key !== '') {
                $headerMap[$col] = $key;
            }
        }

        foreach (['firstname', 'surname', 'gender', 'class_name'] as $requiredHeader) {
            if (! in_array($requiredHeader, $headerMap, true)) {
                throw new \RuntimeException('Missing required column "'.$requiredHeader.'" in the header row.');
            }
        }

        $rows = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $data = array_fill_keys(self::HEADERS, '');
            foreach ($headerMap as $col => $key) {
                if (! array_key_exists($key, $data)) {
                    continue;
                }
                $value = $sheet->getCell(Coordinate::stringFromColumnIndex($col).$row)->getFormattedValue();
                $data[$key] = trim((string) $value);
            }
            $rows[$row] = $data;
        }

        return $rows;
    }

    private function generateStudentCode(): string
    {
        $prefix = SchoolSetting::current()->codeInitials();
        $year = now()->format('Y');

        for ($attempt = 0; $attempt < 40; $attempt++) {
            $code = sprintf('%s-%04d-%s', $prefix, random_int(0, 9999), $year);
            if (! Student::where('student_id', $code)->exists()) {
                return $code;
            }
        }

        return sprintf('%s-%04d-%s', $prefix, random_int(0, 9999), $year);
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace([' ', '-'], '_', $header);

        $aliases = [
            'first_name' => 'firstname',
            'other_name' => 'othername',
            'last_name' => 'surname',
            'class' => 'class_name',
            'date_of_birth' => 'dob',
        ];

        return $aliases[$header] ?? $header;
    }

    private function normalizeKey(?string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    private function normalizeGender(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'male', 'm' => 'Male',
            'female', 'f' => 'Female',
            default => null,
        };
    }

    private function normalizeGuardianType(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'father' => 'Father',
            'mother' => 'Mother',
            'other', 'others' => 'Others',
            default => null,
        };
    }

    private function normalizeStatus(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'inactive' => 'Inactive',
            default => 'Active',
        };
    }

    private function blank($value): bool
    {
        return trim((string) $value) === '';
    }

    private function nullable($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
