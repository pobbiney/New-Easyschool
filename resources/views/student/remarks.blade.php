@php
    $pageName = "student-remarks";
    $subpageName = "add-remarks";
    $selectedClass = $classes->firstWhere('id', $classId);
    $selectedYear = $academicYears->firstWhere('id', $yearId);
    $selectedTerm = $academicTerms->firstWhere('id', $termId);
    $remarkFields = [
        'attitude' => ['Attitude', 255],
        'conduct' => ['Conduct', 255],
        'interest' => ['Interest', 255],
    ];
    $started = false;
    if ($remark) {
        foreach (['attitude', 'conduct', 'interest', 'class_teacher_remark', 'headteacher_signature'] as $field) {
            if (filled($remark->{$field})) {
                $started = true;
                break;
            }
        }
    }
    $nameParts = $student ? (preg_split('/\s+/', trim($student->full_name)) ?: []) : [];
    $initials = strtoupper(mb_substr($nameParts[0] ?? 'S', 0, 1).mb_substr($nameParts[1] ?? '', 0, 1));
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .sr-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.1fr 1.4fr;
        gap: 14px;
        align-items: end;
    }
    .sr-label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .sr-student {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
    }
    .sr-student-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: linear-gradient(180deg, #f8fafc, #fff);
        border-bottom: 1px solid #eef2f7;
    }
    .sr-photo {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, .12);
        color: #1a7a70;
        font-weight: 800;
        flex-shrink: 0;
    }
    .sr-photo img { width: 100%; height: 100%; object-fit: cover; }
    .sr-body { padding: 18px; }
    .sr-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .sr-span-2 { grid-column: span 2; }
    .sr-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #475569;
    }
    .sr-field input {
        width: 100%;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        color: #0f172a;
    }
    .sr-field input:focus {
        outline: none;
        border-color: #25A194;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 161, 148, .15);
    }
    @media (max-width: 991px) {
        .sr-filter-grid,
        .sr-grid { grid-template-columns: 1fr; }
        .sr-span-2 { grid-column: auto; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STUDENT REMARKS</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Add Remarks</span>
            </div>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-chat-quote-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Student remarks</h5>
            <p class="text-sm text-secondary-light mb-0">Choose a class, then one student. The remarks you save print on that student's report card.</p>
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body p-20">
            <form method="GET" action="{{ route('student-remarks') }}" id="sr_filter" class="sr-filter-grid">
                <div>
                    <label class="sr-label" for="sr_year">Academic year</label>
                    <select id="sr_year" name="academic_year_id" class="form-control form-select" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((int) $yearId === (int) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sr-label" for="sr_term">Term</label>
                    <select id="sr_term" name="academic_term_id" class="form-control form-select" required>
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}" @selected((int) $termId === (int) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sr-label" for="sr_class">Class</label>
                    <select id="sr_class" name="school_class_id" class="form-control form-select">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected((int) $classId === (int) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sr-label" for="sr_student">Student</label>
                    <select id="sr_student" name="student_id" class="form-control form-select" @disabled(! $classId)>
                        <option value="">{{ $classId ? 'Select student' : 'Select a class first' }}</option>
                        @foreach($students as $row)
                            <option value="{{ $row->id }}" @selected((int) $studentId === (int) $row->id)>{{ $row->full_name }} · {{ $row->student_id }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if(! $yearId || ! $termId)
        <div class="card ac-list-wrapper">
            <div class="card-body text-center py-56 px-24">
                <span class="ac-avatar mb-12" style="width:52px;height:52px;font-size:22px;"><i class="ri-calendar-line"></i></span>
                <p class="text-secondary-light mb-0">Set the academic year and term under Settings before writing remarks.</p>
            </div>
        </div>
    @elseif(! $classId)
        <div class="card ac-list-wrapper">
            <div class="card-body text-center py-56 px-24">
                <span class="ac-avatar mb-12" style="width:52px;height:52px;font-size:22px;"><i class="ri-group-line"></i></span>
                <p class="text-secondary-light mb-0">Choose a class. The student list will appear next.</p>
            </div>
        </div>
    @elseif($students->isEmpty())
        <div class="card ac-list-wrapper">
            <div class="card-body text-center py-56 px-24">
                <p class="text-secondary-light mb-0">No active students in {{ $selectedClass?->name ?: 'this class' }}.</p>
            </div>
        </div>
    @elseif(! $student)
        <div class="card ac-list-wrapper">
            <div class="card-body text-center py-56 px-24">
                <span class="ac-avatar mb-12" style="width:52px;height:52px;font-size:22px;"><i class="ri-user-line"></i></span>
                <p class="text-secondary-light mb-0">Select the student you want to give remarks.</p>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('student-remarks-process') }}">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $yearId }}">
            <input type="hidden" name="academic_term_id" value="{{ $termId }}">
            <input type="hidden" name="school_class_id" value="{{ $classId }}">
            <input type="hidden" name="student_id" value="{{ $student->id }}">

            <article class="sr-student">
                <div class="sr-student-head">
                    <span class="sr-photo">
                        @if(!empty($student->picture))
                            <img src="{{ asset($student->picture) }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">{{ $student->full_name }}</h6>
                        <span class="text-sm text-secondary-light">{{ $student->student_id }} · {{ $selectedClass?->name }} · {{ $selectedYear?->name }} · {{ $selectedTerm?->name }}</span>
                    </div>
                    <span class="ac-pill {{ $started ? 'ac-pill-emerald' : 'ac-pill-slate' }}">{{ $started ? 'Saved' : 'Blank' }}</span>
                </div>
                <div class="sr-body">
                    <div class="sr-grid">
                        @foreach($remarkFields as $field => [$label, $max])
                            <div class="sr-field">
                                <label for="sr_{{ $field }}">{{ $label }}</label>
                                <input
                                    id="sr_{{ $field }}"
                                    type="text"
                                    name="{{ $field }}"
                                    maxlength="{{ $max }}"
                                    value="{{ old($field, $remark?->{$field} ?? '') }}"
                                >
                            </div>
                        @endforeach
                        <div class="sr-field sr-span-2">
                            <label for="sr_class_teacher_remark">Class teacher's remarks</label>
                            <input
                                id="sr_class_teacher_remark"
                                type="text"
                                name="class_teacher_remark"
                                maxlength="500"
                                value="{{ old('class_teacher_remark', $remark?->class_teacher_remark ?? '') }}"
                            >
                        </div>
                        <div class="sr-field">
                            <label for="sr_headteacher_signature">Headteacher's signature</label>
                            <input
                                id="sr_headteacher_signature"
                                type="text"
                                name="headteacher_signature"
                                maxlength="255"
                                value="{{ old('headteacher_signature', $remark?->headteacher_signature ?? '') }}"
                            >
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-16">
                        <button type="submit" class="btn btn-primary-600">Save remarks</button>
                    </div>
                </div>
            </article>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const filter = document.getElementById('sr_filter');
    const classSelect = document.getElementById('sr_class');
    const studentSelect = document.getElementById('sr_student');

    classSelect.addEventListener('change', function () {
        studentSelect.value = '';
        filter.submit();
    });

    studentSelect.addEventListener('change', function () {
        if (classSelect.value) {
            filter.submit();
        }
    });

    document.getElementById('sr_year').addEventListener('change', function () { filter.submit(); });
    document.getElementById('sr_term').addEventListener('change', function () { filter.submit(); });
</script>
@endsection
