@php
    $pageName = "hr";
    $subpageName = "list-staff";
    $staff = $datas;
    $activeTab = request('tab', 'overview');
    $positionName = $staff->hrPosition?->name ?: ($staff->position ?: '—');
    $initials = strtoupper(substr((string) ($staff->firstname ?? ''), 0, 1).substr((string) ($staff->surname ?? ''), 0, 1));
    $pendingLeave = $leaveRequests->where('status', 'pending')->count();
    $presentDays = $attendance->where('status', 'present')->count();
    $latestSlip = $payslips->first();
    $typeThemes = [
        'Annual Leave' => 'amber',
        'Sick Leave' => 'rose',
        'Maternity Leave' => 'pink',
        'Casual Leave' => 'sky',
        'Study Leave' => 'violet',
    ];
    $typeTheme = function ($type) use ($typeThemes) {
        $name = is_object($type) ? ($type->name ?? '') : (string) $type;

        return $typeThemes[$name] ?? 'teal';
    };
    $leavePill = function ($status) {
        return match ($status) {
            'approved' => 'ac-pill-emerald',
            'rejected' => 'ac-pill-rose',
            default => 'ac-pill-draft',
        };
    };
    $attPill = function ($status) {
        return match ($status) {
            'present' => 'ac-pill-present',
            'absent' => 'ac-pill-absent',
            'late' => 'ac-pill-late',
            'on_leave' => 'ac-pill-excused',
            default => 'ac-pill-slate',
        };
    };
@endphp
@extends('layouts.app')
 
@section('css')
@include('partials._academic-ui-styles')
@include('hr.partials._styles')
<style>
    .sp-hero {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12), rgba(99, 102, 241, 0.08));
        margin-bottom: 24px;
    }
    .sp-photo {
        width: 96px;
        height: 96px;
        border-radius: 18px;
        overflow: hidden;
        border: 3px solid #25A194;
        background: #fff;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 28px;
        color: #0f766e;
    }
    .sp-photo img { width: 100%; height: 100%; object-fit: cover; }
    .sp-name { margin: 0 0 6px; font-size: 24px; font-weight: 800; letter-spacing: -0.03em; }
    .sp-meta { display: flex; flex-wrap: wrap; gap: 8px 16px; color: #64748b; font-size: 13px; }
    .sp-meta i { color: #25A194; }
    .sp-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 6px;
        background: #f8fafc;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 14px;
        margin-bottom: 24px;
    }
    .sp-tabs .nav-link {
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
    .sp-tabs .nav-link.active {
        background: #fff;
        color: #25A194;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }
    .sp-fact {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 14px 16px;
        background: #fafbfc;
        height: 100%;
    }
    .sp-fact span {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .sp-fact strong { font-size: 14px; word-break: break-word; }
    .sp-empty { text-align: center; padding: 48px 20px; color: #64748b; }
    .sp-empty i { font-size: 36px; color: #25A194; display: block; margin-bottom: 10px; }
    .leave-type-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }
    .leave-pill-amber { background: #fef3c7; color: #b45309; }
    .leave-pill-rose { background: #ffe4e6; color: #be123c; }
    .leave-pill-pink { background: #fce7f3; color: #be185d; }
    .leave-pill-sky { background: #e0f2fe; color: #0369a1; }
    .leave-pill-violet { background: #ede9fe; color: #6d28d9; }
    .leave-pill-teal { background: #ccfbf1; color: #0f766e; }
    .sp-section-title {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: #25A194;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sp-section-title::before {
        content: "";
        width: 4px;
        height: 14px;
        background: #25A194;
        border-radius: 2px;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Employees', 'url' => route('list-staff')],
            ['label' => $staff->full_name, 'url' => null, 'active' => true],
        ],
        'title' => $staff->full_name,
        'subtitle' => $staff->employee_id,
        'actions' => '<a href="'.route('list-staff').'" class="btn btn-outline-primary-600"><i class="ri-arrow-left-line"></i> Employees</a>
            <a href="'.route('edit-staff', $id).'" class="btn btn-primary-600"><i class="ri-edit-line"></i> Edit</a>',
    ])

    <div class="sp-hero">
        <div class="d-flex flex-wrap align-items-start gap-20">
            <div class="sp-photo">
                @if(!empty($staff->picture))
                    <img src="{{ asset($staff->picture) }}" alt="{{ $staff->full_name }}">
                                @else
                    {{ $initials ?: 'ST' }}
                                @endif
                            </div>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-8 mb-8">
                    <h2 class="sp-name">{{ $staff->full_name }}</h2>
                    <span class="ac-pill {{ $staff->status === 'Active' ? 'ac-pill-emerald' : 'ac-pill-inactive' }}">{{ $staff->status }}</span>
                        </div>
                <div class="d-flex flex-wrap gap-8 mb-12">
                    <span class="ac-pill ac-pill-teal">{{ $positionName }}</span>
                    @if($staff->department)
                        <span class="ac-pill ac-pill-indigo">{{ $staff->department->name }}</span>
                    @endif
                    @if($staff->employment_type)
                        <span class="ac-pill ac-pill-slate">{{ $staff->employment_type }}</span>
                    @endif
                        </div>
                <div class="sp-meta">
                    @if($staff->email)<span><i class="ri-mail-line"></i> {{ $staff->email }}</span>@endif
                    @if($staff->mobile)<span><i class="ri-phone-line"></i> {{ $staff->mobile }}</span>@endif
                    @if($staff->employee_id)<span><i class="ri-id-card-line"></i> {{ $staff->employee_id }}</span>@endif
                    @if($staff->user)
                        <span><i class="ri-shield-user-line"></i> Login enabled</span>
                                @endif
                            </div>
                                </div>
                                </div>
                                </div>
                                
    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                                                        <div>
                        <p class="text-secondary-light text-sm mb-4">Qualifications</p>
                        <h4 class="fw-semibold mb-0">{{ $listdoc->count() }}</h4>
                                                        </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-graduation-cap-line"></i></span>
                                                    </div>
                                                </div>
                                            </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                                                        <div>
                        <p class="text-secondary-light text-sm mb-4">Pending leave</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $pendingLeave }}</h4>
                                                        </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-calendar-event-line"></i></span>
                                                    </div>
                                                </div>
                                            </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                                                        <div>
                        <p class="text-secondary-light text-sm mb-4">Present (last 30)</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $presentDays }}</h4>
                                                        </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-calendar-check-line"></i></span>
                                                    </div>
                                                </div>
                                            </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                                                        <div>
                        <p class="text-secondary-light text-sm mb-4">Latest net pay</p>
                        <h5 class="fw-semibold mb-0">{{ $latestSlip ? \App\Support\Money::ghs($latestSlip->net) : '—' }}</h5>
                                                        </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-wallet-3-line"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

    <ul class="nav sp-tabs" role="tablist">
        <li><button class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button"><i class="ri-user-line"></i> Overview</button></li>
        <li><button class="nav-link {{ $activeTab === 'edu' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-edu" type="button"><i class="ri-graduation-cap-line"></i> Qualifications</button></li>
        <li><button class="nav-link {{ $activeTab === 'bank' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-bank" type="button"><i class="ri-bank-line"></i> Bank &amp; next of kin</button></li>
        <li><button class="nav-link {{ $activeTab === 'leave' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-leave" type="button"><i class="ri-calendar-event-line"></i> Leave</button></li>
        <li><button class="nav-link {{ $activeTab === 'att' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-att" type="button"><i class="ri-calendar-check-line"></i> Attendance</button></li>
        <li><button class="nav-link {{ $activeTab === 'pay' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-pay" type="button"><i class="ri-file-paper-2-line"></i> Payslips</button></li>
                                                </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="tab-overview">
            <div class="card ac-list-wrapper mb-24">
                <div class="card-body p-24">
                    <h6 class="sp-section-title">Personal</h6>
                    <div class="row g-3 mb-24">
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Gender</span><strong>{{ $staff->gender ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Date of birth</span><strong>{{ $staff->dob ? \Carbon\Carbon::parse($staff->dob)->format('d M Y') : '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Marital status</span><strong>{{ $staff->marital_status ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Nationality</span><strong>{{ $staff->country?->name ?: '—' }}</strong></div></div>
                        <div class="col-12"><div class="sp-fact"><span>Residential address</span><strong>{{ $staff->residential_address ?: '—' }}</strong></div></div>
                                </div>

                    <h6 class="sp-section-title">Employment</h6>
                    <div class="row g-3 mb-24">
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Position</span><strong>{{ $positionName }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Department</span><strong>{{ $staff->department?->name ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Employment type</span><strong>{{ $staff->employment_type ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Pay grade</span><strong>{{ $staff->payGrade?->name ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Appointment</span><strong>{{ $staff->appointment_date?->format('d M Y') ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Confirmation</span><strong>{{ $staff->confirmation_date?->format('d M Y') ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Contract end</span><strong>{{ $staff->contract_end_date?->format('d M Y') ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Basic salary</span><strong>{{ $staff->resolvedBasicSalary() > 0 ? \App\Support\Money::ghs($staff->resolvedBasicSalary()) : '—' }}</strong></div></div>
                            </div>

                    <h6 class="sp-section-title">Statutory</h6>
                                    <div class="row g-3">
                        <div class="col-sm-6"><div class="sp-fact"><span>SSNIT number</span><strong>{{ $staff->ssnit_number ?: '—' }}</strong></div></div>
                        <div class="col-sm-6"><div class="sp-fact"><span>TIN</span><strong>{{ $staff->tin ?: '—' }}</strong></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

        <div class="tab-pane fade {{ $activeTab === 'edu' ? 'show active' : '' }}" id="tab-edu">
            <div class="card ac-list-wrapper">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead><tr><th>Level</th><th>Year</th><th>Qualification</th><th>Institution</th><th></th></tr></thead>
                                    <tbody>
                            @forelse($listdoc as $list)
                                <tr>
                                    <td><span class="ac-pill ac-pill-indigo">{{ $list->level }}</span></td>
                                    <td>{{ $list->year }}</td>
                                    <td class="fw-semibold">{{ $list->qualification }}</td>
                                    <td>{{ $list->institution ?: '—' }}</td>
                                    <td class="text-end">
                                        @if($list->document_path)
                                            <a href="{{ asset('uploads/staffdocs/'.$list->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary-600">
                                                <i class="ri-file-text-line"></i> View
                                            </a>
                                        @else
                                            <span class="text-secondary-light">No file</span>
                                        @endif
                                            </td>
                                        </tr>
                            @empty
                                <tr><td colspan="5"><div class="sp-empty"><i class="ri-graduation-cap-line"></i>No qualifications recorded.</div></td></tr>
                            @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

        <div class="tab-pane fade {{ $activeTab === 'bank' ? 'show active' : '' }}" id="tab-bank">
            <div class="card ac-list-wrapper">
                <div class="card-body p-24">
                    <h6 class="sp-section-title">Bank account</h6>
                    <div class="row g-3 mb-24">
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Bank</span><strong>{{ $staff->bank_name ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Branch</span><strong>{{ $staff->bank_branch ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Account name</span><strong>{{ $staff->account_name ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-3"><div class="sp-fact"><span>Account number</span><strong>{{ $staff->account_number ?: '—' }}</strong></div></div>
                            </div>
                    <h6 class="sp-section-title">Next of kin</h6>
                    <div class="row g-3">
                        <div class="col-sm-6 col-xl-4"><div class="sp-fact"><span>Name</span><strong>{{ $staff->next_of_kin_name ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-4"><div class="sp-fact"><span>Phone</span><strong>{{ $staff->next_of_kin_phone ?: '—' }}</strong></div></div>
                        <div class="col-sm-6 col-xl-4"><div class="sp-fact"><span>Relationship</span><strong>{{ $staff->next_of_kin_relationship ?: '—' }}</strong></div></div>
                                    </div>
                                </div>
                                    </div>
                                </div>

        <div class="tab-pane fade {{ $activeTab === 'leave' ? 'show active' : '' }}" id="tab-leave">
            <div class="card ac-list-wrapper">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                                            <tbody>
                            @forelse($leaveRequests as $leave)
                                <tr>
                                    <td><span class="leave-type-pill leave-pill-{{ $typeTheme($leave->leaveType) }}">{{ $leave->leaveType?->name ?: '—' }}</span></td>
                                    <td>{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</td>
                                    <td>{{ $leave->days }}</td>
                                    <td><span class="ac-pill {{ $leavePill($leave->status) }}">{{ ucfirst($leave->status) }}</span></td>
                                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="sp-empty"><i class="ri-calendar-event-line"></i>No leave records.</div></td></tr>
                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    </div>
                                </div>

        <div class="tab-pane fade {{ $activeTab === 'att' ? 'show active' : '' }}" id="tab-att">
            <div class="card ac-list-wrapper">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead><tr><th>Date</th><th>Status</th><th>In</th><th>Out</th><th>Remarks</th></tr></thead>
                                            <tbody>
                            @forelse($attendance as $row)
                                <tr>
                                    <td>{{ $row->date->format('d M Y') }}</td>
                                    <td><span class="ac-pill {{ $attPill($row->status) }}">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span></td>
                                    <td>{{ $row->check_in ?: '—' }}</td>
                                    <td>{{ $row->check_out ?: '—' }}</td>
                                    <td>{{ $row->remarks ?: '—' }}</td>
                                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="sp-empty"><i class="ri-calendar-check-line"></i>No attendance records in the last 30 days.</div></td></tr>
                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

        <div class="tab-pane fade {{ $activeTab === 'pay' ? 'show active' : '' }}" id="tab-pay">
            <div class="card ac-list-wrapper">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                                    <thead>
                                        <tr>
                                <th>Period</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Net</th>
                                <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            @forelse($payslips as $slip)
                                <tr>
                                    <td class="fw-semibold">{{ $slip->payrollRun?->periodLabel() }}</td>
                                    <td class="text-end">{{ \App\Support\Money::ghs($slip->gross) }}</td>
                                    <td class="text-end fw-semibold" style="color:#0f766e;">{{ \App\Support\Money::ghs($slip->net) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('hr-payslip-print', $slip->id) }}" target="_blank" class="btn btn-sm btn-outline-primary-600">
                                            <i class="ri-printer-line"></i> Print
                                        </a>
                                            </td>
                                        </tr>
                            @empty
                                <tr><td colspan="4"><div class="sp-empty"><i class="ri-file-paper-2-line"></i>No payslips yet.</div></td></tr>
                            @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
</div>
@endsection
