<?php

namespace App\Services\Timetable;

use App\Models\ClassTimetable;
use App\Models\ClassTimetableEntry;
use App\Models\CourseRegistration;
use App\Models\CourseTeachingAssignment;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GesTimetableGenerator
{
    public const DAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];

    /**
     * Save subjects for one class on one or more weekdays. Other days are left as they are.
     *
     * @param  list<int>  $days
     * @param  array<int, int|null>  $courseByPeriodId
     */
    public function saveClassDays(
        SchoolClass $class,
        ?int $yearId,
        ?int $termId,
        array $days,
        Collection $periods,
        array $courseByPeriodId,
    ): ClassTimetable {
        $days = array_values(array_unique(array_map('intval', $days)));
        $days = array_values(array_filter($days, fn ($day) => isset(self::DAYS[$day])));

        if ($days === []) {
            throw new RuntimeException('Choose Monday to Friday for this class.');
        }

        $lessons = $periods->where('kind', 'lesson')->values();
        if ($lessons->isEmpty()) {
            throw new RuntimeException('Add lesson periods before saving this class.');
        }

        $offered = $this->registeredCourseIds($class, $yearId, $termId);
        $teachers = $this->teachersForClass($class, $yearId, $termId);

        return DB::transaction(function () use ($class, $yearId, $termId, $days, $lessons, $courseByPeriodId, $offered, $teachers) {
            $timetable = ClassTimetable::query()->firstOrNew([
                'school_class_id' => $class->id,
                'academic_year_id' => $yearId,
                'academic_term_id' => $termId,
            ]);
            $timetable->status = 'saved';
            $timetable->generated_at = now();
            $timetable->generated_by = Auth::id();
            $timetable->save();

            ClassTimetableEntry::query()
                ->where('class_timetable_id', $timetable->id)
                ->whereIn('day', $days)
                ->delete();

            $busy = $this->teacherBusyMap($yearId, $termId, $class->id);

            foreach ($days as $day) {
                foreach ($lessons as $period) {
                    $courseId = $courseByPeriodId[(int) $period->id] ?? null;
                    $courseId = $courseId ? (int) $courseId : null;
                    if ($courseId && ! isset($offered[$courseId])) {
                        $courseId = null;
                    }

                    $staffId = $courseId ? ($teachers[$courseId] ?? null) : null;
                    if ($staffId && isset($busy[$this->busyKey($staffId, $day, (int) $period->id)])) {
                        $staffId = null;
                    }
                    if ($staffId) {
                        $busy[$this->busyKey($staffId, $day, (int) $period->id)] = true;
                    }

                    ClassTimetableEntry::create([
                        'class_timetable_id' => $timetable->id,
                        'day' => $day,
                        'timetable_period_id' => (int) $period->id,
                        'course_id' => $courseId,
                        'staff_id' => $staffId,
                    ]);
                }
            }

            return $timetable->fresh(['entries.course', 'entries.teacher', 'entries.period']);
        });
    }

    /**
     * @return array<int, int|null>
     */
    public function courseIdsForDay(ClassTimetable $timetable, int $day): array
    {
        return $timetable->entries
            ->where('day', $day)
            ->filter(fn ($entry) => $entry->timetable_period_id && $entry->course_id)
            ->mapWithKeys(fn ($entry) => [(int) $entry->timetable_period_id => (int) $entry->course_id])
            ->all();
    }

    public function findTimetable(SchoolClass $class, ?int $yearId, ?int $termId): ?ClassTimetable
    {
        return ClassTimetable::query()
            ->with(['entries.course', 'entries.teacher', 'entries.period', 'academicYear', 'academicTerm'])
            ->where('school_class_id', $class->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->first();
    }

    /**
     * @return array<int, true>
     */
    private function registeredCourseIds(SchoolClass $class, ?int $yearId, ?int $termId): array
    {
        return CourseRegistration::query()
            ->where('school_class_id', $class->id)
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->when($termId, fn ($query) => $query->where('academic_term_id', $termId))
            ->pluck('course_id')
            ->filter()
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * @return array<int, int|null>
     */
    private function teachersForClass(SchoolClass $class, ?int $yearId, ?int $termId): array
    {
        return CourseTeachingAssignment::query()
            ->where('school_class_id', $class->id)
            ->when($yearId, function ($query) use ($yearId) {
                $query->where(function ($inner) use ($yearId) {
                    $inner->where('academic_year_id', $yearId)->orWhereNull('academic_year_id');
                });
            })
            ->when($termId, function ($query) use ($termId) {
                $query->where(function ($inner) use ($termId) {
                    $inner->where('academic_term_id', $termId)->orWhereNull('academic_term_id');
                });
            })
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->course_id => $row->staff_id ? (int) $row->staff_id : null])
            ->all();
    }

    /**
     * @return array<string, true>
     */
    private function teacherBusyMap(?int $yearId, ?int $termId, int $exceptClassId): array
    {
        $busy = [];

        $entries = ClassTimetableEntry::query()
            ->whereHas('timetable', function ($query) use ($yearId, $termId, $exceptClassId) {
                $query->where('school_class_id', '!=', $exceptClassId)
                    ->where('academic_year_id', $yearId)
                    ->where('academic_term_id', $termId);
            })
            ->whereNotNull('staff_id')
            ->get(['day', 'timetable_period_id', 'staff_id']);

        foreach ($entries as $entry) {
            $busy[$this->busyKey((int) $entry->staff_id, (int) $entry->day, (int) $entry->timetable_period_id)] = true;
        }

        return $busy;
    }

    private function busyKey(int $staffId, int $day, int $periodId): string
    {
        return $staffId.'-'.$day.'-'.$periodId;
    }
}
