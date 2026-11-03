<?php

namespace App\Services\ParentPortal;

use App\Models\ParentPortal\ParentAccount;
use App\Models\Student;
use App\Support\GhanaPhone;
use Illuminate\Support\Collection;

class ParentStudentService
{
    public function normalizePhone(?string $phone): ?string
    {
        return GhanaPhone::normalize($phone);
    }

    public function childrenFor(ParentAccount $parent): Collection
    {
        $phone = $this->normalizePhone($parent->phone);

        if (! $phone) {
            return collect();
        }

        return Student::query()
            ->with(['schoolClass.category'])
            ->where('status', 'Active')
            ->where(function ($query) use ($phone) {
                $query->where('guardian_phone', $phone)
                    ->orWhere('guardian_phone', 'like', '%'.ltrim($phone, '0'));
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get()
            ->filter(fn (Student $student) => $this->phonesMatch($parent->phone, $student->guardian_phone))
            ->values();
    }

    public function ownsStudent(ParentAccount $parent, Student|int $student): bool
    {
        $studentId = $student instanceof Student ? $student->id : $student;

        return $this->childrenFor($parent)->contains(fn (Student $child) => $child->id === $studentId);
    }

    public function findOwnedStudent(ParentAccount $parent, int $studentId): ?Student
    {
        return $this->childrenFor($parent)->firstWhere('id', $studentId);
    }

    public function phonesMatch(?string $parentPhone, ?string $guardianPhone): bool
    {
        $a = $this->normalizePhone($parentPhone);
        $b = $this->normalizePhone($guardianPhone);

        if (! $a || ! $b) {
            return false;
        }

        return $a === $b;
    }
}
