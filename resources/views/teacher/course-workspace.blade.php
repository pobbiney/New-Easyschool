@php $pageName = "teacher-portal"; $subpageName = "teacher-dashboard"; @endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">COURSE SETUP</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / {{ $course->name }} / {{ $schoolClass->name }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('teacher-course-assessments', [$course, $schoolClass]) }}" class="ac-action-pill ac-action-pill-rose"><i class="ri-file-list-3-line"></i> Assessments</a>
            <a href="{{ route('teacher-course-assessment-marks', [$course, $schoolClass]) }}" class="ac-action-pill ac-action-pill-teal"><i class="ri-percent-line"></i> Set marks</a>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(99,102,241,.1);color:#4338ca;"><i class="ri-book-2-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">{{ $course->name }} — {{ $schoolClass->name }}</h5>
            <p class="text-sm text-secondary-light mb-8">Students in this class for your assigned subject.</p>
            <div class="d-flex flex-wrap gap-2">
                <span class="ac-pill ac-pill-indigo"><i class="ri-book-open-line"></i> {{ $course->name }}</span>
                <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> {{ $schoolClass->name }}</span>
                <span class="ac-pill ac-pill-violet"><i class="ri-group-line"></i> {{ $stats['headcount'] }} students</span>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Subject Roster</h6>
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
