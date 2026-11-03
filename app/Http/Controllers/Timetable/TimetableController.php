<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\ClassTimetable;
use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\TimetablePeriod;
use App\Services\Timetable\BellScheduleBuilder;
use App\Services\Timetable\GesTimetableGenerator;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use RuntimeException;

class TimetableController extends Controller
{
    public function __construct(
        private GesTimetableGenerator $generator,
        private BellScheduleBuilder $bell,
    ) {}

    public function index(Request $request)
    {
        $context = $this->periodContext($request);
        $classes = SchoolClass::query()
            ->with(['category', 'classTeacher'])
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $timetables = ClassTimetable::query()
            ->where('academic_year_id', $context['year_id'])
            ->where('academic_term_id', $context['term_id'])
            ->get()
            ->keyBy('school_class_id');

        return view('timetable.index', [
            'classes' => $classes,
            'timetables' => $timetables,
            'period' => $context['period'],
            'academicYears' => $context['years'],
            'academicTerms' => $context['terms'],
            'generatedCount' => $timetables->count(),
        ]);
    }

    public function show(Request $request, SchoolClass $class)
    {
        $context = $this->periodContext($request);
        $periods = TimetablePeriod::query()->orderBy('sort_order')->orderBy('id')->get();
        $timetable = $this->generator->findTimetable($class, $context['year_id'], $context['term_id']);
        $grid = $this->grid($periods, $timetable);

        return view('timetable.show', [
            'class' => $class->load(['category', 'classTeacher']),
            'timetable' => $timetable,
            'periods' => $periods,
            'grid' => $grid,
            'days' => GesTimetableGenerator::DAYS,
            'period' => $context['period'],
            'academicYears' => $context['years'],
            'academicTerms' => $context['terms'],
            'themes' => $this->subjectThemes($grid),
        ]);
    }

    public function print(Request $request, SchoolClass $class)
    {
        $context = $this->periodContext($request);
        $periods = TimetablePeriod::query()->orderBy('sort_order')->orderBy('id')->get();
        $timetable = $this->generator->findTimetable($class, $context['year_id'], $context['term_id']);
        $school = SchoolSetting::current();

        return view('timetable.print', [
            'class' => $class->load(['category', 'classTeacher']),
            'timetable' => $timetable,
            'periods' => $periods,
            'grid' => $this->grid($periods, $timetable),
            'days' => GesTimetableGenerator::DAYS,
            'period' => $context['period'],
            'school' => $school,
            'logoUrl' => $school->logoUrl(),
            'themes' => $this->subjectThemes($this->grid($periods, $timetable)),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'nullable|exists:school_classes,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
        ]);

        return redirect()->route('timetable-periods', array_filter([
            'school_class_id' => $validated['school_class_id'] ?? null,
            'day' => 1,
            'academic_year_id' => $validated['academic_year_id'] ?? AcademicPeriodDefaults::yearId($request),
            'academic_term_id' => $validated['academic_term_id'] ?? AcademicPeriodDefaults::termId($request),
        ]))->with('message_success', 'Pick a class and weekday, then save subjects for that day.');
    }

    public function periods(Request $request)
    {
        $context = $this->periodContext($request);
        $classes = SchoolClass::query()
            ->with(['category', 'classTeacher'])
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $class = $this->resolveClass($request, $classes);
        $day = $this->resolveDay($request);
        $periods = TimetablePeriod::query()->orderBy('sort_order')->orderBy('id')->get();
        $timetable = $class
            ? $this->generator->findTimetable($class, $context['year_id'], $context['term_id'])
            : null;
        $courseByPeriodId = $timetable ? $this->generator->courseIdsForDay($timetable, $day) : [];

        return view('timetable.periods', [
            'classes' => $classes,
            'class' => $class,
            'day' => $day,
            'days' => GesTimetableGenerator::DAYS,
            'periods' => $periods,
            'slots' => $this->bell->editorSlots($periods, $courseByPeriodId),
            'dayStart' => $this->bell->dayStart($periods),
            'courses' => $class
                ? $this->registeredCourses($context['year_id'], $context['term_id'], $class->id)
                : collect(),
            'savedDays' => $this->savedDays($timetable),
            'period' => $context['period'],
            'academicYears' => $context['years'],
            'academicTerms' => $context['terms'],
        ]);
    }

    public function updatePeriods(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'day' => 'required|integer|in:1,2,3,4,5',
            'apply_days' => 'nullable|array',
            'apply_days.*' => 'integer|in:1,2,3,4,5',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'slots' => 'required|array|min:1',
            'slots.*.id' => 'nullable|integer',
            'slots.*.kind' => 'required|in:lesson,break,assembly',
            'slots.*.label' => 'nullable|string|max:80',
            'slots.*.duration_minutes' => 'required|integer|min:5|max:90',
            'slots.*.course_id' => 'nullable|exists:courses,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
        ]);

        $class = SchoolClass::query()->where('status', 'Active')->findOrFail($validated['school_class_id']);
        $yearId = isset($validated['academic_year_id'])
            ? (int) $validated['academic_year_id']
            : AcademicPeriodDefaults::yearId($request);
        $termId = isset($validated['academic_term_id'])
            ? (int) $validated['academic_term_id']
            : AcademicPeriodDefaults::termId($request);
        $days = array_values(array_unique(array_merge(
            [(int) $validated['day']],
            array_map('intval', $validated['apply_days'] ?? [])
        )));

        try {
            $incoming = array_values($validated['slots']);
            $periods = $this->bell->saveDay(substr($validated['start_time'], 0, 5), $incoming);
            $courseByPeriodId = [];
            foreach ($periods->values() as $index => $period) {
                if (! $period->isLesson()) {
                    continue;
                }
                $courseId = $incoming[$index]['course_id'] ?? null;
                $courseByPeriodId[(int) $period->id] = $courseId ? (int) $courseId : null;
            }
            $this->generator->saveClassDays($class, $yearId, $termId, $days, $periods, $courseByPeriodId);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('message_error', $exception->getMessage());
        }

        $dayNames = collect($days)
            ->map(fn ($day) => GesTimetableGenerator::DAYS[$day] ?? '')
            ->filter()
            ->implode(', ');

        return redirect()->route('timetable-periods', array_filter([
            'school_class_id' => $class->id,
            'day' => $validated['day'],
            'academic_year_id' => $yearId,
            'academic_term_id' => $termId,
        ]))->with('message_success', $class->name.' saved for '.$dayNames.'.');
    }

    /**
     * @return array<int, array<int, \App\Models\ClassTimetableEntry|null>>
     */
    private function grid($periods, ?ClassTimetable $timetable): array
    {
        $grid = [];
        $lookup = [];

        if ($timetable) {
            foreach ($timetable->entries as $entry) {
                $lookup[$entry->day][$entry->timetable_period_id] = $entry;
            }
        }

        foreach (array_keys(GesTimetableGenerator::DAYS) as $day) {
            foreach ($periods as $period) {
                $grid[$day][$period->id] = $lookup[$day][$period->id] ?? null;
            }
        }

        return $grid;
    }

    /**
     * @param  array<int, array<int, \App\Models\ClassTimetableEntry|null>>  $grid
     * @return array<int, string>
     */
    private function subjectThemes(array $grid): array
    {
        $themes = ['teal', 'indigo', 'amber', 'sky', 'violet', 'rose', 'emerald', 'orange', 'slate'];
        $map = [];
        $i = 0;

        foreach ($grid as $daySlots) {
            foreach ($daySlots as $entry) {
                $courseId = $entry?->course_id;
                if (! $courseId || isset($map[$courseId])) {
                    continue;
                }
                $map[$courseId] = $themes[$i % count($themes)];
                $i++;
            }
        }

        return $map;
    }

    /**
     * @return array{year_id: int|null, term_id: int|null, period: array, years: \Illuminate\Support\Collection, terms: \Illuminate\Support\Collection}
     */
    private function periodContext(Request $request): array
    {
        return [
            'year_id' => AcademicPeriodDefaults::yearId($request),
            'term_id' => AcademicPeriodDefaults::termId($request),
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'years' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'terms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    private function registeredCourses(?int $yearId, ?int $termId, ?int $classId = null)
    {
        $ids = CourseRegistration::query()
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->when($termId, fn ($query) => $query->where('academic_term_id', $termId))
            ->pluck('course_id')
            ->unique()
            ->filter();

        return Course::query()
            ->whereIn('id', $ids)
            ->where('status', 'Active')
            ->where(function ($query) {
                $query->whereNotNull('parent_id')
                    ->orWhereDoesntHave('subCourses', function ($sub) {
                        $sub->where('status', 'Active');
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function resolveClass(Request $request, $classes): ?SchoolClass
    {
        if ($classes->isEmpty()) {
            return null;
        }

        $id = (int) $request->query('school_class_id', 0);

        return $id > 0
            ? ($classes->firstWhere('id', $id) ?: $classes->first())
            : $classes->first();
    }

    private function resolveDay(Request $request): int
    {
        $day = (int) $request->query('day', 1);

        return isset(GesTimetableGenerator::DAYS[$day]) ? $day : 1;
    }

    /**
     * @return array<int, true>
     */
    private function savedDays(?ClassTimetable $timetable): array
    {
        if (! $timetable) {
            return [];
        }

        return $timetable->entries
            ->whereNotNull('course_id')
            ->pluck('day')
            ->unique()
            ->mapWithKeys(fn ($day) => [(int) $day => true])
            ->all();
    }
}
