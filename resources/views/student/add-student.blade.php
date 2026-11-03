@php $pageName = "student"; $subpageName = "add-student"; @endphp

@extends('layouts.app')

@section('css')
@include('student.partials._wizard-styles')
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STUDENT MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('list-students') }}" class="text-secondary-light hover-text-primary hover-underline"> / Student List</a>
                <span class="text-secondary-light"> / Register New Student</span>
            </div>
        </div>
        <a href="{{ route('list-students') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md"><i class="ri-list-check"></i></span>
            View Student List
        </a>
    </div>

    <p class="text-secondary-light mb-0">Fill in the student details and take a live photo during registration, or upload one if the camera is not available.</p>

    @include('student.partials._wizard-form', [
        'formAction' => route('add-student-process'),
        'submitLabel' => 'Register Student',
        'draftSaveUrl' => route('save-student-draft-process'),
        'studentCode' => $studentCode,
        'academicYears' => $academicYears,
        'schoolClasses' => $schoolClasses,
    ])
</div>

@endsection

@section('scripts')
@include('student.partials._wizard-scripts', [
    'draftSaveUrl' => route('save-student-draft-process'),
    'docCounterStart' => 0,
])
@endsection
