@php
    $pageName = "dashboard";
    $subpageName = "";
    $hasWork = count($kpis) > 0 || count($shortcuts) > 0 || count($charts) > 0 || $isTeacher;
    $kpiSkins = [
        ['grad' => 'gradient-bg-end-1', 'icon' => 'bg-warning-600'],
        ['grad' => 'gradient-bg-end-2', 'icon' => 'bg-blue-600'],
        ['grad' => 'gradient-bg-end-3', 'icon' => 'bg-purple-600'],
        ['grad' => 'gradient-bg-end-4', 'icon' => 'bg-primary-600'],
        ['grad' => 'gradient-bg-end-5', 'icon' => 'bg-success-600'],
        ['grad' => 'gradient-bg-end-6', 'icon' => 'bg-cyan-600'],
    ];
    $shortcutSkins = [
        'teal' => ['grad' => 'gradient-bg-end-4', 'icon' => 'bg-primary-600'],
        'indigo' => ['grad' => 'gradient-bg-end-2', 'icon' => 'bg-blue-600'],
        'sky' => ['grad' => 'gradient-bg-end-6', 'icon' => 'bg-cyan-600'],
        'amber' => ['grad' => 'gradient-bg-end-1', 'icon' => 'bg-warning-600'],
        'violet' => ['grad' => 'gradient-bg-end-3', 'icon' => 'bg-purple-600'],
        'rose' => ['grad' => 'gradient-bg-end-1', 'icon' => 'bg-danger-600'],
        'orange' => ['grad' => 'gradient-bg-end-1', 'icon' => 'bg-warning-600'],
        'emerald' => ['grad' => 'gradient-bg-end-5', 'icon' => 'bg-success-600'],
        'slate' => ['grad' => 'gradient-bg-end-2', 'icon' => 'bg-neutral-600'],
    ];
    $donutCharts = collect($charts ?? [])->where('type', 'donut')->values();
    $otherCharts = collect($charts ?? [])->where('type', '!=', 'donut')->values();
    $sideChart = $donutCharts->first() ?: $otherCharts->first();
    $mainCharts = collect($charts ?? [])->reject(fn ($c) => ($sideChart['id'] ?? null) && $c['id'] === $sideChart['id'])->values();
@endphp
@extends('layouts.app')

@section('css')
<style>
    .dash-kpi-icon,
    .dash-action-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        flex-shrink: 0;
    }
    .dash-action-card {
        display: block;
        text-decoration: none;
        color: inherit;
        height: 100%;
    }
    .dash-action-card:hover { color: inherit; }
    .dash-empty { text-align: center; padding: 48px 16px; color: #64748b; }
    .dash-empty i { font-size: 34px; color: #25A194; display: block; margin-bottom: 8px; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h6 class="fw-semibold mb-0">Dashboard</h6>
            <p class="text-neutral-600 mt-4 mb-0">{{ $greeting }}, {{ $displayName }} · {{ $heroLine }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-8">
            <span class="badge bg-primary-600">{{ $roleName }}</span>
            @if($isTeacher)
                <span class="badge bg-purple-600">Teacher</span>
            @endif
            @if(! empty($period['year_name']))
                <span class="badge bg-info-600">{{ $period['year_name'] }} · {{ $period['term_name'] }}</span>
            @endif
        </div>
    </div>

    @if(! $hasWork)
        <div class="card radius-8">
            <div class="dash-empty">
                <i class="ri-shield-user-line"></i>
                No modules are assigned to this account yet. Ask an administrator to grant menu access.
            </div>
                    </div>
    @else
        <div class="row gy-4">
            <div class="{{ $sideChart ? 'col-xxl-8' : 'col-12' }}">
                @if(count($kpis))
                    <div class="row gy-4">
                        @foreach($kpis as $kpi)
                            @php $skin = $kpiSkins[$loop->index % 6]; @endphp
            <div class="col-xxl-4 col-sm-6">
                                <div class="card shadow-1 radius-8 {{ $skin['grad'] }} h-100">
                <div class="card-body p-20">
                  <div class="d-flex flex-wrap align-items-center gap-3 mb-16">
                                            <div class="dash-kpi-icon {{ $skin['icon'] }}">
                                                <i class="{{ $kpi['icon'] }}"></i>
                    </div>
                                            <p class="fw-medium text-primary-light mb-0">{{ $kpi['label'] }}</p>
                  </div>
                                        <h4 class="mb-0 fw-bold">{{ number_format($kpi['value']) }}</h4>
                                        <p class="fw-medium text-sm text-primary-light mt-12 mb-0">{{ $schoolName }}</p>
                </div>
              </div>
            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($sideChart)
        <div class="col-xxl-4">
                    <div class="card h-100 radius-8 shadow-1">
            <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between px-20 py-16 border-bottom border-neutral-200">
                                <h6 class="text-lg mb-0">{{ $sideChart['title'] }}</h6>
                                <span class="text-sm text-secondary-light">{{ $sideChart['help'] }}</span>
                            </div>
                            <div class="p-20">
                                @php
                                    $hasSeries = ($sideChart['type'] ?? '') === 'donut'
                                        ? collect($sideChart['series'] ?? [])->sum() > 0
                                        : collect($sideChart['categories'] ?? [])->isNotEmpty();
                                @endphp
                                @if($hasSeries)
                                    <div id="{{ $sideChart['id'] }}"></div>
                                @else
                                    <div class="dash-empty py-40">No data yet for this chart.</div>
                                @endif
                      </div>
                    </div>
                  </div>
                </div>
            @endif

            @foreach($mainCharts as $chart)
                <div class="{{ $chart['col'] ?? 'col-lg-6' }}">
                    <div class="card h-100 radius-8 shadow-1">
                    <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between px-20 py-16 border-bottom border-neutral-200">
                                <h6 class="text-lg mb-0">{{ $chart['title'] }}</h6>
                                <span class="text-sm text-secondary-light">{{ $chart['help'] }}</span>
                            </div>
                            <div class="p-20">
                                @php
                                    $hasSeries = ($chart['type'] ?? '') === 'donut'
                                        ? collect($chart['series'] ?? [])->sum() > 0
                                        : collect($chart['categories'] ?? [])->isNotEmpty();
                                @endphp
                                @if($hasSeries)
                                    <div id="{{ $chart['id'] }}"></div>
                                @else
                                    <div class="dash-empty py-40">No data yet for this chart.</div>
                                @endif
                      </div>
                    </div>
                  </div>
                </div>
            @endforeach

            @if(count($shortcuts))
                <div class="col-12">
                    <div class="row gy-4">
                        @foreach($shortcuts as $item)
                            @php $skin = $shortcutSkins[$item['tone']] ?? $kpiSkins[$loop->index % 6]; @endphp
                            <div class="col-sm-6 col-xl-3">
                                <a href="{{ route($item['url']) }}" class="dash-action-card">
                                    <div class="card shadow-1 radius-8 {{ $skin['grad'] }} h-100">
                                        <div class="card-body p-20">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="dash-action-icon {{ $skin['icon'] }}">
                                                    <i class="{{ $item['icon'] }}"></i>
                        </div>
                        <div>
                                                    <h6 class="mb-4">{{ $item['label'] }}</h6>
                                                    <p class="text-sm text-primary-light mb-0">{{ $item['help'] }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                                </a>
                </div>
                        @endforeach
                  </div>
                </div>
            @endif

            @if($isTeacher)
                <div class="col-lg-6">
                    <div class="card h-100 radius-8 shadow-1">
            <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between px-20 py-16 border-bottom border-neutral-200">
                                <h6 class="text-lg mb-0">My homeroom classes</h6>
                                <span class="badge bg-primary-600">Class teacher</span>
              </div>
              <div class="p-20">
                                @forelse($homeroomClasses as $class)
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 {{ $loop->last ? '' : 'mb-16 pb-16 border-bottom border-neutral-200' }}">
                                        <div>
                                            <h6 class="mb-4">{{ $class->name }}</h6>
                                            <span class="text-sm text-secondary-light">Homeroom</span>
              </div>
                                        <div class="d-flex flex-wrap gap-8">
                                            <a href="{{ route('teacher-class-workspace', $class) }}" class="btn btn-sm btn-primary-600">Roster</a>
                                            <a href="{{ route('teacher-class-attendance', $class) }}" class="btn btn-sm btn-warning-600">Attendance</a>
                                            <a href="{{ route('teacher-class-assessments', $class) }}" class="btn btn-sm btn-purple-600">Assessments</a>
                                            <a href="{{ route('teacher-class-gradebook', $class) }}" class="btn btn-sm btn-success-600">Gradebook</a>
            </div>
          </div>
                                @empty
                                    <p class="text-secondary-light mb-0">No homeroom class assigned yet.</p>
                                @endforelse
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 radius-8 shadow-1">
            <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between px-20 py-16 border-bottom border-neutral-200">
                                <h6 class="text-lg mb-0">My subject assignments</h6>
                                <span class="badge bg-purple-600">Course teacher</span>
              </div>
              <div class="p-20">
                                @forelse($subjectAssignments as $assignment)
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 {{ $loop->last ? '' : 'mb-16 pb-16 border-bottom border-neutral-200' }}">
                                        <div>
                                            <h6 class="mb-4">{{ $assignment->course?->name }}</h6>
                                            <span class="text-sm text-secondary-light">{{ $assignment->schoolClass?->name }}</span>
                  </div>
                                        <div class="d-flex flex-wrap gap-8">
                                            <a href="{{ route('teacher-course-workspace', [$assignment->course_id, $assignment->school_class_id]) }}" class="btn btn-sm btn-primary-600">Roster</a>
                                            <a href="{{ route('teacher-course-assessments', [$assignment->course_id, $assignment->school_class_id]) }}" class="btn btn-sm btn-purple-600">Assessments</a>
              </div>
            </div>
                                @empty
                                    <p class="text-secondary-light mb-0">No subject assignments for this term.</p>
                                @endforelse
              </div>
            </div>
                  </div>
                </div>
            @endif

            @if($showPendingLeave)
                <div class="col-12">
                    <div class="card radius-8 shadow-1">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between px-20 py-16 border-bottom border-neutral-200">
                                <h6 class="text-lg mb-0">Pending leave</h6>
                                @if(\Illuminate\Support\Facades\Route::has('hr-leave'))
                                    <a href="{{ route('hr-leave') }}" class="btn btn-sm btn-primary-600">View all</a>
                                @endif
                    </div>
                            <div class="table-responsive">
                                <table class="table bordered-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Staff</th>
                                            <th>Type</th>
                                            <th>Dates</th>
                                            <th>Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingLeave as $leave)
                                            <tr>
                                                <td class="fw-semibold">{{ $leave->staff?->full_name ?: '—' }}</td>
                                                <td><span class="badge bg-warning-600">{{ $leave->leaveType?->name ?: '—' }}</span></td>
                                                <td>{{ $leave->start_date?->format('d M') }} – {{ $leave->end_date?->format('d M Y') }}</td>
                                                <td>{{ $leave->days }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-secondary-light py-20">No pending leave requests.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                  </div>
                </div>
                  </div>
                </div>
            @endif
        </div>
    @endif
    </div>
@endsection

@section('scripts')
<script>
    (function () {
        const charts = @json($charts ?? []);
        if (typeof ApexCharts === 'undefined') return;

        charts.forEach(function (spec) {
            const el = document.querySelector('#' + spec.id);
            if (!el) return;

            const base = {
                chart: { fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
                colors: spec.colors || ['#25A194', '#FF7A2C', '#487FFF', '#8252E9'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: false },
                grid: { borderColor: '#eef2f7', strokeDashArray: 4 },
            };

            let options;
            if (spec.type === 'donut') {
                options = Object.assign({}, base, {
                    chart: Object.assign({}, base.chart, { type: 'donut', height: 280 }),
                    series: spec.series || [],
                    labels: spec.labels || [],
                    stroke: { width: 0 },
                    plotOptions: { pie: { donut: { size: '68%' } } },
                });
            } else if (spec.type === 'area') {
                options = Object.assign({}, base, {
                    chart: Object.assign({}, base.chart, { type: 'area', height: 280 }),
                    series: spec.series || [],
                    xaxis: { categories: spec.categories || [] },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.08 } },
                    yaxis: { min: 0, labels: { formatter: function (v) { return Number(v).toLocaleString(); } } },
                });
            } else {
                options = Object.assign({}, base, {
                    chart: Object.assign({}, base.chart, { type: 'bar', height: 280, stacked: !!spec.stacked }),
                    series: spec.series || [],
                    xaxis: { categories: spec.categories || [] },
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '46%' } },
                    yaxis: { min: 0, labels: { formatter: function (v) { return Number(v).toLocaleString(); } } },
                });
            }

            new ApexCharts(el, options).render();
        });
    })();
</script>
@endsection
