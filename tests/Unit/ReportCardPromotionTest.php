<?php

namespace Tests\Unit;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\GradebookService;
use App\Services\GradingService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ReportCardPromotionTest extends TestCase
{
    private function promotion(int $termOrder, array $scores, ?int $minimum = 200): array
    {
        $nextClass = new SchoolClass;
        $nextClass->name = 'GRADE 8';
        $class = $this->getMockBuilder(SchoolClass::class)->onlyMethods(['nextActiveClass'])->getMock();
        $class->promotion_minimum_mark = $minimum;
        $class->method('nextActiveClass')->willReturn($nextClass);
        $student = new Student;
        $student->setRelation('schoolClass', $class);

        return (new ReflectionMethod(GradebookService::class, 'resolvePromotion'))->invoke(
            new GradebookService(new GradingService),
            $student,
            new Collection(array_map(fn ($score) => ['total_score' => $score], $scores)),
            $termOrder,
        );
    }

    public function test_first_and_second_terms_do_not_show_promotion(): void
    {
        foreach ([1, 2] as $term) {
            $result = $this->promotion($term, [90, 90, 90]);
            $this->assertNull($result['is_promoted']);
            $this->assertNull($result['promotion_label']);
        }
    }

    public function test_third_term_sums_subject_totals_and_accepts_the_minimum(): void
    {
        foreach ([[90, 90], [100, 99]] as $scores) {
            $result = $this->promotion(3, $scores);
            $this->assertFalse($result['is_promoted']);
            $this->assertSame('Repeat', $result['promotion_label']);
        }

        $result = $this->promotion(3, [100, 100, 1]);
        $this->assertSame(201, $result['aggregate_total_score']);
        $this->assertTrue($result['is_promoted']);
        $this->assertSame('GRADE 8', $result['promotion_label']);

        $result = $this->promotion(3, [100, 100]);
        $this->assertSame(200, $result['aggregate_total_score']);
        $this->assertTrue($result['is_promoted']);
        $this->assertSame('GRADE 8', $result['promotion_label']);
    }

    public function test_missing_scores_or_minimum_do_not_qualify(): void
    {
        $this->assertFalse($this->promotion(3, [null])['is_promoted']);
        $this->assertNull($this->promotion(3, [100, 100, 100], null)['is_promoted']);
    }
}
