<?php

namespace App\Services;

use App\Models\AcademicAssessment;
use App\Models\AssessmentType;
use App\Models\ClassAttendance;
use App\Models\ClassCourseAssessmentMark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPromotionLog;
use App\Models\StudentRemark;
use Illuminate\Support\Collection;

class GradebookService
{
    public function __construct(private GradingService $grading) {}

    public function classGradebook(int $classId, int $yearId, int $termId): array
    {
        $schoolClass = SchoolClass::query()->findOrFail($classId);

        $assessmentTypes = AssessmentType::query()
            ->active()
            ->forClassCategory($schoolClass->class_category_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->keyBy('slug');

        $marksByCourse = ClassCourseAssessmentMark::query()
            ->where('school_class_id', $classId)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->get()
            ->groupBy('course_id');

        $assessments = AcademicAssessment::query()
            ->with(['course', 'scores', 'assessmentType'])
            ->where('school_class_id', $classId)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->where(function ($query) {
                $query->where('status', 'published')
                    ->orWhereHas('scores', fn ($scoreQuery) => $scoreQuery->whereNotNull('score'));
            })
            ->orderBy('course_id')
            ->orderBy('type')
            ->orderBy('assessment_date')
            ->orderBy('id')
            ->get();

        $students = Student::query()
            ->where('school_class_id', $classId)
            ->where('status', 'Active')
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();

        $byCourse = $assessments->groupBy(fn ($a) => $a->course_id ?? 0);

        $courseSummaries = $byCourse->map(function (Collection $courseAssessments, $courseId) use ($students, $assessmentTypes, $marksByCourse) {
            $course = $courseId ? $courseAssessments->first()->course : null;
            $courseMarks = collect($marksByCourse->get((int) $courseId, collect()))->keyBy('assessment_type_id');

            $typeColumns = $this->buildTypeColumns($courseAssessments, $assessmentTypes, $courseMarks);

            $studentRows = $students->map(function (Student $student) use ($typeColumns, $assessmentTypes) {
                $typeScores = $typeColumns->mapWithKeys(function (array $column) use ($student) {
                    $aggregate = $this->aggregateTypeScore(
                        $column['assessments'],
                        $student->id,
                        $column['total_score']
                    );

                    return [$column['type']->slug => $aggregate];
                });

                $average = $this->calculateFinalPercentageFromTypeScores($typeScores, $assessmentTypes);

                return [
                    'student' => $student,
                    'type_scores' => $typeScores,
                    'average_percentage' => $average,
                    'letter_grade' => $this->grading->letterGradeForPercentage($average),
                ];
            });

            return [
                'course' => $course,
                'course_name' => $course?->name ?? 'Homeroom Activities',
                'type_columns' => $typeColumns,
                'assessments' => $courseAssessments,
                'students' => $studentRows,
            ];
        })->values();

        $termAverages = $students->map(function (Student $student) use ($courseSummaries) {
            $subjectFinals = $courseSummaries
                ->map(function ($summary) use ($student) {
                    $row = $summary['students']->firstWhere('student.id', $student->id);

                    return $row['average_percentage'] ?? null;
                })
                ->filter(fn ($percentage) => $percentage !== null);

            $average = $subjectFinals->isNotEmpty()
                ? round($subjectFinals->avg(), 0)
                : null;

            return [
                'student' => $student,
                'average_percentage' => $average,
                'letter_grade' => $this->grading->letterGradeForPercentage($average),
            ];
        });

        return [
            'assessments' => $assessments,
            'course_summaries' => $courseSummaries,
            'term_averages' => $termAverages,
        ];
    }

    public function studentReportCard(Student $student, int $yearId, int $termId): array
    {
        $student->loadMissing('schoolClass');
        $gradebook = $this->classGradebook((int) $student->school_class_id, $yearId, $termId);

        $assessmentTypes = AssessmentType::query()
            ->active()
            ->forClassCategory($student->schoolClass?->class_category_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->keyBy('slug');

        $subjectPositionsByCourse = [];

        foreach ($gradebook['course_summaries'] as $summary) {
            $rankings = $summary['students']->map(function (array $row) use ($summary, $assessmentTypes) {
                $components = $this->subjectComponents(
                    $summary['type_columns'],
                    $row['student']->id,
                    $assessmentTypes
                );

                return [
                    'student_id' => $row['student']->id,
                    'total_score' => $components['total_score'],
                ];
            })->filter(fn (array $item) => $item['total_score'] !== null);

            $subjectPositionsByCourse[$summary['course_name']] = $this->assignCompetitionPositions(
                $rankings,
                'total_score'
            );
        }

        $classPositions = $this->assignCompetitionPositions(
            $gradebook['term_averages']
                ->filter(fn (array $row) => ($row['average_percentage'] ?? null) !== null)
                ->map(fn (array $row) => [
                    'student_id' => $row['student']->id,
                    'average_percentage' => $row['average_percentage'],
                ]),
            'average_percentage'
        );

        $attendance = ClassAttendance::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->get();

        $attendanceSummary = [
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
            'total_days' => $attendance->count(),
        ];

        $termAverage = $gradebook['term_averages']->firstWhere('student.id', $student->id);

        $subjectGrades = $gradebook['course_summaries']
            ->map(function (array $summary) use ($student, $assessmentTypes, $subjectPositionsByCourse) {
                $components = $this->subjectComponents(
                    $summary['type_columns'],
                    $student->id,
                    $assessmentTypes
                );

                return [
                    'course_name' => $summary['course_name'],
                    'class_score' => $components['class_score'],
                    'exam_score' => $components['exam_score'],
                    'total_score' => $components['total_score'],
                    'average_percentage' => $components['total_score'],
                    'position' => $subjectPositionsByCourse[$summary['course_name']][$student->id] ?? null,
                    'remark' => $components['remark'],
                    'letter_grade' => $components['letter_grade'],
                ];
            })
            ->sortBy('course_name')
            ->values();

        $promotion = $this->resolvePromotion($student, $subjectGrades);
        $promotion = $this->applyPromotionLogLabel($student, $yearId, $termId, $promotion);
        $remarks = StudentRemark::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->first();

        return [
            'student' => $student,
            'term_average' => $termAverage,
            'subject_grades' => $subjectGrades,
            'attendance' => $attendanceSummary,
            'class_position' => $classPositions[$student->id] ?? null,
            'students_on_roll' => $gradebook['term_averages']->count(),
            'roll_number' => $student->roll_number,
            'aggregate_total_score' => $promotion['aggregate_total_score'],
            'promotion_minimum_mark' => $promotion['promotion_minimum_mark'],
            'is_promoted' => $promotion['is_promoted'],
            'promoted_to' => $promotion['promoted_to'],
            'promotion_label' => $promotion['promotion_label'],
            'attitude' => $remarks?->attitude,
            'conduct' => $remarks?->conduct,
            'interest' => $remarks?->interest,
            'class_teacher_remark' => $remarks?->class_teacher_remark,
            'headteacher_signature' => $remarks?->headteacher_signature,
        ];
    }

    /**
     * Sum all subject total scores and compare against the class promotion minimum.
     */
    private function resolvePromotion(Student $student, Collection $subjectGrades): array
    {
        $schoolClass = $student->schoolClass;

        if (! $schoolClass) {
            return $this->emptyPromotionResult(null);
        }

        $scoredSubjects = $subjectGrades->filter(fn (array $subject) => $subject['total_score'] !== null);
        $aggregateTotal = $scoredSubjects->isNotEmpty()
            ? (int) $scoredSubjects->sum('total_score')
            : null;

        $minimum = $schoolClass->promotion_minimum_mark;
        $nextClass = $schoolClass->nextActiveClass();

        if (! $nextClass) {
            if ($minimum !== null && $aggregateTotal !== null && $aggregateTotal < $minimum) {
                return $this->emptyPromotionResult($aggregateTotal, $minimum, false, null, 'Repeat');
            }

            return $this->emptyPromotionResult($aggregateTotal, $minimum);
        }

        if ($minimum === null) {
            return $this->emptyPromotionResult($aggregateTotal, null, true, $nextClass->name, $nextClass->name);
        }

        if ($aggregateTotal === null) {
            return $this->emptyPromotionResult(null, $minimum, false, null, 'Repeat');
        }

        $isPromoted = $aggregateTotal >= $minimum;

        return $this->emptyPromotionResult(
            $aggregateTotal,
            $minimum,
            $isPromoted,
            $isPromoted ? $nextClass->name : null,
            $isPromoted ? $nextClass->name : 'Repeat'
        );
    }

    private function emptyPromotionResult(
        ?int $aggregateTotal,
        ?int $minimum = null,
        ?bool $isPromoted = null,
        ?string $promotedTo = null,
        ?string $promotionLabel = null
    ): array {
        return [
            'aggregate_total_score' => $aggregateTotal,
            'promotion_minimum_mark' => $minimum,
            'is_promoted' => $isPromoted,
            'promoted_to' => $promotedTo,
            'promotion_label' => $promotionLabel,
        ];
    }

    private function applyPromotionLogLabel(Student $student, int $yearId, int $termId, array $promotion): array
    {
        $log = StudentPromotionLog::query()
            ->with('toClass')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->first();

        if (! $log || ! $log->toClass) {
            return $promotion;
        }

        $label = $log->toClass->name;

        if ($log->isConditional()) {
            $label .= ' (Conditional Promotion)';
        }

        $promotion['promoted_to'] = $log->toClass->name;
        $promotion['promotion_label'] = $label;
        $promotion['is_promoted'] = true;
        $promotion['promotion_type'] = $log->promotion_type;

        return $promotion;
    }

    private function buildTypeColumns(Collection $courseAssessments, Collection $assessmentTypes, Collection $courseMarks): Collection
    {
        return $courseAssessments
            ->groupBy('type')
            ->map(function (Collection $assessments, string $typeSlug) use ($assessmentTypes, $courseMarks) {
                $type = $assessmentTypes->get($typeSlug);

                if (! $type) {
                    return null;
                }

                $mark = $courseMarks->get($type->id);
                $totalScore = (float) ($mark?->total_score ?? $type->total_score ?? $assessments->max('max_score') ?? 0);
                $displayType = clone $type;
                $displayType->total_score = $totalScore;

                return [
                    'type' => $displayType,
                    'total_score' => $totalScore,
                    'assessments' => $assessments->values(),
                    'assessment_count' => $assessments->count(),
                ];
            })
            ->filter()
            ->sortBy(fn (array $column) => [$column['type']->sort_order, $column['type']->name])
            ->values();
    }

    /**
     * Average all test marks for a type, scored against the teacher total for this class + subject.
     */
    private function aggregateTypeScore(Collection $assessments, int $studentId, float $totalScore): ?array
    {
        $rawScores = collect();
        $breakdown = [];

        foreach ($assessments as $assessment) {
            $score = $assessment->scores->firstWhere('student_id', $studentId);

            if (! $score || $score->score === null) {
                continue;
            }

            $rawScores->push((float) $score->score);
            $breakdown[] = [
                'title' => $assessment->title,
                'score' => (float) $score->score,
            ];
        }

        if ($rawScores->isEmpty()) {
            return null;
        }

        $averageScore = $rawScores->avg();
        $percentage = $totalScore > 0 ? ($averageScore / $totalScore) * 100 : null;

        return [
            'average_score' => round($averageScore, 2),
            'total_score' => $totalScore,
            'percentage' => $percentage !== null ? round($percentage, 2) : null,
            'letter_grade' => $this->grading->letterGradeForPercentage($percentage),
            'test_count' => $rawScores->count(),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Final mark = (avg class_assessment type % / 2) + (avg examination_assessment type % / 2)
     */
    private function calculateFinalPercentageFromTypeScores(Collection $typeScores, Collection $assessmentTypes): ?float
    {
        $classPercentages = collect();
        $examinationPercentages = collect();

        foreach ($typeScores as $slug => $aggregate) {
            if (! $aggregate || $aggregate['percentage'] === null) {
                continue;
            }

            $type = $assessmentTypes->get($slug);

            if (! $type) {
                continue;
            }

            if ($type->category === AssessmentType::CATEGORY_CLASS) {
                $classPercentages->push($aggregate['percentage']);
            } elseif ($type->category === AssessmentType::CATEGORY_EXAMINATION) {
                $examinationPercentages->push($aggregate['percentage']);
            }
        }

        $classComponent = $classPercentages->isNotEmpty()
            ? $classPercentages->avg() / 2
            : null;

        $examinationComponent = $examinationPercentages->isNotEmpty()
            ? $examinationPercentages->avg() / 2
            : null;

        if ($classComponent === null && $examinationComponent === null) {
            return null;
        }

        return round(($classComponent ?? 0) + ($examinationComponent ?? 0), 0);
    }

    /**
     * Class score (out of 50) + exam score (out of 50) = total (out of 100).
     */
    private function subjectComponents(Collection $typeColumns, int $studentId, Collection $assessmentTypes): array
    {
        $typeScores = $typeColumns->mapWithKeys(function (array $column) use ($studentId) {
            $aggregate = $this->aggregateTypeScore(
                $column['assessments'],
                $studentId,
                (float) $column['total_score']
            );

            return [$column['type']->slug => $aggregate];
        });

        $classPercentages = collect();
        $examinationPercentages = collect();

        foreach ($typeScores as $slug => $aggregate) {
            if (! $aggregate || $aggregate['percentage'] === null) {
                continue;
            }

            $type = $assessmentTypes->get($slug);

            if (! $type) {
                continue;
            }

            if ($type->category === AssessmentType::CATEGORY_CLASS) {
                $classPercentages->push($aggregate['percentage']);
            } elseif ($type->category === AssessmentType::CATEGORY_EXAMINATION) {
                $examinationPercentages->push($aggregate['percentage']);
            }
        }

        $classScore = $classPercentages->isNotEmpty()
            ? (int) round($classPercentages->avg() / 2, 0)
            : null;

        $examScore = $examinationPercentages->isNotEmpty()
            ? (int) round($examinationPercentages->avg() / 2, 0)
            : null;

        if ($classScore === null && $examScore === null) {
            $total = null;
        } else {
            $total = (int) round(($classScore ?? 0) + ($examScore ?? 0), 0);
        }

        return [
            'class_score' => $classScore,
            'exam_score' => $examScore,
            'total_score' => $total,
            'letter_grade' => $this->grading->letterGradeForPercentage($total),
            'remark' => $this->grading->remarkForPercentage($total),
        ];
    }

    private function assignCompetitionPositions(Collection $items, string $valueKey): array
    {
        $sorted = $items->sortByDesc($valueKey)->values();
        $positions = [];
        $position = 0;
        $lastValue = null;

        foreach ($sorted as $index => $item) {
            $value = $item[$valueKey];

            if ($lastValue === null || $value != $lastValue) {
                $position = $index + 1;
                $lastValue = $value;
            }

            $positions[$item['student_id']] = $position;
        }

        return $positions;
    }
}
