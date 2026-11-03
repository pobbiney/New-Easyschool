@php
    $pageName = 'reports';
    $subpageName = $report['url'];
    $query = request()->query();
    $mix = $report['mix'] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'rate' => 0];
    $pct = fn (int $n) => $mix['total'] ? round(($n / $mix['total']) * 100) : 0;
    $groups = collect($report['rows'])->groupBy('date');
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('reports.partials._styles')
<style>
    .att-tag-present { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .att-tag-absent { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .att-tag-rate { background: linear-gradient(135deg, #25A194, #0f766e); }
    .att-mix-present { background: linear-gradient(90deg, #4ade80, #16a34a); }
    .att-mix-absent { background: linear-gradient(90deg, #fb7185, #e11d48); }
    .att-mix-late { background: linear-gradient(90deg, #fbbf24, #d97706); }
    .att-mix-excused { background: linear-gradient(90deg, #a78bfa, #7c3aed); }
    .att-mix-legend { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 10px; font-size: 12px; font-weight: 700; }
    .att-mix-legend .present { color: #15803d; }
    .att-mix-legend .late { color: #b45309; }
    .att-mix-legend .excused { color: #6d28d9; }
    .att-mix-legend .absent { color: #be123c; }
    .att-group-head {
        background: linear-gradient(90deg, #e0e7ff, #ccfbf1);
        padding: 10px 24px; font-size: 12px; font-weight: 800; color: #3730a3;
        border-bottom: 1px solid #c7d2fe; letter-spacing: .02em;
    }
    .att-pill-present { background: linear-gradient(135deg, #22c55e, #15803d); }
    .att-pill-absent { background: linear-gradient(135deg, #f43f5e, #be123c); }
    .att-pill-late { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .att-pill-excused { background: linear-gradient(135deg, #818cf8, #6d28d9); }
    .att-row-present { background: #f0fdf4; }
    .att-row-absent { background: #fff1f2; }
    .att-row-late { background: #fffbeb; }
    .att-row-excused { background: #eef2ff; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Reports',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Class attendance', 'active' => true],
        ],
        'title' => 'Class attendance',
        'subtitle' => 'Filter a class and date range, then print or export the register.',
        'actions' => view('reports.partials._export-buttons', ['report' => $report, 'query' => $query])->render(),
    ])

    <div class="rpt-hero d-flex align-items-start gap-16">
        <span class="rpt-hero-icon"><i class="ri-calendar-check-line"></i></span>
        <div>
            <div class="rpt-hero-title">Daily class register</div>
            <p class="text-sm text-secondary-light mb-0" style="max-width:620px;">
                Present, absent, late, and excused marks across classes. Use the filters, then export for parents, inspectors, or the office file.
            </p>
            <div class="rpt-hero-tags">
                <span class="rpt-hero-tag att-tag-present"><i class="ri-checkbox-circle-line"></i> Present {{ $pct($mix['present']) }}%</span>
                <span class="rpt-hero-tag att-tag-absent"><i class="ri-close-circle-line"></i> Absent {{ $pct($mix['absent']) }}%</span>
                <span class="rpt-hero-tag att-tag-rate"><i class="ri-time-line"></i> In school {{ $mix['rate'] }}%</span>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-6 col-xl">
            <div class="card shadow-1 radius-8 gradient-bg-end-4 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-12">
                        <div class="rpt-kpi-icon bg-primary-600"><i class="ri-file-list-3-line"></i></div>
                        <p class="fw-medium text-primary-light mb-0">Records</p>
                    </div>
                    <h4 class="mb-0 fw-bold">{{ number_format($mix['total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card shadow-1 radius-8 gradient-bg-end-5 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-12">
                        <div class="rpt-kpi-icon bg-success-600"><i class="ri-user-follow-line"></i></div>
                        <p class="fw-medium text-primary-light mb-0">Present</p>
                    </div>
                    <h4 class="mb-0 fw-bold">{{ number_format($mix['present']) }}</h4>
                    <p class="fw-medium text-sm text-primary-light mt-8 mb-0">{{ $pct($mix['present']) }}% of marks</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card shadow-1 radius-8 gradient-bg-end-8 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-12">
                        <div class="rpt-kpi-icon bg-danger-600"><i class="ri-user-unfollow-line"></i></div>
                        <p class="fw-medium text-primary-light mb-0">Absent</p>
                    </div>
                    <h4 class="mb-0 fw-bold">{{ number_format($mix['absent']) }}</h4>
                    <p class="fw-medium text-sm text-primary-light mt-8 mb-0">{{ $pct($mix['absent']) }}% of marks</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card shadow-1 radius-8 gradient-bg-end-1 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-12">
                        <div class="rpt-kpi-icon bg-warning-600"><i class="ri-time-line"></i></div>
                        <p class="fw-medium text-primary-light mb-0">Late</p>
                    </div>
                    <h4 class="mb-0 fw-bold">{{ number_format($mix['late']) }}</h4>
                    <p class="fw-medium text-sm text-primary-light mt-8 mb-0">{{ $pct($mix['late']) }}% of marks</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="card shadow-1 radius-8 gradient-bg-end-3 h-100">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-12">
                        <div class="rpt-kpi-icon bg-purple-600"><i class="ri-heart-pulse-line"></i></div>
                        <p class="fw-medium text-primary-light mb-0">Excused</p>
                    </div>
                    <h4 class="mb-0 fw-bold">{{ number_format($mix['excused']) }}</h4>
                    <p class="fw-medium text-sm text-primary-light mt-8 mb-0">{{ $pct($mix['excused']) }}% of marks</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rpt-board mb-24">
        <form method="GET" action="{{ route($report['url']) }}" class="rpt-filter">
            <div class="row g-3 align-items-end">
                @foreach($report['filters'] as $filter)
                    <div class="col-sm-6 col-xl-2">
                        <label class="text-sm fw-semibold d-block mb-8">{{ $filter['label'] }}</label>
                        @if(($filter['type'] ?? 'select') === 'date')
                            <input type="date" class="form-control" name="{{ $filter['name'] }}" value="{{ $report['values'][$filter['name']] ?? '' }}">
                        @else
                            <select class="form-control form-select" name="{{ $filter['name'] }}">
                                @foreach(($filter['options'] ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected((string) ($report['values'][$filter['name']] ?? '') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endforeach
                <div class="col-sm-6 col-xl-2">
                    <button type="submit" class="btn btn-primary-600 w-100"><i class="ri-search-line"></i> Apply</button>
                </div>
            </div>
        </form>

        @if($mix['total'] > 0)
            <div class="rpt-mix">
                <div class="d-flex justify-content-between align-items-center mb-10">
                    <span class="text-sm fw-semibold">Attendance mix</span>
                    <span class="ac-pill ac-pill-teal">In school {{ $mix['rate'] }}%</span>
                </div>
                <div class="rpt-mix-bar">
                    <span class="att-mix-present" style="width: {{ $pct($mix['present']) }}%"></span>
                    <span class="att-mix-late" style="width: {{ $pct($mix['late']) }}%"></span>
                    <span class="att-mix-excused" style="width: {{ $pct($mix['excused']) }}%"></span>
                    <span class="att-mix-absent" style="width: {{ $pct($mix['absent']) }}%"></span>
                </div>
                <div class="att-mix-legend">
                    <span class="present"><i class="rpt-dot att-mix-present"></i>Present</span>
                    <span class="late"><i class="rpt-dot att-mix-late"></i>Late</span>
                    <span class="excused"><i class="rpt-dot att-mix-excused"></i>Excused</span>
                    <span class="absent"><i class="rpt-dot att-mix-absent"></i>Absent</span>
                </div>
            </div>
        @endif

        @if(count($report['rows']) === 0)
            <div class="rpt-empty">
                <div class="rpt-empty-icon"><i class="ri-calendar-check-line"></i></div>
                <h6 class="fw-semibold mb-6">No attendance marks yet</h6>
                <p class="text-sm text-secondary-light mb-0 mx-auto" style="max-width:420px;">
                    Choose a class and date range, or wait until a class teacher has taken the register.
                </p>
            </div>
        @else
            <div class="rpt-scroll">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $date => $rows)
                            <tr>
                                <td colspan="4" class="att-group-head p-0">
                                    <div class="att-group-head">{{ $date }}@if($rows->first()['weekday'] ?? null) · {{ $rows->first()['weekday'] }}@endif · {{ $rows->count() }} {{ $rows->count() === 1 ? 'mark' : 'marks' }}</div>
                                </td>
                            </tr>
                            @foreach($rows as $row)
                                @php $tone = abs(crc32((string) ($row['student'] ?? ''))) % 6; @endphp
                                <tr class="att-row-{{ $row['status_key'] ?? 'present' }}">
                                    <td>
                                        <div class="rpt-person">
                                            <span class="rpt-avatar rpt-tone-{{ $tone }}">{{ $row['initials'] ?? 'ST' }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $row['student'] }}</div>
                                                @if(!empty($row['student_id']))
                                                    <div class="text-sm text-secondary-light">{{ $row['student_id'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="rpt-pill rpt-pill-teal">{{ $row['class'] }}</span></td>
                                    <td>
                                        <div class="fw-semibold">{{ $row['date'] }}</div>
                                        <div class="text-sm text-secondary-light">{{ $row['weekday'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="rpt-pill att-pill-{{ $row['status_key'] ?? 'present' }}">
                                            {{ $row['status'] }}
                                        </span>
                                        @if(!empty($row['notes']))
                                            <div class="text-sm text-secondary-light mt-4">{{ $row['notes'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-24 py-16 text-sm text-secondary-light">{{ number_format(count($report['rows'])) }} rows · generated {{ $report['printed_at'] }}</div>
        @endif
    </div>
</div>
@endsection
