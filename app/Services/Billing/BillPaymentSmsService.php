<?php

namespace App\Services\Billing;

use App\Models\BillPayment;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\MNotifyService;
use App\Services\ParentPortal\ParentCommunicationLogService;
use App\Support\GhanaPhone;
use Illuminate\Support\Facades\Log;

class BillPaymentSmsService
{
    public function __construct(
        private MNotifyService $mnotify,
        private ParentCommunicationLogService $communicationLogService,
    ) {}

    /**
     * @return array{sent: bool, phone: string|null, message: string|null}
     */
    public function sendPaymentConfirmation(BillPayment $payment): array
    {
        $payment->loadMissing('student');

        $student = $payment->student;

        if (! $student) {
            return ['sent' => false, 'phone' => null, 'message' => 'Student not found for payment.'];
        }

        $phone = $this->resolveParentPhone($student);

        if (! $phone) {
            return ['sent' => false, 'phone' => null, 'message' => 'No parent or guardian phone number on file.'];
        }

        if (! $this->mnotify->isConfigured()) {
            return ['sent' => false, 'phone' => $phone, 'message' => 'SMS service is not configured.'];
        }

        $message = $this->buildMessage($payment, $student);
        $result = $this->mnotify->sendQuickSms([$phone], $message);

        if ($result === null) {
            return ['sent' => false, 'phone' => $phone, 'message' => 'Unable to send SMS notification.'];
        }

        Log::info('Bill payment SMS sent', [
            'payment_id' => $payment->id,
            'student_id' => $student->id,
            'phone' => $phone,
            'campaign_id' => data_get($result, 'summary._id') ?? data_get($result, 'campaign_id'),
        ]);

        $this->communicationLogService->logPaymentSms($payment, $message);

        return ['sent' => true, 'phone' => $phone, 'message' => 'SMS sent to parent.'];
    }

    private function buildMessage(BillPayment $payment, Student $student): string
    {
        $school = SchoolSetting::current();
        $schoolName = trim((string) ($school->name ?? 'EasySchool'));
        $amount = round((float) $payment->amount + (float) $payment->credit_applied, 2);
        $amountFormatted = number_format($amount, 2);

        return sprintf(
            'Dear Parent, %s fee payment of GHS %s received for %s. Receipt: %s. Thank you. - %s',
            $schoolName,
            $amountFormatted,
            $student->full_name,
            $payment->receipt_no,
            config('mnotify.sender_id', 'EASYSCHOOL')
        );
    }

    private function resolveParentPhone(Student $student): ?string
    {
        $phones = [];

        if ($student->guardian_phone) {
            $phones[] = $student->guardian_phone;
        }

        $guardianType = strtolower(trim((string) $student->guardian_type));

        if (str_contains($guardianType, 'father') && $student->father_phone) {
            array_unshift($phones, $student->father_phone);
        } elseif (str_contains($guardianType, 'mother') && $student->mother_phone) {
            array_unshift($phones, $student->mother_phone);
        }

        if ($student->father_phone) {
            $phones[] = $student->father_phone;
        }

        if ($student->mother_phone) {
            $phones[] = $student->mother_phone;
        }

        foreach ($phones as $phone) {
            $normalized = GhanaPhone::normalize($phone);

            if ($normalized) {
                return $normalized;
            }
        }

        return null;
    }
}
