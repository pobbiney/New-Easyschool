@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-gradebook";
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $gradePillClass = fn (?string $grade) => match (strtoupper(trim((string) $grade)[0] ?? '')) {
        'A' => 'ac-pill-grade-a', 'B' => 'ac-pill-grade-b', 'C' => 'ac-pill-grade-c',
        'D' => 'ac-pill-grade-d', 'F' => 'ac-pill-grade-f', default => 'ac-pill-slate',
    };
    $scoreTone = fn (?float $percentage) => match (true) {
        $percentage === null => 'gb-score-empty',
        $percentage >= 80 => 'gb-score-high',
        $percentage >= 60 => 'gb-score-mid',
        default => 'gb-score-low',
    };
    $studentInitials = fn ($student) => strtoupper(substr($student->firstname ?? '', 0, 1) . substr($student->surname ?? '', 0, 1));
    $courseSummaries = $gradebook['course_summaries'];
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .gb-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .gb-subject-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #fafafa, #fff);
    }

    .gb-subject-tab {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .gb-subject-tab:hover,
    .gb-subject-tab.is-active {
        border-color: rgba(34, 197, 94, 0.35);
        background: rgba(34, 197, 94, 0.08);
        color: #15803d;
    }

    .gb-subject-panel { display: none; }
    .gb-subject-panel.is-active { display: block; }

    .gb-table-wrap {
        overflow: auto;
        max-height: 520px;
        -webkit-overflow-scrolling: touch;
    }

    .gb-grade-table {
        margin: 0;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .gb-grade-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f9fafb;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        padding: 12px 14px;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .gb-grade-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }

    .gb-grade-table tbody tr:hover td {
        background: rgba(34, 197, 94, 0.03);
    }

    .gb-sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
        background: #fff;
        min-width: 220px;
        box-shadow: 1px 0 0 #f3f4f6;
    }

    .gb-grade-table thead th.gb-sticky-col {
        z-index: 3;
        background: #f9fafb;
    }

    .gb-grade-table tbody tr:hover td.gb-sticky-col {
        background: #f6fef9;
    }

    .gb-assessment-head {
        min-width: 96px;
        text-align: center;
    }

    .gb-assessment-head .gb-assessment-type {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #1a7a70;
        text-transform: none;
        letter-spacing: 0;
        margin: 0 auto 4px;
    }

    .gb-assessment-head .gb-assessment-title {
        display: block;
        font-size: 10px;
        font-weight: 600;
        color: #6b7280;
        text-transform: none;
        letter-spacing: 0;
        max-width: 110px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin: 0 auto 4px;
    }

    .gb-assessment-head .gb-assessment-meta {
        font-size: 10px;
        font-weight: 600;
        color: #9ca3af;
        text-transform: none;
        letter-spacing: 0;
    }

    .gb-type-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .gb-score {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 52px;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .gb-score-high { background: rgba(34, 197, 94, 0.12); color: #15803d; }
    .gb-score-mid  { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .gb-score-low  { background: rgba(239, 68, 68, 0.1);  color: #b91c1c; }
    .gb-score-empty { color: #cbd5e1; font-weight: 500; }

    .gb-term-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 14px;
        padding: 20px;
    }

    .gb-term-card {
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 16px;
        background: #fff;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .gb-term-card:hover {
        border-color: rgba(34, 197, 94, 0.2);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    .gb-term-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .gb-progress {
        height: 8px;
        border-radius: 999px;
        background: #eef2f7;
        overflow: hidden;
    }

    .gb-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #22c55e, #25A194);
        transition: width 0.2s ease;
    }

    .gb-empty-subject {
        text-align: center;
        padding: 48px 24px;
        color: #94a3b8;
    }

    .gb-empty-subject i {
        font-size: 36px;
        display: block;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">GRADEBOOK</h1>
            <div>
                <a href="{{ route('teacher-gradebook') }}" class="text-secondary-light hover-text-primary hover-underline">Gradebook</a>
                <span class="text-secondary-light"> / {{ $schoolClass->name }}</span>
            </div>
        </div>
        <a href="{{ route('teacher-class-report-cards-print', array_merge(['class' => $schoolClass], $periodQuery)) }}" target="_blank" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <i class="ri-printer-line"></i> Print Report Cards
        </a>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:56px;height:56px;font-size:22px;background:rgba(34,197,94,.12);color:#15803d;">{{ strtoupper(substr($schoolClass->name, 0, 2)) }}</span>
            <div>
                <h5 class="fw-semibold mb-8">{{ $schoolClass->name }} — Term Gradebook</h5>
                <div class="d-flex flex-wrap gap-2">
                    <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> Homeroom</span>
                    @if($period['year_name'])<span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> {{ $period['year_name'] }} · {{ $period['term_name'] }}</span>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Students</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['students'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-group-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Subjects</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['subjects'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Assessments</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['assessments'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Class Average</p>
                        <h4 class="fw-semibold mb-0 text-success-600">
                            @if($stats['class_average'] !== null)
                                {{ number_format($stats['class_average'], 0) }}%
                            @else
                                —
                            @endif
                        </h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-bar-chart-box-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ac-list-wrapper mb-24">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Subject Breakdown</h6>
                <p class="text-sm text-secondary-light mb-0">Scores are averaged per assessment type (e.g. all CAT 2 tests combined). Final marks = (class type avg ÷ 2) + (examination type avg ÷ 2).</p>
            </div>
            <input type="text" id="gbStudentSearch" class="form-control radius-4" placeholder="Search students..." style="min-width:220px;max-width:280px;">
        </div>

        @if($courseSummaries->isNotEmpty())
        <div class="gb-subject-tabs">
            @foreach($courseSummaries as $index => $summary)
                <button type="button" class="gb-subject-tab @if($index === 0) is-active @endif" data-subject-panel="subject-{{ $index }}">
                    {{ $summary['course_name'] }}
                    <span class="text-secondary-light">({{ $summary['type_columns']->count() }} types)</span>
                </button>
            @endforeach
        </div>

        @foreach($courseSummaries as $index => $summary)
        <div class="gb-subject-panel @if($index === 0) is-active @endif" id="subject-{{ $index }}">
            <div class="gb-toolbar">
                <div>
                    <span class="fw-semibold d-block">{{ $summary['course_name'] }}</span>
                    <span class="text-sm text-secondary-light">{{ $summary['assessments']->count() }} test{{ $summary['assessments']->count() === 1 ? '' : 's' }} across {{ $summary['type_columns']->count() }} type{{ $summary['type_columns']->count() === 1 ? '' : 's' }} · {{ $period['term_name'] ?? 'this term' }}</span>
                    @if($summary['type_columns']->isNotEmpty())
                        <div class="gb-type-legend mt-2">
                            @foreach($summary['type_columns'] as $column)
                                <span class="ac-pill ac-pill-slate" title="{{ $column['type']->categoryLabel() }}">
                                    {{ $column['type']->name }}
                                    @if($column['assessment_count'] > 1)
                                        × {{ $column['assessment_count'] }} tests
                                    @endif
                                    · / {{ number_format($column['type']->total_score, 0) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="gb-table-wrap">
                <table class="table gb-grade-table mb-0">
                    <thead>
                        <tr>
                            <th class="gb-sticky-col">Student</th>
                            @foreach($summary['type_columns'] as $column)
                                @php
                                    $type = $column['type'];
                                    $breakdownTitles = $column['assessments']->pluck('title')->implode(', ');
                                @endphp
                                <th class="gb-assessment-head" title="{{ $type->name }} · {{ $type->categoryLabel() }} · {{ $column['assessment_count'] }} test{{ $column['assessment_count'] === 1 ? '' : 's' }} · Max {{ number_format($type->total_score, 0) }}@if($breakdownTitles) · {{ $breakdownTitles }}@endif">
                                    <span class="gb-assessment-type">{{ $type->name }}</span>
                                    @if($column['assessment_count'] > 1)
                                        <span class="gb-assessment-title">{{ $column['assessment_count'] }} tests avg</span>
                                    @else
                                        <span class="gb-assessment-title">{{ $column['assessments']->first()?->title }}</span>
                                    @endif
                                    <span class="gb-assessment-meta">/ {{ number_format($type->total_score, 0) }}</span>
                                </th>
                            @endforeach
                            <th>Average</th>
                            <th>Grade</th>
                            <th>Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['students'] as $row)
                        <tr class="gb-student-row" data-search="{{ strtolower($row['student']->full_name) }}">
                            <td class="gb-sticky-col">
                                <div class="ac-name-cell">
                                    <span class="ac-avatar" style="width:36px;height:36px;font-size:12px;">{{ $studentInitials($row['student']) }}</span>
                                    <span class="fw-semibold">{{ $row['student']->full_name }}</span>
                                </div>
                            </td>
                            @foreach($summary['type_columns'] as $column)
                                @php
                                    $aggregate = $row['type_scores'][$column['type']->slug] ?? null;
                                    $percentage = $aggregate['percentage'] ?? null;
                                @endphp
                                <td class="text-center">
                                    @if($aggregate)
                                        @php
                                            $tooltip = collect($aggregate['breakdown'] ?? [])
                                                ->map(fn ($item) => $item['title'].': '.number_format($item['score'], 1))
                                                ->implode(' · ');
                                        @endphp
                                        <span class="gb-score {{ $scoreTone($percentage) }}" @if($tooltip) title="{{ $tooltip }}" @endif>
                                            {{ number_format($aggregate['average_score'], 1) }}
                                        </span>
                                        <span class="d-block text-xs text-secondary-light mt-1">/ {{ number_format($aggregate['total_score'], 0) }}</span>
                                    @else
                                        <span class="gb-score-empty">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if($row['average_percentage'] !== null)
                                    <span class="ac-pill ac-pill-violet">{{ number_format($row['average_percentage'], 0) }}%</span>
                                @else
                                    <span class="gb-score-empty">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row['letter_grade'])
                                    <span class="ac-pill {{ $gradePillClass($row['letter_grade']) }}">{{ $row['letter_grade'] }}</span>
                                @else
                                    <span class="gb-score-empty">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('teacher-report-card-print', array_merge(['student' => $row['student']], $periodQuery)) }}" target="_blank" class="ac-action-pill ac-action-pill-indigo">
                                    <i class="ri-printer-line"></i> Print
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        @else
        <div class="gb-empty-subject">
            <i class="ri-file-list-3-line"></i>
            <p class="mb-0">No assessments with scores for {{ $period['term_name'] ?? 'this term' }}. Check the selected term matches where you entered marks.</p>
        </div>
        @endif
    </div>

    <div class="ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-4">Term Overview</h6>
            <p class="text-sm text-secondary-light mb-0">Overall term averages across all subjects · {{ $stats['graded_students'] }} of {{ $stats['students'] }} students graded · 50% class + 50% examination</p>
        </div>
        <div class="gb-term-grid">
            @foreach($gradebook['term_averages'] as $row)
            <div class="gb-term-card gb-student-row" data-search="{{ strtolower($row['student']->full_name) }}">
                <div class="gb-term-top">
                    <div class="ac-name-cell">
                        <span class="ac-avatar" style="width:38px;height:38px;font-size:12px;">{{ $studentInitials($row['student']) }}</span>
                        <div>
                            <span class="fw-semibold d-block">{{ $row['student']->full_name }}</span>
                            <span class="text-xs text-secondary-light">Term average</span>
                        </div>
                    </div>
                    <div class="text-end">
                        @if($row['letter_grade'])
                            <span class="ac-pill {{ $gradePillClass($row['letter_grade']) }}">{{ $row['letter_grade'] }}</span>
                        @endif
                    </div>
                </div>
                @if($row['average_percentage'] !== null)
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-8">
                        <span class="text-sm fw-semibold text-success-600">{{ number_format($row['average_percentage'], 0) }}%</span>
                        <a href="{{ route('teacher-report-card-print', $row['student']) }}" target="_blank" class="ac-action-pill ac-action-pill-indigo">
                            <i class="ri-printer-line"></i> Report
                        </a>
                    </div>
                    <div class="gb-progress">
                        <div class="gb-progress-bar" style="width: {{ min(100, $row['average_percentage']) }}%;"></div>
                    </div>
                @else
                    <p class="text-sm text-secondary-light mb-0">No grades recorded yet.</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const tabs = document.querySelectorAll('.gb-subject-tab');
    const panels = document.querySelectorAll('.gb-subject-panel');
    const search = document.getElementById('gbStudentSearch');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = this.dataset.subjectPanel;
            tabs.forEach(function (t) { t.classList.remove('is-active'); });
            panels.forEach(function (p) { p.classList.remove('is-active'); });
            this.classList.add('is-active');
            const panel = document.getElementById(target);
            if (panel) panel.classList.add('is-active');
        });
    });

    function applySearch() {
        const q = (search?.value || '').trim().toLowerCase();
        document.querySelectorAll('.gb-student-row').forEach(function (row) {
            const match = !q || (row.dataset.search || '').includes(q);
            row.style.display = match ? '' : 'none';
        });
    }

    search?.addEventListener('input', applySearch);
})();
</script>
@endsection
