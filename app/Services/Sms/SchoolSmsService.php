<?php

namespace App\Services\Sms;

use App\Models\SchoolClass;
use App\Models\SmsMessage;
use App\Models\Staff;
use App\Models\Student;
use App\Services\MNotifyService;
use App\Services\ParentPortal\ParentCommunicationLogService;
use App\Support\GhanaPhone;
use App\Support\TeacherCategory;
use RuntimeException;

class SchoolSmsService
{
    public function __construct(
        private MNotifyService $mnotify,
        private ParentCommunicationLogService $communicationLogService,
    ) {}

    /**
     * @return array{label: string, recipients: array<int, array{phone: string, name: string}>, skipped: int}
     */
    public function preview(string $audience, array $options = []): array
    {
        return $this->resolve($audience, $options);
    }

    /**
     * @return array{label: string, sent: int, skipped: int, recipient_count: int, response: array<string, mixed>|null}
     */
    public function send(string $audience, string $message, array $options = []): array
    {
        $resolved = $this->resolve($audience, $options);
        $phones = array_values(array_unique(array_column($resolved['recipients'], 'phone')));

        if ($phones === []) {
            throw new RuntimeException('No valid phone numbers were found for this audience.');
        }

        if (! $this->mnotify->isConfigured()) {
            throw new RuntimeException('SMS is not configured. Add an mNotify API key and sender ID in .env.');
        }

        $response = $this->mnotify->sendQuickSms($phones, $message);

        if ($response === null) {
            throw new RuntimeException('The SMS gateway could not send this message.');
        }

        if (in_array($audience, ['school', 'class', 'individual'], true)) {
            foreach ($resolved['recipients'] as $recipient) {
                $student = null;

                if ($audience === 'individual' && ($options['target_type'] ?? '') === 'student') {
                    $studentId = (int) ($options['target_ids'][0] ?? $options['target_id'] ?? 0);
                    $student = Student::find($studentId);
                }

                $this->communicationLogService->logForPhone(
                    $recipient['phone'],
                    $student,
                    'sms',
                    $message,
                    SmsMessage::class,
                    null,
                );
            }
        }

        return [
            'label' => $resolved['label'],
            'sent' => count($phones),
            'skipped' => $resolved['skipped'],
            'recipient_count' => count($phones),
            'response' => $response,
        ];
    }

    /**
     * @return array{label: string, recipients: array<int, array{phone: string, name: string}>, skipped: int}
     */
    private function resolve(string $audience, array $options = []): array
    {
        return match ($audience) {
            'teachers' => $this->fromStaff($this->teachers(), 'Teachers'),
            'staff' => $this->fromStaff(
                Staff::where('status', 'Active')->orderBy('surname')->get(),
                'All staff'
            ),
            'class' => $this->fromClass((int) ($options['school_class_id'] ?? 0)),
            'school' => $this->fromStudents(
                Student::where('status', 'Active')->orderBy('surname')->get(),
                'Entire school (parents / guardians)'
            ),
            'individual' => $this->fromIndividual(
                (string) ($options['target_type'] ?? ''),
                $this->targetIds($options)
            ),
            default => throw new RuntimeException('Choose who should receive this message.'),
        };
    }

    private function teachers()
    {
        return Staff::query()
            ->where('status', 'Active')
            ->where(function ($query) {
                $query->whereHas('user', function ($user) {
                    $user->where('user_cat', TeacherCategory::id());
                })->orWhereHas('hrPosition', function ($position) {
                    $position->where('name', 'Teacher');
                })->orWhere('position', 'Teacher');
            })
            ->orderBy('surname')
            ->get();
    }

    private function fromStaff($staffMembers, string $label): array
    {
        $recipients = [];
        $skipped = 0;

        foreach ($staffMembers as $staff) {
            $phone = GhanaPhone::normalize($staff->mobile);
            if (! $phone) {
                $skipped++;
                continue;
            }
            $recipients[$phone] = [
                'phone' => $phone,
                'name' => $staff->full_name,
            ];
        }

        return [
            'label' => $label,
            'recipients' => array_values($recipients),
            'skipped' => $skipped,
        ];
    }

    private function fromStudents($students, string $label): array
    {
        $recipients = [];
        $skipped = 0;

        foreach ($students as $student) {
            $phone = $this->studentPhone($student);
            if (! $phone) {
                $skipped++;
                continue;
            }
            $recipients[$phone] = [
                'phone' => $phone,
                'name' => 'Guardian of '.$student->full_name,
            ];
        }

        return [
            'label' => $label,
            'recipients' => array_values($recipients),
            'skipped' => $skipped,
        ];
    }

    private function fromClass(int $classId): array
    {
        $class = SchoolClass::find($classId);
        if (! $class) {
            throw new RuntimeException('Choose a class.');
        }

        $students = Student::where('status', 'Active')
            ->where('school_class_id', $class->id)
            ->orderBy('surname')
            ->get();

        return $this->fromStudents($students, $class->name.' (parents / guardians)');
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function fromIndividual(string $type, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            throw new RuntimeException('Choose at least one person.');
        }

        if ($type === 'staff') {
            $staff = Staff::query()->whereIn('id', $ids)->orderBy('surname')->orderBy('firstname')->get();
            if ($staff->isEmpty()) {
                throw new RuntimeException('Choose a staff member.');
            }

            $names = $staff->pluck('full_name');
            $label = $names->count() === 1
                ? (string) $names->first()
                : ($names->count() <= 4 ? $names->implode(', ') : $names->count().' selected staff');

            return $this->fromStaff($staff, $label);
        }

        if ($type === 'student') {
            $students = Student::query()->whereIn('id', $ids)->orderBy('surname')->orderBy('firstname')->get();
            if ($students->isEmpty()) {
                throw new RuntimeException('Choose a student.');
            }

            $label = $students->count() === 1
                ? 'Guardian of '.$students->first()->full_name
                : $students->count().' selected students (parents / guardians)';

            return $this->fromStudents($students, $label);
        }

        throw new RuntimeException('Choose a staff member or student.');
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<int, int>
     */
    private function targetIds(array $options): array
    {
        $ids = $options['target_ids'] ?? $options['target_id'] ?? [];

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        return array_values(array_filter(array_map('intval', $ids)));
    }

    private function studentPhone(Student $student): ?string
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

        foreach ([$student->father_phone, $student->mother_phone, $student->phone] as $phone) {
            if ($phone) {
                $phones[] = $phone;
            }
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
