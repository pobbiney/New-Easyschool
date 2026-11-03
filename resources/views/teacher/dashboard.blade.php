@php $pageName = "teacher-portal"; $subpageName = "teacher-dashboard"; @endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Teacher Home</span>
            </div>
        </div>
        @if($period['year_name'])
        <span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> {{ $period['year_name'] }} · {{ $period['term_name'] }}</span>
        @endif
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-book-open-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Welcome, {{ $staff?->full_name }}</h5>
            <p class="text-sm text-secondary-light mb-0">Your homeroom classes, subject assignments, attendance, assessments, and gradebook in one place.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Homeroom Classes</p>
                        <h4 class="fw-semibold mb-0 text-primary-600">{{ $stats['homeroom_classes'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-home-smile-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Subject Slots</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $stats['subject_slots'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Present Today</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['present_today'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-user-follow-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <div class="col-lg-6">
            <div class="card ac-list-wrapper h-100">
                <div class="card-header border-bottom bg-base py-16 px-24">
                    <h6 class="text-lg fw-semibold mb-4">My Homeroom Classes</h6>
                    <span class="ac-pill ac-pill-teal">Class teacher</span>
                </div>
                <div class="card-body p-20">
                    @forelse($homeroomClasses as $class)
                    @php $initials = strtoupper(substr($class->name, 0, 2)); @endphp
                    <div class="ac-workspace-card mb-12">
                        <div class="card-top">
                            <div class="ac-name-cell">
                                <span class="ac-avatar">{{ $initials }}</span>
                                <div>
                                    <span class="d-block fw-semibold text-primary-600">{{ $class->name }}</span>
                                    <span class="ac-pill ac-pill-teal">Homeroom</span>
                                </div>
                            </div>
                        </div>
                        <div class="ac-action-pills">
                            <a href="{{ route('teacher-class-workspace', $class) }}" class="ac-action-pill ac-action-pill-teal"><i class="ri-group-line"></i> Roster</a>
                            <a href="{{ route('teacher-class-attendance', $class) }}" class="ac-action-pill ac-action-pill-amber"><i class="ri-calendar-check-line"></i> Attendance</a>
                            <a href="{{ route('teacher-class-assessments', $class) }}" class="ac-action-pill ac-action-pill-rose"><i class="ri-file-list-3-line"></i> Assessments</a>
                            <a href="{{ route('teacher-class-gradebook', $class) }}" class="ac-action-pill ac-action-pill-emerald"><i class="ri-bar-chart-box-line"></i> Gradebook</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-secondary-light mb-0">No homeroom class assigned yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ac-list-wrapper h-100">
                <div class="card-header border-bottom bg-base py-16 px-24">
                    <h6 class="text-lg fw-semibold mb-4">My Subject Assignments</h6>
                    <span class="ac-pill ac-pill-indigo">Course teacher</span>
                </div>
                <div class="card-body p-20">
                    @forelse($subjectAssignments as $assignment)
                    <div class="ac-workspace-card mb-12">
                        <div class="card-top">
                            <div>
                                <span class="d-block fw-semibold">{{ $assignment->course?->name }}</span>
                                <span class="ac-pill ac-pill-indigo"><i class="ri-book-2-line"></i> {{ $assignment->schoolClass?->name }}</span>
                            </div>
                        </div>
                        <div class="ac-action-pills">
                            <a href="{{ route('teacher-course-workspace', [$assignment->course_id, $assignment->school_class_id]) }}" class="ac-action-pill ac-action-pill-indigo"><i class="ri-group-line"></i> Roster</a>
                            <a href="{{ route('teacher-course-assessments', [$assignment->course_id, $assignment->school_class_id]) }}" class="ac-action-pill ac-action-pill-rose"><i class="ri-file-list-3-line"></i> Assessments</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-secondary-light mb-0">No subject assignments for this term.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
