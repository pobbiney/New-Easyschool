<?php

namespace App\Services\Timetable;

use App\Models\ClassTimetableEntry;
use App\Models\TimetablePeriod;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BellScheduleBuilder
{
    /**
     * School-wide bell only. Subjects are stored per class and weekday on timetable entries.
     *
     * @param  list<array{id?: int|null, kind: string, label: string, duration_minutes: int, course_id?: int|null}>  $incoming
     * @return Collection<int, TimetablePeriod>
     */
    public function saveDay(string $startTime, array $incoming): Collection
    {
        if ($incoming === []) {
            throw new RuntimeException('Add at least one period, break, or assembly.');
        }

        $cursor = Carbon::createFromFormat('H:i', substr($startTime, 0, 5));
        $keepIds = [];
        $order = 1;
        $lessonNo = 1;

        DB::transaction(function () use ($incoming, &$cursor, &$keepIds, &$order, &$lessonNo) {
            foreach (array_values($incoming) as $row) {
                $kind = in_array($row['kind'] ?? '', ['lesson', 'break', 'assembly'], true)
                    ? $row['kind']
                    : 'lesson';
                $minutes = $this->clampMinutes($kind, (int) ($row['duration_minutes'] ?? 50));
                $end = $cursor->copy()->addMinutes($minutes);
                $defaultLabel = match ($kind) {
                    'assembly' => 'Assembly & Registration',
                    'break' => 'Break',
                    default => 'Period '.$lessonNo++,
                };
                $payload = [
                    'sort_order' => $order++,
                    'label' => $this->cleanLabel((string) ($row['label'] ?? ''), $defaultLabel),
                    'start_time' => $cursor->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'kind' => $kind,
                    'course_id' => null,
                    'duration_minutes' => $minutes,
                ];

                $id = (int) ($row['id'] ?? 0);
                if ($id > 0) {
                    $period = TimetablePeriod::query()->find($id);
                    if ($period) {
                        $period->fill($payload);
                        $period->save();
                        $keepIds[] = $period->id;
                        $cursor = $end;
                        continue;
                    }
                }

                $period = TimetablePeriod::query()->create($payload);
                $keepIds[] = $period->id;
                $cursor = $end;
            }

            $removed = TimetablePeriod::query()->whereNotIn('id', $keepIds)->pluck('id');
            if ($removed->isNotEmpty()) {
                ClassTimetableEntry::query()->whereIn('timetable_period_id', $removed)->delete();
                TimetablePeriod::query()->whereIn('id', $removed)->delete();
            }
        });

        return TimetablePeriod::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * @param  array<int, int|null>  $courseByPeriodId
     * @return list<array{id: int, kind: string, label: string, duration_minutes: int, course_id: int|null}>
     */
    public function editorSlots(?Collection $periods = null, array $courseByPeriodId = []): array
    {
        $periods = $periods ?: TimetablePeriod::query()->orderBy('sort_order')->orderBy('id')->get();

        return $periods->map(function (TimetablePeriod $period) use ($courseByPeriodId) {
            $courseId = $courseByPeriodId[(int) $period->id] ?? null;

            return [
                'id' => $period->id,
                'kind' => $period->kind,
                'label' => $period->label,
                'duration_minutes' => $period->minutes(),
                'course_id' => $courseId ? (int) $courseId : null,
            ];
        })->values()->all();
    }

    public function dayStart(?Collection $periods = null): string
    {
        $first = ($periods ?: TimetablePeriod::query()->orderBy('sort_order')->orderBy('id')->get())->first();

        return $first ? substr((string) $first->start_time, 0, 5) : '07:30';
    }

    private function clampMinutes(string $kind, int $minutes): int
    {
        if ($kind === 'lesson') {
            return max(20, min(90, $minutes ?: 50));
        }

        return max(5, min(60, $minutes ?: 30));
    }

    private function cleanLabel(string $label, string $fallback): string
    {
        $label = trim($label);

        return $label !== '' ? mb_substr($label, 0, 80) : $fallback;
    }
}
