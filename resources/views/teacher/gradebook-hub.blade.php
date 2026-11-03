@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-gradebook";
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .gb-hero-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .gb-class-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .gb-class-card {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        min-height: 148px;
    }

    .gb-class-card:hover {
        transform: translateY(-2px);
        border-color: rgba(34, 197, 94, 0.28);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.1);
    }

    .gb-class-card-side {
        flex: 0 0 220px;
        padding: 22px 20px;
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(99, 102, 241, 0.06));
        border-right: 1px solid #eef2f7;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
    }

    .gb-class-card-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 16px 20px;
        gap: 0;
    }

    .gb-class-card-actions {
        flex: 0 0 168px;
        padding: 16px 18px;
        border-left: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 10px;
        background: #fafafa;
    }

    @media (max-width: 991px) {
        .gb-class-card {
            flex-direction: column;
            min-height: 0;
        }

        .gb-class-card-side {
            flex: none;
            border-right: none;
            border-bottom: 1px solid #eef2f7;
        }

        .gb-class-card-actions {
            flex: none;
            flex-direction: row;
            border-left: none;
            border-top: 1px solid #f1f5f9;
        }

        .gb-class-card-actions .ac-action-pill {
            flex: 1;
        }
    }

    .gb-class-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .gb-ready-panel {
        position: relative;
        width: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
        padding: 0;
        background: transparent;
        border: none;
        overflow: visible;
        min-height: 0;
    }

    .gb-ready-head {
        flex: 0 0 150px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 0;
    }

    .gb-ready-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: #15803d;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .02em;
        align-self: flex-start;
        box-shadow: 0 4px 12px rgba(21, 128, 61, 0.2);
    }

    .gb-ready-title {
        font-size: 14px;
        font-weight: 700;
        color: #14532d;
        margin: 0;
    }

    .gb-ready-sub {
        font-size: 11px;
        color: #64748b;
        margin: 0;
        line-height: 1.4;
    }

    .gb-ready-grid {
        flex: 1;
        min-width: 0;
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
    }

    @media (max-width: 991px) {
        .gb-ready-panel {
            flex-direction: column;
            align-items: stretch;
        }

        .gb-ready-head {
            flex: none;
        }

        .gb-ready-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .gb-ready-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .gb-ready-tile {
        padding: 12px 8px;
        border-radius: 14px;
        background: linear-gradient(160deg, #f0fdf4 0%, #f8fafc 100%);
        border: 1px solid rgba(34, 197, 94, 0.14);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .gb-class-card:hover .gb-ready-tile {
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    .gb-ready-tile-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        margin-bottom: 6px;
    }

    .gb-ready-tile-icon.emerald { background: rgba(34, 197, 94, 0.14); color: #15803d; }
    .gb-ready-tile-icon.indigo  { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
    .gb-ready-tile-icon.amber   { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .gb-ready-tile-icon.rose    { background: rgba(244, 63, 94, 0.1); color: #be123c; }
    .gb-ready-tile-icon.teal    { background: rgba(20, 184, 166, 0.12); color: #0f766e; }
    .gb-ready-tile-icon.violet  { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }

    .gb-ready-tile-value {
        display: block;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 2px;
    }

    .gb-ready-tile-value.text-sm {
        font-size: 0.95rem;
    }

    .gb-ready-tile-label {
        display: block;
        font-size: 10px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .04em;
        line-height: 1.3;
    }

    .gb-class-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .gb-class-card-actions .ac-action-pill {
        justify-content: center;
        padding: 12px 10px;
        font-size: 12px;
        white-space: nowrap;
    }

    .gb-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .gb-section-head h6 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .gb-empty {
        text-align: center;
        padding: 64px 28px;
        border: 2px dashed #e5e7eb;
        border-radius: 16px;
        background: linear-gradient(180deg, #fafafa, #fff);
    }

    .gb-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 16px;
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .gb-stats-panel {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        margin-bottom: 24px;
    }

    .gb-stats-grid {
        display: grid;
        grid-template-columns: 1.4fr repeat(3, 1fr);
        min-height: 168px;
    }

    @media (max-width: 1199px) {
        .gb-stats-grid {
            grid-template-columns: 1fr 1fr;
        }
        .gb-stats-session { grid-column: 1 / -1; }
    }

    @media (max-width: 575px) {
        .gb-stats-grid { grid-template-columns: 1fr; }
    }

    .gb-stats-session {
        position: relative;
        padding: 28px 30px;
        background: linear-gradient(135deg, #064e3b 0%, #15803d 45%, #22c55e 100%);
        color: #fff;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 16px;
    }

    .gb-stats-session::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -20px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .gb-stats-session::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: 30%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .gb-stats-session > * { position: relative; z-index: 1; }

    .gb-stats-session-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .85;
    }

    .gb-stats-session-term {
        font-size: clamp(1.5rem, 2.5vw, 2rem);
        font-weight: 700;
        line-height: 1.15;
        margin: 0;
    }

    .gb-stats-session-year {
        font-size: 14px;
        opacity: .9;
        margin: 0;
    }

    .gb-stats-session-foot {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .gb-stats-live {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 11px;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    .gb-stats-live-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #bbf7d0;
        box-shadow: 0 0 0 4px rgba(187, 247, 208, 0.25);
    }

    .gb-stats-metric {
        padding: 24px 22px;
        border-left: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 14px;
        transition: background 0.15s ease;
    }

    .gb-stats-metric:hover { background: #fafafa; }

    @media (max-width: 575px) {
        .gb-stats-metric { border-left: none; border-top: 1px solid #f1f5f9; }
    }

    .gb-stats-metric-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .gb-stats-metric-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin: 0 0 6px;
        line-height: 1.3;
    }

    .gb-stats-metric-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin: 0;
        color: #0f172a;
    }

    .gb-stats-metric-value.text-md {
        font-size: 1.15rem;
        line-height: 1.25;
    }

    .gb-stats-metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .gb-stats-metric-icon.emerald { background: rgba(34, 197, 94, 0.12); color: #15803d; }
    .gb-stats-metric-icon.indigo  { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
    .gb-stats-metric-icon.amber   { background: rgba(245, 158, 11, 0.12); color: #b45309; }

    .gb-stats-metric-note {
        font-size: 11px;
        color: #94a3b8;
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / Gradebook</span>
            </div>
        </div>
        <div class="gb-hero-actions">
            @if($period['year_name'])
                <span class="ac-pill ac-pill-indigo d-none d-md-inline-flex"><i class="ri-calendar-line"></i> {{ $period['year_name'] }} · {{ $period['term_name'] }}</span>
            @endif
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body py-16 px-24">
            @include('teacher.partials._academic-period-filter')
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:56px;height:56px;font-size:24px;background:rgba(34,197,94,.12);color:#15803d;"><i class="ri-bar-chart-grouped-line"></i></span>
            <div>
                <h5 class="fw-semibold mb-8">Gradebook Hub</h5>
                <p class="text-sm text-secondary-light mb-0">Review term averages, subject breakdowns, and print report cards for your homeroom classes.</p>
            </div>
        </div>
    </div>

    @php
        $avgStudentsPerClass = $stats['classes'] > 0
            ? round($stats['students'] / $stats['classes'])
            : 0;
    @endphp

    <div class="gb-stats-panel">
        <div class="gb-stats-grid">
            <div class="gb-stats-session">
                <div>
                    <span class="gb-stats-session-label"><i class="ri-calendar-check-line"></i> Active Session</span>
                    <h3 class="gb-stats-session-term mt-2 mb-2">{{ $period['term_name'] ?? 'No term selected' }}</h3>
                    <p class="gb-stats-session-year">{{ $period['year_name'] ?? 'Set academic year in school settings' }}</p>
                </div>
                <div class="gb-stats-session-foot">
                    <span class="gb-stats-live"><span class="gb-stats-live-dot"></span> Gradebook period</span>
                    @if($stats['classes'] > 0)
                        <span class="gb-stats-live"><i class="ri-home-smile-line"></i> {{ $stats['classes'] }} homeroom {{ Str::plural('class', $stats['classes']) }}</span>
                    @endif
                </div>
            </div>

            <div class="gb-stats-metric">
                <div class="gb-stats-metric-top">
                    <div>
                        <p class="gb-stats-metric-label">Homeroom Classes</p>
                        <p class="gb-stats-metric-value">{{ $stats['classes'] }}</p>
                    </div>
                    <span class="gb-stats-metric-icon emerald"><i class="ri-home-smile-line"></i></span>
                </div>
                <p class="gb-stats-metric-note">Classes you can open gradebooks for</p>
            </div>

            <div class="gb-stats-metric">
                <div class="gb-stats-metric-top">
                    <div>
                        <p class="gb-stats-metric-label">Total Students</p>
                        <p class="gb-stats-metric-value">{{ $stats['students'] }}</p>
                    </div>
                    <span class="gb-stats-metric-icon indigo"><i class="ri-group-line"></i></span>
                </div>
                <p class="gb-stats-metric-note">Active students across homeroom classes</p>
            </div>

            <div class="gb-stats-metric">
                <div class="gb-stats-metric-top">
                    <div>
                        <p class="gb-stats-metric-label">Avg. Class Size</p>
                        <p class="gb-stats-metric-value">{{ $stats['classes'] > 0 ? $avgStudentsPerClass : '—' }}</p>
                    </div>
                    <span class="gb-stats-metric-icon amber"><i class="ri-user-community-line"></i></span>
                </div>
                <p class="gb-stats-metric-note">Students per homeroom on average</p>
            </div>
        </div>
    </div>

    @if($homeroomClasses->isNotEmpty())
    <div class="gb-section-head">
        <div>
            <h6>Homeroom Gradebooks</h6>
            <p class="text-sm text-secondary-light mb-0">Open a class to review type averages, subject scores, and print report cards.</p>
        </div>
        <span class="ac-pill ac-pill-emerald">{{ $homeroomClasses->count() }} {{ Str::plural('class', $homeroomClasses->count()) }}</span>
    </div>
    <div class="gb-class-grid">
        @foreach($homeroomClasses as $class)
        @php
            $initials = strtoupper(substr($class->name, 0, 2));
            $preview = $class->gradebook_preview ?? null;
        @endphp
        <div class="gb-class-card">
            <div class="gb-class-card-side">
                <div class="ac-name-cell">
                    <span class="ac-avatar" style="width:48px;height:48px;font-size:18px;background:rgba(34,197,94,.12);color:#15803d;">{{ $initials }}</span>
                    <div>
                        <span class="d-block fw-semibold text-primary-600">{{ $class->name }}</span>
                        <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> Homeroom</span>
                    </div>
                </div>
                <div class="gb-class-meta">
                    @if($period['year_name'])
                        <span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> {{ $period['term_name'] }}</span>
                    @endif
                    <span class="ac-pill ac-pill-emerald"><i class="ri-group-line"></i> {{ $class->students_count }}</span>
                </div>
            </div>

            <div class="gb-class-card-main">
                <div class="gb-ready-panel">
                    <div class="gb-ready-head">
                        <span class="gb-ready-badge"><i class="ri-checkbox-circle-fill"></i> Active</span>
                        <p class="gb-ready-title">Term Gradebook Ready</p>
                        <p class="gb-ready-sub">{{ $period['term_name'] ?? 'This term' }}</p>
                    </div>

                    <div class="gb-ready-grid">
                        <div class="gb-ready-tile">
                            <span class="gb-ready-tile-icon emerald"><i class="ri-group-line"></i></span>
                            <span class="gb-ready-tile-value">{{ $class->students_count }}</span>
                            <span class="gb-ready-tile-label">Students</span>
                        </div>
                        <div class="gb-ready-tile">
                            <span class="gb-ready-tile-icon indigo"><i class="ri-book-open-line"></i></span>
                            <span class="gb-ready-tile-value">{{ $preview['subjects'] ?? '—' }}</span>
                            <span class="gb-ready-tile-label">Subjects</span>
                        </div>
                        <div class="gb-ready-tile">
                            <span class="gb-ready-tile-icon violet"><i class="ri-layout-grid-line"></i></span>
                            <span class="gb-ready-tile-value">{{ $preview['types'] ?? '—' }}</span>
                            <span class="gb-ready-tile-label">Types</span>
                        </div>
                        <div class="gb-ready-tile">
                            <span class="gb-ready-tile-icon amber"><i class="ri-file-list-3-line"></i></span>
                            <span class="gb-ready-tile-value">{{ $preview['tests'] ?? '—' }}</span>
                            <span class="gb-ready-tile-label">Tests</span>
                        </div>
                        <div class="gb-ready-tile">
                            <span class="gb-ready-tile-icon teal"><i class="ri-user-star-line"></i></span>
                            <span class="gb-ready-tile-value text-sm">{{ $preview ? ($preview['graded'].'/'.$preview['students']) : '—' }}</span>
                            <span class="gb-ready-tile-label">Graded</span>
                        </div>
                        <div class="gb-ready-tile">
                            <span class="gb-ready-tile-icon rose"><i class="ri-bar-chart-box-line"></i></span>
                            <span class="gb-ready-tile-value text-sm">{{ isset($preview['class_average']) && $preview['class_average'] !== null ? $preview['class_average'].'%' : '—' }}</span>
                            <span class="gb-ready-tile-label">Class Avg</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gb-class-card-actions">
                <a href="{{ route('teacher-class-gradebook', array_merge(['class' => $class], $periodQuery)) }}" class="ac-action-pill ac-action-pill-emerald">
                    <i class="ri-bar-chart-box-line"></i> Open
                </a>
                <a href="{{ route('teacher-class-report-cards-print', array_merge(['class' => $class], $periodQuery)) }}" target="_blank" class="ac-action-pill ac-action-pill-indigo">
                    <i class="ri-printer-line"></i> Print
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="gb-empty">
        <span class="gb-empty-icon"><i class="ri-home-smile-line"></i></span>
        <h6 class="fw-semibold mb-8">No homeroom classes assigned</h6>
        <p class="text-secondary-light mb-0">Gradebook and report cards are available to homeroom class teachers only.</p>
    </div>
    @endif
</div>
@endsection
