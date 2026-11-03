<?php

namespace App\Services\ParentPortal;

use App\Models\ParentPortal\ParentAccount;
use App\Models\Student;

class ParentAccountService
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function syncFromStudent(Student $student): ?ParentAccount
    {
        if ($student->status !== 'Active') {
            return null;
        }

        $phone = $this->parentStudentService->normalizePhone($student->guardian_phone);

        if (! $phone) {
            return null;
        }

        $existing = ParentAccount::query()
            ->where('phone', $phone)
            ->first();

        if ($existing) {
            $existing->update([
                'guardian_name' => $student->guardian_name ?: $existing->guardian_name,
            ]);

            return $existing;
        }

        return ParentAccount::create([
            'phone' => $phone,
            'guardian_name' => $student->guardian_name,
            'password' => (string) config('parent.default_password', 'Parent123'),
            'status' => ParentAccount::STATUS_ACTIVE,
            'must_change_password' => false,
        ]);
    }

    public function syncAllActiveStudents(): int
    {
        $count = 0;

        Student::query()
            ->where('status', 'Active')
            ->orderBy('id')
            ->chunkById(100, function ($students) use (&$count) {
                foreach ($students as $student) {
                    if ($this->syncFromStudent($student)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function resetToDefault(string $phone): ?ParentAccount
    {
        $account = ParentAccount::query()
            ->where('phone', $phone)
            ->where('status', ParentAccount::STATUS_ACTIVE)
            ->first();

        if (! $account) {
            return null;
        }

        $account->update([
            'password' => (string) config('parent.default_password', 'Parent123'),
            'must_change_password' => true,
        ]);

        return $account->fresh();
    }
}
