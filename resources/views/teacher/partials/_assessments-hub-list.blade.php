@php
    $mode = $mode ?? 'pending';
    $periodQuery = array_filter([
        'academic_year_id' => request('academic_year_id'),
        'academic_term_id' => request('academic_term_id'),
    ], fn ($value) => $value !== null && $value !== '');
    $typePillClass = fn (?string $type) => 'ac-pill-' . ($type ?: 'slate');
    $typeIcon = fn (?string $type) => match ($type) {
        'homework' => 'ri-booklet-line',
        'class_test' => 'ri-file-edit-line',
        'exam' => 'ri-file-shield-2-line',
        'class_assignment' => 'ri-task-line',
        default => 'ri-file-list-3-line',
    };
@endphp

@if($assessments->isNotEmpty())
<div class="ah-toolbar">
    <div class="ah-filter-pills">
        <button type="button" class="ah-filter-pill is-active" data-filter="all">All</button>
        @foreach($assessmentTypes as $assessmentType)
            <button type="button" class="ah-filter-pill" data-filter="{{ $assessmentType->slug }}">{{ $assessmentType->name }}</button>
        @endforeach
    </div>
    <input type="text" id="ahSearch" class="form-control radius-4" placeholder="Search assessments..." style="min-width:220px;max-width:280px;">
</div>
<div class="ac-list-scroll dataTable-wrapper">
    <table class="table mb-0 data-table" id="dataTable">
        <thead>
            <tr>
                <th>Assessment</th>
                <th>Type</th>
                <th>Term</th>
                <th>Class</th>
                <th>Subject</th>
                <th>Max</th>
                <th>Scored</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessments as $assessment)
            @php
                $scored = $assessment->scores->whereNotNull('score')->count();
                $searchText = strtolower($assessment->title . ' ' . ($assessment->schoolClass?->name ?? '') . ' ' . ($assessment->course?->name ?? '') . ' ' . ($assessment->academicTerm?->name ?? '') . ' ' . ($assessment->academicYear?->name ?? ''));
            @endphp
            <tr class="ah-assessment-row" data-type="{{ $assessment->type }}" data-search="{{ $searchText }}">
                <td>
                    <div class="ah-assessment-title">
                        <span class="ah-assessment-icon {{ $assessment->type }}"><i class="{{ $typeIcon($assessment->type) }}"></i></span>
                        <div>
                            <span class="fw-semibold d-block">{{ $assessment->title }}</span>
                            <span class="ah-assessment-meta">
                                @if($assessment->assessment_date)
                                    {{ $assessment->assessment_date->format('d M Y') }}
                                @else
                                    No date set
                                @endif
                                @if($assessment->due_date)
                                    · Due {{ $assessment->due_date->format('d M') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </td>
                <td><span class="ac-pill {{ $typePillClass($assessment->type) }}">{{ $assessment->typeLabel() }}</span></td>
                <td>
                    <span class="ac-pill ac-pill-indigo">{{ $assessment->academicTerm?->name ?? '—' }}</span>
                    @if($assessment->academicYear?->name)
                        <span class="d-block text-xs text-secondary-light mt-1">{{ $assessment->academicYear->name }}</span>
                    @endif
                </td>
                <td><span class="ac-pill ac-pill-teal">{{ $assessment->schoolClass?->name }}</span></td>
                <td><span class="ac-pill ac-pill-indigo">{{ $assessment->course?->name ?? 'Homeroom' }}</span></td>
                <td><span class="ac-pill ac-pill-violet">{{ number_format($assessment->max_score, 0) }} pts</span></td>
                <td><span class="ac-pill ac-pill-slate">{{ $scored }} scored</span></td>
                <td><span class="ac-pill ac-pill-{{ $assessment->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($assessment->status) }}</span></td>
                <td>
                    <div class="ac-action-pills">
                        <a href="{{ route('teacher-assessment-scores', $assessment) }}" class="ac-action-pill ac-action-pill-rose">
                            <i class="ri-edit-2-line"></i> {{ $mode === 'records' ? 'View Scores' : 'Enter Scores' }}
                        </a>
                        @if($mode === 'pending')
                            <form action="{{ route('teacher-assessments-delete', $assessment) }}" method="POST" class="ah-action-form"
                                data-confirm-title="Delete this assessment?"
                                data-confirm="This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ac-action-pill ac-action-pill-amber">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($mode === 'pending')
    @include('teacher.partials._assessment-delete-notice', ['assessments' => $assessments])
@endif
@else
<div class="ah-empty-assessments">
    <span class="ac-avatar d-flex align-items-center justify-content-center"><i class="{{ $mode === 'records' ? 'ri-archive-line' : 'ri-file-add-line' }}"></i></span>
    <h6 class="fw-semibold mb-8">{{ $mode === 'records' ? 'No scored assessments yet' : 'All caught up' }}</h6>
    <p class="text-secondary-light mb-20">
        @if($mode === 'records')
            Assessments move here automatically once marks are entered.
        @else
            Every assessment for this term already has marks entered, or none have been created yet.
        @endif
    </p>
    @if($mode === 'records')
        <a href="{{ route('teacher-assessments', $periodQuery) }}" class="btn btn-primary-600">
            <i class="ri-arrow-left-line"></i> Back to Assessments
        </a>
    @else
        <button type="button" class="btn btn-primary-600" data-bs-toggle="modal" data-bs-target="#createAssessmentModal">
            <i class="ri-add-line"></i> Create Assessment
        </button>
        @if(($stats['records'] ?? 0) > 0)
            <a href="{{ route('teacher-assessment-records', $periodQuery) }}" class="btn btn-outline-primary-600 ms-2">
                <i class="ri-archive-line"></i> View {{ $stats['records'] }} record{{ $stats['records'] === 1 ? '' : 's' }}
            </a>
        @endif
    @endif
</div>
@endif
