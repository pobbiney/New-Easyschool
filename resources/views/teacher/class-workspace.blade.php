@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-dashboard";
    $gradePillClass = fn (?string $grade) => match (strtoupper(trim((string) $grade)[0] ?? '')) {
        'A' => 'ac-pill-grade-a', 'B' => 'ac-pill-grade-b', 'C' => 'ac-pill-grade-c',
        'D' => 'ac-pill-grade-d', 'F' => 'ac-pill-grade-f', default => 'ac-pill-slate',
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">CLASS SETUP</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / {{ $schoolClass->name }} / Roster</span>
            </div>
        </div>
        <div class="ac-action-pills">
            @if($isHomeroom)
            <a href="{{ route('teacher-class-assessments', $schoolClass) }}" class="ac-action-pill ac-action-pill-rose"><i class="ri-file-list-3-line"></i> Assessments</a>
            @endif
            <a href="{{ route('teacher-class-assessment-marks', $schoolClass) }}" class="ac-action-pill ac-action-pill-teal"><i class="ri-percent-line"></i> Set marks</a>
            @if($isHomeroom)
            <a href="{{ route('teacher-class-attendance', $schoolClass) }}" class="ac-action-pill ac-action-pill-amber"><i class="ri-calendar-check-line"></i> Attendance</a>
            <a href="{{ route('teacher-class-gradebook', $schoolClass) }}" class="ac-action-pill ac-action-pill-emerald"><i class="ri-bar-chart-box-line"></i> Gradebook</a>
            @endif
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;">{{ strtoupper(substr($schoolClass->name, 0, 2)) }}</span>
        <div>
            <h5 class="fw-semibold mb-6">{{ $schoolClass->name }} — Class Roster</h5>
            <p class="text-sm text-secondary-light mb-8">Active students in this class.</p>
            <div class="d-flex flex-wrap gap-2">
                @if($isHomeroom)<span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> Homeroom teacher</span>@endif
                <span class="ac-pill ac-pill-indigo"><i class="ri-group-line"></i> {{ $stats['headcount'] }} students</span>
                <span class="ac-pill ac-pill-emerald"><i class="ri-user-follow-line"></i> {{ $stats['present_today'] }} present today</span>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div><p class="text-secondary-light text-sm mb-4">Headcount</p><h4 class="fw-semibold mb-0">{{ $stats['headcount'] }}</h4></div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-group-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div><p class="text-secondary-light text-sm mb-4">Present Today</p><h4 class="fw-semibold mb-0 text-success-600">{{ $stats['present_today'] }}</h4></div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-user-follow-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Student Roster</h6>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" placeholder="Search students...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            @if($students->isNotEmpty())
            <div class="ac-list-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable">
                    <thead>
                        <tr><th>#</th><th>Student</th><th>Student ID</th><th>Gender</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php $initials = strtoupper(substr($student->firstname ?? '', 0, 1) . substr($student->surname ?? '', 0, 1)); @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="ac-name-cell">
                                    <span class="ac-avatar">
                                        @if($student->picture)<img src="{{ asset($student->picture) }}" alt="">@else{{ $initials }}@endif
                                    </span>
                                    <span class="fw-semibold">{{ $student->full_name }}</span>
                                </div>
                            </td>
                            <td><span class="ac-pill ac-pill-slate">{{ $student->student_id }}</span></td>
                            <td><span class="ac-pill ac-pill-sky">{{ $student->gender }}</span></td>
                            <td><span class="ac-pill ac-pill-active">{{ $student->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-56 px-24 text-secondary-light">No active students in this class.</div>
            @endif
        </div>
    </div>
</div>
@endsection
