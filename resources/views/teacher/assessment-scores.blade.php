@php
    $pageName = "teacher-portal";
    $hasRecords = $assessment->hasRecordedScores();
    $subpageName = $hasRecords ? 'teacher-assessment-records' : 'teacher-assessments';
    $typePillClass = fn (?string $type) => 'ac-pill-' . ($type ?: 'slate');
    $gradePillClass = fn (?string $grade) => match (strtoupper(trim((string) $grade)[0] ?? '')) {
        'A' => 'ac-pill-grade-a', 'B' => 'ac-pill-grade-b', 'C' => 'ac-pill-grade-c',
        'D' => 'ac-pill-grade-d', 'F' => 'ac-pill-grade-f', default => 'ac-pill-slate',
    };
    $periodQuery = array_filter([
        'academic_year_id' => $assessment->academic_year_id,
        'academic_term_id' => $assessment->academic_term_id,
    ], fn ($value) => $value !== null && $value !== '');
    $backUrl = $hasRecords
        ? ($assessment->course_id
            ? route('teacher-course-assessment-records', array_merge(['course' => $assessment->course_id, 'class' => $assessment->school_class_id], $periodQuery))
            : route('teacher-class-assessment-records', array_merge(['class' => $assessment->school_class_id], $periodQuery)))
        : ($assessment->course_id
            ? route('teacher-course-assessments', array_merge(['course' => $assessment->course_id, 'class' => $assessment->school_class_id], $periodQuery))
            : route('teacher-class-assessments', array_merge(['class' => $assessment->school_class_id], $periodQuery)));
    $hubUrl = $hasRecords ? route('teacher-assessment-records', $periodQuery) : route('teacher-assessments', $periodQuery);
    $hubLabel = $hasRecords ? 'Assessment Records' : 'Assessments';
    $gradingSchemesJson = $gradingSchemes->map(function ($s) {
        return [
            'min' => (float) $s->min_percentage,
            'max' => (float) $s->max_percentage,
            'grade' => $s->letter_grade,
        ];
    })->values();
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .sc-page { --sc-max: {{ number_format($assessment->max_score, 0, '.', '') }}; }

    .sc-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 0;
    }
    @media (max-width: 991px) { .sc-meta-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .sc-meta-grid { grid-template-columns: 1fr; } }

    .sc-sticky-stats {
        position: sticky;
        top: 4.5rem;
        z-index: 8;
        margin-bottom: 24px;
        padding: 8px 0 16px;
        background: rgba(245, 246, 250, 0.96);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(229, 231, 235, 0.85);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    [data-theme="dark"] .sc-sticky-stats {
        background: rgba(17, 24, 39, 0.96);
        border-bottom-color: rgba(55, 65, 81, 0.85);
    }

    .sc-score-panel {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .sc-score-toolbar {
        padding: 16px 24px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fafafa, #fff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .sc-score-table thead th {
        background: #f9fafb;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        padding: 14px 16px;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .sc-score-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .sc-score-table tbody tr:hover td {
        background: rgba(37, 161, 148, 0.03);
    }

    .sc-score-table tbody tr.is-scored td {
        background: rgba(34, 197, 94, 0.03);
    }

    .sc-score-input-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 160px;
    }

    .sc-score-input {
        width: 88px;
        text-align: center;
        font-weight: 700;
        font-size: 15px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 8px 10px;
    }

    .sc-score-input:focus {
        border-color: #25A194;
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.12);
    }

    .sc-score-input.is-invalid {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.04);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
    }

    .sc-score-max {
        font-size: 12px;
        font-weight: 600;
        color: #9ca3af;
    }

    .sc-pct {
        font-size: 11px;
        font-weight: 700;
        color: #6366f1;
        min-width: 42px;
    }

    .sc-grade-cell .ac-pill { min-width: 36px; justify-content: center; }

    .sc-remarks-input {
        border-radius: 10px;
        font-size: 13px;
        min-width: 180px;
    }

    .sc-footer {
        position: sticky;
        bottom: 0;
        z-index: 10;
        padding: 16px 24px;
        border-top: 1px solid var(--neutral-200, #e5e7eb);
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.06);
    }

    .sc-footer-meta {
        font-size: 13px;
        color: #6b7280;
    }

    .sc-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        font-weight: 700;
        border-radius: 12px;
    }

    .sc-progress-bar {
        height: 6px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
        margin-top: 8px;
    }

    .sc-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #25A194, #6366f1);
        transition: width 0.25s ease;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body sc-page">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ $hubUrl }}" class="text-secondary-light hover-text-primary hover-underline">{{ $hubLabel }}</a>
                <a href="{{ $backUrl }}" class="text-secondary-light hover-text-primary hover-underline"> / {{ $assessment->schoolClass?->name }}</a>
                <span class="text-secondary-light"> / Score Entry</span>
            </div>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-arrow-left-line"></i> Back to {{ $hasRecords ? 'Records' : 'Assessments' }}
        </a>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(244,63,94,.1);color:#be123c;"><i class="ri-edit-box-line"></i></span>
            <div>
                <h5 class="fw-semibold mb-8">{{ $assessment->title }}</h5>
                @if($assessment->description)
                    <p class="text-sm text-secondary-light mb-8">{{ $assessment->description }}</p>
                @endif
                <div class="d-flex flex-wrap gap-2">
                    <span class="ac-pill {{ $typePillClass($assessment->type) }}">{{ $assessment->typeLabel() }}</span>
                    <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> {{ $assessment->schoolClass?->name }}</span>
                    <span class="ac-pill ac-pill-indigo"><i class="ri-book-2-line"></i> {{ $assessment->course?->name ?? 'Homeroom' }}</span>
                    @if($assessment->academicTerm?->name)
                        <span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> {{ $assessment->academicYear?->name }} · {{ $assessment->academicTerm->name }}</span>
                    @endif
                    <span class="ac-pill ac-pill-{{ $assessment->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($assessment->status) }}</span>
                    @if($assessment->assessment_date)
                        <span class="ac-pill ac-pill-slate"><i class="ri-calendar-line"></i> {{ $assessment->assessment_date->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-end">
            <div class="ac-pill ac-pill-violet" style="font-size:14px;padding:8px 16px;">
                <i class="ri-star-line"></i> Max Score: {{ number_format($assessment->max_score, 0) }}
            </div>
        </div>
    </div>

    <div class="sc-sticky-stats">
        <div class="sc-meta-grid">
        <div class="ac-stat-card">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Students</p>
                    <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                </div>
                <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-group-line"></i></span>
            </div>
        </div>
        <div class="ac-stat-card">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Scored</p>
                    <h4 class="fw-semibold mb-0 text-success-600" id="scScoredCount">{{ $stats['scored'] }}</h4>
                </div>
                <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
            </div>
        </div>
        <div class="ac-stat-card">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-secondary-light text-sm mb-4">Pending</p>
                    <h4 class="fw-semibold mb-0 text-warning-600" id="scPendingCount">{{ $stats['pending'] }}</h4>
                </div>
                <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-time-line"></i></span>
            </div>
        </div>
        <div class="ac-stat-card">
            <div>
                <p class="text-secondary-light text-sm mb-4">Completion</p>
                <h4 class="fw-semibold mb-0 text-info-600" id="scCompletionPct">{{ $stats['total'] > 0 ? round(($stats['scored'] / $stats['total']) * 100) : 0 }}%</h4>
                <div class="sc-progress-bar">
                    <div class="sc-progress-fill" id="scProgressFill" style="width: {{ $stats['total'] > 0 ? round(($stats['scored'] / $stats['total']) * 100) : 0 }}%;"></div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <form action="{{ route('teacher-assessment-scores-process', $assessment) }}" method="POST" id="scoreEntryForm">
        @csrf
        <div class="sc-score-panel">
            <div class="sc-score-toolbar">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">Enter Scores</h6>
                    <p class="text-sm text-secondary-light mb-0">Type a score for each student (0 to {{ number_format($assessment->max_score, 0) }}). Letter grades update automatically from your grading scheme.</p>
                </div>
                <div class="m-0">
                    <input type="text" id="scStudentSearch" class="form-control radius-4" placeholder="Filter students..." style="min-width:220px;">
                </div>
            </div>

            <div class="ac-list-scroll">
                <table class="table mb-0 sc-score-table" id="scScoreTable">
                    <thead>
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Student</th>
                            <th style="width:180px;">Score</th>
                            <th style="width:80px;">Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $existing = $existingScores->get($student->id);
                            $initials = strtoupper(substr($student->firstname ?? '', 0, 1) . substr($student->surname ?? '', 0, 1));
                            $hasScore = $existing?->score !== null;
                            $pct = $hasScore ? round(((float) $existing->score / (float) $assessment->max_score) * 100, 1) : null;
                        @endphp
                        <tr class="sc-score-row {{ $hasScore ? 'is-scored' : '' }}" data-name="{{ strtolower($student->full_name . ' ' . $student->student_id) }}">
                            <td class="text-secondary-light fw-semibold">{{ $loop->iteration }}</td>
                            <td>
                                <input type="hidden" name="scores[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                                <div class="ac-name-cell">
                                    <span class="ac-avatar">
                                        @if($student->picture)
                                            <img src="{{ asset($student->picture) }}" alt="">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </span>
                                    <div>
                                        <span class="fw-semibold d-block">{{ $student->full_name }}</span>
                                        <span class="ac-pill ac-pill-slate">{{ $student->student_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="sc-score-input-wrap">
                                    <input type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $assessment->max_score }}"
                                        name="scores[{{ $loop->index }}][score]"
                                        class="form-control sc-score-input sc-live-score"
                                        value="{{ $existing?->score }}"
                                        placeholder="—"
                                        data-grade-cell="grade-{{ $student->id }}"
                                        data-row="{{ $student->id }}">
                                    <span class="sc-score-max">/ {{ number_format($assessment->max_score, 0) }}</span>
                                    <span class="sc-pct" id="pct-{{ $student->id }}">{{ $pct !== null ? $pct.'%' : '' }}</span>
                                </div>
                            </td>
                            <td class="sc-grade-cell">
                                <span class="ac-pill {{ $existing?->letter_grade ? $gradePillClass($existing->letter_grade) : 'ac-pill-slate' }}" id="grade-{{ $student->id }}">
                                    {{ $existing?->letter_grade ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <input type="text"
                                    name="scores[{{ $loop->index }}][remarks]"
                                    class="form-control sc-remarks-input"
                                    value="{{ $existing?->remarks }}"
                                    placeholder="Optional note">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="sc-footer">
                <div class="sc-footer-meta">
                    <i class="ri-information-line"></i>
                    <span id="scFooterSummary">{{ $stats['scored'] }} of {{ $stats['total'] }} students scored</span>
                    · Grades apply when you save
                </div>
                <button type="submit" class="btn btn-primary-600 sc-save-btn">
                    <i class="ri-save-line"></i> Save Scores
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const maxScore = {{ (float) $assessment->max_score }};
    const totalStudents = {{ $stats['total'] }};
    const schemes = @json($gradingSchemesJson);

    const gradeClassMap = {
        A: 'ac-pill-grade-a', B: 'ac-pill-grade-b', C: 'ac-pill-grade-c',
        D: 'ac-pill-grade-d', F: 'ac-pill-grade-f',
    };

    function letterForPct(pct) {
        if (pct === null || isNaN(pct)) return null;
        const match = schemes.find(s => pct >= s.min && pct <= s.max);
        return match ? match.grade : null;
    }

    function scoreValidationMessage(score) {
        if (score === null || isNaN(score)) {
            return 'Please enter a valid score.';
        }
        if (score < 0) {
            return 'Score cannot be negative. Enter a value from 0 to ' + maxScore + '.';
        }
        if (score > maxScore) {
            return 'Score cannot exceed the maximum of ' + maxScore + '.';
        }
        return null;
    }

    function validateScoreInput(input, showToast) {
        const val = input.value.trim();
        if (val === '') {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
            return true;
        }

        const score = parseFloat(val);
        const message = scoreValidationMessage(score);

        if (message) {
            input.classList.add('is-invalid');
            input.setCustomValidity(message);
            if (showToast && typeof showAppToast === 'function') {
                showAppToast('error', message);
            }
            return false;
        }

        input.classList.remove('is-invalid');
        input.setCustomValidity('');
        return true;
    }

    function updateRow(input) {
        const rowId = input.dataset.row;
        const val = input.value.trim();
        const score = val === '' ? null : parseFloat(val);
        const pctEl = document.getElementById('pct-' + rowId);
        const gradeEl = document.getElementById('grade-' + rowId);
        const row = input.closest('tr');

        if (score !== null && !isNaN(score) && scoreValidationMessage(score)) {
            pctEl.textContent = '';
            gradeEl.textContent = '—';
            gradeEl.className = 'ac-pill ac-pill-slate';
            row.classList.remove('is-scored');
            return;
        }

        if (score === null || isNaN(score)) {
            pctEl.textContent = '';
            gradeEl.textContent = '—';
            gradeEl.className = 'ac-pill ac-pill-slate';
            row.classList.remove('is-scored');
            return;
        }

        const pct = maxScore > 0 ? Math.round((score / maxScore) * 1000) / 10 : 0;
        pctEl.textContent = pct + '%';
        const letter = letterForPct(pct);
        gradeEl.textContent = letter || '—';
        gradeEl.className = 'ac-pill ' + (letter && gradeClassMap[letter[0]] ? gradeClassMap[letter[0]] : 'ac-pill-slate');
        row.classList.add('is-scored');
    }

    function refreshStats() {
        const inputs = document.querySelectorAll('.sc-live-score');
        let scored = 0;
        inputs.forEach(i => { if (i.value.trim() !== '') scored++; });
        const pending = totalStudents - scored;
        const pct = totalStudents > 0 ? Math.round((scored / totalStudents) * 100) : 0;

        document.getElementById('scScoredCount').textContent = scored;
        document.getElementById('scPendingCount').textContent = pending;
        document.getElementById('scCompletionPct').textContent = pct + '%';
        document.getElementById('scProgressFill').style.width = pct + '%';
        document.getElementById('scFooterSummary').textContent = scored + ' of ' + totalStudents + ' students scored';
    }

    document.querySelectorAll('.sc-live-score').forEach(input => {
        input.addEventListener('input', function () {
            validateScoreInput(this, false);
            updateRow(this);
            refreshStats();
        });
        input.addEventListener('blur', function () {
            validateScoreInput(this, true);
        });
    });

    document.getElementById('scoreEntryForm')?.addEventListener('submit', function (event) {
        const inputs = Array.from(document.querySelectorAll('.sc-live-score'));
        let firstInvalid = null;

        inputs.forEach(input => {
            if (!validateScoreInput(input, false) && !firstInvalid) {
                firstInvalid = input;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            validateScoreInput(firstInvalid, true);
            firstInvalid.focus();
            if (typeof showAppToast === 'function') {
                showAppToast('error', 'Fix invalid scores before saving. Each score must be between 0 and ' + maxScore + '.');
            }
        }
    });

    document.getElementById('scStudentSearch')?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.sc-score-row').forEach(row => {
            row.style.display = !q || row.dataset.name.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endsection
