<?php

namespace App\Services;

use App\Models\AcademicAssessment;
use App\Models\AssessmentType;
use App\Models\ClassCourseAssessmentMark;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;

class AssessmentMarksService
{
    public function typesForClass(?SchoolClass $class): Collection
    {
        return AssessmentType::query()
            ->active()
            ->forClassCategory($class?->class_category_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function marksFor(int $classId, int $courseId, int $yearId, int $termId): Collection
    {
        return ClassCourseAssessmentMark::mapFor($classId, $courseId, $yearId, $termId);
    }

    public function markForType(int $classId, int $courseId, int $typeId, int $yearId, int $termId): ?ClassCourseAssessmentMark
    {
        return ClassCourseAssessmentMark::query()
            ->where('school_class_id', $classId)
            ->where('course_id', $courseId)
            ->where('assessment_type_id', $typeId)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->first();
    }

    public function usedCount(int $classId, int $courseId, string $typeSlug, int $yearId, int $termId): int
    {
        return AcademicAssessment::query()
            ->where('school_class_id', $classId)
            ->where('course_id', $courseId)
            ->where('type', $typeSlug)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->count();
    }

    public function resolveTypeForClass(SchoolClass $class, string $slug): ?AssessmentType
    {
        return AssessmentType::query()
            ->active()
            ->forClassCategory($class->class_category_id)
            ->where('slug', $slug)
            ->first();
    }

    public function setupOptions(SchoolClass $class, int $courseId, int $yearId, int $termId): array
    {
        $types = $this->typesForClass($class);
        $marks = $this->marksFor($class->id, $courseId, $yearId, $termId);

        return $types->map(function (AssessmentType $type) use ($class, $courseId, $yearId, $termId, $marks) {
            $mark = $marks->get($type->id);
            $used = $this->usedCount($class->id, $courseId, $type->slug, $yearId, $termId);
            $remaining = max(0, (int) $type->max_number - $used);

            return [
                'id' => $type->id,
                'slug' => $type->slug,
                'name' => $type->name,
                'category' => $type->category,
                'category_label' => $type->categoryLabel(),
                'max_number' => (int) $type->max_number,
                'used_count' => $used,
                'remaining' => $remaining,
                'marks_set' => $mark !== null,
                'total_score' => $mark ? (float) $mark->total_score : null,
            ];
        })->values()->all();
    }
}
