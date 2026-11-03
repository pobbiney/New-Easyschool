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
                <span class="text-secondary-light"> / {{ $course->name }} / {{ $schoolClass->name }} / Marks</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('teacher-class-assessment-marks', array_merge(['class' => $schoolClass], $periodQuery)) }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-list-check-2"></i> All subjects
            </a>
            <a href="{{ route('teacher-course-assessments', array_merge(['course' => $course, 'class' => $schoolClass], $periodQuery)) }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
                <i class="ri-file-list-3-line"></i> Assessments
            </a>
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#1a7a70;"><i class="ri-percent-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Set assessment marks</h5>
            <p class="text-sm text-secondary-light mb-8">
                Enter how many marks each assessment type is worth for <strong>{{ $course->name }}</strong> in <strong>{{ $schoolClass->name }}</strong>
                @if(!empty($period['year_name']) && !empty($period['term_name']))
                    during <strong>{{ $period['year_name'] }} · {{ $period['term_name'] }}</strong>
                @endif.
                You must set marks before creating an assessment of that type.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <span class="ac-pill ac-pill-indigo">{{ $course->name }}</span>
                <span class="ac-pill ac-pill-teal">{{ $schoolClass->name }}</span>
                @if($schoolClass->category)
                    <span class="ac-pill ac-pill-slate">{{ $schoolClass->category->name }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Marks for this subject</h6>
        </div>
        <div class="card-body p-24">
            @if($rows->isEmpty())
                <div class="text-center py-40 text-secondary-light">
                    No assessment types are set up for this class category.
                    Ask an administrator to add them under Settings → Assessment Types.
                </div>
            @else
                <form method="POST" action="{{ route('teacher-course-assessment-marks-process', array_merge(['course' => $course, 'class' => $schoolClass], $periodQuery)) }}">
                    @csrf
                    @if(!empty($period['year_id']))
                        <input type="hidden" name="academic_year_id" value="{{ $period['year_id'] }}">
                    @endif
                    @if(!empty($period['term_id']))
                        <input type="hidden" name="academic_term_id" value="{{ $period['term_id'] }}">
                    @endif
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Bucket</th>
                                    <th>Max this term</th>
                                    <th>Created</th>
                                    <th style="width:180px;">Total marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $index => $row)
                                    @php $type = $row['type']; @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $type->name }}</td>
                                        <td><span class="ac-pill ac-pill-slate">{{ $type->categoryLabel() }}</span></td>
                                        <td>{{ $type->max_number }}</td>
                                        <td>{{ $row['used_count'] }}</td>
                                        <td>
                                            <input type="hidden" name="marks[{{ $index }}][assessment_type_id]" value="{{ $type->id }}">
                                            <input type="number" name="marks[{{ $index }}][total_score]" class="form-control"
                                                min="1" max="9999" step="0.01"
                                                value="{{ old('marks.'.$index.'.total_score', $row['total_score'] !== null ? (0 + $row['total_score']) : '') }}"
                                                placeholder="e.g. 20">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-20">
                        <button type="submit" class="btn btn-primary-600">Save marks</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
