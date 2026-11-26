<?php

namespace App\Services\Finance;

use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\BillingItem;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseVaultPosting;
use App\Models\SchoolVault;
use App\Models\SchoolVaultTransaction;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

class SchoolVaultService
{
    /** @var array<string, list<string>> */
    private const TYPE_DIRECTIONS = [
        SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION => [SchoolVaultTransaction::DIRECTION_CREDIT],
        SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE => [SchoolVaultTransaction::DIRECTION_DEBIT],
        SchoolVaultTransaction::TYPE_OPENING_BALANCE => [SchoolVaultTransaction::DIRECTION_CREDIT],
        SchoolVaultTransaction::TYPE_AUTHORIZED_ADJUSTMENT => [
            SchoolVaultTransaction::DIRECTION_CREDIT,
            SchoolVaultTransaction::DIRECTION_DEBIT,
        ],
        SchoolVaultTransaction::TYPE_REVERSAL => [
            SchoolVaultTransaction::DIRECTION_CREDIT,
            SchoolVaultTransaction::DIRECTION_DEBIT,
        ],
    ];

    public function vault(): SchoolVault
    {
        $existing = SchoolVault::query()->where('account_code', SchoolVault::ACCOUNT_CODE)->first();

        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () {
                $vault = SchoolVault::query()
                    ->where('account_code', SchoolVault::ACCOUNT_CODE)
                    ->lockForUpdate()
                    ->first();

                return $vault ?: SchoolVault::unguarded(fn () => SchoolVault::create([
                    'account_code' => SchoolVault::ACCOUNT_CODE,
                    'name' => 'School Vault',
                    'currency' => SchoolVault::CURRENCY,
                    'status' => SchoolVault::STATUS_ACTIVE,
                ]));
            });
        } catch (QueryException $exception) {
            $vault = SchoolVault::query()->where('account_code', SchoolVault::ACCOUNT_CODE)->first();

            if ($vault) {
                return $vault;
            }

            throw $exception;
        }
    }

    public function availableBalance(?SchoolVault $vault = null): string
    {
        $vault ??= $this->vault();
        $this->assertValidVault($vault, false);
        $totals = $this->totalsMinor($vault);

        return $this->minorToDecimal($totals['credits'] - $totals['debits']);
    }

    /** @return array{credits:string,debits:string,balance:string} */
    public function totals(?SchoolVault $vault = null): array
    {
        $vault ??= $this->vault();
        $this->assertValidVault($vault, false);
        $totals = $this->totalsMinor($vault);

        return [
            'credits' => $this->minorToDecimal($totals['credits']),
            'debits' => $this->minorToDecimal($totals['debits']),
            'balance' => $this->minorToDecimal($totals['credits'] - $totals['debits']),
        ];
    }

    public function history(?SchoolVault $vault = null): Builder
    {
        $vault ??= $this->vault();
        $this->assertValidVault($vault, false);

        return SchoolVaultTransaction::query()
            ->where('school_vault_id', $vault->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');
    }

    public function postCredit(
        string $transactionType,
        string|int $amount,
        string $sourceType,
        int $sourceId,
        string $description,
        DateTimeInterface|string|null $transactionDate = null,
        ?int $recordedBy = null,
        ?SchoolVault $vault = null,
    ): SchoolVaultTransaction {
        if ($transactionType === SchoolVaultTransaction::TYPE_REVERSAL) {
            throw new InvalidArgumentException('Reversals must be posted through the reversal method.');
        }

        return $this->post(
            SchoolVaultTransaction::DIRECTION_CREDIT,
            $transactionType,
            $amount,
            $sourceType,
            $sourceId,
            $description,
            $transactionDate,
            $recordedBy,
            null,
            $vault,
        );
    }

    public function postDebit(
        string $transactionType,
        string|int $amount,
        string $sourceType,
        int $sourceId,
        string $description,
        DateTimeInterface|string|null $transactionDate = null,
        ?int $recordedBy = null,
        ?SchoolVault $vault = null,
    ): SchoolVaultTransaction {
        if ($transactionType === SchoolVaultTransaction::TYPE_REVERSAL) {
            throw new InvalidArgumentException('Reversals must be posted through the reversal method.');
        }

        return $this->post(
            SchoolVaultTransaction::DIRECTION_DEBIT,
            $transactionType,
            $amount,
            $sourceType,
            $sourceId,
            $description,
            $transactionDate,
            $recordedBy,
            null,
            $vault,
        );
    }

    public function reverse(
        SchoolVaultTransaction $original,
        string $description,
        DateTimeInterface|string|null $transactionDate = null,
        ?int $recordedBy = null,
    ): SchoolVaultTransaction {
        if ($original->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL || $original->reversal_of_id) {
            throw new InvalidArgumentException('A reversal transaction cannot itself be reversed.');
        }

        $direction = $original->direction === SchoolVaultTransaction::DIRECTION_CREDIT
            ? SchoolVaultTransaction::DIRECTION_DEBIT
            : SchoolVaultTransaction::DIRECTION_CREDIT;

        return $this->post(
            $direction,
            SchoolVaultTransaction::TYPE_REVERSAL,
            (string) $original->amount,
            SchoolVaultTransaction::class,
            $original->id,
            $description,
            $transactionDate,
            $recordedBy,
            $original->id,
            $original->vault,
        );
    }

    private function post(
        string $direction,
        string $transactionType,
        string|int $amount,
        string $sourceType,
        int $sourceId,
        string $description,
        DateTimeInterface|string|null $transactionDate,
        ?int $recordedBy,
        ?int $reversalOfId,
        ?SchoolVault $vault,
    ): SchoolVaultTransaction {
        $amountMinor = $this->decimalToMinor($amount);
        $normalizedAmount = $this->minorToDecimal($amountMinor);
        $description = trim($description);

        // Resolve the identity before opening the financial transaction. On
        // MySQL REPEATABLE READ, an ordinary SELECT inside the transaction
        // would establish a stale snapshot before the subsequent row lock.
        $vaultId = $vault?->id
            ?: SchoolVault::query()->where('account_code', SchoolVault::ACCOUNT_CODE)->value('id')
            ?: $this->vault()->id;

        if ($description === '') {
            throw new InvalidArgumentException('A School Vault transaction description is required.');
        }

        if (! isset(self::TYPE_DIRECTIONS[$transactionType])) {
            throw new InvalidArgumentException('Unsupported School Vault transaction type.');
        }

        if (! in_array($direction, self::TYPE_DIRECTIONS[$transactionType], true)) {
            throw new InvalidArgumentException('The transaction direction is invalid for this transaction type.');
        }

        return DB::transaction(function () use (
            $direction,
            $transactionType,
            $amountMinor,
            $normalizedAmount,
            $sourceType,
            $sourceId,
            $description,
            $transactionDate,
            $recordedBy,
            $reversalOfId,
            $vaultId,
        ) {
            $lockedVault = SchoolVault::query()->whereKey($vaultId)->lockForUpdate()->firstOrFail();
            $this->assertValidVault($lockedVault);

            $source = $this->validateSource($transactionType, $sourceType, $sourceId, $amountMinor, $reversalOfId, $lockedVault);
            $effectiveDate = $this->resolveTransactionDate($source, $transactionDate);

            $existing = SchoolVaultTransaction::query()
                ->where('school_vault_id', $lockedVault->id)
                ->where('transaction_type', $transactionType)
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->first();

            if ($existing) {
                return $this->resolveDuplicate(
                    $existing,
                    $direction,
                    $normalizedAmount,
                    $description,
                    $effectiveDate,
                    $transactionDate !== null || $source instanceof BillPayment || $source instanceof Expense || $source instanceof ExpenseVaultPosting,
                    $recordedBy,
                    $reversalOfId,
                );
            }

            if ($direction === SchoolVaultTransaction::DIRECTION_DEBIT) {
                $totals = $this->totalsMinor($lockedVault);
                $availableMinor = $totals['credits'] - $totals['debits'];

                if ($amountMinor > $availableMinor) {
                    throw new InvalidArgumentException('Insufficient School Vault balance for this debit.');
                }
            }

            return SchoolVaultTransaction::unguarded(fn () => SchoolVaultTransaction::create([
                'school_vault_id' => $lockedVault->id,
                'transaction_number' => $this->generateTransactionNumber(),
                'direction' => $direction,
                'transaction_type' => $transactionType,
                'amount' => $normalizedAmount,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'description' => $description,
                'transaction_date' => $effectiveDate,
                'recorded_by' => $recordedBy,
                'reversal_of_id' => $reversalOfId,
            ]));
        });
    }

    private function validateSource(
        string $transactionType,
        string $sourceType,
        int $sourceId,
        int $amountMinor,
        ?int $reversalOfId,
        SchoolVault $vault,
    ): Model {
        if ($sourceId <= 0 || ! class_exists($sourceType) || ! is_subclass_of($sourceType, Model::class)) {
            throw new InvalidArgumentException('The School Vault source record is invalid.');
        }

        if ($transactionType === SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION) {
            if ($sourceType !== BillPayment::class) {
                throw new InvalidArgumentException('Feeding Fee collections must reference a bill payment.');
            }

            $payment = BillPayment::query()->whereKey($sourceId)->lockForUpdate()->first();

            if (! $payment || $payment->financial_category !== BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
                throw new InvalidArgumentException('Only a classified Feeding Fee payment may credit the School Vault.');
            }

            if ($this->decimalToMinor((string) $payment->amount) !== $amountMinor) {
                throw new InvalidArgumentException('The vault credit must equal the Feeding Fee amount collected.');
            }

            $allocationRows = BillPaymentAllocation::query()
                ->with('studentBill:id,student_id,financial_category')
                ->where('bill_payment_id', $payment->id)
                ->get();
            $allocatedMinor = 0;

            foreach ($allocationRows as $allocation) {
                $bill = $allocation->studentBill;

                if (! $bill
                    || (int) $bill->student_id !== (int) $payment->student_id
                    || $bill->financial_category !== BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
                    throw new InvalidArgumentException('The Feeding Fee payment contains an invalid vault allocation.');
                }

                $allocatedMinor += $this->decimalToMinor((string) $allocation->amount);
            }

            if ($allocationRows->isEmpty() || $allocatedMinor !== $amountMinor) {
                throw new InvalidArgumentException('The Feeding Fee payment allocations must equal the amount collected.');
            }

            return $payment;
        }

        if ($transactionType === SchoolVaultTransaction::TYPE_SCHOOL_OPERATIONAL_EXPENSE) {
            if (! in_array($sourceType, [Expense::class, ExpenseVaultPosting::class], true)) {
                throw new InvalidArgumentException('Operational expense debits must reference an expense record.');
            }

            if ($sourceType === ExpenseVaultPosting::class) {
                $posting = ExpenseVaultPosting::query()->with('expense')->whereKey($sourceId)->lockForUpdate()->first();

                if (! $posting || ! $posting->expense || $this->decimalToMinor((string) $posting->amount) !== $amountMinor) {
                    throw new InvalidArgumentException('The vault debit must equal a valid expense posting amount.');
                }

                return $posting;
            }

            $expense = Expense::query()->whereKey($sourceId)->lockForUpdate()->first();

            if (! $expense || $this->decimalToMinor((string) $expense->amount) !== $amountMinor) {
                throw new InvalidArgumentException('The vault debit must equal the recorded expense amount.');
            }

            return $expense;
        }

        if ($transactionType === SchoolVaultTransaction::TYPE_REVERSAL) {
            if ($sourceType !== SchoolVaultTransaction::class || $reversalOfId !== $sourceId) {
                throw new InvalidArgumentException('A reversal must reference its original vault transaction.');
            }

            $original = SchoolVaultTransaction::query()
                ->whereKey($sourceId)
                ->where('school_vault_id', $vault->id)
                ->lockForUpdate()
                ->first();

            if (! $original || $original->transaction_type === SchoolVaultTransaction::TYPE_REVERSAL) {
                throw new InvalidArgumentException('The original vault transaction cannot be reversed.');
            }

            if ($this->decimalToMinor((string) $original->amount) !== $amountMinor) {
                throw new InvalidArgumentException('A reversal must use the original transaction amount.');
            }

            return $original;
        }

        $source = $sourceType::query()->whereKey($sourceId)->lockForUpdate()->first();

        if (! $source) {
            throw new InvalidArgumentException('The School Vault source record does not exist.');
        }

        return $source;
    }

    private function resolveTransactionDate(Model $source, DateTimeInterface|string|null $requested): Carbon
    {
        if ($source instanceof BillPayment && $source->paid_at) {
            return Carbon::parse($source->paid_at);
        }

        if ($source instanceof Expense && $source->expense_date) {
            return Carbon::parse($source->expense_date)->startOfDay();
        }

        if ($source instanceof ExpenseVaultPosting && $source->expense_date) {
            return Carbon::parse($source->expense_date)->startOfDay();
        }

        return $requested ? Carbon::parse($requested) : now();
    }

    private function resolveDuplicate(
        SchoolVaultTransaction $existing,
        string $direction,
        string $amount,
        string $description,
        Carbon $transactionDate,
        bool $compareTransactionDate,
        ?int $recordedBy,
        ?int $reversalOfId,
    ): SchoolVaultTransaction {
        $matches = $existing->direction === $direction
            && (string) $existing->amount === $amount
            && $existing->description === $description
            && (! $compareTransactionDate || $existing->transaction_date?->equalTo($transactionDate))
            && (int) ($existing->recorded_by ?? 0) === (int) ($recordedBy ?? 0)
            && (int) ($existing->reversal_of_id ?? 0) === (int) ($reversalOfId ?? 0);

        if (! $matches) {
            throw new LogicException('A conflicting School Vault transaction already exists for this source.');
        }

        return $existing;
    }

    /** @return array{credits:int,debits:int} */
    private function totalsMinor(SchoolVault $vault): array
    {
        $credits = 0;
        $debits = 0;

        SchoolVaultTransaction::query()
            ->where('school_vault_id', $vault->id)
            ->get(['direction', 'amount'])
            ->each(function (SchoolVaultTransaction $transaction) use (&$credits, &$debits) {
                $minor = $this->decimalToMinor((string) $transaction->amount);

                if ($transaction->direction === SchoolVaultTransaction::DIRECTION_CREDIT) {
                    $credits += $minor;
                } else {
                    $debits += $minor;
                }
            });

        return ['credits' => $credits, 'debits' => $debits];
    }

    private function assertValidVault(SchoolVault $vault, bool $mustBeActive = true): void
    {
        if (! $vault->exists || $vault->account_code !== SchoolVault::ACCOUNT_CODE || $vault->currency !== SchoolVault::CURRENCY) {
            throw new InvalidArgumentException('The School Vault account is invalid.');
        }

        if ($mustBeActive && $vault->status !== SchoolVault::STATUS_ACTIVE) {
            throw new InvalidArgumentException('The School Vault account is inactive.');
        }
    }

    private function decimalToMinor(string|int $amount): int
    {
        $value = trim((string) $amount);

        if (! preg_match('/^(\d{1,12})(?:\.(\d{1,2}))?$/', $value, $matches)) {
            throw new InvalidArgumentException('Transaction amount must be a positive monetary value with at most two decimal places.');
        }

        $minor = ((int) ltrim($matches[1], '0')) * 100;
        $minor += (int) str_pad($matches[2] ?? '', 2, '0');

        if ($minor <= 0) {
            throw new InvalidArgumentException('Transaction amount must be greater than zero.');
        }

        return $minor;
    }

    private function minorToDecimal(int $minor): string
    {
        $sign = $minor < 0 ? '-' : '';
        $absolute = abs($minor);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }

    private function generateTransactionNumber(): string
    {
        return 'SVT-'.now()->format('Y').'-'.strtoupper((string) Str::ulid());
    }
}
