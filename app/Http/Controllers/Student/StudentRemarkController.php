<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentRemark;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentRemarkController extends Controller
{
    public function index(Request $request)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);
        $classId = $request->filled('school_class_id') ? (int) $request->school_class_id : null;
        $studentId = $request->filled('student_id') ? (int) $request->student_id : null;

        $students = collect();
        $student = null;
        $remark = null;

        if ($classId && $yearId && $termId) {
            $students = Student::query()
                ->where('status', 'Active')
                ->where('school_class_id', $classId)
                ->orderBy('surname')
                ->orderBy('firstname')
                ->get();

            $student = $students->firstWhere('id', $studentId);

            if ($student) {
                $remark = StudentRemark::query()
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $yearId)
                    ->where('academic_term_id', $termId)
                    ->first();
            }
        }

        return view('student.remarks', [
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
            'classes' => SchoolClass::query()->where('status', 'Active')->orderBy('name')->get(),
            'yearId' => $yearId,
            'termId' => $termId,
            'classId' => $classId,
            'studentId' => $student?->id,
            'students' => $students,
            'student' => $student,
            'remark' => $remark,
        ]);
    }

    public function list(Request $request)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);
        $classId = $request->filled('school_class_id') ? (int) $request->school_class_id : null;

        $remarks = StudentRemark::query()
            ->with(['student.schoolClass', 'academicYear', 'academicTerm'])
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->when($termId, fn ($query) => $query->where('academic_term_id', $termId))
            ->when($classId, function ($query) use ($classId) {
                $query->whereHas('student', fn ($student) => $student->where('school_class_id', $classId));
            })
            ->get()
            ->sortBy(fn (StudentRemark $remark) => strtolower(($remark->student?->surname ?? '').' '.($remark->student?->firstname ?? '')))
            ->values();

        return view('student.remarks-list', [
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
            'classes' => SchoolClass::query()->where('status', 'Active')->orderBy('name')->get(),
            'yearId' => $yearId,
            'termId' => $termId,
            'classId' => $classId,
            'remarks' => $remarks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'student_id' => 'required|exists:students,id',
            'attitude' => 'nullable|string|max:255',
            'conduct' => 'nullable|string|max:255',
            'interest' => 'nullable|string|max:255',
            'class_teacher_remark' => 'nullable|string|max:500',
            'headteacher_signature' => 'nullable|string|max:255',
        ]);

        $student = Student::query()
            ->where('status', 'Active')
            ->where('school_class_id', $validated['school_class_id'])
            ->find($validated['student_id']);

        if (! $student) {
            return redirect()
                ->route('student-remarks', [
                    'academic_year_id' => $validated['academic_year_id'],
                    'academic_term_id' => $validated['academic_term_id'],
                    'school_class_id' => $validated['school_class_id'],
                ])
                ->with('message_error', 'Choose a student from the selected class.');
        }

        StudentRemark::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => (int) $validated['academic_year_id'],
                'academic_term_id' => (int) $validated['academic_term_id'],
            ],
            [
                'attitude' => $this->blankToNull($validated['attitude'] ?? null),
                'conduct' => $this->blankToNull($validated['conduct'] ?? null),
                'interest' => $this->blankToNull($validated['interest'] ?? null),
                'class_teacher_remark' => $this->blankToNull($validated['class_teacher_remark'] ?? null),
                'headteacher_signature' => $this->blankToNull($validated['headteacher_signature'] ?? null),
                'updated_by' => Auth::id(),
            ]
        );

        return redirect()
            ->route('student-remarks', [
                'academic_year_id' => $validated['academic_year_id'],
                'academic_term_id' => $validated['academic_term_id'],
                'school_class_id' => $validated['school_class_id'],
                'student_id' => $student->id,
            ])
            ->with('message_success', 'Student remarks saved.');
    }

    private function blankToNull(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
