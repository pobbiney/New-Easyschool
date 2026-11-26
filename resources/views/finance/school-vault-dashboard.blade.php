@php
    $pageName = 'school-vault';
    $subpageName = 'school-vault-dashboard';
    $money = fn ($value) => \App\Support\Money::ghs($value);
    $canCollect = app(\App\Services\UserAccessService::class)->can('student-bills');
    $canExpense = app(\App\Services\UserAccessService::class)->can('expenses');
    $periodLinks = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'term' => 'This Term',
        'custom' => 'Custom Range',
    ];
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('finance.partials.report-styles')
<style>
    .vault-shell { --vault-green:#168f7f; --vault-red:#dc3654; --vault-ink:#10213a; }
    .vault-hero {
        position: relative; overflow: hidden; border-radius: 24px; padding: 28px;
        color: #fff; background: linear-gradient(135deg, #102f38 0%, #116b66 58%, #25a194 100%);
        box-shadow: 0 22px 50px rgba(15, 118, 110, .22);
    }
    .vault-hero::before { content:""; position:absolute; width:260px; height:260px; right:-70px; top:-120px; border-radius:50%; background:rgba(255,255,255,.08); }
    .vault-hero::after { content:""; position:absolute; width:180px; height:180px; right:130px; bottom:-135px; border-radius:50%; border:28px solid rgba(255,255,255,.06); }
    .vault-hero > * { position:relative; z-index:1; }
    .vault-balance-label { color:rgba(255,255,255,.74); font-size:12px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; }
    .vault-balance { font-size:clamp(2.25rem, 6vw, 4rem); line-height:1; letter-spacing:-.055em; font-weight:850; margin:10px 0 12px; }
    .vault-status { display:inline-flex; align-items:center; gap:7px; border:1px solid rgba(255,255,255,.22); background:rgba(255,255,255,.12); padding:7px 12px; border-radius:999px; font-size:12px; font-weight:700; }
    .vault-status i { font-size:8px; color:#8ff6c7; }
    .vault-actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:flex-end; }
    .vault-action { display:inline-flex; align-items:center; gap:8px; padding:11px 16px; border-radius:12px; text-decoration:none; font-weight:700; font-size:13px; }
    .vault-action-light { color:#124b48; background:#fff; }
    .vault-action-ghost { color:#fff; border:1px solid rgba(255,255,255,.28); background:rgba(255,255,255,.1); }
    .vault-filter-card, .vault-panel, .vault-stat { border:1px solid #e7edf3; border-radius:18px; background:var(--white, #fff); box-shadow:0 9px 28px rgba(15,23,42,.045); }
    .vault-filter-card { padding:18px 20px; }
    .vault-periods { display:flex; gap:7px; flex-wrap:wrap; }
    .vault-period { border:1px solid #dce5ec; color:#526174; padding:8px 12px; border-radius:10px; text-decoration:none; font-size:12px; font-weight:700; background:#fff; }
    .vault-period.active { color:#fff; background:#1b8f82; border-color:#1b8f82; box-shadow:0 7px 16px rgba(37,161,148,.2); }
    .vault-stat { padding:18px; height:100%; }
    .vault-stat-icon { width:42px; height:42px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:20px; }
    .vault-stat small { color:#6b7b8e; font-weight:650; }
    .vault-stat strong { display:block; color:var(--vault-ink); font-size:1.25rem; margin-top:8px; }
    .tone-green { color:#087764; background:#e9fbf5; } .tone-red { color:#c72747; background:#fff0f3; }
    .tone-blue { color:#315dd6; background:#eef3ff; } .tone-amber { color:#ad6800; background:#fff7df; }
    .tone-slate { color:#526174; background:#f1f5f9; }
    .vault-panel { overflow:hidden; height:100%; }
    .vault-panel-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:18px 20px; border-bottom:1px solid #edf1f5; }
    .vault-panel-head h6 { margin:0 0 3px; } .vault-panel-body { padding:20px; }
    .vault-progress { height:9px; background:#edf2f6; border-radius:99px; overflow:hidden; }
    .vault-progress span { display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#168f7f,#38c79f); }
    .vault-mini { padding:13px 14px; border:1px solid #edf1f5; border-radius:13px; background:#fbfcfe; height:100%; }
    .vault-mini strong { display:block; color:var(--vault-ink); font-size:17px; }
    .vault-empty { padding:50px 20px; text-align:center; color:#7b899a; }
    .vault-empty i { display:block; font-size:34px; color:#25a194; margin-bottom:7px; }
    .vault-method + .vault-method { margin-top:15px; }
    .vault-method-track { height:7px; border-radius:99px; overflow:hidden; background:#edf2f6; }
    .vault-method-track span { display:block; height:100%; border-radius:99px; background:#25a194; }
    .vault-recon-ok { border:1px solid #bdebdc; background:#f0fdf8; color:#087764; }
    .vault-recon-warn { border:1px solid #fed1d9; background:#fff5f6; color:#a91f39; }
    .vault-recon { border-radius:16px; padding:18px; }
    .vault-recon-row { display:flex; justify-content:space-between; gap:12px; padding-top:11px; margin-top:11px; border-top:1px solid currentColor; border-color:rgba(100,116,139,.18); }
    .vault-table-wrap { overflow:auto; } .vault-table { min-width:1080px; }
    .vault-credit { color:#078269; font-weight:800; } .vault-debit { color:#d12d4d; font-weight:800; }
    .vault-ref { font-size:11px; color:#718096; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; }
    [data-theme=dark] .vault-filter-card, [data-theme=dark] .vault-panel, [data-theme=dark] .vault-stat { border-color:#334155; background:#1e293b; }
    [data-theme=dark] .vault-stat strong, [data-theme=dark] .vault-mini strong { color:#f8fafc; }
    [data-theme=dark] .vault-period { color:#cbd5e1; background:#1e293b; border-color:#475569; }
    [data-theme=dark] .vault-mini { background:#172033; border-color:#334155; }
    [data-theme=dark] .vault-panel-head { border-color:#334155; }
    @media(max-width:767px) { .vault-hero { padding:22px 18px; } .vault-actions { justify-content:flex-start; margin-top:18px; } }
</style>
@endsection

@section('content')
<div class="dashboard-main-body vault-shell">
    @include('partials._page-header', [
        'section' => 'School Vault',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'School Vault', 'active' => true],
        ],
        'title' => 'School Vault',
        'subtitle' => 'Operational Finance Dashboard',
        'actions' => '<span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> '.e($academicPeriod['year_name'] ?: 'No academic year').' · '.e($academicPeriod['term_name'] ?: 'No term').'</span>',
    ])

    @include('finance.partials.report-nav')

    <div class="vault-filter-card mb-24">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-16">
            <div class="vault-periods">
                @foreach($periodLinks as $key => $label)
                    @php
                        $periodQuery = array_filter([
                            'period' => $key,
                            'academic_year_id' => $academicPeriod['year_id'],
                            'academic_term_id' => $academicPeriod['term_id'],
                            'from_date' => $key === 'custom' ? $range['from_value'] : null,
                            'to_date' => $key === 'custom' ? $range['to_value'] : null,
                        ]);
                    @endphp
                    <a class="vault-period {{ $range['key'] === $key ? 'active' : '' }}"
                       href="{{ route('school-vault-dashboard', $periodQuery) }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <span class="text-sm text-secondary-light"><i class="ri-time-line"></i> {{ $range['label'] }}</span>
        </div>
        <form method="GET" action="{{ route('school-vault-dashboard') }}" class="row g-3 align-items-end">
            <input type="hidden" name="period" value="{{ $range['key'] }}">
            <div class="col-sm-6 col-xl-2">
                <label class="text-sm fw-semibold mb-8 d-block">Academic year</label>
                <select class="form-select" name="academic_year_id">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((int) $academicPeriod['year_id'] === (int) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-xl-2">
                <label class="text-sm fw-semibold mb-8 d-block">Academic term</label>
                <select class="form-select" name="academic_term_id">
                    @foreach($academicTerms as $term)
                        <option value="{{ $term->id }}" @selected((int) $academicPeriod['term_id'] === (int) $term->id)>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-xl-2 custom-range-field {{ $range['key'] === 'custom' ? '' : 'd-none' }}">
                <label class="text-sm fw-semibold mb-8 d-block">From</label>
                <input class="form-control" type="date" name="from_date" value="{{ $range['from_value'] }}">
            </div>
            <div class="col-sm-6 col-xl-2 custom-range-field {{ $range['key'] === 'custom' ? '' : 'd-none' }}">
                <label class="text-sm fw-semibold mb-8 d-block">To</label>
                <input class="form-control" type="date" name="to_date" value="{{ $range['to_value'] }}">
            </div>
            <div class="col-sm-6 col-xl-2"><button class="btn btn-primary-600 w-100" type="submit"><i class="ri-filter-3-line"></i> Apply</button></div>
        </form>
    </div>

    <div class="vault-hero mb-24">
        <div class="row align-items-end">
            <div class="col-lg-7">
                <div class="vault-balance-label">Current available balance · all time</div>
                <div class="vault-balance">{{ $money($currentBalance) }}</div>
                <div class="d-flex flex-wrap gap-8 align-items-center">
                    <span class="vault-status"><i class="ri-circle-fill"></i> Vault {{ $vault->status }}</span>
                    <span class="text-sm" style="color:rgba(255,255,255,.72)">As at {{ $generatedAt->format('d M Y, g:i A') }}</span>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="vault-actions">
                    @if($canCollect)<a href="{{ route('student-bills') }}" class="vault-action vault-action-light"><i class="ri-hand-coin-line"></i> Collect Fees</a>@endif
                    @if($canExpense)<a href="{{ route('expenses') }}" class="vault-action vault-action-ghost"><i class="ri-wallet-3-line"></i> Record Expense</a>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        @foreach([
            ['Opening balance', $summary['opening'], 'ri-door-open-line', 'tone-blue'],
            ['Feeding Fee collections', $summary['collections'], 'ri-arrow-down-circle-line', 'tone-green'],
            ['Net operational expenses', $summary['expenses'], 'ri-arrow-up-circle-line', 'tone-red'],
            ['Net cash movement', $summary['net_movement'], 'ri-exchange-funds-line', 'tone-amber'],
            ['Period closing balance', $summary['closing'], 'ri-safe-2-line', 'tone-slate'],
        ] as [$label, $value, $icon, $tone])
            <div class="col-sm-6 col-xl">
                <div class="vault-stat">
                    <div class="vault-stat-icon {{ $tone }}"><i class="{{ $icon }}"></i></div>
                    <small class="d-block mt-12">{{ $label }}</small>
                    <strong>{{ $money($value) }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="vault-panel mb-24">
        <div class="vault-panel-head">
            <div><h6>Feeding Fee collection performance</h6><p class="text-sm text-secondary-light mb-0">Student billing performance for {{ $academicPeriod['year_name'] ?: 'all years' }} · {{ $academicPeriod['term_name'] ?: 'all terms' }}</p></div>
            <span class="ac-pill ac-pill-teal">Optional billing participation</span>
        </div>
        <div class="vault-panel-body">
            <div class="d-flex justify-content-between gap-3 mb-8"><span class="text-sm fw-semibold">Collection progress</span><strong>{{ number_format($feedingPerformance['percentage'], 1) }}%</strong></div>
            <div class="vault-progress mb-20"><span style="width:{{ min(100, $feedingPerformance['percentage']) }}%"></span></div>
            <div class="row g-3">
                @foreach([
                    ['Total billed', $money($feedingPerformance['billed'])],
                    ['Total paid', $money($feedingPerformance['paid'])],
                    ['Outstanding', $money($feedingPerformance['outstanding'])],
                    ['Students with bills', number_format($feedingPerformance['students'])],
                    ['Fully paid', number_format($feedingPerformance['fully_paid'])],
                    ['Partially paid', number_format($feedingPerformance['partially_paid'])],
                    ['Unpaid optional bills', number_format($feedingPerformance['unpaid'])],
                ] as [$label, $value])
                    <div class="col-6 col-md-4 col-xl"><div class="vault-mini"><small class="text-secondary-light">{{ $label }}</small><strong>{{ $value }}</strong></div></div>
                @endforeach
            </div>
            <p class="text-sm text-secondary-light mt-16 mb-0"><i class="ri-information-line"></i> Billing performance can include a historical unposted payment. Outstanding bills are not available Vault cash.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-xl-8">
            <div class="vault-panel">
                <div class="vault-panel-head"><div><h6>Income and expenditure trend</h6><p class="text-sm text-secondary-light mb-0">Grouped by {{ $trendChart['grouping'] }} · totals reconcile to period collection and expense cards</p></div></div>
                <div class="vault-panel-body">
                    @if(collect($trendChart['collections'])->filter()->isNotEmpty() || collect($trendChart['expenses'])->filter()->isNotEmpty())
                        <div id="vaultTrendChart"></div>
                    @else
                        <div class="vault-empty"><i class="ri-line-chart-line"></i>No posted Vault movements in this period.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="vault-panel">
                <div class="vault-panel-head"><div><h6>Payment methods</h6><p class="text-sm text-secondary-light mb-0">Confirmed posted Feeding Fee collections</p></div></div>
                <div class="vault-panel-body">
                    @foreach($paymentMethods as $method)
                        <div class="vault-method">
                            <div class="d-flex justify-content-between gap-3 mb-6"><span class="text-sm fw-semibold">{{ $method['label'] }}</span><span class="text-sm">{{ $money($method['amount']) }}</span></div>
                            <div class="vault-method-track"><span style="width:{{ min(100, $method['percentage']) }}%"></span></div>
                        </div>
                    @endforeach
                    <p class="text-sm text-secondary-light mt-18 mb-0">Paystack values are verified gross collections, not confirmed net bank settlement.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-xl-7">
            <div class="vault-panel">
                <div class="vault-panel-head"><div><h6>Net expenditure by category</h6><p class="text-sm text-secondary-light mb-0">Vault-funded expenses after corrections and cancellations</p></div></div>
                <div class="vault-panel-body">
                    @if(collect($expenseChart['series'])->filter()->isNotEmpty())<div id="vaultExpenseChart"></div>
                    @else<div class="vault-empty"><i class="ri-pie-chart-2-line"></i>No Vault-funded expenses in this period.</div>@endif
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="vault-panel">
                <div class="vault-panel-head"><div><h6>Reconciliation status</h6><p class="text-sm text-secondary-light mb-0">Read-only financial integrity checks</p></div></div>
                <div class="vault-panel-body">
                    <div class="vault-recon {{ $reconciliation['has_issues'] ? 'vault-recon-warn' : 'vault-recon-ok' }}">
                        <div class="d-flex align-items-center gap-3">
                            <i class="{{ $reconciliation['has_issues'] ? 'ri-error-warning-line' : 'ri-checkbox-circle-line' }} text-xl"></i>
                            <div><strong>{{ $reconciliation['has_issues'] ? $reconciliation['count'].' discrepancy item(s)' : 'No discrepancies detected' }}</strong><div class="text-sm">No automatic repairs are performed.</div></div>
                        </div>
                        @foreach($reconciliation['types'] as $type)
                            <div class="vault-recon-row"><div><strong class="text-sm">{{ $type['label'] }}</strong><div class="text-sm">{{ $type['count'] }} item(s)</div></div>@if((float)$type['amount'] !== 0.0)<strong>{{ $money($type['amount']) }}</strong>@endif</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="vault-panel">
        <div class="vault-panel-head"><div><h6>Recent Vault transactions</h6><p class="text-sm text-secondary-light mb-0">Most recent ledger movements within {{ $range['label'] }}</p></div><span class="ac-pill ac-pill-slate">Read only</span></div>
        @if($recentTransactions->isEmpty())
            <div class="vault-empty"><i class="ri-file-list-3-line"></i>No Vault transactions in this period.</div>
        @else
            <div class="vault-table-wrap">
                <table class="table bordered-table mb-0 vault-table">
                    <thead><tr><th>Date</th><th>Transaction reference</th><th>Type</th><th>Description</th><th>Source</th><th>Credit</th><th>Debit</th><th>Responsible</th></tr></thead>
                    <tbody>
                    @foreach($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction['date']?->format('d M Y H:i') }}</td>
                            <td><span class="vault-ref">{{ $transaction['reference'] }}</span></td>
                            <td><span class="ac-pill {{ $transaction['credit'] ? 'ac-pill-teal' : 'ac-pill-rose' }}">{{ $transaction['type'] }}</span></td>
                            <td>{{ $transaction['description'] }}</td>
                            <td>@if($transaction['source_url'])<a href="{{ $transaction['source_url'] }}" class="fw-semibold text-primary-600">{{ $transaction['source_reference'] }}</a>@else{{ $transaction['source_reference'] }}@endif</td>
                            <td class="vault-credit">{{ $transaction['credit'] ? $money($transaction['credit']) : '—' }}</td>
                            <td class="vault-debit">{{ $transaction['debit'] ? $money($transaction['debit']) : '—' }}</td>
                            <td>{{ $transaction['responsible'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;
    const trend = @json($trendChart);
    const expense = @json($expenseChart);
    const dark = document.documentElement.getAttribute('data-theme') === 'dark';
    const foreground = dark ? '#cbd5e1' : '#526174';
    const grid = dark ? '#334155' : '#e8edf3';
    const trendEl = document.querySelector('#vaultTrendChart');
    if (trendEl) new ApexCharts(trendEl, {
        chart:{type:'area',height:330,toolbar:{show:false},fontFamily:'inherit'},
        series:[{name:'Feeding Fee collections',data:trend.collections},{name:'Net operational expenses',data:trend.expenses}],
        colors:['#168f7f','#dc3654'],stroke:{curve:'smooth',width:3},fill:{type:'gradient',gradient:{opacityFrom:.3,opacityTo:.04}},
        dataLabels:{enabled:false},xaxis:{categories:trend.categories,labels:{style:{colors:foreground}}},
        yaxis:{labels:{style:{colors:foreground},formatter:v=>'GHS '+Number(v).toLocaleString()}},
        grid:{borderColor:grid,strokeDashArray:4},legend:{position:'top',horizontalAlign:'right',labels:{colors:foreground}},
        tooltip:{y:{formatter:v=>'GHS '+Number(v).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}}
    }).render();
    const expenseEl = document.querySelector('#vaultExpenseChart');
    if (expenseEl) new ApexCharts(expenseEl, {
        chart:{type:'bar',height:300,toolbar:{show:false},fontFamily:'inherit'},series:[{name:'Net expenditure',data:expense.series}],
        colors:['#dc3654'],plotOptions:{bar:{horizontal:true,borderRadius:5,barHeight:'54%'}},dataLabels:{enabled:false},
        xaxis:{categories:expense.labels,labels:{style:{colors:foreground},formatter:v=>'GHS '+Number(v).toLocaleString()}},
        yaxis:{labels:{style:{colors:foreground}}},grid:{borderColor:grid,strokeDashArray:4},
        tooltip:{y:{formatter:v=>'GHS '+Number(v).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}}
    }).render();
})();
</script>
@endsection
