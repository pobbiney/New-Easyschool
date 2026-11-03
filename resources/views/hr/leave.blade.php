@php
    $pageName = "hr";
    $subpageName = "hr-leave";
    $activeTab = request('tab', session('active_tab', $tab ?? 'requests'));
    $today = now()->toDateString();

    $pendingCount = $requests->where('status', 'pending')->count();
    $approvedCount = $requests->where('status', 'approved')->count();
    $onLeaveToday = $requests->filter(function ($leave) use ($today) {
        return $leave->status === 'approved'
            && $leave->start_date->toDateString() <= $today
            && $leave->end_date->toDateString() >= $today;
    })->unique('staff_id')->count();

    $balancesByStaff = $balances->groupBy('staff_id');

    $typeIcons = [
        'Annual Leave' => 'ri-sun-line',
        'Sick Leave' => 'ri-heart-pulse-line',
        'Maternity Leave' => 'ri-parent-line',
        'Casual Leave' => 'ri-cup-line',
        'Study Leave' => 'ri-book-read-line',
    ];

    $typeThemes = [
        'Annual Leave' => 'amber',
        'Sick Leave' => 'rose',
        'Maternity Leave' => 'pink',
        'Casual Leave' => 'sky',
        'Study Leave' => 'violet',
    ];
    $themeFallback = ['teal', 'orange', 'indigo', 'emerald', 'slate'];
    $typeTheme = function ($type) use ($typeThemes, $themeFallback) {
        $name = is_object($type) ? ($type->name ?? '') : (string) $type;
        if (isset($typeThemes[$name])) {
            return $typeThemes[$name];
        }
        $id = is_object($type) ? (int) $type->id : 0;

        return $themeFallback[$id % count($themeFallback)];
    };

    $initials = function ($staff) {
        return strtoupper(substr((string) ($staff->firstname ?? ''), 0, 1).substr((string) ($staff->surname ?? ''), 0, 1));
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .leave-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 6px;
        background: #f8fafc;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 14px;
        margin-bottom: 24px;
    }
    .leave-tabs .nav-link {
        border: 0;
        background: transparent;
        color: #64748b;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 16px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .leave-tabs .nav-link.active {
        background: #fff;
        color: #25A194;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }
    .leave-tabs .nav-count {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: rgba(37, 161, 148, 0.12);
        color: #1a7a70;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .leave-form-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #f8fffe 100%);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .leave-form-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
    }
    .leave-empty {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }
    .leave-empty i {
        font-size: 36px;
        color: #25A194;
        display: block;
        margin-bottom: 10px;
    }
    .leave-progress {
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        min-width: 80px;
    }
    .leave-progress span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: #25A194;
    }
    .leave-progress.is-low span { background: #f59e0b; }
    .leave-progress.is-empty span { background: #ef4444; }
    .leave-type-card {
        border: 1px solid transparent;
        border-radius: 16px;
        padding: 20px;
        height: 100%;
        transition: box-shadow .15s ease, transform .15s ease;
        position: relative;
        overflow: hidden;
    }
    .leave-type-card::before {
        content: "";
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 5px;
    }
    .leave-type-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .leave-type-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .leave-type-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .leave-theme-amber { background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%); border-color: #fcd34d; }
    .leave-theme-amber::before { background: #f59e0b; }
    .leave-theme-amber .leave-type-icon { background: #f59e0b; color: #fff; }
    .leave-pill-amber { background: #fef3c7; color: #b45309; }

    .leave-theme-rose { background: linear-gradient(180deg, #fff1f2 0%, #fecdd3 100%); border-color: #fda4af; }
    .leave-theme-rose::before { background: #f43f5e; }
    .leave-theme-rose .leave-type-icon { background: #f43f5e; color: #fff; }
    .leave-pill-rose { background: #ffe4e6; color: #be123c; }

    .leave-theme-pink { background: linear-gradient(180deg, #fdf2f8 0%, #fbcfe8 100%); border-color: #f9a8d4; }
    .leave-theme-pink::before { background: #ec4899; }
    .leave-theme-pink .leave-type-icon { background: #ec4899; color: #fff; }
    .leave-pill-pink { background: #fce7f3; color: #be185d; }

    .leave-theme-sky { background: linear-gradient(180deg, #f0f9ff 0%, #bae6fd 100%); border-color: #7dd3fc; }
    .leave-theme-sky::before { background: #0ea5e9; }
    .leave-theme-sky .leave-type-icon { background: #0ea5e9; color: #fff; }
    .leave-pill-sky { background: #e0f2fe; color: #0369a1; }

    .leave-theme-violet { background: linear-gradient(180deg, #f5f3ff 0%, #ddd6fe 100%); border-color: #c4b5fd; }
    .leave-theme-violet::before { background: #8b5cf6; }
    .leave-theme-violet .leave-type-icon { background: #8b5cf6; color: #fff; }
    .leave-pill-violet { background: #ede9fe; color: #6d28d9; }

    .leave-theme-teal { background: linear-gradient(180deg, #f0fdfa 0%, #99f6e4 100%); border-color: #5eead4; }
    .leave-theme-teal::before { background: #14b8a6; }
    .leave-theme-teal .leave-type-icon { background: #14b8a6; color: #fff; }
    .leave-pill-teal { background: #ccfbf1; color: #0f766e; }

    .leave-theme-orange { background: linear-gradient(180deg, #fff7ed 0%, #fed7aa 100%); border-color: #fdba74; }
    .leave-theme-orange::before { background: #ea580c; }
    .leave-theme-orange .leave-type-icon { background: #ea580c; color: #fff; }
    .leave-pill-orange { background: #ffedd5; color: #c2410c; }

    .leave-theme-indigo { background: linear-gradient(180deg, #eef2ff 0%, #c7d2fe 100%); border-color: #a5b4fc; }
    .leave-theme-indigo::before { background: #6366f1; }
    .leave-theme-indigo .leave-type-icon { background: #6366f1; color: #fff; }
    .leave-pill-indigo { background: #e0e7ff; color: #4338ca; }

    .leave-theme-emerald { background: linear-gradient(180deg, #ecfdf5 0%, #a7f3d0 100%); border-color: #6ee7b7; }
    .leave-theme-emerald::before { background: #10b981; }
    .leave-theme-emerald .leave-type-icon { background: #10b981; color: #fff; }
    .leave-pill-emerald { background: #d1fae5; color: #047857; }

    .leave-theme-slate { background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%); border-color: #cbd5e1; }
    .leave-theme-slate::before { background: #64748b; }
    .leave-theme-slate .leave-type-icon { background: #64748b; color: #fff; }
    .leave-pill-slate { background: #e2e8f0; color: #334155; }
    .leave-action-btn {
        min-width: 84px;
    }
    .leave-staff-meta {
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
            ['label' => 'HR', 'url' => route('hr-dashboard')],
            ['label' => 'Leave', 'url' => null, 'active' => true],
        ],
        'title' => 'Leave Management',
        'subtitle' => 'Approve and track leave that teachers submit for '.$year.'.',
    ])

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#25A194;">
            <i class="ri-calendar-event-line"></i>
        </span>
        <div>
            <h5 class="fw-semibold mb-6">Staff leave desk</h5>
            <p class="text-sm text-secondary-light mb-0">Submit a request, review pending approvals, and keep annual balances in one place.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Pending requests</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $pendingCount }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-time-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">On leave today</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $onLeaveToday }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-user-unfollow-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Approved</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $approvedCount }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Leave types</p>
                        <h4 class="fw-semibold mb-0">{{ $leaveTypes->where('status', 'Active')->count() }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-stack-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav leave-tabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-requests" type="button">
                <i class="ri-file-list-3-line"></i> Requests
                @if($pendingCount)<span class="nav-count">{{ $pendingCount }}</span>@endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'balances' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-balances" type="button">
                <i class="ri-pie-chart-line"></i> Balances
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'types' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-types" type="button">
                <i class="ri-settings-3-line"></i> Leave types
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ $activeTab === 'requests' ? 'show active' : '' }}" id="tab-requests">
            <div class="card ac-list-wrapper">
                <div class="card-header py-16 px-24 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-semibold">All requests</h6>
                    <span class="text-sm text-secondary-light">{{ $requests->count() }} record{{ $requests->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $leave)
                                <tr>
                                    <td>
                                        <div class="ac-name-cell">
                                            <span class="ac-avatar">
                                                @if(!empty($leave->staff?->picture))
                                                    <img src="{{ asset($leave->staff->picture) }}" alt="">
                                                @else
                                                    {{ $initials($leave->staff) }}
                                                @endif
                                            </span>
                                            <div>
                                                <div class="fw-semibold">{{ $leave->staff?->full_name }}</div>
                                                <span class="leave-staff-meta">{{ $leave->staff?->position }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="leave-type-pill leave-pill-{{ $typeTheme($leave->leaveType) }}">{{ $leave->leaveType?->name }}</span></td>
                                    <td>
                                        <div class="fw-medium">{{ $leave->start_date->format('d M Y') }}</div>
                                        <span class="leave-staff-meta">to {{ $leave->end_date->format('d M Y') }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ $leave->days }}</td>
                                    <td>
                                        @php
                                            $statusClass = $leave->status === 'approved' ? 'ac-pill-emerald' : ($leave->status === 'rejected' ? 'ac-pill-rose' : 'ac-pill-amber');
                                        @endphp
                                        <span class="ac-pill {{ $statusClass }}">{{ ucfirst($leave->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($leave->status === 'pending')
                                            <div class="d-inline-flex gap-8">
                                                <form method="POST" action="{{ route('hr-leave-review-process') }}">
                                                    @csrf
                                                    <input type="hidden" name="leave_request_id" value="{{ $leave->id }}">
                                                    <input type="hidden" name="decision" value="approved">
                                                    <button class="btn btn-sm btn-success leave-action-btn">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('hr-leave-review-process') }}">
                                                    @csrf
                                                    <input type="hidden" name="leave_request_id" value="{{ $leave->id }}">
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button class="btn btn-sm btn-outline-danger-600 leave-action-btn">Reject</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-secondary-light text-sm">{{ $leave->reviewer?->name ? 'By '.$leave->reviewer->name : '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="leave-empty">
                                            <i class="ri-calendar-check-line"></i>
                                            No leave requests yet. Submit the first one above.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ $activeTab === 'balances' ? 'show active' : '' }}" id="tab-balances">
            <form method="GET" class="card ac-list-wrapper mb-24">
                <input type="hidden" name="tab" value="balances">
                <div class="ac-filter-bar d-flex flex-wrap gap-3 align-items-end">
                    <div>
                        <label class="form-label text-sm fw-semibold mb-8">Leave year</label>
                        <input type="number" name="year" value="{{ $year }}" class="form-control" min="2020" max="2100">
                    </div>
                    <button class="btn btn-outline-primary-600 d-inline-flex align-items-center gap-6">
                        <i class="ri-filter-3-line"></i> Show balances
                    </button>
                </div>
            </form>

            @forelse($balancesByStaff as $staffId => $staffBalances)
                @php $staff = $staffBalances->first()?->staff; @endphp
                <div class="card ac-list-wrapper mb-16">
                    <div class="card-header py-16 px-24 d-flex align-items-center gap-12">
                        <span class="ac-avatar">
                            @if(!empty($staff?->picture))
                                <img src="{{ asset($staff->picture) }}" alt="">
                            @else
                                {{ $initials($staff) }}
                            @endif
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ $staff?->full_name }}</h6>
                            <span class="text-sm text-secondary-light">{{ $staff?->position }}</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th>Leave type</th>
                                    <th>Entitled</th>
                                    <th>Taken</th>
                                    <th>Remaining</th>
                                    <th style="width:160px;">Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffBalances as $balance)
                                    @php
                                        $usedPct = $balance->entitled > 0 ? min(100, round(($balance->taken / $balance->entitled) * 100)) : 0;
                                        $barClass = $usedPct >= 100 ? 'is-empty' : ($usedPct >= 70 ? 'is-low' : '');
                                    @endphp
                                    <tr>
                                        <td><span class="leave-type-pill leave-pill-{{ $typeTheme($balance->leaveType) }}">{{ $balance->leaveType?->name }}</span></td>
                                        <td>{{ $balance->entitled }}</td>
                                        <td>{{ $balance->taken }}</td>
                                        <td class="fw-semibold">{{ $balance->remaining() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-8">
                                                <div class="leave-progress {{ $barClass }} flex-grow-1"><span style="width: {{ $usedPct }}%"></span></div>
                                                <small class="text-secondary-light">{{ $usedPct }}%</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="card ac-list-wrapper">
                    <div class="leave-empty">
                        <i class="ri-pie-chart-line"></i>
                        No balances for {{ $year }}. Add leave types, then open this tab again.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="tab-pane fade {{ $activeTab === 'types' ? 'show active' : '' }}" id="tab-types">
            <div class="leave-form-card">
                <div class="card-header py-16 px-24 d-flex align-items-center gap-12">
                    <span class="ac-avatar" style="width:36px;height:36px;"><i class="ri-add-line"></i></span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Add leave type</h6>
                        <p class="text-xs text-secondary-light mb-0">Changing days per year updates staff entitlements for that leave type.</p>
                    </div>
                </div>
                <div class="card-body p-24">
                    <form method="POST" action="{{ route('hr-leave-types-process') }}" class="row gy-3">
                        @csrf
                        <div class="col-md-3">
                            <label class="text-sm fw-semibold text-primary-light mb-8">Name</label>
                            <input name="name" class="form-control" placeholder="e.g. Compassionate leave" required>
                        </div>
                        <div class="col-md-2">
                            <label class="text-sm fw-semibold text-primary-light mb-8">Days / year</label>
                            <input type="number" name="days_per_year" class="form-control" value="15" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="text-sm fw-semibold text-primary-light mb-8">Pay</label>
                            <select name="is_paid" class="form-control form-select">
                                <option value="1">Paid</option>
                                <option value="0">Unpaid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-sm fw-semibold text-primary-light mb-8">Who can take it</label>
                            <select name="gender_limit" class="form-control form-select">
                                <option value="">All staff</option>
                                <option>Female</option>
                                <option>Male</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-sm fw-semibold text-primary-light mb-8">Status</label>
                            <select name="status" class="form-control form-select">
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-primary-600 w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3">
                @forelse($leaveTypes as $type)
                    @php $theme = $typeTheme($type); @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="leave-type-card leave-theme-{{ $theme }}">
                            <div class="d-flex justify-content-between align-items-start mb-16">
                                <span class="leave-type-icon"><i class="{{ $typeIcons[$type->name] ?? 'ri-calendar-event-line' }}"></i></span>
                                <span class="ac-pill {{ $type->status === 'Active' ? 'ac-pill-emerald' : 'ac-pill-slate' }}">{{ $type->status }}</span>
                            </div>
                            <h6 class="fw-semibold mb-6">{{ $type->name }}</h6>
                            <p class="text-sm mb-16" style="opacity:.8;">
                                {{ $type->days_per_year }} day{{ $type->days_per_year === 1 ? '' : 's' }} each year
                                · {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                                · {{ $type->gender_limit ? $type->gender_limit.' only' : 'All staff' }}
                            </p>
                            <div class="d-flex flex-wrap gap-8">
                            <button type="button" class="btn btn-sm btn-outline-neutral-400 edit-leave-type"
                                data-id="{{ $type->id }}"
                                data-name="{{ $type->name }}"
                                data-days="{{ $type->days_per_year }}"
                                data-paid="{{ $type->is_paid ? '1' : '0' }}"
                                data-gender="{{ $type->gender_limit }}"
                                data-status="{{ $type->status }}">
                                <i class="ri-edit-line"></i> Edit
                            </button>
                            @if($type->isInUse())
                                <button type="button" class="btn btn-sm btn-outline-neutral-400 text-secondary-light"
                                    title="Cannot delete — this leave type is already in use"
                                    disabled style="opacity:0.65;cursor:not-allowed;">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </button>
                            @else
                                <form action="{{ route('hr-leave-types-delete') }}" method="POST" class="d-inline"
                                    data-confirm-title="{{ 'Delete '.$type->name.'?' }}"
                                    data-confirm="This cannot be undone.">
                                    @csrf
                                    <input type="hidden" name="leave_type_id" value="{{ $type->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger-600">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                </form>
                            @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card ac-list-wrapper">
                            <div class="leave-empty">
                                <i class="ri-stack-line"></i>
                                No leave types yet.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editLeaveTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('hr-leave-types-update') }}" class="modal-content">
            @csrf
            <input type="hidden" name="leave_type_id" id="editTypeId">
            <div class="modal-header">
                <h6 class="modal-title">Edit leave type</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="text-sm fw-semibold mb-8">Name</label>
                <input type="text" name="name" id="editTypeName" class="form-control mb-16" required>
                <label class="text-sm fw-semibold mb-8">Days / year</label>
                <input type="number" name="days_per_year" id="editTypeDays" class="form-control mb-16" min="0" required>
                <label class="text-sm fw-semibold mb-8">Pay</label>
                <select name="is_paid" id="editTypePaid" class="form-control form-select mb-16">
                    <option value="1">Paid</option>
                    <option value="0">Unpaid</option>
                </select>
                <label class="text-sm fw-semibold mb-8">Who can take it</label>
                <select name="gender_limit" id="editTypeGender" class="form-control form-select mb-16">
                    <option value="">All staff</option>
                    <option>Female</option>
                    <option>Male</option>
                </select>
                <label class="text-sm fw-semibold mb-8">Status</label>
                <select name="status" id="editTypeStatus" class="form-control form-select">
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary-600">Save changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.edit-leave-type').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('editTypeId').value = this.dataset.id;
            document.getElementById('editTypeName').value = this.dataset.name;
            document.getElementById('editTypeDays').value = this.dataset.days;
            document.getElementById('editTypePaid').value = this.dataset.paid;
            document.getElementById('editTypeGender').value = this.dataset.gender || '';
            document.getElementById('editTypeStatus').value = this.dataset.status;
            new bootstrap.Modal(document.getElementById('editLeaveTypeModal')).show();
        });
    });
</script>
@endsection
