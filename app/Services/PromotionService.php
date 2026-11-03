<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPromotionLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Billing\StudentBillSyncService;

class PromotionService
{
    public function __construct(private GradebookService $gradebook) {}

    public function evaluateStudent(Student $student, int $yearId, int $termId): array
    {
        $student->loadMissing('schoolClass');

        $report = $this->gradebook->studentReportCard($student, $yearId, $termId);

        $nextClass = $student->schoolClass?->nextActiveClass();
        $status = $this->promotionStatus(
            $report['aggregate_total_score'],
            $report['promotion_minimum_mark'],
            $report['is_promoted'],
            $nextClass
        );

        if ($status === 'below' && $this->wasConditionallyPromotedFrom($student, $student->schoolClass, $yearId, $termId)) {
            $status = 'promoted_conditional';
        }

        return [
            'student' => $student,
            'aggregate_total_score' => $report['aggregate_total_score'],
            'promotion_minimum_mark' => $report['promotion_minimum_mark'],
            'is_promoted' => $report['is_promoted'],
            'promoted_to' => $report['promoted_to'],
            'promotion_label' => $report['promotion_label'],
            'promotion_status' => $status,
        ];
    }

    public function classSummary(SchoolClass $class, int $yearId, int $termId): array
    {
        $class->loadMissing('category');
        $nextClass = $class->nextActiveClass();

        $this->backfillPromotionLogs($class, $yearId, $termId);

        $students = Student::query()
            ->where('school_class_id', $class->id)
            ->where('status', 'Active')
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();

        $rows = $students->map(fn (Student $student) => $this->evaluateStudent($student, $yearId, $termId));

        $promoted = $this->promotedFromClass($class, $yearId, $termId);

        return [
            'class' => $class,
            'next_class' => $nextClass,
            'minimum' => $class->promotion_minimum_mark,
            'students' => $rows,
            'eligible' => $rows->where('promotion_status', 'eligible')->values(),
            'below' => $rows->where('promotion_status', 'below')->values(),
            'final_class' => $rows->where('promotion_status', 'final_class')->values(),
            'promoted' => $promoted,
            'counts' => [
                'total' => $rows->count(),
                'eligible' => $rows->where('promotion_status', 'eligible')->count(),
                'below' => $rows->where('promotion_status', 'below')->count(),
                'final_class' => $rows->where('promotion_status', 'final_class')->count(),
                'promoted' => $promoted->count(),
                'promoted_conditional' => $promoted->where('promotion_type', StudentPromotionLog::TYPE_CONDITIONAL)->count(),
            ],
        ];
    }

    public function promotedFromClass(SchoolClass $class, int $yearId, int $termId): Collection
    {
        return StudentPromotionLog::query()
            ->with(['student', 'toClass', 'promotedBy'])
            ->where('from_class_id', $class->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     */
    public function hubSummaries(Collection $classes, int $yearId, int $termId): Collection
    {
        return $classes->map(function (SchoolClass $class) use ($yearId, $termId) {
            $summary = $this->classSummary($class, $yearId, $termId);

            return [
                'class' => $class,
                'next_class' => $summary['next_class'],
                'minimum' => $summary['minimum'],
                'counts' => $summary['counts'],
                'promoted' => $summary['promoted']->count(),
            ];
        });
    }

    public function promoteStudents(
        SchoolClass $fromClass,
        array $studentIds,
        int $yearId,
        int $termId,
        bool $allowManagementOverride = false
    ): array {
        $nextClass = $fromClass->nextActiveClass();

        if (! $nextClass) {
            return [
                'promoted' => 0,
                'skipped' => [],
                'message' => 'This class has no next class in the same category.',
            ];
        }

        $academicYear = AcademicYear::query()->findOrFail($yearId);
        $promoted = 0;
        $skipped = [];
        $promotionType = $allowManagementOverride
            ? StudentPromotionLog::TYPE_CONDITIONAL
            : StudentPromotionLog::TYPE_STANDARD;

        foreach (array_unique($studentIds) as $studentId) {
            $student = Student::query()->find($studentId);

            if (! $student || $student->school_class_id !== $fromClass->id || $student->status !== 'Active') {
                $skipped[] = [
                    'id' => (int) $studentId,
                    'reason' => 'Student is not active in this class.',
                ];

                continue;
            }

            $evaluation = $this->evaluateStudent($student, $yearId, $termId);

            if ($allowManagementOverride) {
                if (! in_array($evaluation['promotion_status'], ['below', 'promoted_conditional'], true)) {
                    $skipped[] = [
                        'id' => (int) $studentId,
                        'reason' => 'Only students below the pass mark can be promoted through management override.',
                    ];

                    continue;
                }
            } elseif ($evaluation['promotion_status'] !== 'eligible') {
                $skipped[] = [
                    'id' => (int) $studentId,
                    'reason' => 'Student does not meet the promotion pass mark.',
                ];

                continue;
            }

            DB::transaction(function () use (
                $student,
                $fromClass,
                $nextClass,
                $academicYear,
                $yearId,
                $termId,
                $evaluation,
                $promotionType
            ) {
                $student->school_class_id = $nextClass->id;
                $student->class_name = $nextClass->name;
                $student->academic_year_id = $academicYear->id;
                $student->academic_year = $academicYear->name;
                $student->academic_term_id = $termId;
                $student->last_promotion_from_class_id = $fromClass->id;
                $student->last_promotion_type = $promotionType;
                $student->last_promoted_at = now();
                $student->updated_by = Auth::id();
                $student->save();

                StudentPromotionLog::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'from_class_id' => $fromClass->id,
                        'academic_year_id' => $yearId,
                        'academic_term_id' => $termId,
                    ],
                    [
                        'to_class_id' => $nextClass->id,
                        'promotion_type' => $promotionType,
                        'aggregate_total_score' => $evaluation['aggregate_total_score'],
                        'promotion_minimum_mark' => $evaluation['promotion_minimum_mark'],
                        'promoted_by' => Auth::id() ?? $student->updated_by ?? 1,
                    ]
                );

                app(StudentBillSyncService::class)->syncForStudent(
                    $student->fresh(['schoolClass.category'])
                );
            });

            $promoted++;
        }

        $message = $promoted > 0
            ? sprintf(
                '%d student(s) promoted to %s%s.',
                $promoted,
                $nextClass->name,
                $allowManagementOverride ? ' (management override)' : ''
            )
            : 'No students were promoted.';

        return [
            'promoted' => $promoted,
            'skipped' => $skipped,
            'next_class' => $nextClass->name,
            'management_override' => $allowManagementOverride,
            'message' => $message,
        ];
    }

    private function backfillPromotionLogs(SchoolClass $class, int $yearId, int $termId): void
    {
        $nextClass = $class->nextActiveClass();

        if (! $nextClass) {
            return;
        }

        Student::query()
            ->where('last_promotion_from_class_id', $class->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->where('school_class_id', $nextClass->id)
            ->get()
            ->each(function (Student $student) use ($class, $nextClass, $yearId, $termId) {
                StudentPromotionLog::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'from_class_id' => $class->id,
                        'academic_year_id' => $yearId,
                        'academic_term_id' => $termId,
                    ],
                    [
                        'to_class_id' => $nextClass->id,
                        'promotion_type' => $student->last_promotion_type ?? StudentPromotionLog::TYPE_STANDARD,
                        'aggregate_total_score' => null,
                        'promotion_minimum_mark' => $class->promotion_minimum_mark,
                        'promoted_by' => $student->updated_by ?? 1,
                        'created_at' => $student->last_promoted_at ?? $student->updated_at,
                        'updated_at' => $student->last_promoted_at ?? $student->updated_at,
                    ]
                );
            });
    }

    private function wasConditionallyPromotedFrom(
        Student $student,
        ?SchoolClass $class,
        int $yearId,
        int $termId
    ): bool {
        if (! $class) {
            return false;
        }

        return StudentPromotionLog::query()
            ->where('student_id', $student->id)
            ->where('from_class_id', $class->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->where('promotion_type', StudentPromotionLog::TYPE_CONDITIONAL)
            ->exists();
    }

    private function promotionStatus(
        ?int $aggregateTotal,
        ?int $minimum,
        ?bool $isPromoted,
        ?SchoolClass $nextClass
    ): string {
        if (! $nextClass) {
            return 'final_class';
        }

        if ($minimum === null) {
            return 'eligible';
        }

        if ($aggregateTotal === null || $isPromoted === false) {
            return 'below';
        }

        return 'eligible';
    }
}
