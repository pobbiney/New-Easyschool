<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\SchoolClass;

class PreschoolAssessmentService
{
    public const CLASS_CATEGORY = 'PRE SCHOOL (CRECHE/NURSERY)';

    private const CATEGORY_STYLES = [
        'Language Activities' => ['icon' => 'ri-chat-3-line', 'tone' => 'green'],
        'Numeracy' => ['icon' => 'ri-numbers-line', 'tone' => 'blue'],
        'Creative - Arts' => ['icon' => 'ri-palette-line', 'tone' => 'purple'],
        'Environmental Studies / Natural Science' => ['icon' => 'ri-leaf-line', 'tone' => 'green'],
        'Psycho - Social Skills' => ['icon' => 'ri-team-line', 'tone' => 'pink'],
    ];

    public function isPreschoolClass(SchoolClass $schoolClass): bool
    {
        $schoolClass->loadMissing('category');

        return strcasecmp(trim((string) $schoolClass->category?->name), self::CLASS_CATEGORY) === 0;
    }

    public function coursesForAssessment(
        SchoolClass $schoolClass,
        ?int $yearId,
        ?int $termId
    ): array {
        if (! $yearId || ! $termId) {
            return [];
        }

        $categoryOrder = array_keys(self::CATEGORY_STYLES);

        $registrations = CourseRegistration::query()
            ->with('course')
            ->where('school_class_id', $schoolClass->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->orderBy('id')
            ->get()
            ->filter(fn (CourseRegistration $registration) => $registration->course?->status === 'Active')
            ->unique('course_id')
            ->sortBy(function (CourseRegistration $registration) use ($categoryOrder) {
                $categoryPosition = array_search($registration->course?->category, $categoryOrder, true);

                return sprintf(
                    '%03d-%010d',
                    $categoryPosition === false ? count($categoryOrder) : $categoryPosition,
                    $registration->id
                );
            });

        return $registrations->mapWithKeys(function (CourseRegistration $registration) {
            /** @var Course $course */
            $course = $registration->course;
            $category = trim((string) $course->category) ?: 'Other';
            $style = self::CATEGORY_STYLES[$category] ?? ['icon' => 'ri-book-open-line', 'tone' => 'cyan'];

            return [
                'course_'.$course->id => [
                    'course_id' => $course->id,
                    'label' => $course->name,
                    'category' => $category,
                    'icon' => $style['icon'],
                    'tone' => $style['tone'],
                ],
            ];
        })->all();
    }

    public function ratingOptions(): array
    {
        return [
            'exceeds_expectations' => ['label' => 'Exceeds Expectations', 'short' => 'Exceeds', 'tone' => 'green'],
            'meets_expectations' => ['label' => 'Meets Expectations', 'short' => 'Meets', 'tone' => 'blue'],
            'developing' => ['label' => 'Developing', 'short' => 'Developing', 'tone' => 'amber'],
            'needs_support' => ['label' => 'Needs Support', 'short' => 'Support', 'tone' => 'rose'],
        ];
    }
}
