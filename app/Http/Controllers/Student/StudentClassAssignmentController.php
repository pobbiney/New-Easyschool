<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentDoc;
use App\Services\Billing\StudentBillSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentClassAssignmentController extends Controller
{
    public function index()
    {
        $activeStudents = Student::query()->where('status', 'Active');

        $assignedCount = (clone $activeStudents)
            ->whereNotNull('school_class_id')
            ->whereNotNull('academic_year_id')
            ->whereNotNull('academic_term_id')
            ->count();

        $totalActive = (clone $activeStudents)->count();

        return view('student.student-class-assignment', [
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'schoolClasses' => SchoolClass::with('category')->where('status', 'Active')->orderBy('name')->get(),
            'stats' => [
                'total' => $totalActive,
                'assigned' => $assignedCount,
                'unassigned' => max($totalActive - $assignedCount, 0),
            ],
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'status' => 'nullable|in:Active,Draft,Inactive',
            'school_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $query = trim($validated['q'] ?? '');

        if (strlen($query) < 2 && empty($validated['status']) && empty($validated['school_class_id'])) {
            return response()->json(['students' => []]);
        }

        $students = Student::query()
            ->with(['schoolClass.category'])
            ->when($validated['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($validated['school_class_id'] ?? null, fn ($q, $v) => $q->where('school_class_id', $v))
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('student_id', 'like', "%{$query}%")
                        ->orWhere('firstname', 'like', "%{$query}%")
                        ->orWhere('othername', 'like', "%{$query}%")
                        ->orWhere('surname', 'like', "%{$query}%")
                        ->orWhere('class_name', 'like', "%{$query}%");
                });
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(30)
            ->get()
            ->map(fn (Student $student) => $this->searchResultPayload($student));

        return response()->json(['students' => $students]);
    }

    public function show($id)
    {
        $student = Student::with([
            'schoolClass.category',
            'academicYear',
            'academicTerm',
            'house',
            'dormitory',
            'bed',
            'docs',
        ])->findOrFail($id);

        $bills = StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear', 'setup.classCategory'])
            ->where('student_id', $student->id)
            ->orderBy('id')
            ->get();

        return response()->json([
            'student' => $this->studentProfilePayload($student),
            'bills' => $bills->map(fn (StudentBill $bill) => $this->billPayload($bill)),
            'bill_summary' => [
                'total_due' => $bills->sum('amount_due'),
                'total_paid' => $bills->sum('amount_paid'),
                'balance' => $bills->sum('balance'),
                'outstanding_count' => $bills->where('balance', '>', 0)->count(),
            ],
            'documents' => $student->docs->map(fn (StudentDoc $doc) => [
                'id' => $doc->id,
                'name' => $doc->doc_name,
                'url' => asset($doc->document_path),
            ]),
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
        ]);

        return response()->json(
            app(StudentBillSyncService::class)->previewSetupForEnrollment(
                (int) $validated['school_class_id'],
                (int) $validated['academic_year_id'],
                (int) $validated['academic_term_id']
            )
        );
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        if ($student->status !== 'Active') {
            return response()->json([
                'message' => 'Only active students can be assigned to a class.',
            ], 422);
        }

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        $syncStats = $this->assignStudentToClass(
            $student,
            $schoolClass,
            $academicYear,
            (int) $validated['academic_term_id']
        );

        $preview = app(StudentBillSyncService::class)->previewSetupForEnrollment(
            (int) $validated['school_class_id'],
            (int) $validated['academic_year_id'],
            (int) $validated['academic_term_id']
        );

        $message = sprintf('Student assigned to %s (%s).', $schoolClass->name, $academicYear->name);

        if ($preview['setup_found']) {
            $message .= sprintf(
                ' %d bill(s) created, %d updated.',
                $syncStats['bills_created'],
                $syncStats['bills_updated']
            );
        } else {
            $message .= ' No bill setup found — no bills synced.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'sync_stats' => $syncStats,
                'preview' => $preview,
                'student' => $this->studentProfilePayload($student->fresh([
                    'schoolClass.category',
                    'academicYear',
                    'academicTerm',
                ])),
            ]);
        }

        return back()->with('message_success', $message);
    }

    public function bulkCandidates(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'unassigned_only' => 'nullable|boolean',
        ]);

        $query = trim($validated['q'] ?? '');
        $unassignedOnly = $request->boolean('unassigned_only');

        $students = Student::query()
            ->with(['schoolClass.category'])
            ->where('status', 'Active')
            ->when($unassignedOnly, fn ($q) => $q->whereNull('school_class_id'))
            ->when($validated['school_class_id'] ?? null, fn ($q, $v) => $q->where('school_class_id', $v))
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('student_id', 'like', "%{$query}%")
                        ->orWhere('firstname', 'like', "%{$query}%")
                        ->orWhere('othername', 'like', "%{$query}%")
                        ->orWhere('surname', 'like', "%{$query}%")
                        ->orWhere('class_name', 'like', "%{$query}%");
                });
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(500)
            ->get()
            ->map(fn (Student $student) => array_merge($this->searchResultPayload($student), [
                'school_class_id' => $student->school_class_id,
                'academic_year_id' => $student->academic_year_id,
                'academic_term_id' => $student->academic_term_id,
            ]));

        return response()->json([
            'students' => $students,
            'total' => $students->count(),
        ]);
    }

    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1|max:500',
            'student_ids.*' => 'integer|exists:students,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
        ]);

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);
        $termId = (int) $validated['academic_term_id'];

        $assigned = 0;
        $skipped = [];
        $syncTotals = ['bills_created' => 0, 'bills_updated' => 0];

        DB::transaction(function () use (
            $validated,
            $schoolClass,
            $academicYear,
            $termId,
            &$assigned,
            &$skipped,
            &$syncTotals
        ) {
            foreach (array_unique($validated['student_ids']) as $studentId) {
                $student = Student::find($studentId);

                if (! $student || $student->status !== 'Active') {
                    $skipped[] = [
                        'id' => (int) $studentId,
                        'reason' => 'Student is not active or was not found.',
                    ];

                    continue;
                }

                $syncStats = $this->assignStudentToClass($student, $schoolClass, $academicYear, $termId);
                $syncTotals['bills_created'] += $syncStats['bills_created'];
                $syncTotals['bills_updated'] += $syncStats['bills_updated'];
                $assigned++;
            }
        });

        $preview = app(StudentBillSyncService::class)->previewSetupForEnrollment(
            (int) $validated['school_class_id'],
            (int) $validated['academic_year_id'],
            (int) $validated['academic_term_id']
        );

        $message = sprintf(
            '%d student(s) assigned to %s (%s).',
            $assigned,
            $schoolClass->name,
            $academicYear->name
        );

        if ($skipped) {
            $message .= sprintf(' %d skipped.', count($skipped));
        }

        if ($preview['setup_found']) {
            $message .= sprintf(
                ' %d bill(s) created, %d updated in total.',
                $syncTotals['bills_created'],
                $syncTotals['bills_updated']
            );
        }

        return response()->json([
            'message' => $message,
            'assigned' => $assigned,
            'skipped' => $skipped,
            'sync_stats' => $syncTotals,
            'preview' => $preview,
        ]);
    }

    private function assignStudentToClass(
        Student $student,
        SchoolClass $schoolClass,
        AcademicYear $academicYear,
        int $termId
    ): array {
        $student->school_class_id = $schoolClass->id;
        $student->class_name = $schoolClass->name;
        $student->academic_year_id = $academicYear->id;
        $student->academic_year = $academicYear->name;
        $student->academic_term_id = $termId;
        $student->updated_by = Auth::id();
        $student->save();

        return app(StudentBillSyncService::class)->syncForStudent(
            $student->fresh(['schoolClass.category'])
        );
    }

    private function searchResultPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'full_name' => $student->full_name,
            'picture' => $student->picture ? asset($student->picture) : null,
            'class_name' => $student->class_name ?: 'Unassigned',
            'category_name' => $student->schoolClass?->category?->name,
            'status' => $student->status,
            'initials' => strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)),
        ];
    }

    private function studentProfilePayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'full_name' => $student->full_name,
            'picture' => $student->picture ? asset($student->picture) : null,
            'initials' => strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)),
            'status' => $student->status,
            'gender' => $student->gender,
            'dob' => $student->dob,
            'phone' => $student->phone,
            'email' => $student->email,
            'category' => $student->category,
            'notes' => $student->notes,
            'section' => $student->section,
            'roll_number' => $student->roll_number,
            'class_name' => $student->class_name,
            'school_class_id' => $student->school_class_id,
            'academic_year_id' => $student->academic_year_id,
            'academic_term_id' => $student->academic_term_id,
            'academic_year' => $student->academicYear?->name ?? $student->academic_year,
            'academic_term' => $student->academicTerm?->name,
            'category_name' => $student->schoolClass?->category?->name,
            'father_name' => $student->father_name,
            'father_phone' => $student->father_phone,
            'mother_name' => $student->mother_name,
            'mother_phone' => $student->mother_phone,
            'guardian_name' => $student->guardian_name,
            'guardian_phone' => $student->guardian_phone,
            'guardian_type' => $student->guardian_type,
            'blood_group' => $student->blood_group,
            'height' => $student->height,
            'weight' => $student->weight,
            'has_nhis' => $student->has_nhis,
            'nhis_number' => $student->nhis_number,
            'nhis_card_name' => $student->nhis_card_name,
            'current_address' => $student->current_address,
            'previous_school_name' => $student->previous_school_name,
            'house_name' => $student->house?->name,
            'dormitory_name' => $student->dormitory?->name,
            'bed_label' => $student->bed?->bed_label,
        ];
    }

    private function billPayload(StudentBill $bill): array
    {
        return [
            'id' => $bill->id,
            'item_name' => $bill->billingItem?->name,
            'is_compulsory' => (bool) $bill->billingItem?->is_compulsory,
            'category_name' => $bill->setup?->classCategory?->name,
            'term_name' => $bill->setup?->academicTerm?->name,
            'year_name' => $bill->setup?->academicYear?->name,
            'amount_due' => (float) $bill->amount_due,
            'amount_paid' => (float) $bill->amount_paid,
            'balance' => (float) $bill->balance,
            'status' => $bill->status,
        ];
    }
}
