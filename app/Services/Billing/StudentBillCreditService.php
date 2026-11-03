<?php

namespace App\Services\Billing;

use App\Models\Student;
use App\Models\StudentBillCreditTransaction;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class StudentBillCreditService
{
    public function creditBalance(Student $student): float
    {
        return round((float) $student->credit_balance, 2);
    }

    public function addCredit(
        Student $student,
        float $amount,
        string $source,
        ?int $billPaymentId = null,
        ?string $notes = null,
    ): StudentBillCreditTransaction {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $lockedStudent = Student::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();
        $newBalance = round((float) $lockedStudent->credit_balance + $amount, 2);

        $lockedStudent->credit_balance = $newBalance;
        $lockedStudent->save();

        return StudentBillCreditTransaction::create([
            'student_id' => $lockedStudent->id,
            'type' => StudentBillCreditTransaction::TYPE_CREDIT,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'source' => $source,
            'bill_payment_id' => $billPaymentId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    public function applyCredit(
        Student $student,
        float $amount,
        ?int $billPaymentId = null,
        ?string $notes = null,
    ): StudentBillCreditTransaction {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $lockedStudent = Student::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();
        $currentBalance = round((float) $lockedStudent->credit_balance, 2);

        if ($amount > $currentBalance + 0.009) {
            throw new InvalidArgumentException('Insufficient credit balance.');
        }

        $newBalance = round($currentBalance - $amount, 2);
        $lockedStudent->credit_balance = $newBalance;
        $lockedStudent->save();

        return StudentBillCreditTransaction::create([
            'student_id' => $lockedStudent->id,
            'type' => StudentBillCreditTransaction::TYPE_DEBIT,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'source' => StudentBillCreditTransaction::SOURCE_PAYMENT_APPLIED,
            'bill_payment_id' => $billPaymentId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }
}
