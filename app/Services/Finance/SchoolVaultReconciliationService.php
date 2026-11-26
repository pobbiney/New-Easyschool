<?php

namespace App\Services\Finance;

use App\Models\BillPayment;
use App\Models\BillingItem;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolVaultTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SchoolVaultReconciliationService
{
    /**
     * @return array{
     *   missing_payment_credits:Collection,
     *   orphan_collection_entries:Collection,
     *   amount_mismatches:Collection,
     *   duplicate_sources:Collection,
     *   school_fees_links:Collection,
     *   issue_count:int
     * }
     */
    public function report(): array
    {
        $feedingPayments = BillPayment::query()
            ->with(['allocations'])
            ->active()
            ->where('financial_category', BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE)
            ->get();
        $collectionEntries = SchoolVaultTransaction::query()
            ->where('transaction_type', SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION)
            ->get();
        $entriesByPayment = $collectionEntries
            ->where('source_type', BillPayment::class)
            ->keyBy('source_id');

        $missing = $feedingPayments
            ->reject(fn (BillPayment $payment) => $entriesByPayment->has($payment->id))
            ->map(fn (BillPayment $payment) => [
                'payment_id' => $payment->id,
                'receipt_no' => $payment->receipt_no,
                'amount' => (string) $payment->amount,
            ])->values();

        $paymentIds = BillPayment::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $orphan = $collectionEntries
            ->filter(fn (SchoolVaultTransaction $entry) => $entry->source_type !== BillPayment::class
                || ! in_array((int) $entry->source_id, $paymentIds, true))
            ->map(fn (SchoolVaultTransaction $entry) => [
                'transaction_id' => $entry->id,
                'transaction_number' => $entry->transaction_number,
                'source_type' => $entry->source_type,
                'source_id' => $entry->source_id,
            ])->values();

        $mismatches = collect();
        foreach ($feedingPayments as $payment) {
            $entry = $entriesByPayment->get($payment->id);

            if (! $entry) {
                continue;
            }

            $paymentMinor = $this->decimalToMinor((string) $payment->amount);
            $ledgerMinor = $this->decimalToMinor((string) $entry->amount);
            $allocatedMinor = $payment->allocations->sum(
                fn ($allocation) => $this->decimalToMinor((string) $allocation->amount)
            );

            if ($paymentMinor !== $ledgerMinor || $paymentMinor !== $allocatedMinor) {
                $mismatches->push([
                    'payment_id' => $payment->id,
                    'receipt_no' => $payment->receipt_no,
                    'transaction_id' => $entry->id,
                    'payment_amount' => (string) $payment->amount,
                    'allocated_amount' => $this->minorToDecimal($allocatedMinor),
                    'ledger_amount' => (string) $entry->amount,
                ]);
            }
        }

        $duplicates = SchoolVaultTransaction::query()
            ->select('school_vault_id', 'transaction_type', 'source_type', 'source_id')
            ->selectRaw('COUNT(*) as entry_count')
            ->groupBy('school_vault_id', 'transaction_type', 'source_type', 'source_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $schoolFeesLinks = $collectionEntries
            ->where('source_type', BillPayment::class)
            ->map(function (SchoolVaultTransaction $entry) {
                $payment = BillPayment::find($entry->source_id);

                if (! $payment || $payment->financial_category === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
                    return null;
                }

                return [
                    'transaction_id' => $entry->id,
                    'transaction_number' => $entry->transaction_number,
                    'payment_id' => $payment->id,
                    'receipt_no' => $payment->receipt_no,
                    'payment_category' => $payment->financial_category,
                ];
            })->filter()->values();

        $expenseReport = $this->expenseReport();

        return [
            'missing_payment_credits' => $missing,
            'orphan_collection_entries' => $orphan,
            'amount_mismatches' => $mismatches,
            'duplicate_sources' => $duplicates,
            'school_fees_links' => $schoolFeesLinks,
            ...$expenseReport,
            'issue_count' => $missing->count()
                + $orphan->count()
                + $mismatches->count()
                + $duplicates->count()
                + $schoolFeesLinks->count()
                + collect($expenseReport)->sum(fn (Collection $issues) => $issues->count()),
        ];
    }

    /** @return array<string, Collection> */
    private function expenseReport(): array
    {
        $empty = [
            'active_expenses_without_debits' => collect(),
            'orphan_expense_debits' => collect(),
            'expense_amount_mismatches' => collect(),
            'cancelled_expenses_without_reversals' => collect(),
            'duplicate_expense_postings' => collect(),
            'incorrect_expense_net_expenditure' => collect(),
        ];

        if (! Schema::hasTable('expenses') || ! Schema::hasTable('expense_vault_postings')) {
            return $empty;
        }

        $expenses = Expense::query()->get()->keyBy('id');
        $postings = ExpenseVaultPosting::query()->get()->keyBy('id');
        $debits = SchoolVaultTransaction::query()
            ->with('reversal')
            ->where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)
            ->get();
        $debitsByExpense = collect();

        foreach ($debits as $debit) {
            $expenseId = null;

            if ($debit->source_type === Expense::class && $expenses->has((int) $debit->source_id)) {
                $expenseId = (int) $debit->source_id;
            } elseif ($debit->source_type === ExpenseVaultPosting::class) {
                $expenseId = $postings->get((int) $debit->source_id)?->expense_id;
            }

            if ($expenseId) {
                $debitsByExpense->put($expenseId, $debitsByExpense->get($expenseId, collect())->push($debit));
            }
        }

        $missing = $expenses
            ->where('status', Expense::STATUS_ACTIVE)
            ->reject(fn (Expense $expense) => $debitsByExpense->has($expense->id))
            ->map(fn (Expense $expense) => [
                'expense_id' => $expense->id,
                'expense_reference' => $expense->expense_number,
                'amount' => (string) $expense->amount,
            ])->values();

        $orphan = $debits->filter(function (SchoolVaultTransaction $debit) use ($expenses, $postings) {
            if ($debit->source_type === Expense::class) {
                return ! $expenses->has((int) $debit->source_id);
            }

            if ($debit->source_type === ExpenseVaultPosting::class) {
                $posting = $postings->get((int) $debit->source_id);

                return ! $posting || ! $expenses->has((int) $posting->expense_id);
            }

            return true;
        })->map(fn (SchoolVaultTransaction $debit) => [
            'transaction_id' => $debit->id,
            'transaction_number' => $debit->transaction_number,
            'source_type' => $debit->source_type,
            'source_id' => $debit->source_id,
        ])->values();

        $amountMismatches = $debits->map(function (SchoolVaultTransaction $debit) use ($expenses, $postings) {
            $source = $debit->source_type === Expense::class
                ? $expenses->get((int) $debit->source_id)
                : ($debit->source_type === ExpenseVaultPosting::class ? $postings->get((int) $debit->source_id) : null);

            if (! $source || $this->decimalToMinor((string) $source->amount) === $this->decimalToMinor((string) $debit->amount)) {
                return null;
            }

            return [
                'transaction_id' => $debit->id,
                'source_type' => $debit->source_type,
                'source_id' => $debit->source_id,
                'expense_amount' => (string) $source->amount,
                'ledger_amount' => (string) $debit->amount,
            ];
        })->filter()->values();

        $cancelledWithoutReversals = $expenses
            ->where('status', Expense::STATUS_CANCELLED)
            ->filter(function (Expense $expense) use ($debitsByExpense) {
                $expenseDebits = $debitsByExpense->get($expense->id, collect());

                return $expenseDebits->isEmpty() || $expenseDebits->contains(fn ($debit) => ! $debit->reversal);
            })
            ->map(fn (Expense $expense) => [
                'expense_id' => $expense->id,
                'expense_reference' => $expense->expense_number,
            ])->values();

        $duplicateSources = SchoolVaultTransaction::query()
            ->select('school_vault_id', 'source_type', 'source_id')
            ->selectRaw('COUNT(*) as entry_count')
            ->where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)
            ->groupBy('school_vault_id', 'source_type', 'source_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        $multipleActivePostings = ExpenseVaultPosting::query()
            ->select('expense_id')
            ->selectRaw('COUNT(*) as entry_count')
            ->where('status', ExpenseVaultPosting::STATUS_ACTIVE)
            ->groupBy('expense_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        $duplicates = $duplicateSources->concat($multipleActivePostings)->values();

        $incorrectNet = $expenses->map(function (Expense $expense) use ($debitsByExpense) {
            $expenseDebits = $debitsByExpense->get($expense->id, collect());
            if ($expenseDebits->isEmpty()) {
                return null;
            }

            $netMinor = $expenseDebits->sum(fn ($debit) => $this->decimalToMinor((string) $debit->amount)
                - ($debit->reversal ? $this->decimalToMinor((string) $debit->reversal->amount) : 0));
            $expectedMinor = $expense->status === Expense::STATUS_CANCELLED
                ? 0
                : $this->decimalToMinor((string) $expense->amount);

            if ($netMinor === $expectedMinor) {
                return null;
            }

            return [
                'expense_id' => $expense->id,
                'expense_reference' => $expense->expense_number,
                'expected_net' => $this->minorToDecimal($expectedMinor),
                'ledger_net' => $this->minorToDecimal($netMinor),
            ];
        })->filter()->values();

        return [
            'active_expenses_without_debits' => $missing,
            'orphan_expense_debits' => $orphan,
            'expense_amount_mismatches' => $amountMismatches,
            'cancelled_expenses_without_reversals' => $cancelledWithoutReversals,
            'duplicate_expense_postings' => $duplicates,
            'incorrect_expense_net_expenditure' => $incorrectNet,
        ];
    }

    private function decimalToMinor(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function minorToDecimal(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
