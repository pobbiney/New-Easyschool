@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-assessments";
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">ASSESSMENTS</h1>
            <div>
                <a href="{{ route('teacher-assessments', $periodQuery) }}" class="text-secondary-light hover-text-primary hover-underline">Assessments</a>
                <span class="text-secondary-light"> / {{ $schoolClass->name }} / Marks</span>
            </div>
        </div>
        <a href="{{ route('teacher-class-assessments', array_merge(['class' => $schoolClass], $periodQuery)) }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <i class="ri-file-list-3-line"></i> Assessments
        </a>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#1a7a70;"><i class="ri-percent-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Assessment marks — {{ $schoolClass->name }}</h5>
            <p class="text-sm text-secondary-light mb-8">
                Choose a subject, then set how many marks each assessment type is worth for
                @if(!empty($period['year_name']) && !empty($period['term_name']))
                    <strong>{{ $period['year_name'] }} · {{ $period['term_name'] }}</strong>.
                @else
                    the current term.
                @endif
            </p>
            <div class="d-flex flex-wrap gap-2">
                <span class="ac-pill ac-pill-teal">{{ $schoolClass->name }}</span>
                @if($schoolClass->category)
                    <span class="ac-pill ac-pill-slate">{{ $schoolClass->category->name }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Subjects</h6>
        </div>
        <div class="card-body p-0">
            @if($courseRows->isEmpty())
                <div class="text-center py-56 px-24 text-secondary-light">No subjects available for this class.</div>
            @else
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks set</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courseRows as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['course']->name }}</td>
                                    <td>
                                        @if($row['complete'])
                                            <span class="ac-pill ac-pill-emerald">{{ $row['set_count'] }} / {{ $row['type_count'] }} complete</span>
                                        @else
                                            <span class="ac-pill ac-pill-amber">{{ $row['set_count'] }} / {{ $row['type_count'] }} set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('teacher-course-assessment-marks', array_merge(['course' => $row['course'], 'class' => $schoolClass], $periodQuery)) }}" class="ac-action-pill ac-action-pill-teal">
                                            <i class="ri-edit-2-line"></i> Set marks
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
