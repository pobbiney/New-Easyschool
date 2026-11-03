@php $pageName = "bill-management"; $subpageName = "student-bills"; @endphp
@extends('layouts.app')
@section('css')
<style>
    .sb-page { --sb-teal: #25A194; --sb-indigo: #6366f1; }

    /* Stat cards */
    .sb-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    @media (max-width: 991px) { .sb-stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .sb-stat-grid { grid-template-columns: 1fr; } }
    .sb-stat {
        position: relative; border-radius: 16px; padding: 20px 22px; background: #fff;
        border: 1px solid #e5e7eb; overflow: hidden;
        box-shadow: 0 4px 18px rgba(15,23,42,.04);
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    }
    .sb-stat::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--sb-teal), var(--sb-indigo));
    }
    .sb-stat.is-paid::before { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .sb-stat.is-outstanding::before { background: linear-gradient(90deg, #f59e0b, #dc2626); }
    .sb-stat-lbl { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
    .sb-stat-val { font-size: 26px; font-weight: 800; color: #111827; letter-spacing: -.02em; line-height: 1.1; }
    .sb-stat-val.is-success { color: #15803d; }
    .sb-stat-val.is-warning { color: #dc2626; }
    .sb-stat-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 22px;
        background: rgba(37,161,148,.1); color: var(--sb-teal);
    }
    .sb-stat.is-paid .sb-stat-icon { background: rgba(34,197,94,.12); color: #15803d; }
    .sb-stat.is-outstanding .sb-stat-icon { background: rgba(245,158,11,.12); color: #b45309; }

    /* Filters */
    .sb-filter-card {
        border: 1px solid #e5e7eb; border-radius: 16px; background: #fff;
        box-shadow: 0 4px 18px rgba(15,23,42,.04); margin-bottom: 24px; overflow: hidden;
    }
    .sb-filter-head {
        padding: 16px 24px; border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        background: linear-gradient(180deg, #fafafa, #fff);
    }
    .sb-filter-head h6 { margin: 0; font-size: 14px; font-weight: 700; color: #374151; display: flex; align-items: center; gap: 8px; }
    .sb-filter-head h6 i { color: var(--sb-teal); }
    .sb-filter-body { padding: 20px 24px 24px; }
    .sb-filter-body label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 6px; display: block; }
    .sb-filter-body .form-select { border-radius: 10px; min-height: 42px; font-size: 13px; }

    /* Ledger table */
    .sb-ledger-card {
        border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden;
        box-shadow: 0 4px 18px rgba(15,23,42,.04); background: #fff;
    }
    .sb-ledger-head {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
        padding: 18px 24px; border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #fafafa, #fff);
    }
    .sb-ledger-head h6 { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
    .sb-ledger-head p { margin: 4px 0 0; font-size: 12px; color: #9ca3af; }
    .sb-ledger-card .dataTable-wrapper { padding: 0; }
    .sb-ledger-card table.dataTable { margin: 0 !important; }
    .sb-ledger-card table.dataTable thead th {
        background: #f9fafb; font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #6b7280; padding: 14px 16px; border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .sb-ledger-card table.dataTable tbody td {
        padding: 14px 16px; vertical-align: middle; font-size: 13px; border-bottom: 1px solid #f3f4f6;
    }
    .sb-ledger-card table.dataTable tbody tr:hover td { background: rgba(37,161,148,.03); }
    .sb-ledger-card .sb-col-actions { min-width: 300px; }

    /* Student cell */
    .sb-student { display: flex; align-items: center; gap: 12px; min-width: 180px; }
    .sb-student-avatar {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; color: var(--sb-teal);
        background: linear-gradient(135deg, rgba(37,161,148,.12), rgba(99,102,241,.08));
        border: 1px solid rgba(37,161,148,.15);
    }
    .sb-student-name { font-weight: 700; color: #111827; line-height: 1.3; }
    .sb-student-id { font-size: 11px; color: #9ca3af; font-weight: 600; }

    /* Money cells */
    .sb-money { font-variant-numeric: tabular-nums; font-weight: 600; color: #374151; }
    .sb-money.is-balance { font-weight: 800; color: #dc2626; }
    .sb-money.is-balance.is-clear { color: #15803d; }
    .sb-money.is-paid { color: #15803d; }

    /* Status & credit pills */
    .status-pill { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .status-pill.Pending { background: rgba(245,158,11,.14); color: #b45309; }
    .status-pill.Partial { background: rgba(59,130,246,.14); color: #1d4ed8; }
    .status-pill.Paid { background: rgba(34,197,94,.14); color: #15803d; }
    .status-pill.No-Bills, .status-pill.no-bills { background: #f3f4f6; color: #6b7280; }
    .status-pill.Credit { background: rgba(34,197,94,.14); color: #15803d; }
    .credit-pill {
        display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
        border-radius: 999px; font-size: 11px; font-weight: 700;
        background: rgba(34,197,94,.14); color: #15803d;
    }
    .credit-zero { color: #d1d5db; font-size: 13px; font-weight: 600; }
    .sb-status-wrap { display: flex; flex-wrap: wrap; gap: 4px; align-items: center; }

    /* Action buttons */
    .sb-actions { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .sb-action {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px; border-radius: 10px; font-size: 12px; font-weight: 700;
        line-height: 1; text-decoration: none; border: 1px solid transparent;
        transition: all .15s ease; white-space: nowrap; cursor: pointer;
    }
    .sb-action i { font-size: 15px; }
    .sb-action-view {
        color: var(--sb-teal); background: rgba(37,161,148,.08); border-color: rgba(37,161,148,.2);
    }
    .sb-action-view:hover { background: rgba(37,161,148,.15); color: #1d8a80; border-color: rgba(37,161,148,.35); }
    .sb-action-print {
        color: #4b5563; background: #f9fafb; border-color: #e5e7eb;
    }
    .sb-action-print:hover { background: #f3f4f6; color: #111827; border-color: #d1d5db; }
    .sb-action-pay {
        color: #fff; background: linear-gradient(135deg, #ef4444, #dc2626);
        border-color: transparent; box-shadow: 0 2px 8px rgba(239,68,68,.3);
    }
    .sb-action-pay i { color: #fff; }
    .sb-action-pay:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,.4); background: linear-gradient(135deg, #f87171, #ef4444); }
    .sb-action-pay.is-disabled {
        opacity: .45; cursor: not-allowed; pointer-events: none;
        background: #e5e7eb; color: #9ca3af; box-shadow: none;
    }
    .sb-action-pay.is-disabled i { color: #9ca3af; }

    @media (max-width: 768px) {
        .sb-action span { display: none; }
        .sb-action { padding: 8px 10px; }
        .sb-col-actions { min-width: 120px !important; }
    }

    /* Modal styles (unchanged) */
    #billBreakdownModal .modal-content { border: none; overflow: hidden; box-shadow: 0 24px 48px rgba(15,23,42,.16); }
    #billBreakdownModal .modal-dialog { max-width: 720px; }

    .bbm-shell { background: #fff; }
    .bbm-hero {
        position: relative;
        padding: 24px 24px 20px;
        background: linear-gradient(135deg, #25A194 0%, #1d8a80 45%, #6366f1 100%);
        color: #fff;
        overflow: hidden;
    }
    .bbm-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -30px;
        width: 140px; height: 140px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .bbm-hero::after {
        content: '';
        position: absolute;
        bottom: -50px; left: 20%;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
    }
    .bbm-hero-top { position: relative; z-index: 1; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .bbm-hero-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .bbm-avatar {
        width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 800; letter-spacing: .02em;
        background: rgba(255,255,255,.18); border: 2px solid rgba(255,255,255,.28);
        backdrop-filter: blur(4px);
    }
    .bbm-title { font-size: 18px; font-weight: 700; margin: 0 0 4px; letter-spacing: -.01em; }
    .bbm-subtitle { font-size: 12px; opacity: .88; margin: 0; }
    .bbm-close {
        width: 34px; height: 34px; border-radius: 10px; border: none; flex-shrink: 0;
        background: rgba(255,255,255,.16); color: #fff; font-size: 18px; line-height: 1;
        display: inline-flex; align-items: center; justify-content: center;
        transition: background .15s;
    }
    .bbm-close:hover { background: rgba(255,255,255,.28); color: #fff; }

    .bbm-chips { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .bbm-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px; border-radius: 999px; font-size: 11px; font-weight: 600;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2);
    }
    .bbm-chip i { font-size: 13px; opacity: .9; }

    .bbm-stats {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 10px; padding: 16px 20px 0;
        margin-top: -12px; position: relative; z-index: 2;
    }
    @media (max-width: 768px) { .bbm-stats { grid-template-columns: repeat(2, 1fr); margin-top: 0; padding-top: 14px; } }
    .bbm-stat {
        border-radius: 14px; padding: 14px 16px; text-align: center;
        border: 1px solid #e5e7eb; background: #fff;
        box-shadow: 0 4px 14px rgba(15,23,42,.06);
    }
    .bbm-stat .lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; }
    .bbm-stat .val { font-size: 17px; font-weight: 800; color: #111827; }
    .bbm-stat.due .val { color: #6366f1; }
    .bbm-stat.paid .val { color: #15803d; }
    .bbm-stat.balance .val { color: #dc2626; }
    .bbm-stat.credit .val { color: #15803d; }

    .bbm-progress-wrap { padding: 14px 20px 0; }
    .bbm-progress-label { display: flex; justify-content: space-between; font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
    .bbm-progress-label span:last-child { color: #25A194; }
    .bbm-progress { height: 8px; border-radius: 999px; background: #f3f4f6; overflow: hidden; }
    .bbm-progress-bar {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, #25A194, #6366f1);
        transition: width .4s ease;
    }

    .bbm-body { padding: 16px 20px 20px; max-height: 380px; overflow-y: auto; }
    .bbm-section-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: #9ca3af; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;
    }
    .bbm-section-title::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

    .bbm-bill {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 14px; border-radius: 13px; margin-bottom: 8px;
        border: 1px solid #f3f4f6; background: #fafafa;
        transition: transform .12s, box-shadow .12s, border-color .12s;
    }
    .bbm-bill:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15,23,42,.06); border-color: #e5e7eb; background: #fff; }
    .bbm-bill.is-paid { border-left: 4px solid #22c55e; }
    .bbm-bill.is-partial { border-left: 4px solid #3b82f6; }
    .bbm-bill.is-pending { border-left: 4px solid #f59e0b; }

    .bbm-bill-icon {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .bbm-bill.is-paid .bbm-bill-icon { background: rgba(34,197,94,.12); color: #15803d; }
    .bbm-bill.is-partial .bbm-bill-icon { background: rgba(59,130,246,.12); color: #1d4ed8; }
    .bbm-bill.is-pending .bbm-bill-icon { background: rgba(245,158,11,.12); color: #b45309; }

    .bbm-bill-info { flex: 1; min-width: 0; }
    .bbm-bill-name { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bbm-bill-meta { font-size: 11px; color: #9ca3af; }

    .bbm-bill-right { text-align: right; flex-shrink: 0; }
    .bbm-bill-balance { font-size: 14px; font-weight: 800; color: #111827; }
    .bbm-bill-sub { font-size: 10px; color: #9ca3af; margin-top: 2px; }

    .bbm-status {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700;
        margin-top: 4px;
    }
    .bbm-status.Paid { background: rgba(34,197,94,.12); color: #15803d; }
    .bbm-status.Partial { background: rgba(59,130,246,.12); color: #1d4ed8; }
    .bbm-status.Pending { background: rgba(245,158,11,.12); color: #b45309; }

    .bbm-footer {
        padding: 14px 20px 18px; border-top: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fafafa, #fff);
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .bbm-footer-label { font-size: 12px; color: #6b7280; font-weight: 600; }
    .bbm-footer-total { font-size: 20px; font-weight: 800; color: #dc2626; letter-spacing: -.02em; }
    .bbm-footer-total.is-clear { color: #15803d; }

    .bbm-loading { padding: 48px 24px; text-align: center; }
    .bbm-spinner {
        width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 14px;
        border: 3px solid rgba(37,161,148,.15); border-top-color: #25A194;
        animation: bbmSpin .7s linear infinite;
    }
    @keyframes bbmSpin { to { transform: rotate(360deg); } }
    .bbm-loading-text { font-size: 13px; color: #9ca3af; font-weight: 600; }

    .bbm-empty { text-align: center; padding: 40px 20px; }
    .bbm-empty-icon {
        width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(99,102,241,.08); color: #6366f1; font-size: 26px;
    }
    .bbm-empty-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 4px; }
    .bbm-empty-sub { font-size: 12px; color: #9ca3af; margin: 0; }
</style>
@endsection
@section('content')
<div class="dashboard-main-body sb-page">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div><a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a><span class="text-secondary-light"> / Student Bills</span></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('category-bill-setup') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6"><i class="ri-settings-3-line"></i> Bill Setup</a>
            <a href="{{ route('student-bills-print', request()->query()) }}" target="_blank" class="btn btn-primary-600 d-flex align-items-center gap-6"><i class="ri-printer-line"></i> Print Ledger</a>
        </div>
    </div>

    <div class="sb-stat-grid">
        <div class="sb-stat">
            <div>
                <div class="sb-stat-lbl">Students in ledger</div>
                <div class="sb-stat-val">{{ $stats['students'] }}</div>
            </div>
            <div class="sb-stat-icon"><i class="ri-group-line"></i></div>
        </div>
        <div class="sb-stat">
            <div>
                <div class="sb-stat-lbl">Total billed</div>
                <div class="sb-stat-val">₵{{ number_format($stats['total_due'], 2) }}</div>
            </div>
            <div class="sb-stat-icon"><i class="ri-file-list-3-line"></i></div>
        </div>
        <div class="sb-stat is-paid">
            <div>
                <div class="sb-stat-lbl">Total collected</div>
                <div class="sb-stat-val is-success">₵{{ number_format($stats['total_paid'], 2) }}</div>
            </div>
            <div class="sb-stat-icon"><i class="ri-check-double-line"></i></div>
        </div>
        <div class="sb-stat is-outstanding">
            <div>
                <div class="sb-stat-lbl">Outstanding</div>
                <div class="sb-stat-val is-warning">₵{{ number_format($stats['outstanding'], 2) }}</div>
            </div>
            <div class="sb-stat-icon"><i class="ri-error-warning-line"></i></div>
        </div>
    </div>

    <div class="sb-filter-card">
        <div class="sb-filter-head">
            <h6><i class="ri-filter-3-line"></i> Filter ledger</h6>
            @if(collect($filters ?? [])->filter()->isNotEmpty())
                <a href="{{ route('student-bills') }}" class="text-sm text-primary-600 fw-semibold text-decoration-none">Clear filters</a>
            @endif
        </div>
        <div class="sb-filter-body">
            <form method="GET" action="{{ route('student-bills') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label>Term</label>
                    <select name="academic_term_id" class="form-select"><option value="">All terms</option>@foreach($academicTerms as $t)<option value="{{ $t->id }}" @selected(($filters['academic_term_id'] ?? '') == $t->id)>{{ $t->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-2">
                    <label>Year</label>
                    <select name="academic_year_id" class="form-select"><option value="">All years</option>@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? '') == $y->id)>{{ $y->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-2">
                    <label>Category</label>
                    <select name="class_category_id" class="form-select"><option value="">All categories</option>@foreach($classCategories as $c)<option value="{{ $c->id }}" @selected(($filters['class_category_id'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-2">
                    <label>Class</label>
                    <select name="school_class_id" class="form-select"><option value="">All classes</option>@foreach($schoolClasses as $c)<option value="{{ $c->id }}" @selected(($filters['school_class_id'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-select"><option value="">All statuses</option>@foreach(['Pending','Partial','Paid','No Bills','Has Credit'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') == $s)>{{ $s }}</option>@endforeach</select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-600 w-100 d-flex align-items-center justify-content-center gap-6"><i class="ri-search-line"></i> Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="sb-ledger-card">
        <div class="sb-ledger-head">
            <div>
                <h6>Student Bill Ledger</h6>
                <p>{{ $rows->count() }} student{{ $rows->count() === 1 ? '' : 's' }} · Click <strong>View bills</strong> for a breakdown</p>
            </div>
            <form class="navbar-search dt-search m-0">
                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search students...">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
            </form>
        </div>
        <div class="dataTable-wrapper">
            @if($rows->isNotEmpty())
            <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Category</th>
                        <th>Total Due</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Credit</th>
                        <th>Status</th>
                        <th class="sb-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    @php
                        $initials = strtoupper(substr($row->student->firstname ?? '', 0, 1) . substr($row->student->surname ?? '', 0, 1));
                    @endphp
                    <tr>
                        <td data-search="{{ $row->student->full_name }} {{ $row->student->student_id }} {{ $row->student->firstname }} {{ $row->student->surname }} {{ $row->student->othername }}">
                            <div class="sb-student">
                                <span class="sb-student-avatar">{{ $initials ?: '?' }}</span>
                                <div>
                                    <div class="sb-student-name">{{ $row->student->full_name }}</div>
                                    <div class="sb-student-id">{{ $row->student->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $row->student->class_name ?: '—' }}</td>
                        <td>{{ $row->student->schoolClass?->category?->name ?: '—' }}</td>
                        <td><span class="sb-money">₵{{ number_format($row->total_due, 2) }}</span></td>
                        <td><span class="sb-money is-paid">₵{{ number_format($row->total_paid, 2) }}</span></td>
                        <td><span class="sb-money is-balance {{ $row->balance <= 0 ? 'is-clear' : '' }}">₵{{ number_format($row->balance, 2) }}</span></td>
                        <td>
                            @if($row->credit_balance > 0)
                                <span class="credit-pill"><i class="ri-wallet-3-line"></i> ₵{{ number_format($row->credit_balance, 2) }}</span>
                            @else
                                <span class="credit-zero">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="sb-status-wrap">
                                <span class="status-pill {{ $row->status === 'No Bills' ? 'no-bills' : $row->status }}">{{ $row->status }}</span>
                                @if($row->credit_balance > 0)
                                    <span class="status-pill Credit">Credit</span>
                                @endif
                            </div>
                        </td>
                        <td class="sb-col-actions">
                            <div class="sb-actions">
                                <button type="button" class="sb-action sb-action-view view-bills-btn" data-id="{{ $row->student->id }}">
                                    <i class="ri-eye-line"></i><span>View bills</span>
                                </button>
                                <a href="{{ route('student-bill-print', array_merge(['id' => $row->student->id], request()->query())) }}" target="_blank" class="sb-action sb-action-print">
                                    <i class="ri-printer-line"></i><span>Print</span>
                                </a>
                                @if($row->balance > 0)
                                    <a href="{{ route('record-bill-payment', $row->student->id) }}" class="sb-action sb-action-pay">
                                        <i class="ri-money-dollar-circle-line"></i><span>Pay now</span>
                                    </a>
                                @else
                                    <span class="sb-action sb-action-pay is-disabled">
                                        <i class="ri-money-dollar-circle-line"></i><span>Pay now</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-56 px-24 text-secondary-light">
                <i class="ri-file-search-line" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
                No students match the selected filters.
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="billBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12">
            <div id="billBreakdownContent" class="bbm-shell">
                <div class="bbm-loading">
                    <div class="bbm-spinner"></div>
                    <p class="bbm-loading-text mb-0">Loading bill breakdown...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    const billsUrl = @json(url('get-student-bills'));

    function bbmEsc(value) {
        return $('<div>').text(value ?? '').html();
    }

    function bbmMoney(value) {
        return parseFloat(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function bbmInitials(name) {
        const parts = (name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '?';
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function bbmStatusClass(status) {
        if (status === 'Paid') return 'is-paid';
        if (status === 'Partial') return 'is-partial';
        return 'is-pending';
    }

    function bbmStatusIcon(status) {
        if (status === 'Paid') return 'ri-checkbox-circle-fill';
        if (status === 'Partial') return 'ri-time-fill';
        return 'ri-error-warning-fill';
    }

    function bbmLoadingHtml() {
        return '<div class="bbm-loading"><div class="bbm-spinner"></div><p class="bbm-loading-text mb-0">Loading bill breakdown...</p></div>';
    }

    function bbmRenderBreakdown(data) {
        const student = data.student || {};
        const summary = data.summary || {};
        const bills = data.bills || [];
        const totalDue = parseFloat(summary.total_due || 0);
        const totalPaid = parseFloat(summary.total_paid || 0);
        const balance = parseFloat(summary.balance || 0);
        const creditBalance = parseFloat(summary.credit_balance || 0);
        const paidPct = totalDue > 0 ? Math.min(100, Math.round((totalPaid / totalDue) * 100)) : 0;
        const initials = bbmInitials(student.full_name);

        let billCards = '';
        if (bills.length) {
            billCards = bills.map(function(b) {
                const statusClass = bbmStatusClass(b.status);
                const period = [b.term_name, b.year_name].filter(Boolean).join(' · ') || '—';
                const category = b.category_name ? ' · ' + b.category_name : '';
                return (
                    '<div class="bbm-bill ' + statusClass + '">' +
                        '<div class="bbm-bill-icon"><i class="' + bbmStatusIcon(b.status) + '"></i></div>' +
                        '<div class="bbm-bill-info">' +
                            '<div class="bbm-bill-name">' + bbmEsc(b.item_name) + '</div>' +
                            '<div class="bbm-bill-meta">' + bbmEsc(period + category) + '</div>' +
                        '</div>' +
                        '<div class="bbm-bill-right">' +
                            '<div class="bbm-bill-balance">₵' + bbmMoney(b.balance) + '</div>' +
                            '<div class="bbm-bill-sub">Due ₵' + bbmMoney(b.amount_due) + ' · Paid ₵' + bbmMoney(b.amount_paid) + '</div>' +
                            '<span class="bbm-status ' + bbmEsc(b.status) + '">' + bbmEsc(b.status) + '</span>' +
                        '</div>' +
                    '</div>'
                );
            }).join('');
        } else {
            billCards = (
                '<div class="bbm-empty">' +
                    '<div class="bbm-empty-icon"><i class="ri-file-list-3-line"></i></div>' +
                    '<div class="bbm-empty-title">No bills found</div>' +
                    '<p class="bbm-empty-sub">This student has no bill items for the selected period.</p>' +
                '</div>'
            );
        }

        const chips = [
            student.class_name ? '<span class="bbm-chip"><i class="ri-book-open-line"></i>' + bbmEsc(student.class_name) + '</span>' : '',
            student.category_name ? '<span class="bbm-chip"><i class="ri-stack-line"></i>' + bbmEsc(student.category_name) + '</span>' : '',
            student.student_id ? '<span class="bbm-chip"><i class="ri-hashtag"></i>' + bbmEsc(student.student_id) + '</span>' : '',
        ].filter(Boolean).join('');

        return (
            '<div class="bbm-hero">' +
                '<div class="bbm-hero-top">' +
                    '<div class="bbm-hero-left">' +
                        '<div class="bbm-avatar">' + bbmEsc(initials) + '</div>' +
                        '<div>' +
                            '<h5 class="bbm-title">' + bbmEsc(student.full_name) + '</h5>' +
                            '<p class="bbm-subtitle">Bill breakdown overview</p>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="bbm-close" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-line"></i></button>' +
                '</div>' +
                (chips ? '<div class="bbm-chips">' + chips + '</div>' : '') +
            '</div>' +
            '<div class="bbm-stats">' +
                '<div class="bbm-stat due"><div class="lbl">Total Due</div><div class="val">₵' + bbmMoney(totalDue) + '</div></div>' +
                '<div class="bbm-stat paid"><div class="lbl">Total Paid</div><div class="val">₵' + bbmMoney(totalPaid) + '</div></div>' +
                '<div class="bbm-stat balance"><div class="lbl">Outstanding</div><div class="val">₵' + bbmMoney(balance) + '</div></div>' +
                '<div class="bbm-stat credit"><div class="lbl">Credit Balance</div><div class="val">₵' + bbmMoney(creditBalance) + '</div></div>' +
            '</div>' +
            (totalDue > 0 ? (
                '<div class="bbm-progress-wrap">' +
                    '<div class="bbm-progress-label"><span>Payment progress</span><span>' + paidPct + '% paid</span></div>' +
                    '<div class="bbm-progress"><div class="bbm-progress-bar" style="width:' + paidPct + '%"></div></div>' +
                '</div>'
            ) : '') +
            '<div class="bbm-body">' +
                '<div class="bbm-section-title">Bill items <span style="font-weight:600;color:#25A194;">(' + bills.length + ')</span></div>' +
                billCards +
            '</div>' +
            '<div class="bbm-footer">' +
                '<span class="bbm-footer-label">Amount still outstanding</span>' +
                '<span class="bbm-footer-total ' + (balance <= 0 ? 'is-clear' : '') + '">₵' + bbmMoney(balance) + '</span>' +
            '</div>'
        );
    }

    function showBillBreakdownModal() {
        const modalEl = document.getElementById('billBreakdownModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function openBillBreakdown(id) {
        $('#billBreakdownContent').html(bbmLoadingHtml());
        showBillBreakdownModal();

        $.get(billsUrl + '/' + id, function(data) {
            $('#billBreakdownContent').html(bbmRenderBreakdown(data));
        }).fail(function() {
            $('#billBreakdownContent').html(
                '<div class="bbm-empty">' +
                    '<div class="bbm-empty-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ri-error-warning-line"></i></div>' +
                    '<div class="bbm-empty-title">Unable to load bills</div>' +
                    '<p class="bbm-empty-sub">Please try again in a moment.</p>' +
                '</div>'
            );
        });
    }

    $(document).on('click', '.view-bills-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).attr('data-id');
        if (!id) {
            return;
        }
        openBillBreakdown(id);
    });
</script>
@endsection
