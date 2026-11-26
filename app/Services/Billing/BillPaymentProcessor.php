<?php

namespace App\Services\Billing;

use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\BillingItem;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentBillCreditTransaction;
use App\Models\SchoolVaultTransaction;
use App\Services\Finance\SchoolVaultService;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillPaymentProcessor
{
    public function __construct(
        private StudentBillCreditService $creditService,
        private SchoolVaultService $schoolVaultService,
        private PaymentMethodEligibility $paymentMethodEligibility,
    ) {}

    public function finalizePayment(
        Student $student,
        string $financialCategory,
        string|int|float $cashAmount,
        string|int|float $creditApplied,
        string $paymentMethod,
        string $paidAt,
        Collection $allocations,
        ?string $reference = null,
        ?string $notes = null,
        ?string $paymentChannel = null,
        ?string $gatewayTransactionId = null,
        ?int $createdBy = null,
        bool $historicalGatewayCompletion = false,
    ): BillPayment {
        $cashMinor = Money::toMinor($cashAmount);
        $creditMinor = Money::toMinor($creditApplied);
        $allocatedMinor = $allocations->sum(fn (array $allocation): int => Money::toMinor($allocation['amount']));
        $totalFundingMinor = $cashMinor + $creditMinor;

        $this->validateFinancialCategory($financialCategory);
        $this->paymentMethodEligibility->assertAllowed($financialCategory, $paymentMethod, $historicalGatewayCompletion);

        if ($financialCategory === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE && $creditMinor > 0) {
            throw new InvalidArgumentException('School Fees credit cannot be applied to Feeding Fee.');
        }

        if ($allocatedMinor <= 0) {
            throw new InvalidArgumentException('No outstanding bills to allocate this payment to.');
        }

        if ($allocatedMinor > $totalFundingMinor) {
            throw new InvalidArgumentException('Allocated amount exceeds available payment and credit.');
        }

        if ($creditMinor > Money::toMinor($this->creditService->creditBalance($student))) {
            throw new InvalidArgumentException('Insufficient credit balance.');
        }

        $payment = BillPayment::create([
            'student_id' => $student->id,
            'financial_category' => $financialCategory,
            'receipt_no' => $this->generateReceiptNumber(),
            'amount' => Money::fromMinor($cashMinor),
            'credit_applied' => Money::fromMinor($creditMinor),
            'credit_generated' => 0,
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'payment_channel' => $paymentChannel,
            'gateway_transaction_id' => $gatewayTransactionId,
            'paid_at' => $paidAt,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);

        if ($creditMinor > 0) {
            $this->creditService->applyCredit(
                $student,
                Money::fromMinor($creditMinor),
                $payment->id,
                'Applied to bill payment '.$payment->receipt_no
            );
        }

        foreach ($allocations as $allocation) {
            $bill = StudentBill::query()
                ->where('id', $allocation['student_bill_id'])
                ->where('student_id', $student->id)
                ->where('financial_category', $financialCategory)
                ->lockForUpdate()
                ->first();

            if (! $bill) {
                throw new InvalidArgumentException('One or more selected bills do not match this student and payment category.');
            }

            $amountMinor = min(Money::toMinor($allocation['amount']), Money::toMinor((string) $bill->balance));

            if ($amountMinor <= 0) {
                continue;
            }

            $amount = Money::fromMinor($amountMinor);

            BillPaymentAllocation::create([
                'bill_payment_id' => $payment->id,
                'student_bill_id' => $bill->id,
                'amount' => $amount,
            ]);

            $bill->amount_paid = Money::fromMinor(Money::toMinor((string) $bill->amount_paid) + $amountMinor);
            $bill->refreshTotals();
            $bill->save();
        }

        $appliedMinor = Money::toMinor((string) $payment->allocations()->sum('amount'));
        $surplusMinor = $totalFundingMinor - $appliedMinor;

        if ($surplusMinor > 0 && $financialCategory === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
            throw new InvalidArgumentException('Feeding Fee payments cannot exceed the selected outstanding balance. Return excess cash as change.');
        }

        if ($surplusMinor > 0) {
            $surplus = Money::fromMinor($surplusMinor);
            $this->creditService->addCredit(
                $student,
                $surplus,
                StudentBillCreditTransaction::SOURCE_OVERPAYMENT,
                $payment->id,
                'Overpayment saved from '.$payment->receipt_no
            );

            $payment->credit_generated = $surplus;
            $payment->save();
        }

        if ($financialCategory === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE) {
            $reference = trim((string) $payment->reference);
            $description = 'Feeding Fee collection '.$payment->receipt_no;

            if ($reference !== '') {
                $description .= ' · '.$reference;
            }

            $this->schoolVaultService->postCredit(
                transactionType: SchoolVaultTransaction::TYPE_FEEDING_FEE_COLLECTION,
                amount: (string) $payment->amount,
                sourceType: BillPayment::class,
                sourceId: $payment->id,
                description: $description,
                transactionDate: $payment->paid_at,
                recordedBy: $payment->created_by,
            );
        }

        return $payment->load(['student', 'allocations.studentBill.billingItem', 'schoolVaultTransactions']);
    }

    public function resolveAllocations(
        int $studentId,
        string $financialCategory,
        string|int|float $cashAmount,
        string|int|float $creditApplied,
        Collection $allocations,
    ): Collection {
        $cashMinor = Money::toMinor($cashAmount);
        $creditMinor = Money::toMinor($creditApplied);
        $totalFundingMinor = $cashMinor + $creditMinor;

        $this->validateFinancialCategory($financialCategory);

        if ($financialCategory === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE && $creditMinor > 0) {
            throw new InvalidArgumentException('School Fees credit cannot be applied to Feeding Fee.');
        }

        if ($allocations->isEmpty()) {
            $allocations = $this->buildAutoAllocations($studentId, $financialCategory, Money::fromMinor($totalFundingMinor));
        }

        $allocatedMinor = $allocations->sum(fn (array $allocation): int => Money::toMinor($allocation['amount']));

        if ($allocatedMinor <= 0) {
            throw new InvalidArgumentException('No outstanding bills to allocate this payment to.');
        }

        if ($allocatedMinor > $totalFundingMinor) {
            throw new InvalidArgumentException('Allocated amount exceeds available payment and credit.');
        }

        if ($allocations->pluck('student_bill_id')->duplicates()->isNotEmpty()) {
            throw new InvalidArgumentException('A bill cannot be allocated more than once in the same payment.');
        }

        foreach ($allocations as $allocation) {
            $bill = StudentBill::query()
                ->where('id', $allocation['student_bill_id'])
                ->where('student_id', $studentId)
                ->where('financial_category', $financialCategory)
                ->first();

            if (! $bill) {
                throw new InvalidArgumentException('One or more selected bills do not match this student and payment category.');
            }

            if (Money::toMinor($allocation['amount']) > Money::toMinor((string) $bill->balance)) {
                throw new InvalidArgumentException('An allocation exceeds the current outstanding bill balance.');
            }
        }

        if ($financialCategory === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE && $totalFundingMinor > $allocatedMinor) {
            throw new InvalidArgumentException('Feeding Fee payments cannot exceed the selected outstanding balance. Return excess cash as change.');
        }

        return $allocations->map(fn (array $allocation): array => [
            ...$allocation,
            'amount' => Money::fromMinor(Money::toMinor($allocation['amount'])),
        ]);
    }

    public function buildAutoAllocations(int $studentId, string $financialCategory, string|int|float $paymentAmount): Collection
    {
        $remainingMinor = Money::toMinor($paymentAmount);
        $allocations = collect();

        $bills = StudentBill::query()
            ->with('billingItem')
            ->where('student_id', $studentId)
            ->where('financial_category', $financialCategory)
            ->where('balance', '>', 0)
            ->get()
            ->sortByDesc(fn (StudentBill $bill) => $bill->billingItem?->is_compulsory ? 1 : 0)
            ->values();

        foreach ($bills as $bill) {
            if ($remainingMinor <= 0) {
                break;
            }

            $amountMinor = min(Money::toMinor((string) $bill->balance), $remainingMinor);
            $allocations->push([
                'student_bill_id' => $bill->id,
                'amount' => Money::fromMinor($amountMinor),
            ]);
            $remainingMinor -= $amountMinor;
        }

        return $allocations;
    }

    public function outstandingBillsForStudent(Student $student, ?string $financialCategory = null): Collection
    {
        return StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear'])
            ->where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->when($financialCategory, fn ($query) => $query->where('financial_category', $financialCategory))
            ->get()
            ->sortByDesc(fn (StudentBill $bill) => $bill->billingItem?->is_compulsory ? 1 : 0)
            ->values()
            ->map(fn (StudentBill $bill) => [
                'id' => $bill->id,
                'financial_category' => $bill->financial_category,
                'item_name' => $bill->billingItem?->name,
                'is_compulsory' => (bool) $bill->billingItem?->is_compulsory,
                'term_name' => $bill->setup?->academicTerm?->name,
                'year_name' => $bill->setup?->academicYear?->name,
                'amount_due' => (float) $bill->amount_due,
                'amount_paid' => (float) $bill->amount_paid,
                'balance' => (float) $bill->balance,
                'progress_percent' => (float) $bill->amount_due > 0
                    ? min(round(((float) $bill->amount_paid / (float) $bill->amount_due) * 100, 1), 100)
                    : 0,
            ]);
    }

    public function financialCategoryForAllocations(int $studentId, Collection $allocations): string
    {
        $billIds = $allocations->pluck('student_bill_id')->filter()->unique()->values();
        $categories = StudentBill::query()
            ->where('student_id', $studentId)
            ->whereIn('id', $billIds)
            ->pluck('financial_category')
            ->unique()
            ->values();

        if ($billIds->isEmpty() || $categories->count() !== 1) {
            throw new InvalidArgumentException('A payment must contain bills from exactly one financial category.');
        }

        return (string) $categories->first();
    }

    private function validateFinancialCategory(string $financialCategory): void
    {
        if (! array_key_exists($financialCategory, BillingItem::financialCategoryOptions())) {
            throw new InvalidArgumentException('The selected payment category is invalid.');
        }
    }

    public function generateReceiptNumber(): string
    {
        $year = (int) now()->format('Y');
        $prefix = 'RCP-'.$year.'-';
        $last = BillPayment::query()
            ->where('receipt_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('receipt_no');

        $observed = ($last && preg_match('/-(\d+)$/', $last, $matches))
            ? (int) $matches[1]
            : 0;

        DB::table('bill_payment_receipt_sequences')->insertOrIgnore([
            'year' => $year,
            'last_number' => $observed,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('bill_payment_receipt_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $sequence = max((int) $row->last_number, $observed) + 1;

        DB::table('bill_payment_receipt_sequences')
            ->where('year', $year)
            ->update(['last_number' => $sequence, 'updated_at' => now()]);

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
