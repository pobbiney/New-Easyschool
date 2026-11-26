<?php

namespace App\Services\Finance;

use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseVaultService
{
    private const FINANCIAL_FIELDS = [
        'expense_category_id',
        'expense_date',
        'amount',
        'payee',
        'payment_method',
        'reference',
        'academic_year_id',
    ];

    public function __construct(private SchoolVaultService $vaultService) {}

    public function create(array $data, ?int $userId, string $requestToken): Expense
    {
        $existing = ExpenseVaultPosting::query()->where('request_token', $requestToken)->first();

        if ($existing) {
            $this->assertMatchingRetry($existing, $data);

            return $existing->expense;
        }

        return DB::transaction(function () use ($data, $userId, $requestToken) {
            $vault = $this->lockVault();

            // A competing request may have committed while this request was
            // waiting for the Vault lock. Re-check under the shared lock so a
            // genuine retry is idempotent instead of failing the unique key.
            $existing = ExpenseVaultPosting::query()
                ->where('request_token', $requestToken)
                ->first();

            if ($existing) {
                $this->assertMatchingRetry($existing, $data);

                return $existing->expense;
            }

            $this->lockActiveCategory((int) $data['expense_category_id']);
            $this->assertSufficientFunds($vault, (string) $data['amount']);

            $expense = Expense::create([
                ...$data,
                'status' => Expense::STATUS_ACTIVE,
                'created_by' => $userId,
            ]);
            $posting = $this->createPosting($expense, 1, $data, $requestToken, 'Original expense posting.', $userId);
            $this->postDebit($posting, $vault, $userId);

            return $expense->load('vaultPostings.vaultTransactions');
        });
    }

    public function update(
        Expense $expense,
        array $data,
        ?int $userId,
        string $requestToken,
        ?string $correctionReason,
    ): Expense {
        $existing = ExpenseVaultPosting::query()->where('request_token', $requestToken)->first();

        if ($existing) {
            if ((int) $existing->expense_id !== (int) $expense->id) {
                throw new InvalidArgumentException('This expense correction request has already been used.');
            }

            $this->assertMatchingRetry($existing, $data);

            return $existing->expense;
        }

        return DB::transaction(function () use ($expense, $data, $userId, $requestToken, $correctionReason) {
            $vault = $this->lockVault();

            $existing = ExpenseVaultPosting::query()
                ->where('request_token', $requestToken)
                ->first();

            if ($existing) {
                if ((int) $existing->expense_id !== (int) $expense->id) {
                    throw new InvalidArgumentException('This expense correction request has already been used.');
                }

                $this->assertMatchingRetry($existing, $data);

                return $existing->expense;
            }

            $lockedExpense = Expense::query()->whereKey($expense->id)->lockForUpdate()->firstOrFail();

            if ($lockedExpense->status !== Expense::STATUS_ACTIVE) {
                throw new InvalidArgumentException('A cancelled expense cannot be edited.');
            }

            $this->lockActiveCategory((int) $data['expense_category_id'], (int) $lockedExpense->expense_category_id);
            $financialChange = $this->hasFinancialChange($lockedExpense, $data);

            if (! $financialChange) {
                $lockedExpense->update([
                    'notes' => $data['notes'],
                    'updated_by' => $userId,
                ]);

                return $lockedExpense;
            }

            $reason = trim((string) $correctionReason);
            if ($reason === '') {
                throw new InvalidArgumentException('Explain why the posted expense is being corrected.');
            }

            $currentPosting = ExpenseVaultPosting::query()
                ->where('expense_id', $lockedExpense->id)
                ->where('status', ExpenseVaultPosting::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $currentPosting) {
                throw new InvalidArgumentException('This historical expense is unposted and cannot use the Vault correction workflow.');
            }

            $debit = $this->postingDebit($currentPosting);
            $this->vaultService->reverse(
                $debit,
                'Expense correction reversal · '.$this->expenseNumber($lockedExpense).' · '.$reason,
                now(),
                $userId,
            );

            $currentPosting->update([
                'status' => ExpenseVaultPosting::STATUS_SUPERSEDED,
                'change_reason' => $reason,
                'ended_at' => now(),
                'ended_by' => $userId,
            ]);

            $lockedExpense->update([
                ...$data,
                'updated_by' => $userId,
            ]);

            $this->assertSufficientFunds($vault, (string) $data['amount']);
            $posting = $this->createPosting(
                $lockedExpense,
                (int) $currentPosting->version + 1,
                $data,
                $requestToken,
                $reason,
                $userId,
            );
            $this->postDebit($posting, $vault, $userId);

            return $lockedExpense->load('vaultPostings.vaultTransactions');
        });
    }

    public function cancel(Expense $expense, string $reason, ?int $userId): Expense
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('A cancellation reason is required.');
        }

        return DB::transaction(function () use ($expense, $reason, $userId) {
            $this->lockVault();
            $lockedExpense = Expense::query()->whereKey($expense->id)->lockForUpdate()->firstOrFail();

            if ($lockedExpense->status === Expense::STATUS_CANCELLED) {
                return $lockedExpense;
            }

            $currentPosting = ExpenseVaultPosting::query()
                ->where('expense_id', $lockedExpense->id)
                ->where('status', ExpenseVaultPosting::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $currentPosting) {
                throw new InvalidArgumentException('This historical expense has no Vault posting to cancel.');
            }

            $this->vaultService->reverse(
                $this->postingDebit($currentPosting),
                'Expense cancellation · '.$this->expenseNumber($lockedExpense).' · '.$reason,
                now(),
                $userId,
            );

            $currentPosting->update([
                'status' => ExpenseVaultPosting::STATUS_CANCELLED,
                'change_reason' => $reason,
                'ended_at' => now(),
                'ended_by' => $userId,
            ]);
            $lockedExpense->update([
                'status' => Expense::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancellation_reason' => $reason,
                'updated_by' => $userId,
            ]);

            return $lockedExpense;
        });
    }

    private function lockVault(): SchoolVault
    {
        // This must be the first Vault read in the transaction. An unlocked
        // lookup followed by lockForUpdate can retain a stale REPEATABLE READ
        // snapshot while a competing expense commits.
        return SchoolVault::query()
            ->where('account_code', SchoolVault::ACCOUNT_CODE)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockActiveCategory(int $categoryId, ?int $currentCategoryId = null): ExpenseCategory
    {
        $category = ExpenseCategory::query()->whereKey($categoryId)->lockForUpdate()->firstOrFail();

        if ($category->status !== 'Active' && $categoryId !== $currentCategoryId) {
            throw new InvalidArgumentException('Choose an active expense category.');
        }

        return $category;
    }

    private function assertSufficientFunds(SchoolVault $vault, string $requested): void
    {
        $availableMinor = $this->decimalToMinor($this->vaultService->availableBalance($vault), true);
        $requestedMinor = $this->decimalToMinor($requested);

        if ($requestedMinor > $availableMinor) {
            throw new InvalidArgumentException(
                'Insufficient funds in the School Vault. The expense cannot be recorded. '
                .'Available: GHS '.$this->minorToDecimal($availableMinor)
                .'; requested: GHS '.$this->minorToDecimal($requestedMinor).'.'
            );
        }
    }

    private function createPosting(
        Expense $expense,
        int $version,
        array $data,
        string $requestToken,
        string $reason,
        ?int $userId,
    ): ExpenseVaultPosting {
        return ExpenseVaultPosting::create([
            'expense_id' => $expense->id,
            'version' => $version,
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'expense_category_id' => $data['expense_category_id'],
            'payee' => $data['payee'],
            'payment_method' => $data['payment_method'],
            'reference' => $data['reference'],
            'academic_year_id' => $data['academic_year_id'],
            'status' => ExpenseVaultPosting::STATUS_ACTIVE,
            'request_token' => $requestToken,
            'change_reason' => $reason,
            'created_by' => $userId,
        ]);
    }

    private function postDebit(ExpenseVaultPosting $posting, SchoolVault $vault, ?int $userId): void
    {
        $description = 'Operational expense '.$this->expenseNumber($posting->expense)
            .' v'.$posting->version.' · '.$posting->payee;

        if ($posting->reference) {
            $description .= ' · '.$posting->reference;
        }

        $this->vaultService->postDebit(
            SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE,
            (string) $posting->amount,
            ExpenseVaultPosting::class,
            $posting->id,
            $description,
            $posting->expense_date,
            $userId,
            $vault,
        );
    }

    private function postingDebit(ExpenseVaultPosting $posting): SchoolVaultTransaction
    {
        $debit = SchoolVaultTransaction::query()
            ->where('transaction_type', SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE)
            ->where('source_type', ExpenseVaultPosting::class)
            ->where('source_id', $posting->id)
            ->lockForUpdate()
            ->first();

        if (! $debit) {
            throw new InvalidArgumentException('The expense Vault debit is missing. Run reconciliation before continuing.');
        }

        return $debit;
    }

    private function hasFinancialChange(Expense $expense, array $data): bool
    {
        foreach (self::FINANCIAL_FIELDS as $field) {
            $current = $field === 'expense_date'
                ? $expense->expense_date?->format('Y-m-d')
                : ($field === 'amount' ? $this->minorToDecimal($this->decimalToMinor((string) $expense->amount)) : $expense->{$field});
            $incoming = $field === 'amount'
                ? $this->minorToDecimal($this->decimalToMinor((string) $data[$field]))
                : $data[$field];

            if ((string) ($current ?? '') !== (string) ($incoming ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function assertMatchingRetry(ExpenseVaultPosting $posting, array $data): void
    {
        foreach (self::FINANCIAL_FIELDS as $field) {
            $posted = $field === 'expense_date'
                ? $posting->expense_date?->format('Y-m-d')
                : ($field === 'amount' ? $this->minorToDecimal($this->decimalToMinor((string) $posting->amount)) : $posting->{$field});
            $requested = $field === 'amount'
                ? $this->minorToDecimal($this->decimalToMinor((string) $data[$field]))
                : $data[$field];

            if ((string) ($posted ?? '') !== (string) ($requested ?? '')) {
                throw new InvalidArgumentException('A conflicting expense request already exists for this request token.');
            }
        }
    }

    private function expenseNumber(Expense $expense): string
    {
        return 'EXP-'.str_pad((string) $expense->id, 6, '0', STR_PAD_LEFT);
    }

    private function decimalToMinor(string $amount, bool $allowZero = false): int
    {
        if (! preg_match('/^(\d{1,12})(?:\.(\d{1,2}))?$/', trim($amount), $matches)) {
            throw new InvalidArgumentException('Expense amount must be a positive monetary value with at most two decimal places.');
        }

        $minor = ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
        if ($minor < 0 || (! $allowZero && $minor === 0)) {
            throw new InvalidArgumentException('Expense amount must be greater than zero.');
        }

        return $minor;
    }

    private function minorToDecimal(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
