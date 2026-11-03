@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-assessment-records";
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('teacher.partials._assessments-hub-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / Assessment Records</span>
            </div>
        </div>
        <div class="ah-hero-actions">
            <a href="{{ route('teacher-assessments', $periodQuery) }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
                <i class="ri-add-large-line"></i> New Assessment
            </a>
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:56px;height:56px;font-size:24px;background:rgba(14,165,233,.1);color:#0369a1;"><i class="ri-archive-line"></i></span>
            <div>
                <h5 class="fw-semibold mb-8">Assessment Records</h5>
                <p class="text-sm text-secondary-light mb-0">Completed assessments with marks entered. These are kept here so the main Assessments page stays focused on new entries.</p>
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
                        <p class="text-secondary-light text-sm mb-4">Scored Assessments</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-archive-line"></i></span>
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
                        <p class="text-secondary-light text-sm mb-4">Student Scores</p>
                        <h4 class="fw-semibold mb-0 text-primary-600">{{ $stats['scores_entered'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-edit-box-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Awaiting Scores</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['pending'] ?? 0 }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-draft-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ah-workspace-grid">
        <div class="ah-panel">
            <div class="ah-panel-head">
                <h6 class="text-lg fw-semibold mb-4">Homeroom Classes</h6>
                <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> Scored records by class</span>
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
                        <a href="{{ route('teacher-class-assessment-records', array_merge(['class' => $class], $periodQuery)) }}" class="ac-action-pill ac-action-pill-teal">
                            <i class="ri-arrow-right-line"></i> Records
                        </a>
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
                <h6 class="text-lg fw-semibold mb-4">Subject Assignments</h6>
                <span class="ac-pill ac-pill-indigo"><i class="ri-book-open-line"></i> Scored records by subject</span>
            </div>
            <div class="ah-panel-body">
                @forelse($subjectAssignments as $assignment)
                <div class="ah-slot-card">
                    <div class="ah-slot-top">
                        <div>
                            <span class="fw-semibold d-block">{{ $assignment->course?->name }}</span>
                            <span class="ac-pill ac-pill-indigo"><i class="ri-group-line"></i> {{ $assignment->schoolClass?->name }}</span>
                        </div>
                        <a href="{{ route('teacher-course-assessment-records', array_merge(['course' => $assignment->course_id, 'class' => $assignment->school_class_id], $periodQuery)) }}" class="ac-action-pill ac-action-pill-indigo">
                            <i class="ri-arrow-right-line"></i> Records
                        </a>
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
                <h6 class="text-lg fw-semibold mb-4">Scored Assessments</h6>
                <p class="text-sm text-secondary-light mb-0">Assessments that already have marks entered for {{ $period['term_name'] ?? 'the selected term' }}.</p>
            </div>
            @if(($stats['pending'] ?? 0) > 0)
                <a href="{{ route('teacher-assessments', $periodQuery) }}" class="ac-action-pill ac-action-pill-teal">
                    <i class="ri-arrow-left-line"></i> {{ $stats['pending'] }} awaiting scores
                </a>
            @endif
        </div>

        @include('teacher.partials._assessments-hub-list', [
            'assessments' => $assessments,
            'assessmentTypes' => $assessmentTypes,
            'mode' => 'records',
            'stats' => $stats,
        ])
    </div>
</div>
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
