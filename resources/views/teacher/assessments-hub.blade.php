@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-assessments";
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $typePillClass = fn (?string $type) => 'ac-pill-' . ($type ?: 'slate');
    $typeIcon = fn (?string $type) => match ($type) {
        'homework' => 'ri-booklet-line',
        'class_test' => 'ri-file-edit-line',
        'exam' => 'ri-file-shield-2-line',
        'class_assignment' => 'ri-task-line',
        default => 'ri-file-list-3-line',
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('teacher.partials._assessments-hub-styles')
<style>
    .ah-delete-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin: 0 20px 20px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid rgba(245, 158, 11, 0.25);
        background: rgba(245, 158, 11, 0.08);
        color: #92400e;
    }

    .ah-delete-notice i {
        font-size: 20px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .ah-delete-notice-title {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #78350f;
    }

    .ah-delete-notice-text {
        font-size: 12px;
        line-height: 1.55;
        margin: 0;
        color: #92400e;
    }

    .ac-action-pill.is-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / Assessments</span>
            </div>
        </div>
        <div class="ah-hero-actions">
            @if(($stats['records'] ?? 0) > 0)
                <a href="{{ route('teacher-assessment-records', $periodQuery) }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                    <i class="ri-archive-line"></i> Assessment Records ({{ $stats['records'] }})
                </a>
            @endif
            <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal" data-bs-target="#createAssessmentModal">
                <i class="ri-add-large-line"></i> New Assessment
            </button>
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:56px;height:56px;font-size:24px;background:rgba(244,63,94,.1);color:#be123c;"><i class="ri-file-list-3-line"></i></span>
            <div>
                <h5 class="fw-semibold mb-8">Assessments Hub</h5>
                <p class="text-sm text-secondary-light mb-0">Create new assessments and enter scores for the selected term. Once marks are recorded, assessments move to <a href="{{ route('teacher-assessment-records', $periodQuery) }}" class="text-primary-600 fw-semibold">Assessment Records</a>.</p>
                <div class="ah-type-legend">
                    @foreach($assessmentTypes as $assessmentType)
                        <span class="ac-pill ac-pill-{{ $assessmentType->slug }}">{{ $assessmentType->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Awaiting Scores</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Published</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['published'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Drafts</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['draft'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-draft-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Scored Records</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $stats['records'] ?? 0 }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-archive-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ah-workspace-grid">
        <div class="ah-panel">
            <div class="ah-panel-head">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <h6 class="text-lg fw-semibold mb-4">Homeroom Classes</h6>
                        <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> Class teacher · {{ $stats['homeroom_slots'] }}</span>
                    </div>
                </div>
            </div>
            <div class="ah-panel-body">
                @forelse($homeroomClasses as $class)
                @php $initials = strtoupper(substr($class->name, 0, 2)); @endphp
                <div class="ah-slot-card">
                    <div class="ah-slot-top">
                        <div class="ac-name-cell">
                            <span class="ac-avatar">{{ $initials }}</span>
                            <div>
                                <span class="fw-semibold d-block text-primary-600">{{ $class->name }}</span>
                                <span class="ac-pill ac-pill-teal">Homeroom</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('teacher-class-assessment-marks', array_merge(['class' => $class], $periodQuery)) }}" class="ac-action-pill ac-action-pill-teal">
                                <i class="ri-percent-line"></i> Marks
                            </a>
                            <a href="{{ route('teacher-class-assessments', array_merge(['class' => $class], $periodQuery)) }}" class="ac-action-pill ac-action-pill-teal">
                                <i class="ri-arrow-right-line"></i> Open
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="ah-empty-slot">
                    <i class="ri-home-smile-line"></i>
                    <p class="text-secondary-light mb-0">No homeroom class assigned.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="ah-panel">
            <div class="ah-panel-head">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <h6 class="text-lg fw-semibold mb-4">Subject Assignments</h6>
                        <span class="ac-pill ac-pill-indigo"><i class="ri-book-open-line"></i> Course teacher · {{ $stats['subject_slots'] }}</span>
                    </div>
                </div>
            </div>
            <div class="ah-panel-body">
                @forelse($subjectAssignments as $assignment)
                <div class="ah-slot-card">
                    <div class="ah-slot-top">
                        <div>
                            <span class="fw-semibold d-block">{{ $assignment->course?->name }}</span>
                            <span class="ac-pill ac-pill-indigo"><i class="ri-group-line"></i> {{ $assignment->schoolClass?->name }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('teacher-course-assessment-marks', array_merge(['course' => $assignment->course_id, 'class' => $assignment->school_class_id], $periodQuery)) }}" class="ac-action-pill ac-action-pill-indigo">
                                <i class="ri-percent-line"></i> Marks
                            </a>
                            <a href="{{ route('teacher-course-assessments', array_merge(['course' => $assignment->course_id, 'class' => $assignment->school_class_id], $periodQuery)) }}" class="ac-action-pill ac-action-pill-indigo">
                                <i class="ri-arrow-right-line"></i> Open
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="ah-empty-slot">
                    <i class="ri-book-open-line"></i>
                    <p class="text-secondary-light mb-0">No subject assignments this term.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="ah-panel ah-assessment-panel">
        <div class="ah-panel-head d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Awaiting Score Entry</h6>
                <p class="text-sm text-secondary-light mb-0">Assessments with no marks entered yet for {{ $period['term_name'] ?? 'the selected term' }}.</p>
            </div>
        </div>

        @include('teacher.partials._assessments-hub-list', [
            'assessments' => $assessments,
            'assessmentTypes' => $assessmentTypes,
            'mode' => 'pending',
            'stats' => $stats,
        ])
    </div>
</div>

@include('teacher.partials._create-assessment-modal', [
    'homeroomClasses' => $homeroomClasses,
    'subjectAssignments' => $subjectAssignments,
    'assessmentTypes' => $assessmentTypes,
    'period' => $period,
])
@endsection

@section('scripts')
<script>
(function () {
    const rows = document.querySelectorAll('.ah-assessment-row');
    const pills = document.querySelectorAll('.ah-filter-pill');
    const search = document.getElementById('ahSearch');
    let activeFilter = 'all';

    function applyFilters() {
        const q = (search?.value || '').trim().toLowerCase();
        rows.forEach(row => {
            const typeMatch = activeFilter === 'all' || row.dataset.type === activeFilter;
            const searchMatch = !q || (row.dataset.search || '').includes(q);
            row.style.display = typeMatch && searchMatch ? '' : 'none';
        });
    }

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('is-active'));
            this.classList.add('is-active');
            activeFilter = this.dataset.filter;
            applyFilters();
        });
    });

    search?.addEventListener('input', applyFilters);
})();
</script>
@endsection
