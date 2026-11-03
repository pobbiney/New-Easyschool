@php
    $pageName = 'reports';
    $subpageName = $report['url'];
    $query = request()->query();
    $looks = [
        'students' => ['icon' => 'ri-graduation-cap-line', 'blurb' => 'Learners on the school roll, with class, gender, and contact details.'],
        'enrolment' => ['icon' => 'ri-pie-chart-2-line', 'blurb' => 'Headcount by class and gender for planning, inspectors, and the office file.'],
        'staff' => ['icon' => 'ri-team-line', 'blurb' => 'Employees and their departments, positions, and current status.'],
        'leave' => ['icon' => 'ri-calendar-schedule-line', 'blurb' => 'Leave applications with type, dates, and approval status.'],
        'staff-attendance' => ['icon' => 'ri-fingerprint-line', 'blurb' => 'Daily staff attendance marks, check-in, and check-out times.'],
        'payroll' => ['icon' => 'ri-wallet-3-line', 'blurb' => 'Payslip totals by staff and period, including SSNIT, PAYE, and net pay.'],
        'fee-collection' => ['icon' => 'ri-hand-coin-line', 'blurb' => 'Fee payments received, with receipt, class, method, and amount.'],
        'outstanding-bills' => ['icon' => 'ri-bill-line', 'blurb' => 'Bills that still have a balance, grouped by student and item.'],
        'pos-sales' => ['icon' => 'ri-store-2-line', 'blurb' => 'Shop sales recorded at the POS, with customer, payment, and cashier.'],
        'expenses' => ['icon' => 'ri-money-cny-circle-line', 'blurb' => 'Outgoing school spend by category, payee, and payment method.'],
        'sms' => ['icon' => 'ri-message-3-line', 'blurb' => 'Messages sent from the school, with audience, status, and counts.'],
    ];
    $look = $looks[$report['key']] ?? ['icon' => 'ri-file-chart-line', 'blurb' => $report['subtitle']];
    $kpiSkins = [
        ['grad' => 'gradient-bg-end-4', 'icon' => 'bg-primary-600'],
        ['grad' => 'gradient-bg-end-5', 'icon' => 'bg-success-600'],
        ['grad' => 'gradient-bg-end-1', 'icon' => 'bg-warning-600'],
        ['grad' => 'gradient-bg-end-3', 'icon' => 'bg-purple-600'],
        ['grad' => 'gradient-bg-end-2', 'icon' => 'bg-blue-600'],
        ['grad' => 'gradient-bg-end-6', 'icon' => 'bg-cyan-600'],
        ['grad' => 'gradient-bg-end-8', 'icon' => 'bg-danger-600'],
    ];
    $kpiIcon = function (string $label): string {
        $l = strtolower($label);

        return match (true) {
            str_contains($l, 'student') => 'ri-graduation-cap-line',
            str_contains($l, 'staff') => 'ri-team-line',
            str_contains($l, 'class') => 'ri-building-4-line',
            str_contains($l, 'present') => 'ri-user-follow-line',
            str_contains($l, 'absent') => 'ri-user-unfollow-line',
            str_contains($l, 'late') => 'ri-time-line',
            str_contains($l, 'excused') => 'ri-heart-pulse-line',
            str_contains($l, 'request') => 'ri-mail-send-line',
            str_contains($l, 'day') => 'ri-calendar-line',
            str_contains($l, 'record') => 'ri-file-list-3-line',
            str_contains($l, 'payslip') => 'ri-file-text-line',
            str_contains($l, 'payment') => 'ri-hand-coin-line',
            str_contains($l, 'sale') => 'ri-store-2-line',
            str_contains($l, 'bill') => 'ri-bill-line',
            str_contains($l, 'campaign') => 'ri-megaphone-line',
            str_contains($l, 'message') => 'ri-message-3-line',
            str_contains($l, 'gross'), str_contains($l, 'net'), str_contains($l, 'collect'),
            str_contains($l, 'spent'), str_contains($l, 'outstanding'), str_contains($l, 'total') => 'ri-money-cny-circle-line',
            default => 'ri-bar-chart-2-line',
        };
    };
    $nameKeys = ['name', 'student', 'staff', 'customer', 'payee', 'cashier', 'by'];
    $pillKeys = ['class', 'department', 'position', 'type', 'method', 'audience', 'category', 'gender', 'period', 'item', 'year'];
    $statusTone = function ($value): string {
        $v = strtolower(trim((string) $value));

        return match (true) {
            in_array($v, ['active', 'present', 'approved', 'paid', 'sent', 'success', 'completed', 'cash'], true) => 'ok',
            in_array($v, ['inactive', 'absent', 'rejected', 'failed', 'cancelled', 'overdue', 'unpaid'], true) => 'bad',
            in_array($v, ['pending', 'late', 'draft', 'queued'], true) => 'warn',
            in_array($v, ['excused', 'on leave', 'momo', 'mobile money'], true) => 'info',
            default => 'slate',
        };
    };
    $pillTone = function (string $key, $value) use ($statusTone): string {
        if ($key === 'gender') {
            return strtolower((string) $value) === 'female' ? 'pink' : (strtolower((string) $value) === 'male' ? 'sky' : 'slate');
        }

        return match ($key) {
            'class' => 'teal',
            'department', 'position', 'period', 'year' => 'info',
            'type', 'category', 'item', 'audience' => 'warn',
            'method' => $statusTone($value),
            default => 'slate',
        };
    };
    $initials = function ($value): string {
        $words = preg_split('/\s+/', trim((string) $value)) ?: [];
        $letters = collect($words)->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');

        return strtoupper($letters !== '' ? $letters : 'NA');
    };
    $enrolmentMix = null;
    if ($report['key'] === 'enrolment' && count($report['rows'])) {
        $male = (int) collect($report['rows'])->sum('male');
        $female = (int) collect($report['rows'])->sum('female');
        $other = (int) collect($report['rows'])->sum('other');
        $head = max(1, $male + $female + $other);
        $enrolmentMix = compact('male', 'female', 'other', 'head');
    }
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('reports.partials._styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Reports',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => $report['title'], 'active' => true],
        ],
        'title' => $report['title'],
        'subtitle' => $report['subtitle'],
        'actions' => view('reports.partials._export-buttons', ['report' => $report, 'query' => $query])->render(),
    ])

    <div class="rpt-hero d-flex align-items-start gap-16">
        <span class="rpt-hero-icon"><i class="{{ $look['icon'] }}"></i></span>
        <div>
            <div class="rpt-hero-title">{{ $report['title'] }}</div>
            <p class="text-sm text-secondary-light mb-0" style="max-width:620px;">{{ $look['blurb'] }}</p>
            @if($report['totals'])
                <div class="rpt-hero-tags">
                    @foreach($report['totals'] as $total)
                        <span class="rpt-hero-tag rpt-tag-{{ $loop->index % 6 }}"><i class="{{ $kpiIcon($total['label']) }}"></i> {{ $total['label'] }} {{ $total['value'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if($report['totals'])
        <div class="row gy-4 mb-24">
            @foreach($report['totals'] as $total)
                @php $skin = $kpiSkins[$loop->index % count($kpiSkins)]; @endphp
                <div class="col-6 col-xl">
                    <div class="card shadow-1 radius-8 {{ $skin['grad'] }} h-100">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-12">
                                <div class="rpt-kpi-icon {{ $skin['icon'] }}"><i class="{{ $kpiIcon($total['label']) }}"></i></div>
                                <p class="fw-medium text-primary-light mb-0">{{ $total['label'] }}</p>
                            </div>
                            <h4 class="mb-0 fw-bold">{{ $total['value'] }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="rpt-board mb-24">
        @if($report['filters'])
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
        @endif

        @if($enrolmentMix)
            <div class="rpt-mix">
                <div class="d-flex justify-content-between align-items-center mb-10">
                    <span class="text-sm fw-semibold">Gender mix</span>
                    <span class="ac-pill ac-pill-teal">{{ number_format($enrolmentMix['male'] + $enrolmentMix['female'] + $enrolmentMix['other']) }} students</span>
                </div>
                <div class="rpt-mix-bar">
                    <span class="rpt-mix-male" style="width: {{ round(($enrolmentMix['male'] / $enrolmentMix['head']) * 100) }}%"></span>
                    <span class="rpt-mix-female" style="width: {{ round(($enrolmentMix['female'] / $enrolmentMix['head']) * 100) }}%"></span>
                    <span class="rpt-mix-other" style="width: {{ round(($enrolmentMix['other'] / $enrolmentMix['head']) * 100) }}%"></span>
                </div>
                <div class="rpt-mix-legend">
                    <span class="male"><i class="rpt-dot rpt-mix-male"></i>Male {{ number_format($enrolmentMix['male']) }}</span>
                    <span class="female"><i class="rpt-dot rpt-mix-female"></i>Female {{ number_format($enrolmentMix['female']) }}</span>
                    <span class="other"><i class="rpt-dot rpt-mix-other"></i>Other {{ number_format($enrolmentMix['other']) }}</span>
                </div>
            </div>
        @endif

        @if(count($report['rows']) === 0)
            <div class="rpt-empty">
                <div class="rpt-empty-icon"><i class="{{ $look['icon'] }}"></i></div>
                <h6 class="fw-semibold mb-6">No records match these filters</h6>
                <p class="text-sm text-secondary-light mb-0 mx-auto" style="max-width:420px;">
                    Change the filters above, or wait until this module has data to export.
                </p>
            </div>
        @else
            <div class="rpt-scroll">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            @foreach($report['columns'] as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['rows'] as $row)
                            <tr>
                                @foreach($report['columns'] as $column)
                                    @php
                                        $key = $column['key'];
                                        $value = $row[$key] ?? '—';
                                    @endphp
                                    <td>
                                        @if(! empty($column['money']))
                                            <span class="rpt-money">{{ $value }}</span>
                                        @elseif($key === 'status')
                                            <span class="rpt-pill rpt-pill-{{ $statusTone($value) }}">{{ $value }}</span>
                                        @elseif(in_array($key, $nameKeys, true))
                                            <div class="rpt-person">
                                                <span class="rpt-avatar rpt-tone-{{ abs(crc32((string) $value)) % 6 }}">{{ $initials($value) }}</span>
                                                <span class="fw-semibold">{{ $value }}</span>
                                            </div>
                                        @elseif(in_array($key, $pillKeys, true))
                                            <span class="rpt-pill rpt-pill-{{ $pillTone($key, $value) }}">{{ $value }}</span>
                                        @elseif($key === 'male')
                                            <span class="rpt-num-male">{{ $value }}</span>
                                        @elseif($key === 'female')
                                            <span class="rpt-num-female">{{ $value }}</span>
                                        @elseif($key === 'total')
                                            <span class="rpt-num-total">{{ $value }}</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-24 py-16 text-sm text-secondary-light">{{ number_format(count($report['rows'])) }} rows · generated {{ $report['printed_at'] }}</div>
        @endif
    </div>
</div>
@endsection
