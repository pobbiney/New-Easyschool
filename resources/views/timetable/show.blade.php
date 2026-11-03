@php
    $pageName = "timetable";
    $subpageName = "timetable";
    $printQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ]);
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .tt-grid-wrap { overflow-x: auto; }
    .tt-grid { min-width: 860px; margin: 0; }
    .tt-grid th, .tt-grid td { vertical-align: middle; }
    .tt-time {
        min-width: 150px;
        white-space: nowrap;
        background: #f8fafc;
        font-weight: 600;
    }
    .tt-time small { display: block; font-weight: 500; color: #64748b; }
    .tt-cell { min-width: 140px; }
    .tt-subject { font-weight: 700; display: block; font-size: 13px; }
    .tt-teacher { display: block; font-size: 11px; color: #64748b; margin-top: 2px; }
    .tt-kind {
        text-align: center;
        font-weight: 700;
        font-size: 12px;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
        background: #f8fafc;
    }
    .tt-kind.is-break { background: #fffbeb; color: #b45309; }
    .tt-kind.is-assembly { background: #eef2ff; color: #4338ca; }
    .tt-empty { text-align: center; padding: 48px 16px; color: #64748b; }
    .tt-empty i { font-size: 34px; color: #25A194; display: block; margin-bottom: 8px; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Timetable',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Class Timetables', 'url' => route('timetable')],
            ['label' => $class->name, 'url' => null, 'active' => true],
        ],
        'title' => $class->name.' timetable',
        'subtitle' => ($class->category?->name ?: 'Class').($class->classTeacher ? ' · '.$class->classTeacher->full_name : ''),
        'actions' => view('timetable.partials._show-actions', compact('class', 'period', 'printQuery'))->render(),
    ])

    <div class="card ac-list-wrapper mb-24">
        <div class="card-header py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-12">
            <h6 class="mb-0 fw-semibold">Weekly grid</h6>
            @include('teacher.partials._academic-period-filter', ['periodFilterAction' => route('timetable-show', $class)])
        </div>

        @if(! $timetable)
            <div class="tt-empty">
                <i class="ri-calendar-schedule-line"></i>
                No timetable for {{ $class->name }} in the selected year and term.
                <div class="mt-16">
                    <a class="btn btn-primary-600" href="{{ route('timetable-periods', array_filter(['school_class_id' => $class->id, 'day' => 1, 'academic_year_id' => $period['year_id'], 'academic_term_id' => $period['term_id']])) }}">
                        <i class="ri-time-line"></i> Set {{ $class->name }} Monday to Friday
                    </a>
                </div>
            </div>
        @else
            <div class="tt-grid-wrap">
                <table class="table bordered-table mb-0 tt-grid">
                    <thead>
                        <tr>
                            <th>Time</th>
                            @foreach($days as $dayName)
                                <th>{{ $dayName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $slot)
                            <tr>
                                <td class="tt-time">
                                    {{ $slot->label }}
                                    <small>{{ $slot->timeLabel() }}</small>
                                </td>
                                @foreach($days as $day => $dayName)
                                    @if($slot->kind !== 'lesson')
                                        <td class="tt-kind is-{{ $slot->kind }}">{{ $slot->label }}</td>
                                    @else
                                        @php $entry = $grid[$day][$slot->id] ?? null; @endphp
                                        <td class="tt-cell">
                                            @if($entry?->course)
                                                <span class="ac-pill ac-pill-{{ $themes[$entry->course_id] ?? 'teal' }}">{{ $entry->course->name }}</span>
                                                @if($entry->teacher)
                                                    <span class="tt-teacher">{{ $entry->teacher->full_name }}</span>
                                                @endif
                                            @else
                                                <span class="text-secondary-light">—</span>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
