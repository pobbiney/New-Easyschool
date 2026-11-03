@php
    $pageName = "hr";
    $subpageName = "hr-attendance";
    $isToday = $dateCarbon->isToday();
    $prevDate = $dateCarbon->copy()->subDay()->toDateString();
    $nextDate = $dateCarbon->copy()->addDay()->toDateString();
    $todayDate = now()->toDateString();
    $headcount = $staffMembers->count();
    $marked = $headcount - (int) ($summary['unmarked'] ?? 0);
    $initials = function ($staff) {
        return strtoupper(substr((string) ($staff->firstname ?? ''), 0, 1).substr((string) ($staff->surname ?? ''), 0, 1));
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('hr.partials._styles')
<style>
    .at-hero {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12), rgba(99, 102, 241, 0.08));
        margin-bottom: 24px;
    }
    .at-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: #f8fafc;
    }
    .at-search { min-width: 220px; max-width: 280px; }
    .at-status {
        min-width: 132px;
        font-weight: 600;
    }
    .at-status.is-present { color: #15803d; background: #f0fdf4; }
    .at-status.is-absent { color: #be123c; background: #fff1f2; }
    .at-status.is-late { color: #b45309; background: #fffbeb; }
    .at-status.is-on_leave { color: #0369a1; background: #f0f9ff; }
    .staff-meta {
        display: block;
        font-size: 12px;
        color: #64748b;
        font-weight: 400;
    }
    .at-empty {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }
    .at-empty i {
        font-size: 36px;
        color: #25A194;
        display: block;
        margin-bottom: 10px;
    }
    .at-save {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid var(--neutral-200, #e5e7eb);
        background: #fff;
    }
    .at-leave-note {
        display: block;
        font-size: 11px;
        color: #0369a1;
        margin-top: 6px;
        font-weight: 600;
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
            ['label' => 'Attendance', 'url' => null, 'active' => true],
        ],
        'title' => 'Staff Attendance',
        'subtitle' => 'Daily mark sheet for all active employees.',
        'actions' => '<a href="'.route('hr-leave').'" class="btn btn-outline-primary-600"><i class="ri-calendar-event-line"></i> Leave</a>',
    ])

    <div class="at-hero">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-16">
            <div class="d-flex align-items-start gap-16">
                <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#25A194;">
                    <i class="ri-calendar-check-line"></i>
                </span>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-8 mb-6">
                        <h5 class="fw-semibold mb-0">{{ $dateCarbon->format('l, d F Y') }}</h5>
                        @if($isToday)
                            <span class="ac-pill ac-pill-teal">Today</span>
                        @endif
                    </div>
                    <p class="text-sm text-secondary-light mb-0">
                        {{ $headcount }} active staff
                        &nbsp;&bull;&nbsp; {{ $marked }} marked
                        @if(($summary['unmarked'] ?? 0) > 0)
                            &nbsp;&bull;&nbsp; {{ $summary['unmarked'] }} not saved yet
                        @endif
                    </p>
                </div>
            </div>
            <form method="GET" class="d-flex flex-wrap align-items-end gap-8">
                <a href="{{ route('hr-attendance', ['date' => $prevDate]) }}" class="btn btn-outline-neutral-400" title="Previous day">
                    <i class="ri-arrow-left-s-line"></i>
                </a>
                <div>
                    <label class="text-sm fw-semibold mb-8">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                </div>
                <a href="{{ route('hr-attendance', ['date' => $nextDate]) }}" class="btn btn-outline-neutral-400" title="Next day">
                    <i class="ri-arrow-right-s-line"></i>
                </a>
                @if(! $isToday)
                    <a href="{{ route('hr-attendance', ['date' => $todayDate]) }}" class="btn btn-outline-primary-600">Today</a>
                @endif
            </form>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Present</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $summary['present'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Absent</p>
                        <h4 class="fw-semibold mb-0 text-danger-600">{{ $summary['absent'] }}</h4>
                    </div>
                    <span class="stat-icon bg-danger-100 text-danger-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Late</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $summary['late'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-time-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">On leave</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $summary['on_leave'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-user-unfollow-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Unmarked</p>
                        <h4 class="fw-semibold mb-0">{{ $summary['unmarked'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-question-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('hr-attendance-process') }}" id="attendanceForm">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <div class="card ac-list-wrapper">
            <div class="at-toolbar">
                <div>
                    <h6 class="mb-0 fw-semibold">Daily mark sheet</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">Staff on approved leave are locked for this date.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-8">
                    <button type="button" class="btn btn-sm btn-outline-primary-600" id="markAllPresent">Mark unmarked present</button>
                    <div class="at-search">
                        <input type="search" id="attendanceSearch" class="form-control" placeholder="Search staff…">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table bordered-table mb-0" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Status</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffMembers as $member)
                            @php
                                $record = $records->get($member->id);
                                $forcedLeave = in_array($member->id, $onLeaveIds, true);
                                $status = $forcedLeave ? 'on_leave' : ($record->status ?? 'present');
                                $position = $member->hrPosition?->name ?: ($member->position ?: '—');
                                $department = $member->department?->name;
                            @endphp
                            <tr data-search="{{ strtolower(trim($member->full_name.' '.($member->employee_id ?? '').' '.$position.' '.($department ?? ''))) }}" data-unmarked="{{ $record || $forcedLeave ? '0' : '1' }}">
                                <td>
                                    <div class="ac-name-cell">
                                        <span class="ac-avatar">
                                            @if(!empty($member->picture))
                                                <img src="{{ asset($member->picture) }}" alt="">
                                            @else
                                                {{ $initials($member) ?: 'ST' }}
                                            @endif
                                        </span>
                                        <div>
                                            <strong>{{ $member->full_name }}</strong>
                                            <span class="staff-meta">
                                                {{ $member->employee_id ?: 'No staff ID' }} · {{ $position }}
                                                @if($department) · {{ $department }} @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select name="attendance[{{ $member->id }}][status]" class="form-control form-select at-status is-{{ $status }}" @disabled($forcedLeave)>
                                        @foreach(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'on_leave' => 'On leave'] as $value => $label)
                                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @if($forcedLeave)
                                        <input type="hidden" name="attendance[{{ $member->id }}][status]" value="on_leave">
                                        <span class="at-leave-note">Approved leave</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="time" name="attendance[{{ $member->id }}][check_in]" class="form-control" value="{{ $record->check_in ?? '' }}" @disabled($forcedLeave)>
                                </td>
                                <td>
                                    <input type="time" name="attendance[{{ $member->id }}][check_out]" class="form-control" value="{{ $record->check_out ?? '' }}" @disabled($forcedLeave)>
                                </td>
                                <td>
                                    <input type="text" name="attendance[{{ $member->id }}][remarks]" class="form-control" value="{{ $record->remarks ?? '' }}" placeholder="Optional" @disabled($forcedLeave)>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="at-empty">
                                        <i class="ri-team-line"></i>
                                        No active staff to mark. Add employees first.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($staffMembers->isNotEmpty())
                <div class="at-save">
                    <p class="text-sm text-secondary-light mb-0">Saving writes the mark sheet for {{ $dateCarbon->format('d M Y') }}.</p>
                    <button class="btn btn-primary-600">
                        <i class="ri-save-line"></i> Save attendance
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const search = document.getElementById('attendanceSearch');
        const rows = document.querySelectorAll('#attendanceTable tbody tr[data-search]');
        const markAll = document.getElementById('markAllPresent');

        function paintSelect(select) {
            select.classList.remove('is-present', 'is-absent', 'is-late', 'is-on_leave');
            select.classList.add('is-' + select.value);
        }

        document.querySelectorAll('.at-status').forEach(function (select) {
            paintSelect(select);
            select.addEventListener('change', function () {
                paintSelect(select);
            });
        });

        if (search) {
            search.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                rows.forEach(function (row) {
                    row.style.display = !q || row.getAttribute('data-search').includes(q) ? '' : 'none';
                });
            });
        }

        if (markAll) {
            markAll.addEventListener('click', function () {
                rows.forEach(function (row) {
                    if (row.getAttribute('data-unmarked') !== '1') return;
                    const select = row.querySelector('.at-status');
                    if (!select || select.disabled) return;
                    select.value = 'present';
                    paintSelect(select);
                });
            });
        }
    })();
</script>
@endsection
