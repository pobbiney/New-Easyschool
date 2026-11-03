@php
    $pageName = "teacher-portal";
    $scope = $scope ?? 'pending';
    $subpageName = $scope === 'records' ? 'teacher-assessment-records' : 'teacher-assessments';
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $typePillClass = fn (?string $type) => 'ac-pill-' . ($type ?: 'slate');
    $recordsRoute = $course
        ? route('teacher-course-assessment-records', array_merge(['course' => $course, 'class' => $schoolClass], $periodQuery))
        : route('teacher-class-assessment-records', array_merge(['class' => $schoolClass], $periodQuery));
    $pendingRoute = $course
        ? route('teacher-course-assessments', array_merge(['course' => $course, 'class' => $schoolClass], $periodQuery))
        : route('teacher-class-assessments', array_merge(['class' => $schoolClass], $periodQuery));
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .ah-scope-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
    .ah-scope-tab {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .ah-scope-tab:hover,
    .ah-scope-tab.is-active {
        border-color: rgba(37, 161, 148, 0.35);
        background: rgba(37, 161, 148, 0.08);
        color: #1a7a70;
    }
    .ah-delete-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin: 16px 20px 20px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid rgba(245, 158, 11, 0.25);
        background: rgba(245, 158, 11, 0.08);
        color: #92400e;
    }
    .ah-delete-notice i { font-size: 20px; flex-shrink: 0; margin-top: 1px; }
    .ah-delete-notice-title { font-size: 13px; font-weight: 700; margin-bottom: 4px; color: #78350f; }
    .ah-delete-notice-text { font-size: 12px; line-height: 1.55; margin: 0; color: #92400e; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">ASSESSMENTS</h1>
            <div>
                <a href="{{ route('teacher-assessments', $periodQuery) }}" class="text-secondary-light hover-text-primary hover-underline">Assessments</a>
                @if($scope === 'records')
                    <span class="text-secondary-light"> / </span>
                    <a href="{{ route('teacher-assessment-records', $periodQuery) }}" class="text-secondary-light hover-text-primary hover-underline">Assessment Records</a>
                @endif
                <span class="text-secondary-light"> / @if($course){{ $course->name }} / @endif{{ $schoolClass?->name }}</span>
            </div>
        </div>
        @if($scope === 'pending')
            <div class="d-flex flex-wrap gap-2">
                @if($course)
                    <a href="{{ route('teacher-course-assessment-marks', array_merge(['course' => $course, 'class' => $schoolClass], $periodQuery)) }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                        <i class="ri-percent-line"></i> Set marks
                    </a>
                @else
                    <a href="{{ route('teacher-class-assessment-marks', array_merge(['class' => $schoolClass], $periodQuery)) }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                        <i class="ri-percent-line"></i> Set marks
                    </a>
                @endif
                <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal" data-bs-target="#createAssessmentModal">
                    <i class="ri-add-large-line"></i> New Assessment
                </button>
            </div>
        @else
            <a href="{{ $pendingRoute }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
                <i class="ri-add-large-line"></i> New Assessment
            </a>
        @endif
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(244,63,94,.1);color:#be123c;"><i class="ri-file-list-3-line"></i></span>
            <div>
                <h5 class="fw-semibold mb-6">@if($course){{ $course->name }} — @endif{{ $schoolClass?->name }}</h5>
                <div class="d-flex flex-wrap gap-2">
                    @if($course)<span class="ac-pill ac-pill-indigo">{{ $course->name }}</span>@endif
                    <span class="ac-pill ac-pill-teal">{{ $schoolClass?->name }}</span>
                    <span class="ac-pill ac-pill-rose">{{ $assessments->count() }} {{ $scope === 'records' ? 'record' : 'pending' }}{{ $assessments->count() === 1 ? '' : 's' }}</span>
                </div>
            </div>
        </div>
        <div class="ah-scope-tabs">
            <a href="{{ $pendingRoute }}" class="ah-scope-tab @if($scope === 'pending') is-active @endif">
                Awaiting Scores ({{ $stats['pending'] ?? 0 }})
            </a>
            <a href="{{ $recordsRoute }}" class="ah-scope-tab @if($scope === 'records') is-active @endif">
                Records ({{ $stats['records'] ?? 0 }})
            </a>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex justify-content-between flex-wrap gap-3">
            <h6 class="text-lg fw-semibold mb-0">{{ $scope === 'records' ? 'Scored Assessments' : 'Awaiting Score Entry' }}</h6>
            <form class="navbar-search dt-search m-0">
                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" placeholder="Search...">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
            </form>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($assessments->isNotEmpty())
            <div class="ac-list-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable">
                    <thead>
                        <tr><th>Title</th><th>Term</th><th>Course</th><th>Type</th><th>Max</th><th>Scored</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                        <tr>
                            <td class="fw-semibold">{{ $assessment->title }}</td>
                            <td>
                                <span class="ac-pill ac-pill-indigo">{{ $assessment->academicTerm?->name ?? '—' }}</span>
                                @if($assessment->academicYear?->name)
                                    <span class="d-block text-xs text-secondary-light mt-1">{{ $assessment->academicYear->name }}</span>
                                @endif
                            </td>
                            <td><span class="ac-pill ac-pill-emerald">{{ $assessment->course?->name ?? 'Homeroom' }}</span></td>
                            <td><span class="ac-pill {{ $typePillClass($assessment->type) }}">{{ $assessment->typeLabel() }}</span></td>
                            <td>{{ number_format($assessment->max_score, 0) }}</td>
                            <td><span class="ac-pill ac-pill-violet">{{ $assessment->scores->whereNotNull('score')->count() }} scored</span></td>
                            <td><span class="ac-pill ac-pill-{{ $assessment->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($assessment->status) }}</span></td>
                            <td>
                                <div class="ac-action-pills">
                                    <a href="{{ route('teacher-assessment-scores', $assessment) }}" class="ac-action-pill ac-action-pill-rose">
                                        <i class="ri-edit-2-line"></i> {{ $scope === 'records' ? 'View Scores' : 'Enter Scores' }}
                                    </a>
                                    @if($scope === 'pending')
                                        <form action="{{ route('teacher-assessments-delete', $assessment) }}" method="POST" class="d-inline"
                                            data-confirm-title="Delete this assessment?"
                                            data-confirm="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ac-action-pill ac-action-pill-amber border-0" style="cursor:pointer;font:inherit;">
                                                <i class="ri-delete-bin-line"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($scope === 'pending')
                @include('teacher.partials._assessment-delete-notice', ['assessments' => $assessments])
            @endif
            @else
            <div class="text-center py-56 px-24 text-secondary-light">
                @if($scope === 'records')
                    No scored assessments for this workspace yet.
                @else
                    All assessments here already have marks entered, or none have been created yet.
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@if($scope === 'pending')
@include('teacher.partials._create-assessment-modal', [
    'homeroomClasses' => $homeroomClasses,
    'subjectAssignments' => $subjectAssignments,
    'assessmentTypes' => $assessmentTypes,
    'defaultClassId' => $defaultClassId ?? $schoolClass?->id,
    'defaultCourseId' => $defaultCourseId ?? $course?->id,
    'lockClass' => $lockClass ?? false,
    'lockCourse' => isset($course),
    'schoolClass' => $schoolClass,
    'period' => $period,
])
@endif
@endsection
