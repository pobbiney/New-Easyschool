@php $pageName = "hr"; $subpageName = "hr-dashboard"; @endphp
@extends('layouts.app')
@section('css')
@include('hr.partials._styles')
@endsection
@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HR Dashboard', 'url' => null, 'active' => true],
        ],
        'title' => 'HR Dashboard',
        'subtitle' => 'People, leave, attendance, and payroll at a glance.',
    ])

    <div class="row g-3 mb-24">
        @foreach([
            ['label' => 'Active staff', 'value' => $stats['headcount'], 'icon' => 'ri-team-line', 'bg' => 'bg-primary-50 text-primary-600'],
            ['label' => 'On leave today', 'value' => $stats['on_leave'], 'icon' => 'ri-calendar-event-line', 'bg' => 'bg-warning-100 text-warning-600'],
            ['label' => 'Pending leave', 'value' => $stats['pending_leave'], 'icon' => 'ri-time-line', 'bg' => 'bg-info-100 text-info-600'],
            ['label' => 'Present today', 'value' => $stats['present_today'], 'icon' => 'ri-checkbox-circle-line', 'bg' => 'bg-success-100 text-success-600'],
        ] as $card)
        <div class="col-md-3 col-sm-6">
            <div class="hr-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">{{ $card['label'] }}</div>
                        <h4 class="fw-semibold mb-0">{{ $card['value'] }}</h4>
                    </div>
                    <span class="stat-icon {{ $card['bg'] }}"><i class="{{ $card['icon'] }}"></i></span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card hr-list-wrapper">
                <div class="card-header py-16 px-24 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Pending leave requests</h6>
                    <a href="{{ route('hr-leave') }}" class="text-sm">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead><tr><th>Staff</th><th>Type</th><th>Dates</th><th>Days</th></tr></thead>
                        <tbody>
                            @forelse($pendingLeave as $leave)
                                <tr>
                                    <td>{{ $leave->staff?->full_name }}</td>
                                    <td>{{ $leave->leaveType?->name }}</td>
                                    <td>{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</td>
                                    <td>{{ $leave->days }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary-light py-20">No pending requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card hr-list-wrapper mb-24">
                <div class="card-header py-16 px-24">
                    <h6 class="mb-0 fw-semibold">Latest payroll</h6>
                </div>
                <div class="card-body p-24">
                    @if($stats['last_payroll_label'])
                        <p class="mb-8 fw-semibold">{{ $stats['last_payroll_label'] }}</p>
                        <p class="mb-8">Status: <span class="hr-pill hr-pill-info">{{ ucfirst($stats['last_payroll_status']) }}</span></p>
                        <p class="mb-0">Net pay: <strong>{{ \App\Support\Money::ghs($stats['last_payroll_net']) }}</strong></p>
                    @else
                        <p class="text-secondary-light mb-0">No payroll run yet.</p>
                    @endif
                    <a href="{{ route('hr-payroll') }}" class="btn btn-primary-600 mt-16">Open payroll</a>
                </div>
            </div>
            <div class="card hr-list-wrapper">
                <div class="card-header py-16 px-24"><h6 class="mb-0 fw-semibold">Recent payrolls</h6></div>
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <tbody>
                            @forelse($recentPayrolls as $run)
                                <tr>
                                    <td>{{ $run->periodLabel() }}</td>
                                    <td>{{ ucfirst($run->status) }}</td>
                                    <td>{{ \App\Support\Money::ghs($run->total_net) }}</td>
                                </tr>
                            @empty
                                <tr><td class="text-secondary-light">None yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
