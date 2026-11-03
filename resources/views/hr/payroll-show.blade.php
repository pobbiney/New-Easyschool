@php
    $pageName = "hr";
    $subpageName = "hr-payroll";
    $status = strtolower((string) $run->status);
    $statusPill = match ($status) {
        'paid' => 'ac-pill-emerald',
        'approved' => 'ac-pill-teal',
        default => 'ac-pill-draft',
    };
    $steps = [
        ['key' => 'draft', 'label' => 'Draft', 'icon' => 'ri-file-list-3-line'],
        ['key' => 'approved', 'label' => 'Approved', 'icon' => 'ri-shield-check-line'],
        ['key' => 'paid', 'label' => 'Paid', 'icon' => 'ri-bank-card-line'],
    ];
    $stepIndex = match ($status) {
        'approved' => 1,
        'paid' => 2,
        default => 0,
    };
    $totalDeductions = (float) $run->total_ssnit_employee + (float) $run->total_paye + (float) $run->total_other_deductions;
    $initials = function ($staff) {
        return strtoupper(substr((string) ($staff->firstname ?? ''), 0, 1).substr((string) ($staff->surname ?? ''), 0, 1));
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
        color: #94a3b8;
        border: 1px solid #e2e8f0;
    }
    .pr-step.is-done {
        background: rgba(37, 161, 148, 0.12);
        color: #0f766e;
        border-color: rgba(37, 161, 148, 0.25);
    }
    .pr-step.is-current {
        background: #25A194;
        color: #fff;
        border-color: #25A194;
    }
    .pr-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
        color: #64748b;
        font-size: 13px;
        margin-top: 10px;
    }
    .pr-stat .stat-value {
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
    }
    .pr-stat.is-net {
        background: linear-gradient(180deg, #f0fdfa 0%, #fff 100%);
        border-color: rgba(37, 161, 148, 0.28);
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
    .pr-search {
        min-width: 240px;
        max-width: 320px;
    }
    .pr-money {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .pr-net {
        font-weight: 700;
        color: #0f766e;
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
    .staff-meta {
        display: block;
        font-size: 12px;
        color: #64748b;
        font-weight: 400;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Payroll', 'url' => route('hr-payroll')],
            ['label' => $run->periodLabel(), 'url' => null, 'active' => true],
        ],
        'title' => $run->periodLabel().' payroll',
        'subtitle' => 'Review earnings, SSNIT, PAYE, and net pay before approval.',
        'actions' => '<a href="'.route('hr-payroll').'" class="btn btn-outline-primary-600"><i class="ri-arrow-left-line"></i> All runs</a>
            <a href="'.route('hr-payslips', ['run_id' => $run->id]).'" class="btn btn-outline-neutral-400"><i class="ri-file-paper-2-line"></i> Payslips</a>',
    ])

    <div class="pr-hero">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-16">
            <div class="d-flex align-items-start gap-16">
                <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#25A194;">
                    <i class="ri-wallet-3-line"></i>
                </span>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-8 mb-6">
                        <h5 class="fw-semibold mb-0">{{ $run->periodLabel() }} payroll run</h5>
                        <span class="ac-pill {{ $statusPill }}">{{ ucfirst($status) }}</span>
                    </div>
                    <p class="text-sm text-secondary-light mb-0">
                        {{ $run->employee_count }} employee{{ $run->employee_count === 1 ? '' : 's' }}
                        &nbsp;&bull;&nbsp; Total deductions {{ \App\Support\Money::ghs($totalDeductions) }}
                    </p>
                    <div class="pr-meta">
                        @if($run->run_date)
                            <span><i class="ri-calendar-line"></i> Generated {{ $run->run_date->format('d M Y') }}</span>
                        @endif
                        @if($run->approved_at)
                            <span><i class="ri-checkbox-circle-line"></i> Approved {{ $run->approved_at->format('d M Y') }}</span>
                        @endif
                        @if($run->paid_at)
                            <span><i class="ri-bank-card-line"></i> Paid {{ $run->paid_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-8">
                @if($run->status === 'draft')
                    <form method="POST" action="{{ route('hr-payroll-approve', $run->id) }}" onsubmit="return confirm('Approve this payroll run? Totals will be locked.');">
                        @csrf
                        <button class="btn btn-success"><i class="ri-shield-check-line"></i> Approve payroll</button>
                    </form>
                @elseif($run->status === 'approved')
                    <form method="POST" action="{{ route('hr-payroll-paid', $run->id) }}" onsubmit="return confirm('Mark this payroll as paid?');">
                        @csrf
                        <button class="btn btn-primary-600"><i class="ri-bank-card-line"></i> Mark as paid</button>
                    </form>
                @else
                    <span class="ac-pill ac-pill-emerald"><i class="ri-checkbox-circle-fill"></i> Payroll paid</span>
                @endif
            </div>
        </div>
        <div class="pr-steps">
            @foreach($steps as $i => $step)
                <span class="pr-step {{ $i < $stepIndex ? 'is-done' : ($i === $stepIndex ? 'is-current' : '') }}">
                    <i class="{{ $step['icon'] }}"></i> {{ $step['label'] }}
                    @if(! $loop->last)
                        <i class="ri-arrow-right-s-line"></i>
                    @endif
                </span>
            @endforeach
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-2">
            <div class="ac-stat-card pr-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Employees</p>
                        <h5 class="fw-semibold mb-0 stat-value">{{ $run->employee_count }}</h5>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-team-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="ac-stat-card pr-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Gross</p>
                        <h6 class="fw-semibold mb-0 stat-value">{{ \App\Support\Money::ghs($run->total_gross) }}</h6>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-funds-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="ac-stat-card pr-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">PAYE</p>
                        <h6 class="fw-semibold mb-0 stat-value text-warning-600">{{ \App\Support\Money::ghs($run->total_paye) }}</h6>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-percent-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="ac-stat-card pr-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">SSNIT</p>
                        <h6 class="fw-semibold mb-0 stat-value">{{ \App\Support\Money::ghs($run->total_ssnit_employee) }}</h6>
                    </div>
                    <span class="stat-icon bg-violet-100 text-violet-600" style="background:#ede9fe;color:#6d28d9;"><i class="ri-shield-user-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="ac-stat-card pr-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Other</p>
                        <h6 class="fw-semibold mb-0 stat-value">{{ \App\Support\Money::ghs($run->total_other_deductions) }}</h6>
                    </div>
                    <span class="stat-icon bg-danger-100 text-danger-600"><i class="ri-subtract-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="ac-stat-card pr-stat is-net">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Net pay</p>
                        <h6 class="fw-semibold mb-0 stat-value text-success-600">{{ \App\Support\Money::ghs($run->total_net) }}</h6>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-money-dollar-circle-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="pr-toolbar">
            <div>
                <h6 class="mb-0 fw-semibold">Employee payslips</h6>
                <p class="text-sm text-secondary-light mb-0 mt-4">Employer SSNIT this run: {{ \App\Support\Money::ghs($run->total_ssnit_employer) }}</p>
            </div>
            <div class="pr-search">
                <input type="search" id="payrollSearch" class="form-control" placeholder="Search staff…">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table bordered-table mb-0" id="payrollTable">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th class="text-end">Basic</th>
                        <th class="text-end">Gross</th>
                        <th class="text-end">SSNIT</th>
                        <th class="text-end">PAYE</th>
                        <th class="text-end">Other</th>
                        <th class="text-end">Net</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($run->payslips as $slip)
                        @php $staff = $slip->staff; @endphp
                        <tr data-search="{{ strtolower(trim(($staff?->full_name ?? '').' '.($staff?->employee_id ?? '').' '.($staff?->position ?? ''))) }}">
                            <td>
                                <div class="ac-name-cell">
                                    <span class="ac-avatar">
                                        @if(!empty($staff?->picture))
                                            <img src="{{ asset($staff->picture) }}" alt="">
                                        @else
                                            {{ $initials($staff) ?: 'ST' }}
                                        @endif
                                    </span>
                                    <div>
                                        <strong>{{ $staff?->full_name ?: '—' }}</strong>
                                        <span class="staff-meta">
                                            {{ $staff?->employee_id ?: 'No staff ID' }}
                                            @if($staff?->hrPosition?->name || $staff?->position)
                                                · {{ $staff?->hrPosition?->name ?: $staff?->position }}
                                            @endif
                                            @if($slip->unpaid_leave_days)
                                                · {{ $slip->unpaid_leave_days }} unpaid day{{ $slip->unpaid_leave_days === 1 ? '' : 's' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($slip->basic) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($slip->gross) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($slip->ssnit_employee) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($slip->paye) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($slip->other_deductions) }}</td>
                            <td class="text-end pr-money pr-net">{{ \App\Support\Money::ghs($slip->net) }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr-payslip-print', $slip->id) }}" target="_blank" class="btn btn-sm btn-outline-primary-600">
                                    <i class="ri-printer-line"></i> Print
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="pr-empty">
                                    <i class="ri-file-unknow-line"></i>
                                    No payslips in this run. Assign pay grades or basic salaries, then generate the draft again.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($run->payslips->isNotEmpty())
                    <tfoot>
                        <tr class="fw-semibold">
                            <td>Totals</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->payslips->sum('basic')) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_gross) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_ssnit_employee) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_paye) }}</td>
                            <td class="text-end pr-money">{{ \App\Support\Money::ghs($run->total_other_deductions) }}</td>
                            <td class="text-end pr-money pr-net">{{ \App\Support\Money::ghs($run->total_net) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const input = document.getElementById('payrollSearch');
        const rows = document.querySelectorAll('#payrollTable tbody tr[data-search]');
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
