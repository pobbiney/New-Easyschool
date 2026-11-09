<?php

namespace App\Services;

use App\Models\CourseTeachingAssignment;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use App\Support\AcademicPeriodDefaults;
use App\Support\TeacherCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TeacherAccessService
{
    public function isTeacher(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        return (int) $user->user_cat === TeacherCategory::id()
            && $user->staff_id
            && $user->status === 'Active';
    }

    public function staffId(?User $user = null): ?int
    {
        $user = $user ?? auth()->user();

        if (! $user || $user->status !== 'Active' || ! $user->staff_id) {
            return null;
        }

        if ($this->isTeacher($user) || app(UserAccessService::class)->can('teacher-dashboard', $user)) {
            return (int) $user->staff_id;
        }

        return null;
    }

    public function staff(?User $user = null): ?Staff
    {
        $staffId = $this->staffId($user);

        return $staffId ? Staff::find($staffId) : null;
    }

    public function homeroomClasses(int $staffId): Collection
    {
        return SchoolClass::query()
            ->whereHas('classTeacherAssignments', fn ($query) => $query->where('staff_id', $staffId))
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();
    }

    public function subjectAssignments(int $staffId, ?int $yearId = null, ?int $termId = null): Collection
    {
        return CourseTeachingAssignment::query()
            ->with(['course.parent', 'schoolClass'])
            ->where('staff_id', $staffId)
            ->when($yearId, fn ($q) => $q->where(function ($query) use ($yearId) {
                $query->where('academic_year_id', $yearId)
                    ->orWhereNull('academic_year_id');
            }))
            ->when($termId, fn ($q) => $q->where(function ($query) use ($termId) {
                $query->where('academic_term_id', $termId)
                    ->orWhereNull('academic_term_id');
            }))
            ->get()
            ->sortBy(fn ($a) => ($a->schoolClass?->name ?? '').($a->course?->name ?? ''))
            ->values();
    }

    public function ownsHomeroomClass(int $staffId, int $classId): bool
    {
        return SchoolClass::query()
            ->where('id', $classId)
            ->whereHas('classTeacherAssignments', fn ($query) => $query->where('staff_id', $staffId))
            ->exists();
    }

    public function ownsSubjectAssignment(int $staffId, int $courseId, int $classId, ?int $yearId = null, ?int $termId = null): bool
    {
        return CourseTeachingAssignment::query()
            ->where('staff_id', $staffId)
            ->where('course_id', $courseId)
            ->where('school_class_id', $classId)
            ->when($yearId, fn ($q) => $q->where(function ($query) use ($yearId) {
                $query->where('academic_year_id', $yearId)
                    ->orWhereNull('academic_year_id');
            }))
            ->when($termId, fn ($q) => $q->where(function ($query) use ($termId) {
                $query->where('academic_term_id', $termId)
                    ->orWhereNull('academic_term_id');
            }))
            ->exists();
    }

    public function canAccessClass(int $staffId, int $classId, ?int $courseId = null, ?Request $request = null): bool
    {
        if ($this->ownsHomeroomClass($staffId, $classId)) {
            return true;
        }

        if ($courseId) {
            $yearId = AcademicPeriodDefaults::yearId($request);
            $termId = AcademicPeriodDefaults::termId($request);

            return $this->ownsSubjectAssignment($staffId, $courseId, $classId, $yearId, $termId);
        }

        return CourseTeachingAssignment::query()
            ->where('staff_id', $staffId)
            ->where('school_class_id', $classId)
            ->exists();
    }

    public function studentsForClass(int $classId): Collection
    {
        return Student::query()
            ->where('school_class_id', $classId)
            ->where('status', 'Active')
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();
    }

    public function assertCanAccessClass(int $staffId, int $classId, ?int $courseId = null, ?Request $request = null): void
    {
        if (! $this->canAccessClass($staffId, $classId, $courseId, $request)) {
            abort(403, 'You do not have access to this class.');
        }
    }

    public function assertHomeroomTeacher(int $staffId, int $classId): void
    {
        if (! $this->ownsHomeroomClass($staffId, $classId)) {
            abort(403, 'Only the class teacher can perform this action.');
        }
    }
}
