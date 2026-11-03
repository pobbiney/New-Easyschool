<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\ClassCategory;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentBillController extends Controller
{
    public function editIndex()
    {
        return view('billing.edit-student-bills', [
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
        ]);

        $query = trim($validated['q'] ?? '');

        if (strlen($query) < 2) {
            return response()->json(['students' => []]);
        }

        $yearId = (int) $validated['academic_year_id'];
        $termId = (int) $validated['academic_term_id'];

        $students = Student::query()
            ->with(['schoolClass.category'])
            ->where(function ($inner) use ($query) {
                $inner->where('student_id', 'like', "%{$query}%")
                    ->orWhere('firstname', 'like', "%{$query}%")
                    ->orWhere('othername', 'like', "%{$query}%")
                    ->orWhere('surname', 'like', "%{$query}%");
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(30)
            ->get()
            ->map(function (Student $student) use ($yearId, $termId) {
                $bills = $this->billsForPeriodQuery($student->id, $yearId, $termId)->get();

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'class_name' => $student->class_name ?: 'Unassigned',
                    'category_name' => $student->schoolClass?->category?->name,
                    'status' => $student->status,
                    'initials' => strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)),
                    'bill_count' => $bills->count(),
                    'balance' => (float) $bills->sum('balance'),
                ];
            });

        return response()->json(['students' => $students]);
    }

    public function updateBill(Request $request)
    {
        $validated = $request->validate([
            'student_bill_id' => 'required|exists:student_bills,id',
            'amount_due' => 'required|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
        ]);

        $bill = StudentBill::with(['billingItem', 'setup.academicTerm', 'setup.academicYear', 'setup.classCategory'])
            ->findOrFail($validated['student_bill_id']);

        $setup = $bill->setup;
        if (! $setup
            || (int) $setup->academic_year_id !== (int) $validated['academic_year_id']
            || (int) $setup->academic_term_id !== (int) $validated['academic_term_id']) {
            return response()->json([
                'message' => 'This bill does not belong to the selected academic year and term.',
            ], 422);
        }

        if ((int) $bill->academic_year_id !== (int) $validated['academic_year_id']
            || (int) $bill->academic_term_id !== (int) $validated['academic_term_id']) {
            return response()->json([
                'message' => 'This bill does not belong to the selected academic year and term.',
            ], 422);
        }

        if ((float) $validated['amount_due'] < (float) $bill->amount_paid) {
            return response()->json([
                'message' => 'Amount due cannot be less than the amount already paid (₵'.number_format($bill->amount_paid, 2).').',
            ], 422);
        }

        $bill->amount_due = $validated['amount_due'];
        $bill->refreshTotals();
        $bill->save();

        return response()->json([
            'message' => 'Bill updated successfully.',
            'bill' => $this->billPayload($bill),
        ]);
    }

    public function index(Request $request)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);
        $rows = $this->buildLedgerRows($request, $yearId, $termId);
        $allBills = StudentBill::query()->get();

        return view('billing.student-bills', [
            'rows' => $rows,
            'classCategories' => ClassCategory::where('status', 'Active')->orderBy('name')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
            'filters' => $this->ledgerFilters($request, $yearId, $termId),
            'stats' => [
                'students' => $rows->count(),
                'total_due' => $allBills->sum('amount_due'),
                'total_paid' => $allBills->sum('amount_paid'),
                'outstanding' => $allBills->sum('balance'),
            ],
        ]);
    }

    public function printBillsIndex(Request $request)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        return view('billing.print-bills', [
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'schoolClasses' => SchoolClass::query()
                ->with('category')
                ->withCount(['students' => fn ($query) => $query->where('status', 'Active')])
                ->where('status', 'Active')
                ->orderBy('name')
                ->get(),
            'filters' => $this->ledgerFilters($request, $yearId, $termId),
        ]);
    }

    public function printClassStatements(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'school_class_id' => 'required|exists:school_classes,id',
        ]);

        $yearId = (int) $validated['academic_year_id'];
        $termId = (int) $validated['academic_term_id'];
        $classId = (int) $validated['school_class_id'];
        $filters = $this->ledgerFilters($request, $yearId, $termId);
        $schoolClass = SchoolClass::with('category')->findOrFail($classId);

        $students = Student::query()
            ->with(['schoolClass.category'])
            ->where('status', 'Active')
            ->where('school_class_id', $classId)
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();

        $statements = $students
            ->map(fn (Student $student) => $this->buildStatementPayload($student, $yearId, $termId))
            ->values();

        return view('billing.print-class-bills-statements', [
            'statements' => $statements,
            'className' => $schoolClass->name,
            'filterLabels' => $this->ledgerFilterLabels($filters),
            'school' => SchoolSetting::current(),
            'printedAt' => now(),
        ]);
    }

    public function printLedger(Request $request)
    {
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);
        $rows = $this->buildLedgerRows($request, $yearId, $termId);
        $filters = $this->ledgerFilters($request, $yearId, $termId);

        return view('billing.print-student-bills-ledger', [
            'rows' => $rows,
            'filters' => $filters,
            'filterLabels' => $this->ledgerFilterLabels($filters),
            'school' => SchoolSetting::current(),
            'printedAt' => now(),
        ]);
    }

    public function printStatement(Request $request, $id)
    {
        $student = Student::with(['schoolClass.category', 'academicYear', 'academicTerm'])->findOrFail($id);
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);
        $payload = $this->buildStatementPayload($student, $yearId, $termId);

        return view('billing.print-student-bill-statement', [
            'student' => $payload['student'],
            'bills' => $payload['bills'],
            'compulsoryBills' => $payload['compulsoryBills'],
            'optionalBills' => $payload['optionalBills'],
            'arrears' => $payload['arrears'],
            'summary' => $payload['summary'],
            'filterLabels' => $this->ledgerFilterLabels($this->ledgerFilters($request, $yearId, $termId)),
            'school' => SchoolSetting::current(),
            'printedAt' => now(),
        ]);
    }

    private function buildStatementPayload(Student $student, ?int $yearId, ?int $termId): array
    {
        $bills = $this->billsForPeriodQuery($student->id, $yearId, $termId)
            ->orderBy('id')
            ->get();

        $compulsoryBills = $bills
            ->filter(fn (StudentBill $bill) => (bool) $bill->billingItem?->is_compulsory)
            ->values();
        $optionalBills = $bills
            ->reject(fn (StudentBill $bill) => (bool) $bill->billingItem?->is_compulsory)
            ->values();
        $arrears = $this->previousArrears($student->id, $yearId, $termId);

        $balance = (float) $bills->sum('balance');
        $arrearsTotal = (float) $arrears->sum('balance');
        $creditBalance = (float) $student->credit_balance;

        return [
            'student' => $student,
            'bills' => $bills,
            'compulsoryBills' => $compulsoryBills,
            'optionalBills' => $optionalBills,
            'arrears' => $arrears,
            'summary' => [
                'total_due' => (float) $bills->sum('amount_due'),
                'compulsory_due' => (float) $compulsoryBills->sum('amount_due'),
                'optional_due' => (float) $optionalBills->sum('amount_due'),
                'total_paid' => (float) $bills->sum('amount_paid'),
                'balance' => $balance,
                'arrears' => $arrearsTotal,
                'credit_balance' => $creditBalance,
                'net_payable' => max(round($balance + $arrearsTotal - $creditBalance, 2), 0),
            ],
        ];
    }

    private function previousArrears(int $studentId, ?int $yearId, ?int $termId)
    {
        if (! $yearId || ! $termId) {
            return collect();
        }

        $currentYear = AcademicYear::query()->find($yearId);
        $currentTerm = AcademicTerm::query()->find($termId);
        if (! $currentYear || ! $currentTerm) {
            return collect();
        }

        return StudentBill::query()
            ->with(['billingItem', 'academicYear', 'academicTerm', 'setup.academicYear', 'setup.academicTerm'])
            ->where('student_id', $studentId)
            ->where('balance', '>', 0)
            ->where(function ($query) use ($yearId, $termId) {
                $query->where('academic_year_id', '!=', $yearId)
                    ->orWhere('academic_term_id', '!=', $termId);
            })
            ->orderBy('academic_year_id')
            ->orderBy('academic_term_id')
            ->orderBy('id')
            ->get()
            ->filter(fn (StudentBill $bill) => $this->billPeriodIsBefore($bill, $currentYear, $currentTerm))
            ->values();
    }

    private function billPeriodIsBefore(StudentBill $bill, AcademicYear $currentYear, AcademicTerm $currentTerm): bool
    {
        $billYearName = (string) ($bill->academicYear?->name ?? $bill->setup?->academicYear?->name ?? '');
        $currentYearName = (string) $currentYear->name;

        if ($billYearName !== '' && $currentYearName !== '' && $billYearName !== $currentYearName) {
            return $billYearName < $currentYearName;
        }

        $billTermOrder = (int) ($bill->academicTerm?->sort_order ?? $bill->setup?->academicTerm?->sort_order ?? 0);

        return $billTermOrder > 0 && $billTermOrder < (int) $currentTerm->sort_order;
    }

    private function buildLedgerRows(Request $request, ?int $yearId, ?int $termId)
    {
        return Student::query()
            ->with(['schoolClass.category', 'academicYear', 'academicTerm'])
            ->where('status', 'Active')
            ->when($termId, fn ($q, $v) => $q->where('academic_term_id', $v))
            ->when($yearId, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when($request->class_category_id, function ($q, $v) {
                $q->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('class_category_id', $v));
            })
            ->when($request->school_class_id, fn ($q, $v) => $q->where('school_class_id', $v))
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get()
            ->map(function (Student $student) use ($request, $yearId, $termId) {
                $billsQuery = StudentBill::query()->where('student_id', $student->id);

                if ($termId || $yearId || $request->class_category_id) {
                    $billsQuery->whereHas('setup', function ($setupQuery) use ($request, $student, $yearId, $termId) {
                        if ($termId) {
                            $setupQuery->where('academic_term_id', $termId);
                        }
                        if ($yearId) {
                            $setupQuery->where('academic_year_id', $yearId);
                        }
                        if ($request->class_category_id) {
                            $setupQuery->where('class_category_id', $request->class_category_id);
                        } elseif ($student->schoolClass?->class_category_id) {
                            $setupQuery->where('class_category_id', $student->schoolClass->class_category_id);
                        }
                    });
                }

                $bills = $billsQuery->get();
                $totalDue = $bills->sum('amount_due');
                $totalPaid = $bills->sum('amount_paid');
                $balance = $bills->sum('balance');
                $creditBalance = (float) $student->credit_balance;

                $status = 'No Bills';
                if ($bills->isNotEmpty()) {
                    if ($balance <= 0 && $totalPaid > 0) {
                        $status = 'Paid';
                    } elseif ($totalPaid > 0) {
                        $status = 'Partial';
                    } else {
                        $status = 'Pending';
                    }
                }

                return (object) [
                    'student' => $student,
                    'total_due' => $totalDue,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                    'credit_balance' => $creditBalance,
                    'status' => $status,
                ];
            })
            ->when($request->status === 'Has Credit', fn ($collection) => $collection->filter(
                fn ($row) => $row->credit_balance > 0
            ))
            ->when($request->status && $request->status !== 'Has Credit', function ($collection) use ($request) {
                return $collection->filter(fn ($row) => $row->status === $request->status);
            })
            ->values();
    }

    private function ledgerFilters(Request $request, ?int $yearId, ?int $termId): array
    {
        return [
            'academic_term_id' => $request->has('academic_term_id') ? $request->input('academic_term_id') : ($termId ?? ''),
            'academic_year_id' => $request->has('academic_year_id') ? $request->input('academic_year_id') : ($yearId ?? ''),
            'class_category_id' => $request->input('class_category_id', ''),
            'school_class_id' => $request->input('school_class_id', ''),
            'status' => $request->input('status', ''),
        ];
    }

    private function ledgerFilterLabels(array $filters): array
    {
        $labels = [];

        if ($filters['academic_term_id']) {
            $labels[] = AcademicTerm::find($filters['academic_term_id'])?->name.' Term';
        }
        if ($filters['academic_year_id']) {
            $labels[] = AcademicYear::find($filters['academic_year_id'])?->name;
        }
        if ($filters['class_category_id']) {
            $labels[] = ClassCategory::find($filters['class_category_id'])?->name;
        }
        if ($filters['school_class_id']) {
            $labels[] = SchoolClass::find($filters['school_class_id'])?->name;
        }
        if ($filters['status']) {
            $labels[] = $filters['status'];
        }

        return array_values(array_filter($labels));
    }

    public function show(Request $request, $id)
    {
        $student = Student::with(['schoolClass.category', 'academicYear', 'academicTerm'])->findOrFail($id);

        $yearId = $request->query('academic_year_id');
        $termId = $request->query('academic_term_id');

        $bills = $this->billsForPeriodQuery($student->id, $yearId, $termId)
            ->orderBy('id')
            ->get()
            ->map(fn (StudentBill $bill) => $this->billPayload($bill));

        $yearName = $yearId ? AcademicYear::find($yearId)?->name : null;
        $termName = $termId ? AcademicTerm::find($termId)?->name : null;

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'class_name' => $student->class_name,
                'category_name' => $student->schoolClass?->category?->name,
            ],
            'period' => [
                'academic_year_id' => $yearId ? (int) $yearId : null,
                'academic_term_id' => $termId ? (int) $termId : null,
                'year_name' => $yearName,
                'term_name' => $termName,
            ],
            'bills' => $bills,
            'summary' => [
                'total_due' => $bills->sum('amount_due'),
                'total_paid' => $bills->sum('amount_paid'),
                'balance' => $bills->sum('balance'),
                'credit_balance' => (float) $student->credit_balance,
            ],
        ]);
    }

    public function outstanding($id)
    {
        $student = Student::with(['schoolClass.category'])->findOrFail($id);

        $bills = StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear'])
            ->where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->get()
            ->sortByDesc(fn (StudentBill $bill) => $bill->billingItem?->is_compulsory ? 1 : 0)
            ->values()
            ->map(fn (StudentBill $bill) => [
                'id' => $bill->id,
                'item_name' => $bill->billingItem?->name,
                'is_compulsory' => (bool) $bill->billingItem?->is_compulsory,
                'term_name' => $bill->setup?->academicTerm?->name,
                'year_name' => $bill->setup?->academicYear?->name,
                'amount_due' => (float) $bill->amount_due,
                'amount_paid' => (float) $bill->amount_paid,
                'balance' => (float) $bill->balance,
            ]);

        return response()->json([
            'student_id' => $student->id,
            'student_id_code' => $student->student_id,
            'full_name' => $student->full_name,
            'class_name' => $student->class_name,
            'category_name' => $student->schoolClass?->category?->name,
            'bills' => $bills,
            'total_outstanding' => $bills->sum('balance'),
        ]);
    }

    private function billsForPeriodQuery(int $studentId, ?int $yearId = null, ?int $termId = null)
    {
        return StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear', 'setup.classCategory'])
            ->where('student_id', $studentId)
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->when($termId, fn ($query) => $query->where('academic_term_id', $termId));
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
            'amount_due' => $bill->amount_due,
            'amount_paid' => $bill->amount_paid,
            'balance' => $bill->balance,
            'status' => $bill->status,
        ];
    }
}
