@php $pageName = "teacher-portal"; $subpageName = "teacher-attendance"; @endphp
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
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / Attendance</span>
            </div>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(245,158,11,.12);color:#b45309;"><i class="ri-calendar-check-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Attendance</h5>
            <p class="text-sm text-secondary-light mb-0">Daily mark sheets for your homeroom classes. Subject teachers have view-only access elsewhere.</p>
        </div>
    </div>

    <div class="row gy-4">
        @forelse($homeroomClasses as $class)
        @php $initials = strtoupper(substr($class->name, 0, 2)); @endphp
        <div class="col-md-4 col-sm-6">
            <div class="ac-workspace-card">
                <div class="card-top">
                    <div class="ac-name-cell">
                        <span class="ac-avatar">{{ $initials }}</span>
                        <div>
                            <span class="d-block fw-semibold text-primary-600">{{ $class->name }}</span>
                            <span class="ac-pill ac-pill-teal">Homeroom</span>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-secondary-light mb-12">Daily mark sheet for this class.</p>
                <a href="{{ route('teacher-class-attendance', $class) }}" class="ac-action-pill ac-action-pill-amber"><i class="ri-calendar-check-line"></i> Open Mark Sheet</a>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card ac-list-wrapper">
                <div class="card-body text-center py-56 px-24">
                    <span class="ac-pill ac-pill-amber mb-12"><i class="ri-information-line"></i> Homeroom only</span>
                    <p class="text-secondary-light mb-0">You are not assigned as a class teacher. Attendance recording is available to homeroom teachers only.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
