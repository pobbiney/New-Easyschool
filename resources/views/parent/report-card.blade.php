@extends('layouts.parent')

@section('title', 'Report Card — '.$student->full_name)
@section('page-title', 'Report Card')
@section('page-subtitle', $student->full_name)

@section('css')
<style>
    .rc {
        --r-teal: #25A194;
        --r-teal-d: #0f766e;
        --r-ink: #0f172a;
        --r-muted: #64748b;
        --r-border: #e2e8f0;
        --r-green: #10b981;
        --r-red: #ef4444;
        --r-amber: #f59e0b;
        --r-blue: #3b82f6;
    }

    .rc-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .rc-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .rc-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .rc-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }
    .rc-hero-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .rc-hero-title {
        font-size: clamp(1.6rem, 4vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        margin-bottom: 6px;
    }
    .rc-hero-period {
        font-size: 14px;
        opacity: .9;
        font-weight: 600;
    }
    .rc-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .rc-hero-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .rc-hero-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .rc-student-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        padding: 10px 16px 10px 10px;
        border: 1px solid rgba(255,255,255,.2);
    }
    .rc-student-chip img,
    .rc-student-chip .av {
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
    .rc-student-chip b { display: block; font-size: 14px; }
    .rc-student-chip small { opacity: .8; font-size: 12px; }

    .rc-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .rc-bar-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .rc-bar select {
        border: 1px solid var(--r-border);
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        color: var(--r-ink);
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .rc-print-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 18px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--r-teal-d), var(--r-teal));
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(37,161,148,.28);
        transition: transform .12s, box-shadow .12s;
    }
    .rc-print-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(37,161,148,.35);
        color: #fff;
    }

    .rc-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .rc-stat {
        background: #fff;
        border: 1px solid var(--r-border);
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .rc-stat-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--r-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }
    .rc-stat-value {
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
    }
    .rc-stat-value.green { color: var(--r-green); }
    .rc-stat-value.red { color: var(--r-red); }
    .rc-stat-value.amber { color: var(--r-amber); }
    .rc-stat-value.blue { color: var(--r-blue); }
    .rc-stat-value.teal { color: var(--r-teal-d); }

    .rc-card {
        background: #fff;
        border: 1px solid var(--r-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
        margin-bottom: 20px;
    }
    .rc-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--r-border);
        background: #fafafa;
    }
    .rc-card-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--r-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rc-card-head h3 i { color: var(--r-teal); }

    .rc-table-wrap { overflow-x: auto; }
    .rc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .rc-table thead th {
        padding: 14px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--r-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--r-border);
        white-space: nowrap;
    }
    .rc-table thead th.text-end { text-align: right; }
    .rc-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .rc-table tbody tr:hover { background: #fafafa; }
    .rc-table tbody tr:last-child td { border-bottom: none; }
    .rc-table .subject { font-weight: 700; color: var(--r-ink); }
    .rc-table .text-end { text-align: right; }
    .rc-table .score {
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
    .rc-grade {
        display: inline-block;
        min-width: 36px;
        text-align: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #e6f7f5;
        color: var(--r-teal-d);
    }
    .rc-position {
        font-weight: 700;
        color: var(--r-muted);
    }

    .rc-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 20px;
        background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
        border-top: 1px solid #ccfbf1;
    }
    .rc-summary-item {
        flex: 1;
        min-width: 160px;
    }
    .rc-summary-item label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--r-muted);
        margin-bottom: 4px;
    }
    .rc-summary-item div {
        font-size: 18px;
        font-weight: 800;
        color: var(--r-teal-d);
    }
    .rc-promotion {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
    }
    .rc-promotion.yes { background: #dcfce7; color: #166534; }
    .rc-promotion.no { background: #fee2e2; color: #991b1b; }
    .rc-promotion.pending { background: #f1f5f9; color: var(--r-muted); }

    .rc-empty {
        padding: 48px 24px;
        text-align: center;
    }
    .rc-empty i {
        font-size: 48px;
        color: #cbd5e1;
        display: block;
        margin-bottom: 12px;
    }
    .rc-empty h3 {
        font-size: 18px;
        font-weight: 800;
        color: var(--r-ink);
        margin: 0 0 6px;
    }
    .rc-empty p {
        color: var(--r-muted);
        margin: 0;
        font-size: 14px;
        max-width: 420px;
        margin-inline: auto;
    }

    .rc-note {
        padding: 16px 20px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid var(--r-border);
        font-size: 13px;
        color: var(--r-muted);
        line-height: 1.5;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .rc-note i { color: var(--r-teal); font-size: 18px; margin-top: 1px; }

    @media (max-width: 640px) {
        .rc-bar { flex-direction: column; align-items: stretch; }
        .rc-bar-filters { flex-direction: column; }
        .rc-bar select { width: 100%; }
        .rc-print-btn { width: 100%; justify-content: center; }
    }
</style>
@endsection

@section('content')
@php
    $initials = strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1));
    $periodLabel = trim(($period['term_name'] ?? '').' '.($period['year_name'] ?? '')) ?: 'Select period';
    $termAverage = $report['term_average'] ?? null;
    $subjectGrades = $report['subject_grades'] ?? collect();
    $attendance = $report['attendance'] ?? null;
    $hasGrades = $subjectGrades->isNotEmpty() && $subjectGrades->contains(fn ($s) => $s['total_score'] !== null);
@endphp

<div class="rc">
    <div class="rc-hero">
        <div class="rc-hero-inner">
            <div>
                <div class="rc-hero-label">Terminal report</div>
                <div class="rc-hero-title">{{ $student->full_name }}</div>
                <div class="rc-hero-period">{{ $periodLabel }}</div>
                @if($report)
                    <div class="rc-hero-meta">
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
            <div class="rc-student-chip">
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

    <form method="GET" id="rcForm" class="rc-bar">
        <div class="rc-bar-filters">
            <select name="academic_year_id" onchange="document.getElementById('rcForm').submit()">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($period['year_id'] == $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
            <select name="academic_term_id" onchange="document.getElementById('rcForm').submit()">
                @foreach($academicTerms as $term)
                    <option value="{{ $term->id }}" @selected($period['term_id'] == $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                formaction="{{ route('parent.report-card.print', $student) }}"
                formtarget="_blank"
                class="rc-print-btn">
            <i class="ri-printer-line"></i> Print official report card
        </button>
    </form>

    @if($attendance && $attendance['total_days'] > 0)
        <div class="rc-stats">
            <div class="rc-stat">
                <div class="rc-stat-label">Present</div>
                <div class="rc-stat-value green">{{ $attendance['present'] }}</div>
            </div>
            <div class="rc-stat">
                <div class="rc-stat-label">Absent</div>
                <div class="rc-stat-value red">{{ $attendance['absent'] }}</div>
            </div>
            <div class="rc-stat">
                <div class="rc-stat-label">Late</div>
                <div class="rc-stat-value amber">{{ $attendance['late'] }}</div>
            </div>
            <div class="rc-stat">
                <div class="rc-stat-label">Total days</div>
                <div class="rc-stat-value teal">{{ $attendance['total_days'] }}</div>
            </div>
        </div>
    @endif

    <div class="rc-card">
        <div class="rc-card-head">
            <h3><i class="ri-book-read-line"></i> Subject performance</h3>
        </div>

        @if(!$report)
            <div class="rc-empty">
                <i class="ri-calendar-line"></i>
                <h3>Select a period</h3>
                <p>Choose an academic year and term above to view grades and print the report card.</p>
            </div>
        @elseif(!$hasGrades)
            <div class="rc-empty">
                <i class="ri-file-search-line"></i>
                <h3>No grades yet</h3>
                <p>Grades for {{ $periodLabel }} have not been published yet. Check back later or contact the school.</p>
            </div>
        @else
            <div class="rc-table-wrap">
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="text-end">Class score</th>
                            <th class="text-end">Exam score</th>
                            <th class="text-end">Total</th>
                            <th>Grade</th>
                            <th class="text-end">Position</th>
                            <th>Remark</th>
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
                                        <span class="rc-grade">{{ $subject['letter_grade'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!empty($subject['position']))
                                        <span class="rc-position">{{ $subject['position'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $subject['remark'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($report)
                <div class="rc-summary">
                    @if($termAverage && $termAverage['average_percentage'] !== null)
                        <div class="rc-summary-item">
                            <label>Term average</label>
                            <div>{{ number_format($termAverage['average_percentage'], 1) }}%</div>
                        </div>
                    @endif
                    @if(!empty($report['aggregate_total_score']))
                        <div class="rc-summary-item">
                            <label>Aggregate score</label>
                            <div>{{ $report['aggregate_total_score'] }}</div>
                        </div>
                    @endif
                    @if(isset($report['is_promoted']))
                        <div class="rc-summary-item">
                            <label>Promotion status</label>
                            <div>
                                @if($report['is_promoted'] === true)
                                    <span class="rc-promotion yes"><i class="ri-checkbox-circle-fill"></i> Promoted</span>
                                @elseif($report['is_promoted'] === false)
                                    <span class="rc-promotion no"><i class="ri-close-circle-fill"></i> Not promoted</span>
                                @else
                                    <span class="rc-promotion pending">{{ $report['promotion_label'] ?? 'Pending' }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if(!empty($report['promoted_to']))
                        <div class="rc-summary-item">
                            <label>Promoted to</label>
                            <div>{{ $report['promoted_to'] }}</div>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>

    <div class="rc-note">
        <i class="ri-information-line"></i>
        <div>
            This page shows a summary of your child's results. Use <strong>Print official report card</strong> to open the school's formal terminal report in a new tab for printing or saving as PDF.
        </div>
    </div>
</div>
@endsection
