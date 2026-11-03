<?php

namespace App\Services\Billing;

use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentBillCreditTransaction;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class BillPaymentProcessor
{
    public function __construct(private StudentBillCreditService $creditService) {}

    public function finalizePayment(
        Student $student,
        float $cashAmount,
        float $creditApplied,
        string $paymentMethod,
        string $paidAt,
        Collection $allocations,
        ?string $reference = null,
        ?string $notes = null,
        ?string $paymentChannel = null,
        ?string $gatewayTransactionId = null,
        ?int $createdBy = null,
    ): BillPayment {
        $cashAmount = round($cashAmount, 2);
        $creditApplied = round($creditApplied, 2);
        $allocatedTotal = round($allocations->sum('amount'), 2);
        $totalFunding = round($cashAmount + $creditApplied, 2);

        if ($allocatedTotal <= 0) {
            throw new InvalidArgumentException('No outstanding bills to allocate this payment to.');
        }

        if ($allocatedTotal > $totalFunding + 0.009) {
            throw new InvalidArgumentException('Allocated amount exceeds available payment and credit.');
        }

        if ($creditApplied > $this->creditService->creditBalance($student) + 0.009) {
            throw new InvalidArgumentException('Insufficient credit balance.');
        }

        $payment = BillPayment::create([
            'student_id' => $student->id,
            'receipt_no' => $this->generateReceiptNumber(),
            'amount' => $cashAmount,
            'credit_applied' => $creditApplied,
            'credit_generated' => 0,
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'payment_channel' => $paymentChannel,
            'gateway_transaction_id' => $gatewayTransactionId,
            'paid_at' => $paidAt,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);

        if ($creditApplied > 0) {
            $this->creditService->applyCredit(
                $student,
                $creditApplied,
                $payment->id,
                'Applied to bill payment '.$payment->receipt_no
            );
        }

        foreach ($allocations as $allocation) {
            $bill = StudentBill::query()
                ->where('id', $allocation['student_bill_id'])
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = min((float) $allocation['amount'], (float) $bill->balance);

            if ($amount <= 0) {
                continue;
            }

            BillPaymentAllocation::create([
                'bill_payment_id' => $payment->id,
                'student_bill_id' => $bill->id,
                'amount' => $amount,
            ]);

            $bill->amount_paid = round((float) $bill->amount_paid + $amount, 2);
            $bill->refreshTotals();
            $bill->save();
        }

        $appliedTotal = round($payment->allocations()->sum('amount'), 2);
        $surplus = round($totalFunding - $appliedTotal, 2);

        if ($surplus > 0.009) {
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

        return $payment->load(['student', 'allocations.studentBill.billingItem']);
    }

    public function resolveAllocations(
        int $studentId,
        float $cashAmount,
        float $creditApplied,
        Collection $allocations,
    ): Collection {
        $totalFunding = round($cashAmount + $creditApplied, 2);

        if ($allocations->isEmpty()) {
            $allocations = $this->buildAutoAllocations($studentId, $totalFunding);
        }

        $allocatedTotal = round($allocations->sum('amount'), 2);

        if ($allocatedTotal <= 0) {
            throw new InvalidArgumentException('No outstanding bills to allocate this payment to.');
        }

        if ($allocatedTotal > $totalFunding + 0.009) {
            throw new InvalidArgumentException('Allocated amount exceeds available payment and credit.');
        }

        foreach ($allocations as $allocation) {
            $bill = StudentBill::query()
                ->where('id', $allocation['student_bill_id'])
                ->where('student_id', $studentId)
                ->first();

            if (! $bill) {
                throw new InvalidArgumentException('One or more selected bills are invalid for this student.');
            }
        }

        return $allocations;
    }

    public function buildAutoAllocations(int $studentId, float $paymentAmount): Collection
    {
        $remaining = $paymentAmount;
        $allocations = collect();

        $bills = StudentBill::query()
            ->with('billingItem')
            ->where('student_id', $studentId)
            ->where('balance', '>', 0)
            ->get()
            ->sortByDesc(fn (StudentBill $bill) => $bill->billingItem?->is_compulsory ? 1 : 0)
            ->values();

        foreach ($bills as $bill) {
            if ($remaining <= 0) {
                break;
            }

            $amount = min((float) $bill->balance, $remaining);
            $allocations->push([
                'student_bill_id' => $bill->id,
                'amount' => $amount,
            ]);
            $remaining -= $amount;
        }

        return $allocations;
    }

    public function outstandingBillsForStudent(Student $student): Collection
    {
        return StudentBill::query()
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
    }

    public function generateReceiptNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'RCP-'.$year.'-';
        $last = BillPayment::query()
            ->where('receipt_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('receipt_no');

        $sequence = 1;

        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
