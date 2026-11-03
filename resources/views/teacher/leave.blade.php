@php $pageName = "leave"; $subpageName = "leave-application"; @endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">LEAVE</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Request leave</span>
            </div>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-calendar-event-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Request leave</h5>
            <p class="text-sm text-secondary-light mb-0">Apply for your own leave. An administrator or HR approves or rejects the request.</p>
        </div>
    </div>

    @if(! $staff)
        <div class="card ac-list-wrapper">
            <div class="card-body text-center py-56 px-24">
                <span class="ac-pill ac-pill-amber mb-12"><i class="ri-information-line"></i> Staff record required</span>
                <p class="text-secondary-light mb-0">Your login is not linked to a staff record, so leave cannot be submitted. Ask an administrator to link your account.</p>
            </div>
        </div>
    @else
        <div class="row gy-4 mb-24">
            @forelse($balances as $balance)
                <div class="col-sm-6 col-xl-3">
                    <div class="ac-stat-card h-100">
                        <p class="text-secondary-light text-sm mb-4">{{ $balance->leaveType?->name }}</p>
                        <h4 class="fw-semibold mb-4">{{ $balance->remaining() }} <span class="text-sm fw-medium text-secondary-light">days left</span></h4>
                        <p class="text-xs text-secondary-light mb-0">{{ $balance->taken }} used of {{ $balance->entitled }} in {{ $year }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-sm text-secondary-light mb-0">No leave types are available for you yet.</p>
                </div>
            @endforelse
        </div>

        <div class="card ac-list-wrapper mb-24">
            <div class="card-header py-16 px-24">
                <h6 class="mb-0 fw-semibold">New leave request</h6>
                <p class="text-sm text-secondary-light mb-0 mt-4">This request is for {{ $staff->full_name }} and stays pending until it is reviewed.</p>
            </div>
            <div class="card-body p-24">
                <form method="POST" action="{{ route('leave-application-process') }}" class="row gy-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="text-sm fw-semibold text-primary-light mb-8">Leave type</label>
                        <select name="leave_type_id" class="form-control form-select" required>
                            <option value="">Select type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ (string) old('leave_type_id') === (string) $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->days_per_year }} days{{ $type->is_paid ? '' : ', unpaid' }})
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id') <small class="text-danger-600">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="text-sm fw-semibold text-primary-light mb-8">Start date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                        @error('start_date') <small class="text-danger-600">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="text-sm fw-semibold text-primary-light mb-8">End date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                        @error('end_date') <small class="text-danger-600">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12">
                        <label class="text-sm fw-semibold text-primary-light mb-8">Reason <span class="text-secondary-light fw-normal">(optional)</span></label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="Short note for the approver">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary-600 d-inline-flex align-items-center gap-6">
                            <i class="ri-send-plane-line"></i> Submit request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card ac-list-wrapper">
            <div class="card-header py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">My requests</h6>
                <span class="text-sm text-secondary-light">{{ $requests->count() }} record{{ $requests->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Period</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $leave)
                            <tr>
                                <td class="fw-semibold">{{ $leave->leaveType?->name }}</td>
                                <td>
                                    <div class="fw-medium">{{ $leave->start_date->format('d M Y') }}</div>
                                    <span class="text-sm text-secondary-light">to {{ $leave->end_date->format('d M Y') }}</span>
                                </td>
                                <td>{{ $leave->days }}</td>
                                <td>{{ $leave->reason ?: '—' }}</td>
                                <td>
                                    @php
                                        $statusClass = $leave->status === 'approved' ? 'ac-pill-emerald' : ($leave->status === 'rejected' ? 'ac-pill-rose' : 'ac-pill-amber');
                                    @endphp
                                    <span class="ac-pill {{ $statusClass }}">{{ ucfirst($leave->status) }}</span>
                                    @if($leave->review_note)
                                        <span class="d-block text-sm text-secondary-light mt-4">{{ $leave->review_note }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary-light py-24">You have not applied for leave yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
