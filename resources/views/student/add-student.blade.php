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

    <p class="text-secondary-light mb-16">Register one student with the form below, or bulk upload many records from Excel.</p>

    @if(session('message_success'))
        <div class="alert alert-success radius-8 mb-16">{{ session('message_success') }}</div>
    @endif
    @if(session('message_error'))
        <div class="alert alert-danger radius-8 mb-16">{{ session('message_error') }}</div>
    @endif

    @include('student.partials._bulk-import', [
        'academicYears' => $academicYears,
        'academicTerms' => $academicTerms,
    ])

    <div class="d-flex align-items-center gap-12 mb-16">
        <span class="text-sm fw-semibold text-secondary-light text-uppercase">Or register one student</span>
        <span class="flex-grow-1 border-top"></span>
    </div>

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
