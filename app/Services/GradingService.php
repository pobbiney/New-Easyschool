<?php

namespace App\Services;

use App\Models\GradingScheme;

class GradingService
{
    public function letterGradeForPercentage(?float $percentage): ?string
    {
        if ($percentage === null) {
            return null;
        }

        $scheme = GradingScheme::query()
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderByDesc('min_percentage')
            ->first();

        return $scheme?->letter_grade;
    }

    public function remarkForPercentage(?float $percentage): ?string
    {
        if ($percentage === null) {
            return null;
        }

        $scheme = GradingScheme::query()
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderByDesc('min_percentage')
            ->first();

        return $scheme?->remark;
    }

    public function gradeScore(?float $score, float $maxScore): array
    {
        if ($score === null || $maxScore <= 0) {
            return ['percentage' => null, 'letter_grade' => null];
        }

        $percentage = round(($score / $maxScore) * 100, 2);

        return [
            'percentage' => $percentage,
            'letter_grade' => $this->letterGradeForPercentage($percentage),
        ];
    }
}
