<?php

namespace App\Services\Finance;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillPayment;
use App\Models\BillingItem;
use App\Models\ClassCategory;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolClass;
use App\Models\SchoolVaultTransaction;
use App\Models\StudentBill;
use App\Models\VaultCashReconciliation;
use App\Support\AcademicPeriodDefaults;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchoolVaultReportingService
{
    public function __construct(
        private SchoolVaultService $vaultService,
        private SchoolVaultReconciliationService $reconciliationService,
    ) {}

    /** @return array<string,mixed> */
    public function filterOptions(): array
    {
        return [
            'academicYears' => AcademicYear::query()->orderByDesc('id')->get(),
            'academicTerms' => AcademicTerm::query()->orderBy('sort_order')->get(),
            'classCategories' => ClassCategory::query()->orderBy('name')->get(),
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'expenseCategories' => ExpenseCategory::query()->orderBy('name')->get(),
        ];
    }

    /** @return array{year_id:?int,term_id:?int,year_name:?string,term_name:?string} */
    public function academicPeriod(Request $request): array
    {
        $yearId = $request->filled('academic_year_id') ? (int) $request->input('academic_year_id') : AcademicPeriodDefaults::yearId();
        $termId = $request->filled('academic_term_id') ? (int) $request->input('academic_term_id') : AcademicPeriodDefaults::termId();

        return [
            'year_id' => $yearId,
            'term_id' => $termId,
            'year_name' => $yearId ? AcademicYear::query()->whereKey($yearId)->value('name') : null,
            'term_name' => $termId ? AcademicTerm::query()->whereKey($termId)->value('name') : null,
        ];
    }

    /** @return array{key:string,label:string,from:Carbon,to:Carbon,term_calendar_available:bool} */
    public function dateRange(array $values, ?array $academicPeriod = null): array
    {
        $period = $academicPeriod ?? ['year_id' => null, 'term_id' => null];
        $key = $values['period'] ?? 'month';
        $now = now();
        $calendar = null;

        if ($key === 'today') {
            [$from, $to, $label] = [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'];
        } elseif ($key === 'week') {
            [$from, $to, $label] = [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'];
        } elseif ($key === 'term') {
            $calendar = Schema::hasTable('academic_term_calendars')
                ? DB::table('academic_term_calendars')->where('academic_year_id', $period['year_id'])->where('academic_term_id', $period['term_id'])->first()
                : null;
            $from = $calendar ? Carbon::parse($calendar->opening_date)->startOfDay() : $now->copy()->startOfMonth();
            $to = $calendar ? Carbon::parse($calendar->vacation_date)->endOfDay() : $now->copy()->endOfMonth();
            $label = $calendar ? 'Academic Term' : 'Academic Term · calendar unavailable, using current month';
        } elseif ($key === 'custom') {
            $from = Carbon::parse($values['from_date'])->startOfDay();
            $to = Carbon::parse($values['to_date'])->endOfDay();
            $label = $from->format('d M Y').' – '.$to->format('d M Y');
        } else {
            $key = 'month';
            [$from, $to, $label] = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'];
        }

        return ['key' => $key, 'label' => $label, 'from' => $from, 'to' => $to, 'term_calendar_available' => (bool) $calendar];
    }

    /** @return array<string,mixed> */
    public function collections(Request $request, bool $paginate = true): array
    {
        $academic = $this->academicPeriod($request);
        $billQuery = StudentBill::query()
            ->with(['student.schoolClass.category', 'academicYear', 'academicTerm'])
            ->where('financial_category', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE)
            ->when($academic['year_id'], fn ($q, $id) => $q->where('academic_year_id', $id))
            ->when($academic['term_id'], fn ($q, $id) => $q->where('academic_term_id', $id));

        $this->applyStudentFilters($billQuery, $request);
        if ($request->filled('payment_status')) {
            $billQuery->where('status', $request->input('payment_status'));
        }

        $bills = $billQuery->orderBy('student_id')->get();
        $studentRows = $bills->groupBy('student_id')->map(function (Collection $studentBills) {
            $bill = $studentBills->first();
            $billed = $studentBills->sum(fn ($row) => $this->decimalToMinor((string) $row->amount_due));
            $paid = $studentBills->sum(fn ($row) => $this->decimalToMinor((string) $row->amount_paid));
            $outstanding = $studentBills->sum(fn ($row) => $this->decimalToMinor((string) $row->balance));

            return [
                'student_db_id' => $bill->student_id,
                'student_id' => $bill->student?->student_id ?: '—',
                'student_name' => $bill->student?->full_name ?: 'Unknown student',
                'class' => $bill->student?->schoolClass?->name ?: ($bill->student?->class_name ?: '—'),
                'academic_period' => trim(($bill->academicYear?->name ?: '—').' · '.($bill->academicTerm?->name ?: '—')),
                'billed' => $this->minorToDecimal($billed),
                'paid' => $this->minorToDecimal($paid),
                'outstanding' => $this->minorToDecimal($outstanding),
                'status' => $outstanding === 0 && $paid > 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Pending'),
            ];
        })->sortBy('student_name')->values();

        $paymentQuery = BillPayment::query()
            ->with(['student.schoolClass.category', 'allocations.studentBill.academicYear', 'allocations.studentBill.academicTerm'])
            ->active()
            ->where('financial_category', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE)
            ->whereHas('allocations.studentBill', function ($q) use ($academic) {
                $q->where('financial_category', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE)
                    ->when($academic['year_id'], fn ($query, $id) => $query->where('academic_year_id', $id))
                    ->when($academic['term_id'], fn ($query, $id) => $query->where('academic_term_id', $id));
            });
        $this->applyPaymentFilters($paymentQuery, $request);
        $payments = $paymentQuery->orderByDesc('paid_at')->orderByDesc('id')->get();
        $paymentIds = $payments->pluck('id');
        $postings = SchoolVaultTransaction::query()
            ->where('transaction_type', SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION)
            ->where('source_type', BillPayment::class)
            ->whereIn('source_id', $paymentIds)
            ->get()->keyBy('source_id');

        $paymentRows = $payments->map(function (BillPayment $payment) use ($postings) {
            $bill = $payment->allocations->pluck('studentBill')->filter()->first();
            return [
                'id' => $payment->id,
                'receipt' => $payment->receipt_no,
                'date' => $payment->paid_at,
                'student_id' => $payment->student?->student_id ?: '—',
                'student_name' => $payment->student?->full_name ?: 'Unknown student',
                'academic_period' => trim(($bill?->academicYear?->name ?: '—').' · '.($bill?->academicTerm?->name ?: '—')),
                'method' => $payment->payment_method,
                'amount' => (string) $payment->amount,
                'posted' => $postings->has($payment->id),
                'vault_reference' => $postings->get($payment->id)?->transaction_number,
            ];
        });

        $billedMinor = $bills->sum(fn ($bill) => $this->decimalToMinor((string) $bill->amount_due));
        $paidMinor = $bills->sum(fn ($bill) => $this->decimalToMinor((string) $bill->amount_paid));
        $outstandingMinor = $bills->sum(fn ($bill) => $this->decimalToMinor((string) $bill->balance));
        $postedMinor = $postings->sum(fn ($row) => $this->decimalToMinor((string) $row->amount));

        return [
            ...$this->filterOptions(),
            'academicPeriod' => $academic,
            'values' => $request->query(),
            'summary' => [
                'billed' => $this->minorToDecimal($billedMinor),
                'confirmed' => $this->minorToDecimal($paidMinor),
                'posted' => $this->minorToDecimal($postedMinor),
                'outstanding' => $this->minorToDecimal($outstandingMinor),
                'percentage' => $billedMinor > 0 ? round(($paidMinor / $billedMinor) * 100, 1) : 0,
                'students' => $studentRows->count(),
                'fully_paid' => $studentRows->where('status', 'Paid')->count(),
                'partially_paid' => $studentRows->where('status', 'Partial')->count(),
                'unpaid' => $studentRows->where('status', 'Pending')->count(),
            ],
            'students' => $paginate ? $this->paginateCollection($studentRows, 20, 'students_page', $request) : $studentRows,
            'payments' => $paginate ? $this->paginateCollection($paymentRows, 20, 'payments_page', $request) : $paymentRows,
        ];
    }

    /** @return array<string,mixed> */
    public function transactions(Request $request, bool $paginate = true): array
    {
        $vault = $this->vaultService->vault();
        $all = SchoolVaultTransaction::query()
            ->with(['recorder', 'reversedTransaction'])
            ->where('school_vault_id', $vault->id)
            ->orderBy('transaction_date')->orderBy('id')->get();
        $maps = $this->sourceMaps($all);
        $running = 0;
        $rows = $all->map(function (SchoolVaultTransaction $transaction) use (&$running, $maps) {
            $running += $this->signedMinor($transaction);
            return $this->transactionRow($transaction, $running, $maps);
        });

        $filtered = $rows->filter(function (array $row) use ($request) {
            if ($request->filled('from_date') && $row['date']->lt(Carbon::parse($request->input('from_date'))->startOfDay())) return false;
            if ($request->filled('to_date') && $row['date']->gt(Carbon::parse($request->input('to_date'))->endOfDay())) return false;
            if ($request->filled('transaction_type') && $row['transaction_type'] !== $request->input('transaction_type')) return false;
            if ($request->filled('direction') && $row['direction'] !== $request->input('direction')) return false;
            if ($request->filled('payment_method') && $row['payment_method'] !== $request->input('payment_method')) return false;
            if ($request->filled('expense_category_id') && (int) $row['expense_category_id'] !== (int) $request->input('expense_category_id')) return false;
            if ($request->filled('reference') && ! str_contains(strtolower($row['reference']), strtolower((string) $request->input('reference')))) return false;
            if ($request->filled('source_reference') && ! str_contains(strtolower($row['source_reference']), strtolower((string) $request->input('source_reference')))) return false;
            return true;
        })->sortByDesc(fn ($row) => sprintf('%s-%020d', $row['date']->format('Y-m-d H:i:s.u'), $row['id']))->values();

        $creditMinor = $filtered->sum(fn ($row) => $row['direction'] === SchoolVaultTransaction::DIRECTION_CREDIT ? $this->decimalToMinor($row['amount']) : 0);
        $debitMinor = $filtered->sum(fn ($row) => $row['direction'] === SchoolVaultTransaction::DIRECTION_DEBIT ? $this->decimalToMinor($row['amount']) : 0);

        return [
            ...$this->filterOptions(),
            'values' => $request->query(),
            'currentBalance' => $this->vaultService->availableBalance($vault),
            'totals' => ['credits' => $this->minorToDecimal($creditMinor), 'debits' => $this->minorToDecimal($debitMinor), 'net' => $this->minorToDecimal($creditMinor - $debitMinor)],
            'transactions' => $paginate ? $this->paginateCollection($filtered, 25, 'page', $request) : $filtered,
        ];
    }

    /** @return array<string,mixed> */
    public function statement(Request $request): array
    {
        $academic = $this->academicPeriod($request);
        $range = $this->dateRange($request->query(), $academic);
        $vault = $this->vaultService->vault();
        $before = SchoolVaultTransaction::query()->where('school_vault_id', $vault->id)->where('transaction_date', '<', $range['from'])->get();
        $period = SchoolVaultTransaction::query()->with('reversedTransaction')->where('school_vault_id', $vault->id)
            ->whereBetween('transaction_date', [$range['from'], $range['to']])->orderBy('transaction_date')->orderBy('id')->get();
        $maps = $this->sourceMaps($period);
        $opening = $before->sum(fn ($row) => $this->signedMinor($row));
        $movement = $period->sum(fn ($row) => $this->signedMinor($row));
        $income = [];
        $expenses = [];
        $collectionMinor = 0;
        $expenseMinor = 0;
        $otherGroups = [];

        foreach ($period as $transaction) {
            $classification = $this->classification($transaction);
            $source = $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL ? $transaction->reversedTransaction : $transaction;
            $signed = $this->signedMinor($transaction);
            if ($classification === SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION) {
                $payment = $source?->source_type === BillPayment::class ? $maps['payments']->get((int) $source->source_id) : null;
                if ($payment?->financial_category === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
                    $method = $payment->payment_method ?: 'Unidentified method';
                    $income[$method] = ($income[$method] ?? 0) + $signed;
                    $collectionMinor += $signed;
                } else {
                    $label = $payment ? 'Invalid non-Feeding payment link' : 'Invalid or orphaned collection source';
                    $otherGroups[$label] = ($otherGroups[$label] ?? 0) + $signed;
                }
            } elseif ($classification === SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE) {
                $categoryId = $this->expenseCategoryId($source, $maps);
                $expenses[$categoryId ?: 0] = ($expenses[$categoryId ?: 0] ?? 0) - $signed;
                $expenseMinor -= $signed;
            } else {
                $label = $this->transactionTypeLabel($transaction).' · '.ucfirst($transaction->direction);
                $otherGroups[$label] = ($otherGroups[$label] ?? 0) + $signed;
            }
        }

        $categoryNames = ExpenseCategory::query()->whereIn('id', array_filter(array_keys($expenses)))->pluck('name', 'id');
        $other = $movement - $collectionMinor + $expenseMinor;

        return [
            'academicPeriod' => $academic,
            'academicYears' => AcademicYear::query()->orderByDesc('id')->get(),
            'academicTerms' => AcademicTerm::query()->orderBy('sort_order')->get(),
            'range' => $range,
            'currentBalance' => $this->vaultService->availableBalance($vault),
            'income' => collect($income)->map(fn ($minor, $label) => ['label' => $label, 'amount' => $this->minorToDecimal($minor)])->values(),
            'expenses' => collect($expenses)->map(fn ($minor, $id) => ['label' => $id ? ($categoryNames[$id] ?? 'Uncategorised') : 'Uncategorised', 'amount' => $this->minorToDecimal($minor)])->values(),
            'otherMovements' => collect($otherGroups)->filter()->map(fn ($minor, $label) => ['label' => $label, 'amount' => $this->minorToDecimal($minor)])->values(),
            'summary' => [
                'opening' => $this->minorToDecimal($opening),
                'collections' => $this->minorToDecimal($collectionMinor),
                'expenses' => $this->minorToDecimal($expenseMinor),
                'other_movements' => $this->minorToDecimal($other),
                'net_movement' => $this->minorToDecimal($movement),
                'closing' => $this->minorToDecimal($opening + $movement),
            ],
        ];
    }

    /** @return array<string,mixed> */
    public function reconciliation(Request $request): array
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()))->startOfDay();
        $daily = $this->dailyMovements($date);

        return [
            'date' => $date,
            'integrity' => $this->reconciliationService->report(),
            'daily' => $daily,
            'record' => VaultCashReconciliation::query()->with('recorder')->where('school_vault_id', $this->vaultService->vault()->id)->whereDate('reconciliation_date', $date)->first(),
            'history' => VaultCashReconciliation::query()->with('recorder')->orderByDesc('reconciliation_date')->paginate(20)->withQueryString(),
            'physicalCashSupported' => false,
            'physicalCashLimitation' => 'Opening physical cash and cash-transfer records are not stored. Expected closing cash and variance therefore cannot be calculated reliably.',
        ];
    }

    /** @return array<string,mixed> */
    public function dailyMovements(Carbon $date): array
    {
        $transactions = SchoolVaultTransaction::query()->with('reversedTransaction')
            ->whereBetween('transaction_date', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->get();
        $maps = $this->sourceMaps($transactions);
        $methods = [];
        $cashCollections = 0;
        $cashExpenses = 0;

        foreach ($transactions as $transaction) {
            $classification = $this->classification($transaction);
            $source = $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL ? $transaction->reversedTransaction : $transaction;
            if ($classification === SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION) {
                $payment = $source?->source_type === BillPayment::class ? $maps['payments']->get((int) $source->source_id) : null;
                if ($payment?->financial_category !== BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
                    continue;
                }
                $method = $payment->payment_method ?: 'Unidentified';
                $minor = $this->signedMinor($transaction);
                $methods[$method]['collections'] = ($methods[$method]['collections'] ?? 0) + $minor;
                if ($method === 'Cash') $cashCollections += $minor;
            } elseif ($classification === SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE) {
                $method = $this->expensePaymentMethod($source, $maps) ?: 'Unidentified';
                $minor = -$this->signedMinor($transaction);
                $methods[$method]['expenses'] = ($methods[$method]['expenses'] ?? 0) + $minor;
                if ($method === 'Cash') $cashExpenses += $minor;
            }
        }

        return [
            'methods' => collect($methods)->map(fn ($values, $method) => [
                'method' => $method,
                'collections' => $this->minorToDecimal($values['collections'] ?? 0),
                'expenses' => $this->minorToDecimal($values['expenses'] ?? 0),
                'net' => $this->minorToDecimal(($values['collections'] ?? 0) - ($values['expenses'] ?? 0)),
            ])->values(),
            'cash_collections' => $this->minorToDecimal($cashCollections),
            'cash_expenditure' => $this->minorToDecimal($cashExpenses),
            'cash_net_movement' => $this->minorToDecimal($cashCollections - $cashExpenses),
        ];
    }

    /** @return array<string,Collection> */
    private function sourceMaps(Collection $transactions): array
    {
        $sources = $transactions->flatMap(fn ($row) => [$row, $row->reversedTransaction])->filter();
        return [
            'payments' => BillPayment::query()->whereIn('id', $sources->where('source_type', BillPayment::class)->pluck('source_id'))->get()->keyBy('id'),
            'postings' => ExpenseVaultPosting::query()->whereIn('id', $sources->where('source_type', ExpenseVaultPosting::class)->pluck('source_id'))->get()->keyBy('id'),
            'expenses' => Expense::query()->whereIn('id', $sources->where('source_type', Expense::class)->pluck('source_id'))->get()->keyBy('id'),
        ];
    }

    /** @return array<string,mixed> */
    private function transactionRow(SchoolVaultTransaction $transaction, int $runningMinor, array $maps): array
    {
        $source = $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL ? $transaction->reversedTransaction : $transaction;
        $payment = $source?->source_type === BillPayment::class ? $maps['payments']->get((int) $source->source_id) : null;
        $posting = $source?->source_type === ExpenseVaultPosting::class ? $maps['postings']->get((int) $source->source_id) : null;
        $expense = $source?->source_type === Expense::class ? $maps['expenses']->get((int) $source->source_id) : null;
        $sourceReference = $payment?->receipt_no
            ?: ($posting ? 'EXP-'.str_pad((string) $posting->expense_id, 6, '0', STR_PAD_LEFT).' v'.$posting->version : ($expense?->expense_number ?: '—'));

        return [
            'id' => $transaction->id,
            'reference' => $transaction->transaction_number,
            'date' => $transaction->transaction_date,
            'transaction_type' => $transaction->transaction_type,
            'type' => $this->transactionTypeLabel($transaction),
            'direction' => $transaction->direction,
            'description' => $transaction->description,
            'source_reference' => $sourceReference,
            'amount' => (string) $transaction->amount,
            'credit' => $transaction->direction === SchoolVaultTransaction::DIRECTION_CREDIT ? (string) $transaction->amount : null,
            'debit' => $transaction->direction === SchoolVaultTransaction::DIRECTION_DEBIT ? (string) $transaction->amount : null,
            'running_balance' => $this->minorToDecimal($runningMinor),
            'recorded_by' => $transaction->recorder?->name ?: 'System',
            'reversal_reference' => $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL ? $transaction->reversedTransaction?->transaction_number : null,
            'payment_method' => $payment?->payment_method ?: $posting?->payment_method ?: $expense?->payment_method,
            'expense_category_id' => $this->expenseCategoryId($source, $maps),
        ];
    }

    private function applyStudentFilters($query, Request $request): void
    {
        $query->when($request->input('school_class_id'), fn ($q, $id) => $q->whereHas('student', fn ($student) => $student->where('school_class_id', $id)))
            ->when($request->input('class_category_id'), fn ($q, $id) => $q->whereHas('student.schoolClass', fn ($class) => $class->where('class_category_id', $id)))
            ->when($request->input('student'), function ($q, $search) {
                $q->whereHas('student', fn ($student) => $student->where(function ($names) use ($search) {
                    $names->where('student_id', 'like', '%'.$search.'%')->orWhere('firstname', 'like', '%'.$search.'%')
                        ->orWhere('othername', 'like', '%'.$search.'%')->orWhere('surname', 'like', '%'.$search.'%');
                }));
            });
    }

    private function applyPaymentFilters($query, Request $request): void
    {
        $query->when($request->input('payment_method'), fn ($q, $method) => $q->where('payment_method', $method))
            ->when($request->input('payment_from'), fn ($q, $date) => $q->where('paid_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($request->input('payment_to'), fn ($q, $date) => $q->where('paid_at', '<=', Carbon::parse($date)->endOfDay()));
        $this->applyStudentFilters($query, $request);
    }

    private function expenseCategoryId(?SchoolVaultTransaction $source, array $maps): ?int
    {
        if ($source?->source_type === ExpenseVaultPosting::class) return (int) $maps['postings']->get((int) $source->source_id)?->expense_category_id ?: null;
        if ($source?->source_type === Expense::class) return (int) $maps['expenses']->get((int) $source->source_id)?->expense_category_id ?: null;
        return null;
    }

    private function expensePaymentMethod(?SchoolVaultTransaction $source, array $maps): ?string
    {
        if ($source?->source_type === ExpenseVaultPosting::class) return $maps['postings']->get((int) $source->source_id)?->payment_method;
        if ($source?->source_type === Expense::class) return $maps['expenses']->get((int) $source->source_id)?->payment_method;
        return null;
    }

    private function classification(SchoolVaultTransaction $transaction): string
    {
        return $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL
            ? ($transaction->reversedTransaction?->transaction_type ?: SchoolVaultTransaction::TYPE_REVERSAL)
            : $transaction->transaction_type;
    }

    private function transactionTypeLabel(SchoolVaultTransaction $transaction): string
    {
        return match ($transaction->transaction_type) {
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION => 'Feeding Fee collection',
            SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE => 'Operational expense',
            SchoolVaultTransaction::TYPE_OPENING_BALANCE => 'Opening balance',
            SchoolVaultTransaction::TYPE_AUTHORIZED_ADJUSTMENT => 'Authorized adjustment',
            SchoolVaultTransaction::TYPE_REVERSAL => 'Reversal / compensating entry',
            default => str($transaction->transaction_type)->replace('_', ' ')->title()->toString(),
        };
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input($pageName, 1));
        return (new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, [
            'path' => $request->url(), 'pageName' => $pageName, 'query' => $request->query(),
        ]));
    }

    private function signedMinor(SchoolVaultTransaction $transaction): int
    {
        $minor = $this->decimalToMinor((string) $transaction->amount);
        return $transaction->direction === SchoolVaultTransaction::DIRECTION_CREDIT ? $minor : -$minor;
    }

    public function decimalToMinor(string $amount): int
    {
        $negative = str_starts_with(trim($amount), '-');
        $amount = ltrim(trim($amount), '+-');
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $minor = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
        return $negative ? -$minor : $minor;
    }

    public function minorToDecimal(int $minor): string
    {
        $sign = $minor < 0 ? '-' : '';
        $minor = abs($minor);
        return $sign.intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
