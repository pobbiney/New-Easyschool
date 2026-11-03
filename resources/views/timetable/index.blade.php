@php
    $pageName = "timetable";
    $subpageName = "timetable";
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .tt-stat {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 18px 20px;
        background: #fff;
        height: 100%;
    }
    .tt-stat .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .tt-empty { text-align: center; padding: 48px 16px; color: #64748b; }
    .tt-empty i { font-size: 34px; color: #25A194; display: block; margin-bottom: 8px; }
    button.ac-action-pill { font: inherit; cursor: pointer; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Timetable',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Class Timetables', 'url' => null, 'active' => true],
        ],
        'title' => 'Class Timetables',
        'subtitle' => 'Open a class, then set Monday to Friday under Period times. Each class and weekday can have different subjects.',
        'actions' => '<a href="'.route('timetable-periods').'" class="btn btn-outline-primary-600"><i class="ri-time-line"></i> Period times</a>',
    ])

    <div class="ac-hero mb-24">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-16">
            <div>
                <h5 class="fw-semibold mb-6">One class, five weekdays</h5>
                <p class="text-sm text-secondary-light mb-0">
                    Period times is where you choose the class, then set Monday, Tuesday, Wednesday, Thursday, and Friday separately.
                </p>
            </div>
            <a href="{{ route('timetable-periods') }}" class="btn btn-primary-600">
                <i class="ri-time-line"></i> Set a class week
            </a>
        </div>
    </div>

    <div class="row g-3 mb-24">
        <div class="col-md-4">
            <div class="tt-stat">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Active classes</div>
                        <h4 class="fw-semibold mb-0">{{ $classes->count() }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-layout-grid-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tt-stat">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Weeks saved</div>
                        <h4 class="fw-semibold mb-0">{{ $generatedCount }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tt-stat">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Not yet set</div>
                        <h4 class="fw-semibold mb-0">{{ max($classes->count() - $generatedCount, 0) }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-time-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-12">
            <h6 class="mb-0 fw-semibold">Classes</h6>
            @include('teacher.partials._academic-period-filter', ['periodFilterAction' => route('timetable')])
        </div>
        <div class="table-responsive">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Category</th>
                        <th>Class teacher</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        @php $tt = $timetables->get($class->id); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $class->name }}</td>
                            <td>{{ $class->category?->name ?: '—' }}</td>
                            <td>{{ $class->classTeacher?->full_name ?: '—' }}</td>
                            <td>
                                @if($tt)
                                    <span class="ac-pill ac-pill-emerald"><i class="ri-checkbox-circle-line"></i> Saved {{ $tt->generated_at?->format('d M H:i') }}</span>
                                @else
                                    <span class="ac-pill ac-pill-amber">Not set</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-8 justify-content-end">
                                    <a class="ac-action-pill ac-action-pill-teal" href="{{ route('timetable-show', array_filter(['class' => $class->id, 'academic_year_id' => $period['year_id'], 'academic_term_id' => $period['term_id']])) }}">
                                        <i class="ri-eye-line"></i> View
                                    </a>
                                    <a class="ac-action-pill ac-action-pill-indigo" href="{{ route('timetable-periods', array_filter(['school_class_id' => $class->id, 'day' => 1, 'academic_year_id' => $period['year_id'], 'academic_term_id' => $period['term_id']])) }}">
                                        <i class="ri-time-line"></i> Set week
                                    </a>
                                    @if($tt)
                                        <a class="ac-action-pill ac-action-pill-amber" target="_blank" href="{{ route('timetable-print', array_filter(['class' => $class->id, 'academic_year_id' => $period['year_id'], 'academic_term_id' => $period['term_id']])) }}">
                                            <i class="ri-printer-line"></i> Print
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="tt-empty"><i class="ri-layout-grid-line"></i>No active classes yet.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
