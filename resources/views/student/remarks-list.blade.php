@php
    $pageName = "student-remarks";
    $subpageName = "list-remarks";
    $selectedClass = $classes->firstWhere('id', $classId);
    $selectedYear = $academicYears->firstWhere('id', $yearId);
    $selectedTerm = $academicTerms->firstWhere('id', $termId);
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .sr-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.2fr auto;
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
    .sr-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
    }
    .sr-card-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: linear-gradient(180deg, #f8fafc, #fff);
        border-bottom: 1px solid #eef2f7;
    }
    .sr-photo {
        width: 48px;
        height: 48px;
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
    .sr-body { padding: 16px 18px 18px; }
    .sr-facts {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .sr-fact {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 10px 12px;
    }
    .sr-fact span {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .sr-fact strong {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
    }
    .sr-span-2 { grid-column: span 2; }
    @media (max-width: 991px) {
        .sr-filter-grid,
        .sr-facts { grid-template-columns: 1fr; }
        .sr-span-2 { grid-column: auto; }
        .sr-card-head { align-items: flex-start; }
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
                <span class="text-secondary-light"> / List Remarks</span>
            </div>
        </div>
        <a href="{{ route('student-remarks') }}" class="btn btn-primary-600 d-inline-flex align-items-center gap-6">
            <i class="ri-add-line"></i> Add Remarks
        </a>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-file-list-3-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Saved remarks</h5>
            <p class="text-sm text-secondary-light mb-0">Review remarks already written for this term, then open a student to edit them.</p>
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body p-20">
            <form method="GET" action="{{ route('list-student-remarks') }}" id="sr_filter" class="sr-filter-grid">
                <div>
                    <label class="sr-label" for="sr_year">Academic year</label>
                    <select id="sr_year" name="academic_year_id" class="form-control form-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((int) $yearId === (int) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sr-label" for="sr_term">Term</label>
                    <select id="sr_term" name="academic_term_id" class="form-control form-select">
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}" @selected((int) $termId === (int) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sr-label" for="sr_class">Class</label>
                    <select id="sr_class" name="school_class_id" class="form-control form-select">
                        <option value="">All classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected((int) $classId === (int) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary-600 d-inline-flex align-items-center justify-content-center gap-6">
                    <i class="ri-search-line"></i> Show
                </button>
            </form>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Period</p>
                <h5 class="fw-semibold mb-0">{{ $selectedTerm?->name ?: 'Term' }}</h5>
                <p class="text-sm text-secondary-light mb-0 mt-4">{{ $selectedYear?->name }}</p>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Class</p>
                <h5 class="fw-semibold mb-0">{{ $selectedClass?->name ?: 'All classes' }}</h5>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Remarks</p>
                <h4 class="fw-semibold mb-0">{{ $remarks->count() }}</h4>
            </div>
        </div>
    </div>

    @if($remarks->isNotEmpty())
        <div class="card ac-list-wrapper mb-16">
            <div class="card-body p-16">
                <label class="sr-label" for="sr_search">Find a student</label>
                <input type="search" id="sr_search" class="form-control" placeholder="Search by name, ID, or class">
            </div>
        </div>
    @endif

    <div class="d-flex flex-column gap-16" id="sr_list">
        @forelse($remarks as $remark)
            @php
                $name = $remark->student?->full_name ?: 'Student';
                $nameParts = preg_split('/\s+/', trim($name)) ?: [];
                $initials = strtoupper(mb_substr($nameParts[0] ?? 'S', 0, 1).mb_substr($nameParts[1] ?? '', 0, 1));
                $className = $remark->student?->schoolClass?->name ?: $remark->student?->class_name ?: '—';
            @endphp
            <article class="sr-card" data-search="{{ strtolower($name.' '.($remark->student?->student_id).' '.$className) }}">
                <div class="sr-card-head">
                    <span class="sr-photo">
                        @if(!empty($remark->student?->picture))
                            <img src="{{ asset($remark->student->picture) }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">{{ $name }}</h6>
                        <span class="text-sm text-secondary-light">{{ $remark->student?->student_id }} · {{ $className }}</span>
                    </div>
                    <a href="{{ route('student-remarks', [
                        'academic_year_id' => $remark->academic_year_id,
                        'academic_term_id' => $remark->academic_term_id,
                        'school_class_id' => $remark->student?->school_class_id,
                        'student_id' => $remark->student_id,
                    ]) }}" class="btn btn-sm btn-outline-primary-600">Edit</a>
                </div>
                <div class="sr-body">
                    <div class="sr-facts">
                        <div class="sr-fact">
                            <span>Attitude</span>
                            <strong>{{ $remark->attitude ?: '—' }}</strong>
                        </div>
                        <div class="sr-fact">
                            <span>Conduct</span>
                            <strong>{{ $remark->conduct ?: '—' }}</strong>
                        </div>
                        <div class="sr-fact">
                            <span>Interest</span>
                            <strong>{{ $remark->interest ?: '—' }}</strong>
                        </div>
                        <div class="sr-fact sr-span-2">
                            <span>Class teacher's remarks</span>
                            <strong>{{ $remark->class_teacher_remark ?: '—' }}</strong>
                        </div>
                        <div class="sr-fact">
                            <span>Headteacher's signature</span>
                            <strong>{{ $remark->headteacher_signature ?: '—' }}</strong>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="card ac-list-wrapper">
                <div class="card-body text-center py-56 px-24">
                    <span class="ac-avatar mb-12" style="width:52px;height:52px;font-size:22px;"><i class="ri-chat-quote-line"></i></span>
                    <p class="text-secondary-light mb-12">No remarks saved for this period.</p>
                    <a href="{{ route('student-remarks') }}" class="btn btn-primary-600">Add remarks</a>
                </div>
            </div>
        @endforelse
    </div>

    <p class="text-secondary-light text-center py-24 mb-0 d-none" id="sr_empty">No student matches that search.</p>
</div>
@endsection

@section('scripts')
<script>
    const search = document.getElementById('sr_search');
    const cards = Array.from(document.querySelectorAll('.sr-card'));
    const empty = document.getElementById('sr_empty');
    if (search) {
        search.addEventListener('input', function () {
            const query = search.value.trim().toLowerCase();
            let shown = 0;
            cards.forEach(function (card) {
                const match = card.dataset.search.includes(query);
                card.classList.toggle('d-none', !match);
                if (match) shown += 1;
            });
            empty.classList.toggle('d-none', shown !== 0);
        });
    }
</script>
@endsection
