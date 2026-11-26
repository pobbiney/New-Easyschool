<?php

namespace App\Services\Finance;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillPayment;
use App\Models\BillPendingPayment;
use App\Models\BillingItem;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolVaultTransaction;
use App\Models\StudentBill;
use App\Services\UserAccessService;
use App\Support\AcademicPeriodDefaults;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchoolVaultDashboardService
{
    public function __construct(
        private SchoolVaultService $vaultService,
        private SchoolVaultReconciliationService $reconciliationService,
        private UserAccessService $access,
        private SchoolVaultReportingService $reportingService,
    ) {}

    /** @return array<string, mixed> */
    public function payload(Request $request, array $validated = []): array
    {
        $period = $this->academicPeriod($request);
        $range = $this->dateRange($validated, $period);
        $vault = $this->vaultService->vault();
        $periodTransactions = SchoolVaultTransaction::query()
            ->with(['recorder', 'reversedTransaction'])
            ->where('school_vault_id', $vault->id)
            ->whereBetween('transaction_date', [$range['from'], $range['to']])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
        $openingTransactions = SchoolVaultTransaction::query()
            ->where('school_vault_id', $vault->id)
            ->where('transaction_date', '<', $range['from'])
            ->get(['direction', 'amount']);

        $openingMinor = $openingTransactions->sum(fn ($transaction) => $this->signedMinor($transaction));
        $periodMovementMinor = $periodTransactions->sum(fn ($transaction) => $this->signedMinor($transaction));
        $classified = $this->classifiedMovements($periodTransactions);
        $closingMinor = $openingMinor + $periodMovementMinor;
        $recent = $periodTransactions->sortByDesc(fn ($transaction) => sprintf(
            '%s-%020d',
            $transaction->transaction_date?->format('Y-m-d H:i:s.u'),
            $transaction->id,
        ))->take(12)->values();
        $sourceMaps = $this->sourceMaps($periodTransactions->concat($recent));
        $reconciliation = $this->reconciliationService->report();
        $statement = $this->reportingService->statement($request);
        $user = $request->user();
        $sourceAccess = [
            'student-bills' => $user ? $this->access->can('student-bills', $user) : false,
            'expenses' => $user ? $this->access->can('expenses', $user) : false,
        ];

        return [
            'vault' => $vault,
            'currentBalance' => $this->vaultService->availableBalance($vault),
            'range' => [
                ...$range,
                'from_value' => $range['from']->toDateString(),
                'to_value' => $range['to']->toDateString(),
            ],
            'academicPeriod' => $period,
            'academicYears' => AcademicYear::query()->orderByDesc('id')->get(),
            'academicTerms' => AcademicTerm::query()->orderBy('sort_order')->get(),
            'summary' => $statement['summary'],
            'feedingPerformance' => $this->feedingPerformance($period['year_id'], $period['term_id']),
            'trendChart' => $this->trendChart($periodTransactions, $range),
            'expenseChart' => $this->expenseBreakdown($periodTransactions, $sourceMaps),
            'recentTransactions' => $recent->map(fn (SchoolVaultTransaction $transaction) => $this->transactionRow($transaction, $sourceMaps, $sourceAccess)),
            'paymentMethods' => $this->paymentMethodBreakdown($periodTransactions, $sourceMaps),
            'reconciliation' => $this->reconciliationSummary($reconciliation),
            'generatedAt' => now(),
        ];
    }

    /** @return array{year_id:?int,term_id:?int,year_name:?string,term_name:?string} */
    private function academicPeriod(Request $request): array
    {
        $yearId = $request->filled('academic_year_id')
            ? (int) $request->input('academic_year_id')
            : AcademicPeriodDefaults::yearId();
        $termId = $request->filled('academic_term_id')
            ? (int) $request->input('academic_term_id')
            : AcademicPeriodDefaults::termId();

        return [
            'year_id' => $yearId,
            'term_id' => $termId,
            'year_name' => $yearId ? AcademicYear::query()->whereKey($yearId)->value('name') : null,
            'term_name' => $termId ? AcademicTerm::query()->whereKey($termId)->value('name') : null,
        ];
    }

    /** @return array{key:string,label:string,from:Carbon,to:Carbon,term_calendar_available:bool} */
    private function dateRange(array $validated, array $academicPeriod): array
    {
        $key = $validated['period'] ?? 'month';
        $now = now();
        $calendarAvailable = false;

        if ($key === 'today') {
            [$from, $to, $label] = [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'];
        } elseif ($key === 'week') {
            [$from, $to, $label] = [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'];
        } elseif ($key === 'term') {
            $calendar = Schema::hasTable('academic_term_calendars')
                ? DB::table('academic_term_calendars')
                    ->where('academic_year_id', $academicPeriod['year_id'])
                    ->where('academic_term_id', $academicPeriod['term_id'])
                    ->first()
                : null;
            $calendarAvailable = (bool) $calendar;
            $from = $calendar ? Carbon::parse($calendar->opening_date)->startOfDay() : $now->copy()->startOfMonth();
            $to = $calendar ? Carbon::parse($calendar->vacation_date)->endOfDay() : $now->copy()->endOfMonth();
            $label = $calendar ? 'This Term' : 'This Term · calendar unavailable, using current month';
        } elseif ($key === 'custom') {
            $from = Carbon::parse($validated['from_date'])->startOfDay();
            $to = Carbon::parse($validated['to_date'])->endOfDay();
            $label = $from->format('d M Y').' – '.$to->format('d M Y');
        } else {
            $key = 'month';
            [$from, $to, $label] = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'];
        }

        return [
            'key' => $key,
            'label' => $label,
            'from' => $from,
            'to' => $to,
            'term_calendar_available' => $calendarAvailable,
        ];
    }

    /** @return array{collections:int,expenses:int} */
    private function classifiedMovements(Collection $transactions): array
    {
        $collections = 0;
        $expenses = 0;

        foreach ($transactions as $transaction) {
            $classification = $this->classification($transaction);
            $signed = $this->signedMinor($transaction);

            if ($classification === SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION) {
                $collections += $signed;
            } elseif ($classification === SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE) {
                $expenses -= $signed;
            }
        }

        return ['collections' => $collections, 'expenses' => $expenses];
    }

    private function classification(SchoolVaultTransaction $transaction): string
    {
        if ($transaction->transaction_type !== SchoolVaultTransaction::TYPE_REVERSAL) {
            return $transaction->transaction_type;
        }

        return $transaction->reversedTransaction?->transaction_type ?: SchoolVaultTransaction::TYPE_REVERSAL;
    }

    /** @return array<string, mixed> */
    private function feedingPerformance(?int $yearId, ?int $termId): array
    {
        $bills = StudentBill::query()
            ->where('financial_category', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE)
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->when($termId, fn ($query) => $query->where('academic_term_id', $termId))
            ->get(['student_id', 'amount_due', 'amount_paid', 'balance']);
        $billedMinor = $bills->sum(fn ($bill) => $this->decimalToMinor((string) $bill->amount_due));
        $paidMinor = $bills->sum(fn ($bill) => $this->decimalToMinor((string) $bill->amount_paid));
        $outstandingMinor = $bills->sum(fn ($bill) => $this->decimalToMinor((string) $bill->balance));

        return [
            'billed' => $this->minorToDecimal($billedMinor),
            'paid' => $this->minorToDecimal($paidMinor),
            'outstanding' => $this->minorToDecimal($outstandingMinor),
            'percentage' => $billedMinor > 0 ? round(($paidMinor / $billedMinor) * 100, 1) : 0,
            'students' => $bills->pluck('student_id')->unique()->count(),
            'fully_paid' => $bills->filter(fn ($bill) => $this->decimalToMinor((string) $bill->amount_due) > 0
                && $this->decimalToMinor((string) $bill->balance) === 0)->pluck('student_id')->unique()->count(),
            'partially_paid' => $bills->filter(fn ($bill) => $this->decimalToMinor((string) $bill->amount_paid) > 0
                && $this->decimalToMinor((string) $bill->balance) > 0)->pluck('student_id')->unique()->count(),
            'unpaid' => $bills->filter(fn ($bill) => $this->decimalToMinor((string) $bill->amount_paid) === 0
                && $this->decimalToMinor((string) $bill->balance) > 0)->pluck('student_id')->unique()->count(),
        ];
    }

    /** @return array{categories:list<string>,collections:list<float>,expenses:list<float>,grouping:string} */
    private function trendChart(Collection $transactions, array $range): array
    {
        $days = max(1, $range['from']->diffInDays($range['to']) + 1);
        $grouping = $days <= 31 ? 'day' : ($days <= 120 ? 'week' : 'month');
        $buckets = $this->chartBuckets($range['from'], $range['to'], $grouping);
        $values = [];

        foreach ($transactions as $transaction) {
            $key = $this->bucketKey($transaction->transaction_date, $grouping);
            $classification = $this->classification($transaction);
            $signed = $this->signedMinor($transaction);

            if ($classification === SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION) {
                $values[$key]['collections'] = ($values[$key]['collections'] ?? 0) + $signed;
            } elseif ($classification === SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE) {
                $values[$key]['expenses'] = ($values[$key]['expenses'] ?? 0) - $signed;
            }
        }

        return [
            'categories' => array_values($buckets),
            'collections' => collect(array_keys($buckets))->map(fn ($key) => $this->minorToFloat($values[$key]['collections'] ?? 0))->all(),
            'expenses' => collect(array_keys($buckets))->map(fn ($key) => $this->minorToFloat($values[$key]['expenses'] ?? 0))->all(),
            'grouping' => $grouping,
        ];
    }

    /** @return array<string, string> */
    private function chartBuckets(Carbon $from, Carbon $to, string $grouping): array
    {
        $cursor = $grouping === 'week' ? $from->copy()->startOfWeek() : ($grouping === 'month' ? $from->copy()->startOfMonth() : $from->copy()->startOfDay());
        $buckets = [];

        while ($cursor->lte($to)) {
            $key = $this->bucketKey($cursor, $grouping);
            $buckets[$key] = $grouping === 'month' ? $cursor->format('M Y') : ($grouping === 'week' ? 'Week of '.$cursor->format('d M') : $cursor->format('d M'));
            if ($grouping === 'month') {
                $cursor->addMonth();
            } elseif ($grouping === 'week') {
                $cursor->addWeek();
            } else {
                $cursor->addDay();
            }
        }

        return $buckets;
    }

    private function bucketKey(Carbon|string|null $date, string $grouping): string
    {
        $value = Carbon::parse($date);

        return $grouping === 'month'
            ? $value->startOfMonth()->toDateString()
            : ($grouping === 'week' ? $value->startOfWeek()->toDateString() : $value->toDateString());
    }

    /** @return array<string, Collection> */
    private function sourceMaps(Collection $transactions): array
    {
        $sourceTransactions = $transactions
            ->flatMap(fn ($transaction) => [$transaction, $transaction->reversedTransaction])
            ->filter();
        $paymentIds = $sourceTransactions->where('source_type', BillPayment::class)->pluck('source_id')->unique();
        $postingIds = $sourceTransactions->where('source_type', ExpenseVaultPosting::class)->pluck('source_id')->unique();
        $expenseIds = $sourceTransactions->where('source_type', Expense::class)->pluck('source_id')->unique();

        return [
            'payments' => BillPayment::query()->whereIn('id', $paymentIds)->get()->keyBy('id'),
            'postings' => ExpenseVaultPosting::query()->whereIn('id', $postingIds)->get()->keyBy('id'),
            'expenses' => Expense::query()->whereIn('id', $expenseIds)->get()->keyBy('id'),
        ];
    }

    /** @return array{labels:list<string>,series:list<float>} */
    private function expenseBreakdown(Collection $transactions, array $maps): array
    {
        $categoryMinor = [];

        foreach ($transactions as $transaction) {
            if ($this->classification($transaction) !== SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE) {
                continue;
            }

            $source = $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL
                ? $transaction->reversedTransaction
                : $transaction;
            $categoryId = $source?->source_type === ExpenseVaultPosting::class
                ? $maps['postings']->get((int) $source->source_id)?->expense_category_id
                : ($source?->source_type === Expense::class ? $maps['expenses']->get((int) $source->source_id)?->expense_category_id : null);
            $categoryMinor[$categoryId ?: 0] = ($categoryMinor[$categoryId ?: 0] ?? 0) - $this->signedMinor($transaction);
        }

        $names = ExpenseCategory::query()->whereIn('id', array_filter(array_keys($categoryMinor)))->pluck('name', 'id');
        arsort($categoryMinor);

        return [
            'labels' => collect(array_keys($categoryMinor))->map(fn ($id) => $id ? ($names[$id] ?? 'Uncategorised') : 'Uncategorised')->values()->all(),
            'series' => collect(array_values($categoryMinor))->map(fn ($minor) => $this->minorToFloat($minor))->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function transactionRow(SchoolVaultTransaction $transaction, array $maps, array $sourceAccess): array
    {
        $source = $transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL
            ? $transaction->reversedTransaction
            : $transaction;
        $sourceReference = '—';
        $sourceUrl = null;

        if ($source?->source_type === BillPayment::class) {
            $payment = $maps['payments']->get((int) $source->source_id);
            $sourceReference = $payment?->receipt_no ?: 'Payment #'.$source->source_id;
            $sourceUrl = $payment && $sourceAccess['student-bills'] ? route('bill-payment-receipt', $payment->id) : null;
        } elseif ($source?->source_type === ExpenseVaultPosting::class) {
            $posting = $maps['postings']->get((int) $source->source_id);
            $sourceReference = $posting ? 'EXP-'.str_pad((string) $posting->expense_id, 6, '0', STR_PAD_LEFT).' v'.$posting->version : 'Expense posting #'.$source->source_id;
            $sourceUrl = $posting && $sourceAccess['expenses'] ? route('expenses') : null;
        } elseif ($source?->source_type === Expense::class) {
            $expense = $maps['expenses']->get((int) $source->source_id);
            $sourceReference = $expense?->expense_number ?: 'Expense #'.$source->source_id;
            $sourceUrl = $expense && $sourceAccess['expenses'] ? route('expenses') : null;
        } elseif ($transaction->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL) {
            $sourceReference = $transaction->reversedTransaction?->transaction_number ?: 'Original transaction';
        }

        $label = match ($transaction->transaction_type) {
            SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION => 'Feeding Fee collection',
            SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE => ($source?->source_type === ExpenseVaultPosting::class
                && (int) ($maps['postings']->get((int) $source->source_id)?->version ?? 1) > 1) ? 'Expense correction' : 'Operational expense',
            SchoolVaultTransaction::TYPE_REVERSAL => 'Reversal',
            SchoolVaultTransaction::TYPE_OPENING_BALANCE => 'Opening balance',
            SchoolVaultTransaction::TYPE_AUTHORIZED_ADJUSTMENT => 'Adjustment',
            default => str($transaction->transaction_type)->replace('_', ' ')->title()->toString(),
        };

        return [
            'reference' => $transaction->transaction_number,
            'date' => $transaction->transaction_date,
            'type' => $label,
            'description' => $transaction->description,
            'source_reference' => $sourceReference,
            'source_url' => $sourceUrl,
            'credit' => $transaction->direction === SchoolVaultTransaction::DIRECTION_CREDIT ? (string) $transaction->amount : null,
            'debit' => $transaction->direction === SchoolVaultTransaction::DIRECTION_DEBIT ? (string) $transaction->amount : null,
            'responsible' => $transaction->recorder?->name ?: 'System',
        ];
    }

    /** @return list<array{label:string,amount:string,percentage:float}> */
    private function paymentMethodBreakdown(Collection $transactions, array $maps): array
    {
        $collectionTransactions = $transactions->where('transaction_type', SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION);
        $paymentIds = $collectionTransactions->where('source_type', BillPayment::class)->pluck('source_id')->unique();
        $instruments = Schema::hasTable('bill_pending_payments')
            ? BillPendingPayment::query()->whereIn('bill_payment_id', $paymentIds)->where('status', BillPendingPayment::STATUS_APPROVED)->pluck('bank_instrument', 'bill_payment_id')
            : collect();
        $totals = ['Cash' => 0, 'Bank' => 0, 'Cheque' => 0, 'Bankers draft' => 0, 'Paystack' => 0];

        foreach ($collectionTransactions as $transaction) {
            $payment = $maps['payments']->get((int) $transaction->source_id);
            if (! $payment) {
                continue;
            }

            $label = match ($payment->payment_method) {
                'Paystack' => 'Paystack',
                'Cash' => 'Cash',
                'Bank' => match ($instruments[$payment->id] ?? null) {
                    'Cheque' => 'Cheque',
                    'Bankers Draft' => 'Bankers draft',
                    default => 'Bank',
                },
                default => 'Bank',
            };
            $totals[$label] += $this->decimalToMinor((string) $transaction->amount);
        }

        $grandTotal = array_sum($totals);

        return collect($totals)->map(fn ($minor, $label) => [
            'label' => $label,
            'amount' => $this->minorToDecimal($minor),
            'percentage' => $grandTotal > 0 ? round(($minor / $grandTotal) * 100, 1) : 0,
        ])->values()->all();
    }

    /** @return array{has_issues:bool,count:int,types:list<array{key:string,label:string,count:int,amount:string}>} */
    private function reconciliationSummary(array $report): array
    {
        $labels = [
            'missing_payment_credits' => 'Feeding payments missing Vault credits',
            'orphan_collection_entries' => 'Orphaned collection entries',
            'amount_mismatches' => 'Collection amount mismatches',
            'duplicate_sources' => 'Duplicate collection sources',
            'school_fees_links' => 'School Fees incorrectly linked',
            'active_expenses_without_debits' => 'Active expenses missing Vault debits',
            'orphan_expense_debits' => 'Orphaned expense debits',
            'expense_amount_mismatches' => 'Expense amount mismatches',
            'cancelled_expenses_without_reversals' => 'Cancelled expenses missing reversals',
            'duplicate_expense_postings' => 'Duplicate expense postings',
            'incorrect_expense_net_expenditure' => 'Incorrect net expense movements',
        ];
        $types = [];

        foreach ($labels as $key => $label) {
            $issues = collect($report[$key] ?? []);
            if ($issues->isEmpty()) {
                continue;
            }

            $amountMinor = $issues->sum(function ($issue) {
                foreach (['amount', 'payment_amount', 'expense_amount', 'ledger_amount'] as $field) {
                    if (isset($issue[$field])) {
                        return $this->decimalToMinor((string) $issue[$field]);
                    }
                }

                return 0;
            });
            $types[] = [
                'key' => $key,
                'label' => $label,
                'count' => $issues->count(),
                'amount' => $this->minorToDecimal($amountMinor),
            ];
        }

        return [
            'has_issues' => (int) ($report['issue_count'] ?? 0) > 0,
            'count' => (int) ($report['issue_count'] ?? 0),
            'types' => $types,
        ];
    }

    private function signedMinor(object $transaction): int
    {
        $minor = $this->decimalToMinor((string) $transaction->amount);

        return $transaction->direction === SchoolVaultTransaction::DIRECTION_CREDIT ? $minor : -$minor;
    }

    private function decimalToMinor(string $amount): int
    {
        $negative = str_starts_with(trim($amount), '-');
        $value = ltrim(trim($amount), '-');
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $minor = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return $negative ? -$minor : $minor;
    }

    private function minorToDecimal(int $minor): string
    {
        $sign = $minor < 0 ? '-' : '';
        $absolute = abs($minor);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    private function minorToFloat(int $minor): float
    {
        return round($minor / 100, 2);
    }
}
