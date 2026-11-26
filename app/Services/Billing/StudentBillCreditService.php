<?php

namespace App\Services\Billing;

use App\Models\Student;
use App\Models\StudentBillCreditTransaction;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class StudentBillCreditService
{
    public function creditBalance(Student $student): string
    {
        return Money::fromMinor(Money::toMinor((string) $student->credit_balance));
    }

    public function addCredit(
        Student $student,
        string|int|float $amount,
        string $source,
        ?int $billPaymentId = null,
        ?string $notes = null,
    ): StudentBillCreditTransaction {
        $amountMinor = Money::toMinor($amount);

        if ($amountMinor <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $lockedStudent = Student::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();
        $newBalance = Money::fromMinor(Money::toMinor((string) $lockedStudent->credit_balance) + $amountMinor);

        $lockedStudent->credit_balance = $newBalance;
        $lockedStudent->save();

        return StudentBillCreditTransaction::create([
            'student_id' => $lockedStudent->id,
            'type' => StudentBillCreditTransaction::TYPE_CREDIT,
            'amount' => Money::fromMinor($amountMinor),
            'balance_after' => $newBalance,
            'source' => $source,
            'bill_payment_id' => $billPaymentId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    public function applyCredit(
        Student $student,
        string|int|float $amount,
        ?int $billPaymentId = null,
        ?string $notes = null,
    ): StudentBillCreditTransaction {
        $amountMinor = Money::toMinor($amount);

        if ($amountMinor <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $lockedStudent = Student::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();
        $currentMinor = Money::toMinor((string) $lockedStudent->credit_balance);

        if ($amountMinor > $currentMinor) {
            throw new InvalidArgumentException('Insufficient credit balance.');
        }

        $newBalance = Money::fromMinor($currentMinor - $amountMinor);
        $lockedStudent->credit_balance = $newBalance;
        $lockedStudent->save();

        return StudentBillCreditTransaction::create([
            'student_id' => $lockedStudent->id,
            'type' => StudentBillCreditTransaction::TYPE_DEBIT,
            'amount' => Money::fromMinor($amountMinor),
            'balance_after' => $newBalance,
            'source' => StudentBillCreditTransaction::SOURCE_PAYMENT_APPLIED,
            'bill_payment_id' => $billPaymentId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }
}
