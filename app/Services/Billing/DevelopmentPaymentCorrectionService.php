<?php

namespace App\Services\Billing;

use App\Models\BillPayment;
use App\Models\BillingItem;
use App\Models\StudentBill;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DevelopmentPaymentCorrectionService
{
    /**
     * Void an unposted local development payment while retaining its receipt and allocations.
     */
    public function voidUnpostedPayment(
        string $receiptNo,
        string $expectedAmount,
        string $reason,
        string $actor,
        ?int $actorUserId = null,
    ): BillPayment {
        if (trim($reason) === '' || trim($actor) === '') {
            throw new InvalidArgumentException('A correction reason and maintenance actor are required.');
        }

        return DB::transaction(function () use ($receiptNo, $expectedAmount, $reason, $actor, $actorUserId) {
            $payment = BillPayment::query()
                ->where('receipt_no', $receiptNo)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->isVoided()) {
                return $payment->load(['allocations.studentBill', 'schoolVaultTransactions']);
            }

            if ($payment->financial_category !== BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE
                || $payment->payment_method !== 'Cash'
                || Money::toMinor((string) $payment->amount) !== Money::toMinor($expectedAmount)) {
                throw new InvalidArgumentException('The payment identity, category, method or amount does not match the authorized correction.');
            }

            if (Money::toMinor((string) $payment->credit_applied) !== 0
                || Money::toMinor((string) $payment->credit_generated) !== 0) {
                throw new InvalidArgumentException('Payments involving student credit require a separate correction procedure.');
            }

            if ($payment->schoolVaultTransactions()->lockForUpdate()->exists()) {
                throw new InvalidArgumentException('This payment affected the School Vault and cannot use the unposted-payment correction procedure.');
            }

            if ($payment->gatewayTransactions()->exists() || $payment->pendingBankPayments()->exists()) {
                throw new InvalidArgumentException('This payment has gateway or bank workflow dependencies and cannot use this correction procedure.');
            }

            $allocations = $payment->allocations()->lockForUpdate()->get();
            $allocatedMinor = $allocations->sum(fn ($allocation): int => Money::toMinor((string) $allocation->amount));

            if ($allocations->isEmpty() || $allocatedMinor !== Money::toMinor((string) $payment->amount)) {
                throw new InvalidArgumentException('The payment allocation does not match the authorized payment amount.');
            }

            foreach ($allocations->sortBy('student_bill_id') as $allocation) {
                $bill = StudentBill::query()->whereKey($allocation->student_bill_id)->lockForUpdate()->firstOrFail();

                if ($bill->student_id !== $payment->student_id
                    || $bill->financial_category !== $payment->financial_category) {
                    throw new InvalidArgumentException('An allocation no longer matches the payment student or financial category.');
                }

                $paidMinor = Money::toMinor((string) $bill->amount_paid);
                $allocationMinor = Money::toMinor((string) $allocation->amount);

                if ($allocationMinor > $paidMinor) {
                    throw new InvalidArgumentException('The student bill no longer contains the allocated amount being corrected.');
                }

                $bill->amount_paid = Money::fromMinor($paidMinor - $allocationMinor);
                $bill->refreshTotals();
                $bill->save();
            }

            $payment->forceFill([
                'status' => BillPayment::STATUS_VOIDED,
                'voided_at' => now(),
                'voided_by' => $actorUserId,
                'voided_actor' => trim($actor),
                'void_reason' => trim($reason),
                'updated_by' => $actorUserId,
            ])->save();

            return $payment->fresh()->load(['allocations.studentBill', 'schoolVaultTransactions']);
        }, 5);
    }
}
