<?php

namespace App\Services\ParentPortal;

use App\Models\BillPayment;
use App\Models\ParentPortal\ParentAccount;
use App\Models\ParentPortal\ParentCommunicationLog;
use App\Models\Student;
use App\Support\GhanaPhone;

class ParentCommunicationLogService
{
    public function logForPhone(
        ?string $phone,
        ?Student $student,
        string $channel,
        string $message,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): void {
        $normalized = GhanaPhone::normalize($phone);

        if (! $normalized) {
            return;
        }

        $parentAccount = ParentAccount::query()->where('phone', $normalized)->first();

        ParentCommunicationLog::create([
            'parent_account_id' => $parentAccount?->id,
            'student_id' => $student?->id,
            'channel' => $channel,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'sent_at' => now(),
        ]);
    }

    public function logPaymentSms(BillPayment $payment, string $message): void
    {
        $payment->loadMissing('student');
        $student = $payment->student;

        if (! $student) {
            return;
        }

        $this->logForPhone(
            $student->guardian_phone,
            $student,
            ParentCommunicationLog::CHANNEL_PAYMENT,
            $message,
            BillPayment::class,
            $payment->id,
        );
    }
}
