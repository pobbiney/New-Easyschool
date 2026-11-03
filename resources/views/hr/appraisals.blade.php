@php
    $pageName = "hr";
    $subpageName = "hr-appraisals";
    $draftCount = $appraisals->where('status', 'draft')->count();
    $finalCount = $appraisals->where('status', 'final')->count();
    $averageOverall = $appraisals->count() ? round((float) $appraisals->avg('overall'), 2) : 0;
    $criteriaIcons = [
        'punctuality' => 'ri-time-line',
        'professionalism' => 'ri-user-star-line',
        'teamwork' => 'ri-team-line',
        'job_knowledge' => 'ri-book-open-line',
        'communication' => 'ri-chat-3-line',
    ];
    $initials = function ($staff) {
        return strtoupper(substr((string) ($staff->firstname ?? ''), 0, 1).substr((string) ($staff->surname ?? ''), 0, 1));
    };
    $scoreColor = function ($score) {
        $score = (float) $score;
        if ($score >= 4) return 'emerald';
        if ($score >= 3) return 'teal';
        if ($score >= 2) return 'amber';
        return 'rose';
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('hr.partials._styles')
<style>
    .ap-hero {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12), rgba(99, 102, 241, 0.08));
        margin-bottom: 24px;
    }
    .ap-form {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .ap-form .card-header {
        background: transparent;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        padding: 16px 20px;
    }
    .ap-score-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 14px;
        padding: 16px;
        background: #f8fafc;
        height: 100%;
    }
    .ap-score-card label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .ap-score-card i {
        color: #25A194;
        font-size: 18px;
    }
    .ap-score-card .hint {
        font-size: 11px;
        color: #64748b;
        margin-top: 6px;
    }
    .ap-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: #f8fafc;
    }
    .ap-search { min-width: 240px; max-width: 320px; }
    .staff-meta {
        display: block;
        font-size: 12px;
        color: #64748b;
        font-weight: 400;
    }
    .ap-empty {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }
    .ap-empty i {
        font-size: 36px;
        color: #25A194;
        display: block;
        margin-bottom: 10px;
    }
    .ap-overall {
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HR', 'url' => route('hr-dashboard')],
            ['label' => 'Appraisals', 'url' => null, 'active' => true],
        ],
        'title' => 'Appraisals',
        'subtitle' => 'Termly performance reviews scored 0–5 against the current academic session.',
    ])

    <div class="ap-hero">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#25A194;">
                <i class="ri-star-line"></i>
            </span>
            <div>
                <h5 class="fw-semibold mb-6">Staff appraisal desk</h5>
                <p class="text-sm text-secondary-light mb-0">
                    Score staff for the selected academic year and term. Period defaults to the session set in school settings.
                    @if(!empty($defaultAcademicTermName) && !empty($defaultAcademicYearName))
                        Current session: <strong>{{ $defaultAcademicTermName }} · {{ $defaultAcademicYearName }}</strong>.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Appraisals</p>
                        <h4 class="fw-semibold mb-0">{{ $appraisals->count() }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Drafts</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $draftCount }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-draft-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Final</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $finalCount }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Average score</p>
                        <h4 class="fw-semibold mb-0">{{ number_format($averageOverall, 2) }} / 5</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-bar-chart-2-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ap-form">
        <div class="card-header">
            <h6 class="mb-0 fw-semibold">New appraisal</h6>
            <p class="text-sm text-secondary-light mb-0 mt-4">Scores start at 0. Use 0–5 for each criterion.</p>
        </div>
        <div class="card-body p-20">
            <form method="POST" action="{{ route('hr-appraisals-process') }}" class="row gy-3">
                @csrf
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Staff</label>
                    <select name="staff_id" class="form-control form-select" required>
                        <option value="">Select staff</option>
                        @foreach($staffMembers as $member)
                            <option value="{{ $member->id }}" @selected((string) old('staff_id') === (string) $member->id)>{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Academic year</label>
                    <select name="academic_year_id" class="form-control form-select" required>
                        <option value="">Select year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $selectedYearId) === (string) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Term</label>
                    <select name="academic_term_id" class="form-control form-select" required>
                        <option value="">Select term</option>
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}" @selected((string) old('academic_term_id', $selectedTermId) === (string) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                    @if($academicTerms->isEmpty())
                        <div class="text-sm text-danger-600 mt-6">Add academic terms in Settings before creating an appraisal.</div>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Status</label>
                    <select name="status" class="form-control form-select">
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                        <option value="final" @selected(old('status') === 'final')>Final</option>
                    </select>
                </div>

                @foreach($criteria as $key => $label)
                    <div class="col-md-4 col-xl">
                        <div class="ap-score-card">
                            <label for="score_{{ $key }}">
                                <i class="{{ $criteriaIcons[$key] ?? 'ri-star-line' }}"></i>
                                {{ $label }}
                            </label>
                            <input
                                type="number"
                                id="score_{{ $key }}"
                                min="0"
                                max="5"
                                step="1"
                                name="scores[{{ $key }}]"
                                class="form-control"
                                value="{{ old('scores.'.$key, 0) }}"
                                required
                            >
                            <div class="hint">0 – 5</div>
                        </div>
                    </div>
                @endforeach

                <div class="col-12">
                    <label class="text-sm fw-semibold mb-8">Comments</label>
                    <textarea name="comments" class="form-control" rows="3" placeholder="Optional notes for this review">{{ old('comments') }}</textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary-600" @disabled($academicTerms->isEmpty() || $academicYears->isEmpty())>
                        <i class="ri-save-line"></i> Save appraisal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="ap-toolbar">
            <div>
                <h6 class="mb-0 fw-semibold">Saved appraisals</h6>
                <p class="text-sm text-secondary-light mb-0 mt-4">{{ $finalCount }} final · {{ $draftCount }} draft</p>
            </div>
            <div class="ap-search">
                <input type="search" id="appraisalSearch" class="form-control" placeholder="Search staff or period…">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table bordered-table mb-0" id="appraisalTable">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Period</th>
                        <th>Overall</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appraisals as $appraisal)
                        @php $staff = $appraisal->staff; @endphp
                        <tr data-search="{{ strtolower(trim(($staff?->full_name ?? '').' '.$appraisal->periodLabel().' '.$appraisal->status)) }}">
                            <td>
                                <div class="ac-name-cell">
                                    <span class="ac-avatar">
                                        @if(!empty($staff?->picture))
                                            <img src="{{ asset($staff->picture) }}" alt="">
                                        @else
                                            {{ $initials($staff) ?: 'ST' }}
                                        @endif
                                    </span>
                                    <div>
                                        <strong>{{ $staff?->full_name ?: '—' }}</strong>
                                        <span class="staff-meta">{{ $staff?->employee_id ?: 'No staff ID' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="ac-pill ac-pill-indigo">{{ $appraisal->periodLabel() }}</span></td>
                            <td>
                                <span class="ac-pill ac-pill-{{ $scoreColor($appraisal->overall) }} ap-overall">
                                    {{ number_format((float) $appraisal->overall, 2) }} / 5
                                </span>
                            </td>
                            <td>
                                <span class="ac-pill {{ $appraisal->status === 'final' ? 'ac-pill-emerald' : 'ac-pill-draft' }}">
                                    {{ ucfirst($appraisal->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('hr-appraisals-show', $appraisal->id) }}" class="btn btn-sm btn-outline-primary-600">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ap-empty">
                                    <i class="ri-star-line"></i>
                                    No appraisals yet. Score a staff member for the current term to get started.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const input = document.getElementById('appraisalSearch');
        const rows = document.querySelectorAll('#appraisalTable tbody tr[data-search]');
        if (!input) return;
        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.style.display = !q || row.getAttribute('data-search').includes(q) ? '' : 'none';
            });
        });
    })();
</script>
@endsection
