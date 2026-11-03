@php
    $pageName = "hr";
    $subpageName = "hr-payroll";
    $draftCount = $runs->where('status', 'draft')->count();
    $approvedCount = $runs->where('status', 'approved')->count();
    $paidCount = $runs->where('status', 'paid')->count();
    $latest = $runs->first();
    $ytdNet = $runs->where('period_year', $year)->where('status', 'paid')->sum('total_net');
    $statusPill = function ($status) {
        return match (strtolower((string) $status)) {
            'paid' => 'ac-pill-emerald',
            'approved' => 'ac-pill-teal',
            default => 'ac-pill-draft',
        };
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('hr.partials._styles')
<style>
    .pr-hero {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12), rgba(99, 102, 241, 0.08));
        margin-bottom: 24px;
    }
    .pr-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
    }
    .pr-step {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #fff;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .pr-generate {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .pr-generate .card-header {
        background: transparent;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        padding: 16px 20px;
    }
    .pr-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: #f8fafc;
    }
    .pr-search { min-width: 240px; max-width: 320px; }
    .pr-money { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .pr-net { font-weight: 700; color: #0f766e; }
    .pr-period { font-weight: 700; }
    .pr-period-meta {
        display: block;
        font-size: 12px;
        color: #64748b;
        font-weight: 400;
    }
    .pr-empty {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }
    .pr-empty i {
        font-size: 36px;
        color: #25A194;
        display: block;
        margin-bottom: 10px;
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
            ['label' => 'Payroll', 'url' => null, 'active' => true],
        ],
        'title' => 'Payroll',
        'subtitle' => 'Generate a monthly draft, review, approve, then mark as paid.',
        'actions' => '<a href="'.route('hr-payslips').'" class="btn btn-outline-primary-600"><i class="ri-file-paper-2-line"></i> Payslips</a>
            <a href="'.route('hr-salary-structures').'" class="btn btn-outline-neutral-400"><i class="ri-stack-line"></i> Salary structures</a>',
    ])

    <div class="pr-hero">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#25A194;">
                <i class="ri-wallet-3-line"></i>
            </span>
            <div>
                <h5 class="fw-semibold mb-6">Monthly payroll desk</h5>
                <p class="text-sm text-secondary-light mb-0">
                    Create a draft for the month, check each employee’s SSNIT and PAYE, then approve and mark paid.
                    @if($latest)
                        Latest run: <strong>{{ $latest->periodLabel() }}</strong> ({{ ucfirst($latest->status) }}).
                    @endif
                </p>
            </div>
        </div>
        <div class="pr-steps">
            <span class="pr-step"><i class="ri-file-list-3-line"></i> 1. Generate draft</span>
            <span class="pr-step"><i class="ri-search-eye-line"></i> 2. Review payslips</span>
            <span class="pr-step"><i class="ri-shield-check-line"></i> 3. Approve</span>
            <span class="pr-step"><i class="ri-bank-card-line"></i> 4. Mark paid</span>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Payroll runs</p>
                        <h4 class="fw-semibold mb-0">{{ $runs->count() }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-calendar-schedule-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Drafts to review</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $draftCount }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Awaiting payment</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $approvedCount }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-shield-check-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">{{ $year }} net paid</p>
                        <h5 class="fw-semibold mb-0 text-success-600">{{ \App\Support\Money::ghs($ytdNet) }}</h5>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-money-dollar-circle-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="pr-generate">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-semibold">Generate a draft</h6>
                <p class="text-sm text-secondary-light mb-0 mt-4">Uses current pay grades, allowances, SSNIT, and PAYE. Existing drafts for the same month are replaced.</p>
            </div>
        </div>
        <div class="card-body p-20">
            <form method="POST" action="{{ route('hr-payroll-generate') }}" class="row gy-3 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Year</label>
                    <input type="number" name="period_year" class="form-control" value="{{ $year }}" min="2020" max="2100" required>
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Month</label>
                    <select name="period_month" class="form-control form-select" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary-600">
                        <i class="ri-add-circle-line"></i> Generate draft
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="pr-toolbar">
            <div>
                <h6 class="mb-0 fw-semibold">Payroll runs</h6>
                <p class="text-sm text-secondary-light mb-0 mt-4">{{ $paidCount }} paid · {{ $approvedCount }} approved · {{ $draftCount }} draft</p>
            </div>
            <div class="pr-search">
                <input type="search" id="payrollRunSearch" class="form-control" placeholder="Search period or status…">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table bordered-table mb-0" id="payrollRunTable">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th class="text-end">Employees</th>
                        <th class="text-end">Gross</th>
                        <th class="text-end">PAYE</th>
                        <th class="text-end">SSNIT</th>
                        <th class="text-end">Net</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($runs as $run)
                        <tr data-search="{{ strtolower($run->periodLabel().' '.$run->status) }}">
                            <td>
                                <span class="pr-period">{{ $run->periodLabel() }}</span>
                                <span class="pr-period-meta">
                                    @if($run->run_date) Generated {{ $run->run_date->format('d M Y') }} @endif
                                    @if($run->paid_at) · Paid {{ $run->paid_at->format('d M Y') }} @endif
                                </span>
                            </td>
                            <td class="text-end pr-money">{{ $run->employee_count }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_gross) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_paye) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_ssnit_employee) }}</td>
                            <td class="text-end pr-money pr-net">{{ \App\Support\Money::ghs($run->total_net) }}</td>
                            <td><span class="ac-pill {{ $statusPill($run->status) }}">{{ ucfirst($run->status) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('hr-payroll-show', $run->id) }}" class="btn btn-sm btn-outline-primary-600">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="pr-empty">
                                    <i class="ri-wallet-3-line"></i>
                                    No payroll runs yet. Generate a draft for this month to get started.
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
        const input = document.getElementById('payrollRunSearch');
        const rows = document.querySelectorAll('#payrollRunTable tbody tr[data-search]');
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
