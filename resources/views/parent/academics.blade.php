@extends('layouts.parent')

@section('title', 'Academics — '.$student->full_name)
@section('page-title', 'Academics')
@section('page-subtitle', $student->full_name)

@section('css')
<style>
    .ac {
        --a-teal: #25A194;
        --a-teal-d: #0f766e;
        --a-ink: #0f172a;
        --a-muted: #64748b;
        --a-border: #e2e8f0;
        --a-green: #10b981;
        --a-red: #ef4444;
        --a-amber: #f59e0b;
        --a-blue: #3b82f6;
    }

    .ac-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .ac-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .ac-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .ac-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }
    .ac-hero-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .ac-hero-title {
        font-size: clamp(1.6rem, 4vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        margin-bottom: 6px;
    }
    .ac-hero-period {
        font-size: 14px;
        opacity: .9;
        font-weight: 600;
    }
    .ac-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .ac-hero-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .ac-hero-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .ac-student-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        padding: 10px 16px 10px 10px;
        border: 1px solid rgba(255,255,255,.2);
    }
    .ac-student-chip img,
    .ac-student-chip .av {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        background: rgba(255,255,255,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
    }
    .ac-student-chip b { display: block; font-size: 14px; }
    .ac-student-chip small { opacity: .8; font-size: 12px; }

    .ac-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .ac-bar-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .ac-bar select {
        border: 1px solid var(--a-border);
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        color: var(--a-ink);
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .ac-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 18px;
        border-radius: 12px;
        border: 1.5px solid var(--a-teal);
        background: #f0fdfa;
        color: var(--a-teal-d);
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        transition: all .12s;
    }
    .ac-link-btn:hover {
        background: var(--a-teal);
        color: #fff;
    }

    .ac-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .ac-stat {
        background: #fff;
        border: 1px solid var(--a-border);
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .ac-stat-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--a-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }
    .ac-stat-value {
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
    }
    .ac-stat-value.green { color: var(--a-green); }
    .ac-stat-value.red { color: var(--a-red); }
    .ac-stat-value.amber { color: var(--a-amber); }
    .ac-stat-value.blue { color: var(--a-blue); }
    .ac-stat-value.teal { color: var(--a-teal-d); }

    .ac-attendance-ring {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: #fff;
        border: 1px solid var(--a-border);
        border-radius: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .ac-ring {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: conic-gradient(var(--a-green) 0deg, var(--a-green) calc(var(--pct) * 3.6deg), #f1f5f9 calc(var(--pct) * 3.6deg));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ac-ring-inner {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: var(--a-teal-d);
        line-height: 1.1;
    }
    .ac-ring-inner small {
        font-size: 10px;
        font-weight: 700;
        color: var(--a-muted);
        text-transform: uppercase;
    }
    .ac-attendance-copy h3 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 800;
        color: var(--a-ink);
    }
    .ac-attendance-copy p {
        margin: 0;
        font-size: 13px;
        color: var(--a-muted);
        line-height: 1.5;
    }

    .ac-card {
        background: #fff;
        border: 1px solid var(--a-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
        margin-bottom: 20px;
    }
    .ac-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--a-border);
        background: #fafafa;
    }
    .ac-card-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--a-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ac-card-head h3 i { color: var(--a-teal); }
    .ac-card-head .count {
        font-size: 12px;
        font-weight: 700;
        color: var(--a-muted);
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .ac-table-wrap { overflow-x: auto; }
    .ac-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .ac-table thead th {
        padding: 14px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--a-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--a-border);
        white-space: nowrap;
    }
    .ac-table thead th.text-end { text-align: right; }
    .ac-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .ac-table tbody tr:hover { background: #fafafa; }
    .ac-table tbody tr:last-child td { border-bottom: none; }
    .ac-table .subject { font-weight: 700; color: var(--a-ink); }
    .ac-table .text-end { text-align: right; }
    .ac-table .score {
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
    .ac-grade {
        display: inline-block;
        min-width: 36px;
        text-align: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #e6f7f5;
        color: var(--a-teal-d);
    }
    .ac-position {
        font-weight: 700;
        color: var(--a-muted);
    }

    .ac-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 20px;
        background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
        border-top: 1px solid #ccfbf1;
    }
    .ac-summary-item {
        flex: 1;
        min-width: 140px;
    }
    .ac-summary-item label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--a-muted);
        margin-bottom: 4px;
    }
    .ac-summary-item div {
        font-size: 20px;
        font-weight: 800;
        color: var(--a-teal-d);
    }

    .ac-empty {
        padding: 48px 24px;
        text-align: center;
    }
    .ac-empty i {
        font-size: 48px;
        color: #cbd5e1;
        display: block;
        margin-bottom: 12px;
    }
    .ac-empty h3 {
        font-size: 18px;
        font-weight: 800;
        color: var(--a-ink);
        margin: 0 0 6px;
    }
    .ac-empty p {
        color: var(--a-muted);
        margin: 0;
        font-size: 14px;
        max-width: 420px;
        margin-inline: auto;
    }

    @media (max-width: 640px) {
        .ac-bar { flex-direction: column; align-items: stretch; }
        .ac-bar-filters { flex-direction: column; }
        .ac-bar select { width: 100%; }
        .ac-link-btn { width: 100%; justify-content: center; }
        .ac-attendance-ring { flex-direction: column; text-align: center; }
    }
</style>
@endsection

@section('content')
@php
    $initials = strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1));
    $periodLabel = trim(($period['term_name'] ?? '').' '.($period['year_name'] ?? '')) ?: 'Select period';
    $termAverage = $report['term_average'] ?? null;
    $subjectGrades = $report['subject_grades'] ?? collect();
    $hasGrades = $subjectGrades->isNotEmpty() && $subjectGrades->contains(fn ($s) => $s['total_score'] !== null);
    $attendancePct = ($attendanceSummary && $attendanceSummary['total_days'] > 0)
        ? round(($attendanceSummary['present'] / $attendanceSummary['total_days']) * 100)
        : 0;
@endphp

<div class="ac">
    <div class="ac-hero">
        <div class="ac-hero-inner">
            <div>
                <div class="ac-hero-label">Academic overview</div>
                <div class="ac-hero-title">{{ $student->full_name }}</div>
                <div class="ac-hero-period">{{ $periodLabel }}</div>
                @if($report)
                    <div class="ac-hero-meta">
                        @if($termAverage && $termAverage['average_percentage'] !== null)
                            <div>Term average<strong>{{ number_format($termAverage['average_percentage'], 1) }}%</strong></div>
                        @endif
                        @if(!empty($report['class_position']))
                            <div>Class position<strong>{{ $report['class_position'] }} / {{ $report['students_on_roll'] ?? '—' }}</strong></div>
                        @endif
                        @if($termAverage && !empty($termAverage['letter_grade']))
                            <div>Overall grade<strong>{{ $termAverage['letter_grade'] }}</strong></div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="ac-student-chip">
                @if($student->picture)
                    <img src="{{ asset($student->picture) }}" alt="">
                @else
                    <div class="av">{{ $initials }}</div>
                @endif
                <div>
                    <b>{{ $student->full_name }}</b>
                    <small>{{ $student->schoolClass?->name ?? $student->class_name }}</small>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" id="acForm" class="ac-bar">
        <div class="ac-bar-filters">
            <select name="academic_year_id" onchange="document.getElementById('acForm').submit()">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($period['year_id'] == $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
            <select name="academic_term_id" onchange="document.getElementById('acForm').submit()">
                @foreach($academicTerms as $term)
                    <option value="{{ $term->id }}" @selected($period['term_id'] == $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('parent.report-card', $student) }}?academic_year_id={{ $period['year_id'] }}&academic_term_id={{ $period['term_id'] }}"
           class="ac-link-btn">
            <i class="ri-file-chart-line"></i> View report card
        </a>
    </form>

    @if($attendanceSummary && $attendanceSummary['total_days'] > 0)
        <div class="ac-attendance-ring">
            <div class="ac-ring" style="--pct: {{ $attendancePct }}">
                <div class="ac-ring-inner">
                    <span>{{ $attendancePct }}%</span>
                    <small>Present</small>
                </div>
            </div>
            <div class="ac-attendance-copy">
                <h3>Attendance — {{ $period['term_name'] ?? 'this term' }}</h3>
                <p>{{ $student->firstname }} was present on {{ $attendanceSummary['present'] }} of {{ $attendanceSummary['total_days'] }} recorded days.</p>
            </div>
        </div>

        <div class="ac-stats">
            <div class="ac-stat">
                <div class="ac-stat-label">Present</div>
                <div class="ac-stat-value green">{{ $attendanceSummary['present'] }}</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat-label">Absent</div>
                <div class="ac-stat-value red">{{ $attendanceSummary['absent'] }}</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat-label">Late</div>
                <div class="ac-stat-value amber">{{ $attendanceSummary['late'] }}</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat-label">Excused</div>
                <div class="ac-stat-value blue">{{ $attendanceSummary['excused'] }}</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat-label">Total days</div>
                <div class="ac-stat-value teal">{{ $attendanceSummary['total_days'] }}</div>
            </div>
        </div>
    @endif

    <div class="ac-card">
        <div class="ac-card-head">
            <h3><i class="ri-book-read-line"></i> Subject grades</h3>
            @if($hasGrades)
                <span class="count">{{ $subjectGrades->count() }} {{ Str::plural('subject', $subjectGrades->count()) }}</span>
            @endif
        </div>

        @if(!$report)
            <div class="ac-empty">
                <i class="ri-calendar-line"></i>
                <h3>Select a period</h3>
                <p>Choose an academic year and term above to view grades and attendance.</p>
            </div>
        @elseif(!$hasGrades)
            <div class="ac-empty">
                <i class="ri-file-search-line"></i>
                <h3>No grades yet</h3>
                <p>Grades for {{ $periodLabel }} have not been published yet. Check back later.</p>
            </div>
        @else
            <div class="ac-table-wrap">
                <table class="ac-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="text-end">Class</th>
                            <th class="text-end">Exam</th>
                            <th class="text-end">Total</th>
                            <th>Grade</th>
                            <th class="text-end">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjectGrades as $subject)
                            <tr>
                                <td><span class="subject">{{ $subject['course_name'] ?? '—' }}</span></td>
                                <td class="text-end score">{{ $subject['class_score'] !== null ? number_format($subject['class_score'], 1) : '—' }}</td>
                                <td class="text-end score">{{ $subject['exam_score'] !== null ? number_format($subject['exam_score'], 1) : '—' }}</td>
                                <td class="text-end score">{{ $subject['total_score'] !== null ? number_format($subject['total_score'], 1) : '—' }}</td>
                                <td>
                                    @if(!empty($subject['letter_grade']))
                                        <span class="ac-grade">{{ $subject['letter_grade'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!empty($subject['position']))
                                        <span class="ac-position">{{ $subject['position'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($termAverage && $termAverage['average_percentage'] !== null)
                <div class="ac-summary">
                    <div class="ac-summary-item">
                        <label>Term average</label>
                        <div>{{ number_format($termAverage['average_percentage'], 1) }}%</div>
                    </div>
                    @if(!empty($termAverage['letter_grade']))
                        <div class="ac-summary-item">
                            <label>Overall grade</label>
                            <div>{{ $termAverage['letter_grade'] }}</div>
                        </div>
                    @endif
                    @if(!empty($report['class_position']))
                        <div class="ac-summary-item">
                            <label>Class position</label>
                            <div>{{ $report['class_position'] }} / {{ $report['students_on_roll'] ?? '—' }}</div>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
