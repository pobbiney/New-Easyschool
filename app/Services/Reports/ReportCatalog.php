<?php

namespace App\Services\Reports;

use App\Models\AcademicYear;
use App\Models\BillPayment;
use App\Models\ClassAttendance;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\HrDepartment;
use App\Models\HrLeaveRequest;
use App\Models\HrPayslip;
use App\Models\HrPosition;
use App\Models\HrStaffAttendance;
use App\Models\Pos\PosSale;
use App\Models\SchoolClass;
use App\Models\SmsMessage;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentBill;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class ReportCatalog
{
    public const KEYS = [
        'students' => 'Student list',
        'enrolment' => 'Enrolment',
        'staff' => 'Staff list',
        'leave' => 'Leave',
        'staff-attendance' => 'Staff attendance',
        'payroll' => 'Payroll',
        'fee-collection' => 'Fee collection',
        'outstanding-bills' => 'Outstanding bills',
        'pos-sales' => 'POS sales',
        'expenses' => 'Expenses',
        'class-attendance' => 'Class attendance',
        'sms' => 'SMS log',
    ];

    public static function url(string $key): string
    {
        return 'report-'.$key;
    }

    /**
     * @return array<int, array{url: string, key: string, title: string}>
     */
    public static function menu(): array
    {
        return collect(self::KEYS)
            ->map(fn ($title, $key) => [
                'url' => self::url($key),
                'key' => $key,
                'title' => $title,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     key: string,
     *     url: string,
     *     title: string,
     *     subtitle: string,
     *     columns: list<array{key: string, label: string, money?: bool}>,
     *     rows: list<array<string, mixed>>,
     *     totals: list<array{label: string, value: string}>,
     *     filters: list<array{name: string, label: string, type: string, options?: array}>,
     *     values: array<string, mixed>
     * }
     */
    public function build(string $key, Request $request): array
    {
        if (! isset(self::KEYS[$key])) {
            throw new RuntimeException('Unknown report.');
        }

        return match ($key) {
            'students' => $this->students($request),
            'enrolment' => $this->enrolment($request),
            'staff' => $this->staff($request),
            'leave' => $this->leave($request),
            'staff-attendance' => $this->staffAttendance($request),
            'payroll' => $this->payroll($request),
            'fee-collection' => $this->feeCollection($request),
            'outstanding-bills' => $this->outstandingBills($request),
            'pos-sales' => $this->posSales($request),
            'expenses' => $this->expenses($request),
            'class-attendance' => $this->classAttendance($request),
            'sms' => $this->sms($request),
            default => throw new RuntimeException('Unknown report.'),
        };
    }

    private function students(Request $request): array
    {
        $values = [
            'school_class_id' => $request->input('school_class_id'),
            'status' => $request->input('status', 'Active'),
            'gender' => $request->input('gender'),
        ];

        $query = Student::query()->orderBy('surname')->orderBy('firstname');
        $this->whenValue($query, $values['school_class_id'], 'school_class_id');
        $this->whenValue($query, $values['status'], 'status');
        $this->whenValue($query, $values['gender'], 'gender');

        $students = $query->get();

        return $this->pack('students', 'Active learners and their classes.', [
            ['key' => 'student_id', 'label' => 'ID'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'class', 'label' => 'Class'],
            ['key' => 'gender', 'label' => 'Gender'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'phone', 'label' => 'Phone'],
        ], $students->map(fn (Student $student) => [
            'student_id' => $student->student_id,
            'name' => $student->full_name,
            'class' => $student->class_name ?: '—',
            'gender' => $student->gender ?: '—',
            'status' => $student->status,
            'phone' => $student->phone ?: ($student->guardian_phone ?: '—'),
        ])->all(), [
            ['label' => 'Students', 'value' => number_format($students->count())],
        ], $this->studentFilters($values), $values);
    }

    private function enrolment(Request $request): array
    {
        $values = [
            'status' => $request->input('status', 'Active'),
        ];

        $query = Student::query();
        $this->whenValue($query, $values['status'], 'status');

        $rows = $query->get()
            ->groupBy(fn (Student $student) => $student->class_name ?: 'Unassigned')
            ->map(function (Collection $group, string $class) {
                return [
                    'class' => $class,
                    'male' => $group->filter(fn (Student $s) => strtolower((string) $s->gender) === 'male')->count(),
                    'female' => $group->filter(fn (Student $s) => strtolower((string) $s->gender) === 'female')->count(),
                    'other' => $group->filter(fn (Student $s) => ! in_array(strtolower((string) $s->gender), ['male', 'female'], true))->count(),
                    'total' => $group->count(),
                ];
            })
            ->sortBy('class')
            ->values();

        return $this->pack('enrolment', 'Headcount by class and gender.', [
            ['key' => 'class', 'label' => 'Class'],
            ['key' => 'male', 'label' => 'Male'],
            ['key' => 'female', 'label' => 'Female'],
            ['key' => 'other', 'label' => 'Other'],
            ['key' => 'total', 'label' => 'Total'],
        ], $rows->all(), [
            ['label' => 'Classes', 'value' => number_format($rows->count())],
            ['label' => 'Students', 'value' => number_format($rows->sum('total'))],
        ], [
            $this->statusFilter($values['status'], 'Student status'),
        ], $values);
    }

    private function staff(Request $request): array
    {
        $values = [
            'status' => $request->input('status', 'Active'),
            'department_id' => $request->input('department_id'),
            'position_id' => $request->input('position_id'),
        ];

        $query = Staff::query()->with(['department', 'hrPosition'])->orderBy('surname')->orderBy('firstname');
        $this->whenValue($query, $values['status'], 'status');
        $this->whenValue($query, $values['department_id'], 'department_id');
        $this->whenValue($query, $values['position_id'], 'position_id');

        $staff = $query->get();

        return $this->pack('staff', 'Employees and their roles.', [
            ['key' => 'employee_id', 'label' => 'Staff ID'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'department', 'label' => 'Department'],
            ['key' => 'position', 'label' => 'Position'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'status', 'label' => 'Status'],
        ], $staff->map(fn (Staff $member) => [
            'employee_id' => $member->employee_id ?: '—',
            'name' => $member->full_name,
            'department' => $member->department?->name ?: '—',
            'position' => $member->hrPosition?->name ?: ($member->position ?: '—'),
            'phone' => $member->mobile ?: '—',
            'status' => $member->status,
        ])->all(), [
            ['label' => 'Staff', 'value' => number_format($staff->count())],
        ], [
            $this->statusFilter($values['status']),
            [
                'name' => 'department_id',
                'label' => 'Department',
                'type' => 'select',
                'options' => ['' => 'All'] + HrDepartment::orderBy('name')->pluck('name', 'id')->all(),
            ],
            [
                'name' => 'position_id',
                'label' => 'Position',
                'type' => 'select',
                'options' => ['' => 'All'] + HrPosition::orderBy('name')->pluck('name', 'id')->all(),
            ],
        ], $values);
    }

    private function leave(Request $request): array
    {
        $values = $this->dateValues($request) + [
            'status' => $request->input('status'),
        ];

        $query = HrLeaveRequest::query()->with(['staff', 'leaveType'])->orderByDesc('start_date');
        $this->whenDateFrom($query, $values['from_date'], 'start_date');
        $this->whenDateTo($query, $values['to_date'], 'end_date');
        $this->whenValue($query, $values['status'], 'status');

        $rows = $query->get();

        return $this->pack('leave', 'Leave applications and their status.', [
            ['key' => 'staff', 'label' => 'Staff'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'start', 'label' => 'Start'],
            ['key' => 'end', 'label' => 'End'],
            ['key' => 'days', 'label' => 'Days'],
            ['key' => 'status', 'label' => 'Status'],
        ], $rows->map(fn (HrLeaveRequest $row) => [
            'staff' => $row->staff?->full_name ?: '—',
            'type' => $row->leaveType?->name ?: '—',
            'start' => $row->start_date?->format('d M Y'),
            'end' => $row->end_date?->format('d M Y'),
            'days' => $row->days,
            'status' => ucfirst((string) $row->status),
        ])->all(), [
            ['label' => 'Requests', 'value' => number_format($rows->count())],
            ['label' => 'Days', 'value' => number_format((float) $rows->sum('days'))],
        ], [
            ...$this->dateFilters(),
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'],
            ],
        ], $values);
    }

    private function staffAttendance(Request $request): array
    {
        $values = $this->dateValues($request);

        $query = HrStaffAttendance::query()->with('staff')->orderByDesc('date');
        $this->whenDateFrom($query, $values['from_date'], 'date');
        $this->whenDateTo($query, $values['to_date'], 'date');

        $rows = $query->get();

        return $this->pack('staff-attendance', 'Daily staff attendance marks.', [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'staff', 'label' => 'Staff'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'check_in', 'label' => 'Check in'],
            ['key' => 'check_out', 'label' => 'Check out'],
        ], $rows->map(fn (HrStaffAttendance $row) => [
            'date' => $row->date?->format('d M Y'),
            'staff' => $row->staff?->full_name ?: '—',
            'status' => ucfirst((string) $row->status),
            'check_in' => $row->check_in ?: '—',
            'check_out' => $row->check_out ?: '—',
        ])->all(), [
            ['label' => 'Records', 'value' => number_format($rows->count())],
        ], $this->dateFilters(), $values);
    }

    private function payroll(Request $request): array
    {
        $values = [
            'period_year' => $request->input('period_year'),
            'period_month' => $request->input('period_month'),
        ];

        $query = HrPayslip::query()->with(['staff', 'payrollRun'])->orderByDesc('id');
        if ($values['period_year'] || $values['period_month']) {
            $query->whereHas('payrollRun', function ($run) use ($values) {
                if ($values['period_year']) {
                    $run->where('period_year', $values['period_year']);
                }
                if ($values['period_month']) {
                    $run->where('period_month', $values['period_month']);
                }
            });
        }

        $rows = $query->get();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        return $this->pack('payroll', 'Payslip totals by staff and period.', [
            ['key' => 'period', 'label' => 'Period'],
            ['key' => 'staff', 'label' => 'Staff'],
            ['key' => 'gross', 'label' => 'Gross', 'money' => true],
            ['key' => 'ssnit', 'label' => 'SSNIT', 'money' => true],
            ['key' => 'paye', 'label' => 'PAYE', 'money' => true],
            ['key' => 'net', 'label' => 'Net', 'money' => true],
        ], $rows->map(fn (HrPayslip $row) => [
            'period' => $row->payrollRun?->periodLabel() ?: '—',
            'staff' => $row->staff?->full_name ?: '—',
            'gross' => Money::ghs($row->gross),
            'ssnit' => Money::ghs($row->ssnit_employee),
            'paye' => Money::ghs($row->paye),
            'net' => Money::ghs($row->net),
            'gross_raw' => (float) $row->gross,
            'net_raw' => (float) $row->net,
        ])->all(), [
            ['label' => 'Payslips', 'value' => number_format($rows->count())],
            ['label' => 'Gross', 'value' => Money::ghs($rows->sum('gross'))],
            ['label' => 'Net', 'value' => Money::ghs($rows->sum('net'))],
        ], [
            [
                'name' => 'period_year',
                'label' => 'Year',
                'type' => 'select',
                'options' => ['' => 'All'] + collect(range((int) date('Y') - 3, (int) date('Y') + 1))->mapWithKeys(fn ($y) => [$y => $y])->all(),
            ],
            [
                'name' => 'period_month',
                'label' => 'Month',
                'type' => 'select',
                'options' => ['' => 'All'] + $months,
            ],
        ], $values);
    }

    private function feeCollection(Request $request): array
    {
        $values = $this->dateValues($request) + [
            'school_class_id' => $request->input('school_class_id'),
        ];

        $query = BillPayment::query()->with('student')->orderByDesc('paid_at');
        $this->whenDateFrom($query, $values['from_date'], 'paid_at');
        $this->whenDateTo($query, $values['to_date'], 'paid_at');
        if ($values['school_class_id']) {
            $query->whereHas('student', fn ($student) => $student->where('school_class_id', $values['school_class_id']));
        }

        $rows = $query->get();

        return $this->pack('fee-collection', 'Fee payments received.', [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'receipt', 'label' => 'Receipt'],
            ['key' => 'student', 'label' => 'Student'],
            ['key' => 'class', 'label' => 'Class'],
            ['key' => 'method', 'label' => 'Method'],
            ['key' => 'amount', 'label' => 'Amount', 'money' => true],
        ], $rows->map(fn (BillPayment $row) => [
            'date' => $row->paid_at?->format('d M Y H:i'),
            'receipt' => $row->receipt_no,
            'student' => $row->student?->full_name ?: '—',
            'class' => $row->student?->class_name ?: '—',
            'method' => $row->payment_method ?: '—',
            'amount' => Money::ghs($row->amount),
        ])->all(), [
            ['label' => 'Payments', 'value' => number_format($rows->count())],
            ['label' => 'Collected', 'value' => Money::ghs($rows->sum('amount'))],
        ], [
            ...$this->dateFilters(),
            $this->classFilter(),
        ], $values);
    }

    private function outstandingBills(Request $request): array
    {
        $values = [
            'school_class_id' => $request->input('school_class_id'),
            'academic_year_id' => $request->input('academic_year_id'),
        ];

        $query = StudentBill::query()
            ->with(['student', 'billingItem', 'academicYear'])
            ->where('balance', '>', 0)
            ->orderByDesc('balance');
        if ($values['school_class_id']) {
            $query->whereHas('student', fn ($student) => $student->where('school_class_id', $values['school_class_id']));
        }
        $this->whenValue($query, $values['academic_year_id'], 'academic_year_id');

        $rows = $query->get();

        return $this->pack('outstanding-bills', 'Bills that still have a balance.', [
            ['key' => 'student', 'label' => 'Student'],
            ['key' => 'class', 'label' => 'Class'],
            ['key' => 'item', 'label' => 'Item'],
            ['key' => 'year', 'label' => 'Year'],
            ['key' => 'due', 'label' => 'Due', 'money' => true],
            ['key' => 'paid', 'label' => 'Paid', 'money' => true],
            ['key' => 'balance', 'label' => 'Balance', 'money' => true],
        ], $rows->map(fn (StudentBill $row) => [
            'student' => $row->student?->full_name ?: '—',
            'class' => $row->student?->class_name ?: '—',
            'item' => $row->billingItem?->name ?: '—',
            'year' => $row->academicYear?->name ?: '—',
            'due' => Money::ghs($row->amount_due),
            'paid' => Money::ghs($row->amount_paid),
            'balance' => Money::ghs($row->balance),
        ])->all(), [
            ['label' => 'Bills', 'value' => number_format($rows->count())],
            ['label' => 'Outstanding', 'value' => Money::ghs($rows->sum('balance'))],
        ], [
            $this->classFilter(),
            [
                'name' => 'academic_year_id',
                'label' => 'Academic year',
                'type' => 'select',
                'options' => ['' => 'All'] + AcademicYear::orderByDesc('id')->pluck('name', 'id')->all(),
            ],
        ], $values);
    }

    private function posSales(Request $request): array
    {
        $values = $this->dateValues($request);

        $query = PosSale::query()->with(['student', 'cashier'])->orderByDesc('sold_at');
        $this->whenDateFrom($query, $values['from_date'], 'sold_at');
        $this->whenDateTo($query, $values['to_date'], 'sold_at');

        $rows = $query->get();

        return $this->pack('pos-sales', 'Shop sales recorded at the POS.', [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'receipt', 'label' => 'Receipt'],
            ['key' => 'customer', 'label' => 'Customer'],
            ['key' => 'method', 'label' => 'Payment'],
            ['key' => 'total', 'label' => 'Total', 'money' => true],
            ['key' => 'cashier', 'label' => 'Cashier'],
        ], $rows->map(fn (PosSale $row) => [
            'date' => $row->sold_at?->format('d M Y H:i'),
            'receipt' => $row->receipt_no,
            'customer' => $row->buyerLabel(),
            'method' => $row->payment_method,
            'total' => Money::ghs($row->total),
            'cashier' => $row->cashier?->name ?: '—',
        ])->all(), [
            ['label' => 'Sales', 'value' => number_format($rows->count())],
            ['label' => 'Total', 'value' => Money::ghs($rows->sum('total'))],
        ], $this->dateFilters(), $values);
    }

    private function expenses(Request $request): array
    {
        $values = $this->dateValues($request) + [
            'expense_category_id' => $request->input('expense_category_id'),
        ];

        $query = Expense::query()->with(['category', 'recorder'])->orderByDesc('expense_date');
        $this->whenDateFrom($query, $values['from_date'], 'expense_date');
        $this->whenDateTo($query, $values['to_date'], 'expense_date');
        $this->whenValue($query, $values['expense_category_id'], 'expense_category_id');

        $rows = $query->get();

        return $this->pack('expenses', 'Outgoing school spend.', [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'payee', 'label' => 'Paid to'],
            ['key' => 'method', 'label' => 'Method'],
            ['key' => 'reference', 'label' => 'Reference'],
            ['key' => 'amount', 'label' => 'Amount', 'money' => true],
        ], $rows->map(fn (Expense $row) => [
            'date' => $row->expense_date?->format('d M Y'),
            'category' => $row->category?->name ?: '—',
            'payee' => $row->payee,
            'method' => $row->payment_method,
            'reference' => $row->reference ?: '—',
            'amount' => Money::ghs($row->amount),
        ])->all(), [
            ['label' => 'Records', 'value' => number_format($rows->count())],
            ['label' => 'Spent', 'value' => Money::ghs($rows->sum('amount'))],
        ], [
            ...$this->dateFilters(),
            [
                'name' => 'expense_category_id',
                'label' => 'Category',
                'type' => 'select',
                'options' => ['' => 'All'] + ExpenseCategory::orderBy('name')->pluck('name', 'id')->all(),
            ],
        ], $values);
    }

    private function classAttendance(Request $request): array
    {
        $values = $this->dateValues($request) + [
            'school_class_id' => $request->input('school_class_id'),
            'status' => $request->input('status'),
        ];

        $query = ClassAttendance::query()->with(['student', 'schoolClass'])->orderByDesc('date')->orderBy('id');
        $this->whenDateFrom($query, $values['from_date'], 'date');
        $this->whenDateTo($query, $values['to_date'], 'date');
        $this->whenValue($query, $values['school_class_id'], 'school_class_id');
        $this->whenValue($query, $values['status'] ?? null, 'status');

        $records = $query->get();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();
        $total = $records->count();
        $rate = $total ? round((($present + $late) / $total) * 100) : 0;

        $payload = $this->pack('class-attendance', 'See who was present, absent, late, or excused.', [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'class', 'label' => 'Class'],
            ['key' => 'student', 'label' => 'Student'],
            ['key' => 'status', 'label' => 'Status'],
        ], $records->map(function (ClassAttendance $row) {
            $name = $row->student?->full_name ?: '—';
            $compact = preg_replace('/\s+/', '', $name) ?: 'ST';

            return [
                'date' => $row->date?->format('d M Y'),
                'weekday' => $row->date?->format('l'),
                'class' => $row->schoolClass?->name ?: '—',
                'student' => $name,
                'student_id' => $row->student?->student_id ?: '',
                'initials' => strtoupper(substr($compact, 0, 2)),
                'status' => $row->statusLabel(),
                'status_key' => $row->status,
                'notes' => $row->notes,
            ];
        })->all(), [
            ['label' => 'Records', 'value' => number_format($total)],
            ['label' => 'Present', 'value' => number_format($present)],
            ['label' => 'Absent', 'value' => number_format($absent)],
            ['label' => 'Late', 'value' => number_format($late)],
            ['label' => 'Excused', 'value' => number_format($excused)],
        ], [
            ...$this->dateFilters(),
            $this->classFilter(),
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    '' => 'All statuses',
                    'present' => 'Present',
                    'absent' => 'Absent',
                    'late' => 'Late',
                    'excused' => 'Excused',
                ],
            ],
        ], $values);

        $payload['mix'] = [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'rate' => $rate,
        ];

        return $payload;
    }

    private function sms(Request $request): array
    {
        $values = $this->dateValues($request);

        $query = SmsMessage::query()->with('creator')->orderByDesc('created_at');
        $this->whenDateFrom($query, $values['from_date'], 'created_at');
        $this->whenDateTo($query, $values['to_date'], 'created_at');

        $rows = $query->get();

        return $this->pack('sms', 'Messages sent from the school.', [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'audience', 'label' => 'Audience'],
            ['key' => 'message', 'label' => 'Message'],
            ['key' => 'sent', 'label' => 'Sent'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'by', 'label' => 'By'],
        ], $rows->map(fn (SmsMessage $row) => [
            'date' => $row->created_at?->format('d M Y H:i'),
            'audience' => $row->audience_label ?: ucfirst((string) $row->audience),
            'message' => Str::limit((string) $row->message, 80),
            'sent' => $row->sent_count,
            'status' => ucfirst((string) $row->status),
            'by' => $row->creator?->name ?: '—',
        ])->all(), [
            ['label' => 'Campaigns', 'value' => number_format($rows->count())],
            ['label' => 'Messages sent', 'value' => number_format((int) $rows->sum('sent_count'))],
        ], $this->dateFilters(), $values);
    }

    /**
     * @param  list<array{key: string, label: string, money?: bool}>  $columns
     * @param  list<array<string, mixed>>  $rows
     * @param  list<array{label: string, value: string}>  $totals
     * @param  list<array{name: string, label: string, type: string, options?: array}>  $filters
     * @param  array<string, mixed>  $values
     */
    private function pack(string $key, string $subtitle, array $columns, array $rows, array $totals, array $filters, array $values): array
    {
        return [
            'key' => $key,
            'url' => self::url($key),
            'title' => self::KEYS[$key],
            'subtitle' => $subtitle,
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $totals,
            'filters' => $filters,
            'values' => $values,
            'printed_at' => now()->format('d M Y H:i'),
        ];
    }

    private function studentFilters(array $values): array
    {
        return [
            $this->classFilter(),
            $this->statusFilter($values['status'] ?? '', 'Status'),
            [
                'name' => 'gender',
                'label' => 'Gender',
                'type' => 'select',
                'options' => ['' => 'All', 'Male' => 'Male', 'Female' => 'Female'],
            ],
        ];
    }

    private function classFilter(): array
    {
        return [
            'name' => 'school_class_id',
            'label' => 'Class',
            'type' => 'select',
            'options' => ['' => 'All classes'] + SchoolClass::where('status', 'Active')->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    private function statusFilter(string $current, string $label = 'Status'): array
    {
        return [
            'name' => 'status',
            'label' => $label,
            'type' => 'select',
            'options' => ['' => 'All', 'Active' => 'Active', 'Inactive' => 'Inactive'],
        ];
    }

    private function dateFilters(): array
    {
        return [
            ['name' => 'from_date', 'label' => 'From', 'type' => 'date'],
            ['name' => 'to_date', 'label' => 'To', 'type' => 'date'],
        ];
    }

    /**
     * @return array{from_date:?string, to_date:?string}
     */
    private function dateValues(Request $request): array
    {
        return [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];
    }

    private function whenValue($query, $value, string $column): void
    {
        if ($value !== null && $value !== '') {
            $query->where($column, $value);
        }
    }

    private function whenDateFrom($query, $value, string $column): void
    {
        if ($value) {
            $query->whereDate($column, '>=', $value);
        }
    }

    private function whenDateTo($query, $value, string $column): void
    {
        if ($value) {
            $query->whereDate($column, '<=', $value);
        }
    }
}
