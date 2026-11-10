<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDoc;
use App\Models\AcademicYear;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\House;
use App\Services\Billing\StudentBillSyncService;
use App\Services\ParentPortal\ParentAccountService;
use App\Services\Student\StudentBulkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class StudentController extends Controller
{
    public function getAddStudentView()
    {
        $studentCode = $this->generateStudentCode();

        return view('student.add-student', [
            'studentCode' => $studentCode,
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function addStudent(Request $request)
    {
        $request->validate($this->studentValidationRules());
        $request->validate($this->parentValidationRules());

        if ($request->student_record_id) {
            $student = Student::findOrFail($request->student_record_id);
        } else {
            if (Student::where('student_id', $request->student_id)->where('status', '!=', 'Draft')->count() > 0) {
                return back()->with('message_error', 'Student ID already exists.');
            }
            $student = new Student();
            $student->created_by = Auth::id();
        }

        $this->fillStudentFromRequest($student, $request);
        $student->status = $this->resolveFinalStudentStatus($request);
        $student->updated_by = Auth::id();
        $student->save();

        if ($student->status === 'Active') {
            app(StudentBillSyncService::class)->syncForStudent($student->fresh(['schoolClass.category']));
            app(ParentAccountService::class)->syncFromStudent($student->fresh());
        }

        $this->saveStudentDocuments($request, $student->id);

        return redirect()->route('list-students')->with('message_success', 'Student registered successfully.');
    }

    public function downloadBulkStudentTemplate(StudentBulkImportService $importer)
    {
        $classNames = SchoolClass::where('status', 'Active')->orderBy('name')->pluck('name')->all();

        return $importer->downloadTemplate($classNames);
    }

    public function bulkImportStudents(Request $request, StudentBulkImportService $importer)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'students_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $result = $importer->import(
                $request->file('students_file'),
                (int) $request->academic_year_id,
                (int) $request->academic_term_id
            );
        } catch (\Throwable $e) {
            return back()->with('message_error', $e->getMessage());
        }

        if ($result['imported'] === 0 && $result['failed'] > 0) {
            return back()
                ->with('message_error', 'No students were imported. Fix the errors below and try again.')
                ->with('bulk_import_errors', $result['errors']);
        }

        $message = $result['imported'].' student'.($result['imported'] === 1 ? '' : 's').' imported successfully.';
        if ($result['failed'] > 0) {
            $message .= ' '.$result['failed'].' row'.($result['failed'] === 1 ? '' : 's').' failed.';
        }

        return redirect()
            ->route('list-students')
            ->with('message_success', $message)
            ->with('bulk_import_errors', $result['errors']);
    }

    public function saveStudentDraft(Request $request)
    {
        $step = (int) $request->current_step;

        if ($step === 1) {
            $request->validate($this->studentValidationRules());
        }

        if ($step === 2) {
            $request->validate($this->parentValidationRules());
        }

        if ($request->student_record_id) {
            $student = Student::findOrFail($request->student_record_id);
            $previousStatus = $student->status;
        } else {
            if (Student::where('student_id', $request->student_id)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID already exists.',
                ], 422);
            }

            $student = new Student();
            $student->created_by = Auth::id();
            $previousStatus = null;
        }

        $this->fillStudentFromRequest($student, $request);

        if ($previousStatus && $previousStatus !== 'Draft') {
            $student->status = $previousStatus;
        } else {
            $student->status = 'Draft';
        }
        $student->updated_by = Auth::id();
        $student->save();

        $this->saveStudentDocuments($request, $student->id);

        return response()->json([
            'success' => true,
            'message' => 'Progress saved. You can continue later.',
            'student_record_id' => $student->id,
        ]);
    }

    public function getListStudentsView()
    {
        $students = Student::with(['house', 'dormitory', 'bed'])
            ->orderBy('id', 'desc')
            ->get();

        $houses = House::where('status', 'Active')->orderBy('name')->get();
        $schoolClasses = SchoolClass::where('status', 'Active')->orderBy('name')->pluck('name');
        $categories = Student::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('student.list-students', [
            'students' => $students,
            'houses' => $houses,
            'schoolClasses' => $schoolClasses,
            'categories' => $categories,
        ]);
    }

    public function getEditStudentView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);
        $docs = StudentDoc::where('student_id', $decodeId)->get();

        return view('student.edit-student', [
            'student' => $student,
            'id' => $id,
            'docs' => $docs,
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function updateStudent(Request $request, $id)
    {
        $request->validate($this->studentValidationRules());
        $request->validate($this->parentValidationRules());

        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);

        $idExists = Student::where('student_id', $request->student_id)
            ->where('id', '!=', $decodeId)
            ->count();

        if ($idExists > 0) {
            return back()->with('message_error', 'Student ID already exists.');
        }

        $this->fillStudentFromRequest($student, $request);
        $student->status = $this->resolveFinalStudentStatus($request);
        $student->updated_by = Auth::id();
        $student->save();

        if ($student->status === 'Active') {
            app(StudentBillSyncService::class)->syncForStudent($student->fresh(['schoolClass.category']));
            app(ParentAccountService::class)->syncFromStudent($student->fresh());
        }

        $this->saveStudentDocuments($request, $student->id);

        return redirect()->route('list-students')->with('message_success', 'Student updated successfully.');
    }

    public function getStudentDetailsView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);
        $docs = StudentDoc::where('student_id', $decodeId)->get();

        return view('student.view-student-details', [
            'student' => $student,
            'docs' => $docs,
            'id' => $id,
        ]);
    }

    public function printStudentDetailsView($id)
    {
        $decodeId = Crypt::decrypt($id);
        $student = Student::findOrFail($decodeId);

        return view('student.print-student-details', $this->buildStudentPrintViewData($student, $id, true));
    }

    public function publicStudentProfileView($id)
    {
        $student = Student::findOrFail($id);
        $encryptedId = Crypt::encrypt($student->id);

        return view('student.print-student-details', $this->buildStudentPrintViewData($student, $encryptedId, false));
    }

    public function scanByCode(string $code)
    {
        $student = Student::where('student_id', $code)->firstOrFail();
        $encryptedId = Crypt::encrypt($student->id);

        return view('student.print-student-details', $this->buildStudentPrintViewData($student, $encryptedId, false));
    }

    private function buildStudentPrintViewData(Student $student, $encryptedId, $autoPrint)
    {
        $docs = StudentDoc::where('student_id', $student->id)->get();
        $school = SchoolSetting::current();

        return [
            'student' => $student,
            'docs' => $docs,
            'school' => $school,
            'id' => $encryptedId,
            'title' => 'Student Profile',
            'autoPrint' => $autoPrint,
            'isPublicView' => !$autoPrint,
            'profileUrl' => URL::signedRoute('public-student-profile', ['id' => $student->id]),
        ];
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

    private function studentValidationRules()
    {
        return [
            'student_id' => 'required',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'firstname' => 'required',
            'surname' => 'required',
            'gender' => 'required',
            'dob' => 'nullable|string|max:40',
            'phone' => 'nullable|string|max:30',
        ];
    }

    private function parentValidationRules()
    {
        return [
            'father_name' => 'nullable|string|max:120',
            'father_phone' => 'nullable|string|max:30',
            'mother_name' => 'nullable|string|max:120',
            'mother_phone' => 'nullable|string|max:30',
            'guardian_type' => 'nullable|string|max:40',
            'guardian_name' => 'nullable|string|max:120',
            'guardian_phone' => 'nullable|string|max:30',
        ];
    }

    private function fillStudentFromRequest(Student $student, Request $request)
    {
        $schoolClass = SchoolClass::findOrFail($request->school_class_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);

        $student->student_id = trim($request->student_id);
        $student->school_class_id = $schoolClass->id;
        $student->class_name = $schoolClass->name;
        $student->academic_year_id = $academicYear->id;
        $student->academic_year = $academicYear->name;
        $student->academic_term_id = $request->academic_term_id;
        $student->section = $this->cleanInput($request->section) ?? '';
        $student->roll_number = $this->cleanInput($request->roll_number) ?? '';
        $student->firstname = trim($request->firstname);
        $student->othername = $this->cleanInput($request->othername);
        $student->surname = trim($request->surname);
        $student->category = $this->cleanInput($request->category);
        $student->gender = trim($request->gender);
        $student->dob = $this->cleanInput($request->dob) ?? '';
        $student->phone = $this->cleanInput($request->phone) ?? '';
        $student->email = $this->cleanInput($request->email);

        $student->father_name = $this->cleanInput($request->father_name);
        $student->father_phone = $this->cleanInput($request->father_phone);
        $student->father_occupation = $this->cleanInput($request->father_occupation);

        $student->mother_name = $this->cleanInput($request->mother_name);
        $student->mother_phone = $this->cleanInput($request->mother_phone);
        $student->mother_occupation = $this->cleanInput($request->mother_occupation);

        $student->guardian_type = $this->cleanInput($request->guardian_type);
        $student->guardian_name = $this->cleanInput($request->guardian_name);
        $student->guardian_email = $this->cleanInput($request->guardian_email);
        $student->guardian_phone = $this->cleanInput($request->guardian_phone);
        $student->guardian_occupation = $this->cleanInput($request->guardian_occupation);
        $student->guardian_address = $this->cleanInput($request->guardian_address);

        $student->blood_group = $this->cleanInput($request->blood_group);
        $student->height = $this->cleanInput($request->height);
        $student->weight = $this->cleanInput($request->weight);
        $student->has_nhis = $this->cleanInput($request->has_nhis);
        $student->nhis_number = $request->has_nhis === 'Yes'
            ? $this->cleanInput($request->nhis_number)
            : null;
        $student->nhis_card_name = $request->has_nhis === 'Yes'
            ? $this->cleanInput($request->nhis_card_name)
            : null;
        $student->current_address = $this->cleanInput($request->current_address);
        $student->previous_school_name = $this->cleanInput($request->previous_school_name);
        $student->notes = $this->cleanInput($request->notes);

        if ($request->hasFile('picture')) {
            $student->picture = $this->uploadPhoto($request->file('picture'), 'uploads/student-photos', $student->picture);
        } elseif ($request->filled('picture_data')) {
            $stored = $this->storeCapturedPhoto($request->input('picture_data'), 'uploads/student-photos', $student->picture);
            if ($stored) {
                $student->picture = $stored;
            }
        }

        if ($request->hasFile('father_photo')) {
            $student->father_photo = $this->uploadPhoto($request->file('father_photo'), 'uploads/parent-photos', $student->father_photo);
        }

        if ($request->hasFile('mother_photo')) {
            $student->mother_photo = $this->uploadPhoto($request->file('mother_photo'), 'uploads/parent-photos', $student->mother_photo);
        }

        if ($request->hasFile('guardian_photo')) {
            $student->guardian_photo = $this->uploadPhoto($request->file('guardian_photo'), 'uploads/parent-photos', $student->guardian_photo);
        }
    }

    private function resolveFinalStudentStatus(Request $request): string
    {
        $status = trim($request->status ?? '');

        return in_array($status, ['Active', 'Inactive'], true) ? $status : 'Active';
    }

    private function cleanInput($value)
    {
        if ($value === null) {
            return null;
        }

        return trim($value);
    }

    private function uploadPhoto($file, $folder, $oldPath = null)
    {
        if (!empty($oldPath) && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $directory = public_path($folder);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file->move($directory, $filename);

        return $folder . '/' . $filename;
    }

    private function storeCapturedPhoto(?string $dataUrl, string $folder, $oldPath = null): ?string
    {
        if (! is_string($dataUrl) || ! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/i', $dataUrl, $matches)) {
            return null;
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($binary === false || strlen($binary) < 100 || strlen($binary) > 5 * 1024 * 1024) {
            return null;
        }

        if (! empty($oldPath) && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $filename = time().'_'.uniqid().'.'.$extension;
        $directory = public_path($folder);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($directory.DIRECTORY_SEPARATOR.$filename, $binary);

        return $folder.'/'.$filename;
    }

    private function saveStudentDocuments(Request $request, $studentId)
    {
        if (!$request->has('documents')) {
            return;
        }

        foreach ($request->documents as $index => $doc) {
            $file = $request->file("documents.$index.document");

            if (!$file || empty($doc['doc_name'])) {
                continue;
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/studentdocs'), $filename);

            StudentDoc::create([
                'student_id' => $studentId,
                'doc_name' => $doc['doc_name'],
                'document_path' => 'uploads/studentdocs/' . $filename,
                'created_by' => Auth::id(),
            ]);
        }
    }
}
