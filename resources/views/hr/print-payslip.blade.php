@extends('layouts.print')

@section('css')
<style>
    .payslip-banner {
        margin: 20px 40px 8px;
        padding: 18px 22px;
        border: 1px solid var(--brand-border);
        border-radius: 14px;
        background: linear-gradient(135deg, var(--brand-light) 0%, #fff 62%);
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 18px;
        align-items: center;
    }
    .payslip-photo {
        width: 76px;
        height: 76px;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid var(--brand-primary);
        background: #fff;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: var(--brand-dark);
        font-size: 22px;
    }
    .payslip-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .payslip-name {
        margin: 0 0 4px;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .payslip-sub {
        color: var(--muted);
        font-size: 13px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
    }
    .payslip-sub strong { color: var(--brand-dark); }
    .payslip-ref {
        text-align: right;
        min-width: 160px;
    }
    .payslip-ref .label {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 4px;
    }
    .payslip-ref .value {
        font-size: 15px;
        font-weight: 800;
        color: var(--ink);
    }
    .payslip-facts {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 16px 40px 8px;
    }
    .payslip-fact {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 12px 14px;
        background: #fafbfc;
    }
    .payslip-fact span {
        display: block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 4px;
    }
    .payslip-fact strong {
        font-size: 13px;
        font-weight: 700;
        color: var(--ink);
        word-break: break-word;
    }
    .payslip-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 12px 40px 8px;
    }
    .payslip-col {
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
    }
    .payslip-col h3 {
        margin: 0;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: #fff;
    }
    .payslip-col.earn h3 { background: #0f766e; }
    .payslip-col.deduct h3 { background: #b45309; }
    .payslip-table {
        width: 100%;
        border-collapse: collapse;
    }
    .payslip-table td {
        padding: 9px 14px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .payslip-table tr:last-child td { border-bottom: none; }
    .payslip-table td:last-child {
        text-align: right;
        font-weight: 700;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .payslip-table .empty {
        color: var(--muted);
        font-weight: 500;
        text-align: center;
        padding: 18px 14px;
    }
    .payslip-table tfoot td {
        background: #f8fafc;
        font-weight: 800;
        border-top: 1px solid var(--line);
    }
    .payslip-col.deduct tfoot td { color: #b45309; }
    .payslip-col.earn tfoot td { color: #0f766e; }
    .payslip-totals {
        margin: 16px 40px 8px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: stretch;
    }
    .payslip-notes {
        border: 1px dashed var(--line);
        border-radius: 12px;
        padding: 14px 16px;
        color: var(--muted);
        font-size: 12px;
        background: #fff;
    }
    .payslip-notes strong { color: var(--ink); }
    .payslip-net {
        min-width: 260px;
        background: linear-gradient(135deg, #145a52, #25A194);
        color: #fff;
        border-radius: 14px;
        padding: 16px 20px;
        text-align: right;
        box-shadow: 0 10px 24px rgba(20, 90, 82, 0.18);
    }
    .payslip-net .label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 4px;
    }
    .payslip-net .amount {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
    }
    .payslip-net .hint {
        margin-top: 6px;
        font-size: 11px;
        opacity: 0.85;
    }
    .payslip-sigs {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 24px;
        padding: 28px 40px 8px;
    }
    .payslip-sig {
        text-align: center;
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
    }
    .payslip-sig span {
        display: block;
        border-top: 1px solid #94a3b8;
        margin-top: 42px;
        padding-top: 8px;
    }
    .confidential {
        text-align: center;
        font-size: 10px;
        color: #94a3b8;
        padding: 8px 40px 28px;
        letter-spacing: 0.3px;
    }
    @media print {
        .payslip-banner,
        .payslip-fact,
        .payslip-col h3,
        .payslip-net,
        .payslip-table tfoot td {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .payslip-banner,
        .payslip-facts,
        .payslip-split,
        .payslip-totals,
        .payslip-sigs {
            padding-left: 28px;
            padding-right: 28px;
        }
        .payslip-banner { margin-left: 28px; margin-right: 28px; }
        .payslip-totals { margin-left: 28px; margin-right: 28px; }
    }
    @media (max-width: 720px) {
        .payslip-banner,
        .payslip-facts,
        .payslip-split,
        .payslip-totals,
        .payslip-sigs {
            grid-template-columns: 1fr;
            padding-left: 20px;
            padding-right: 20px;
        }
        .payslip-banner {
            margin-left: 20px;
            margin-right: 20px;
            text-align: center;
            justify-items: center;
        }
        .payslip-ref { text-align: center; }
        .payslip-net { text-align: center; min-width: 0; }
        .payslip-sub { justify-content: center; }
    }
</style>
@endsection

@section('content')
@php
    $staff = $payslip->staff;
    $run = $payslip->payrollRun;
    $lines = collect($payslip->lines ?? []);
    $earnings = $lines->where('type', 'earning')->values();
    $deductions = $lines->where('type', 'deduction')->values();
    $totalDeductions = (float) $deductions->sum('amount');
    $logoUrl = $school->logoUrl();
    $period = $run?->periodLabel() ?: '—';
    $slipNo = 'PS-'.str_pad((string) $payslip->id, 5, '0', STR_PAD_LEFT);
    $status = strtolower((string) ($run?->status ?: 'draft'));
    $position = $staff?->hrPosition?->name ?: ($staff?->position ?: '—');
    $department = $staff?->department?->name ?: '—';
    $initials = strtoupper(substr((string) ($staff?->firstname ?? ''), 0, 1).substr((string) ($staff?->surname ?? ''), 0, 1));
    $bankLine = trim(collect([$staff?->bank_name, $staff?->account_number])->filter()->implode(' · ')) ?: '—';
@endphp

<div class="print-sheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
            <i class="ri-close-line"></i> Close
        </button>
    </div>

    <header class="letterhead">
        <div class="letterhead-logo">
            <img src="{{ $logoUrl }}" alt="{{ $school->name ?: 'School' }}">
        </div>
        <div>
            <h2 class="letterhead-school">{{ $school->name ?: 'EasySchool' }}</h2>
            @if(!empty($school->motto))
                <p class="letterhead-motto">"{{ $school->motto }}"</p>
            @endif
            <div class="letterhead-meta">
                @if(!empty($school->address))
                    <span><i class="ri-map-pin-line"></i> {{ $school->address }}</span>
                @endif
                @if(!empty($school->phone))
                    <span><i class="ri-phone-line"></i> {{ $school->phone }}</span>
                @endif
                @if(!empty($school->email))
                    <span><i class="ri-mail-line"></i> {{ $school->email }}</span>
                @endif
            </div>
        </div>
        <div class="qr-block" style="min-width:132px;">
            <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--brand-dark);">Payslip</div>
            <div style="font-size:18px;font-weight:800;margin:6px 0 2px;">{{ $slipNo }}</div>
            <div class="qr-block-label">{{ $period }}</div>
        </div>
    </header>

    <div class="doc-head">
        <h1>Employee Payslip</h1>
        <p>{{ $period }} &nbsp;&bull;&nbsp; Issued {{ now()->format('d M Y') }}</p>
    </div>

    <div class="payslip-banner">
        <div class="payslip-photo">
            @if(!empty($staff?->picture))
                <img src="{{ asset($staff->picture) }}" alt="{{ $staff->full_name }}">
            @else
                {{ $initials ?: 'ST' }}
            @endif
        </div>
        <div>
            <h2 class="payslip-name">{{ $staff?->full_name ?: 'Employee' }}</h2>
            <div class="payslip-sub">
                <span>Staff ID: <strong>{{ $staff?->employee_id ?: '—' }}</strong></span>
                <span>{{ $position }}</span>
                <span>{{ $department }}</span>
            </div>
        </div>
        <div class="payslip-ref">
            <div class="label">Payroll status</div>
            <div class="value">
                <span class="status-pill {{ $status === 'paid' ? 'active' : ($status === 'approved' ? 'draft' : 'inactive') }}">
                    {{ ucfirst($status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="payslip-facts">
        <div class="payslip-fact">
            <span>SSNIT number</span>
            <strong>{{ $staff?->ssnit_number ?: '—' }}</strong>
        </div>
        <div class="payslip-fact">
            <span>TIN</span>
            <strong>{{ $staff?->tin ?: '—' }}</strong>
        </div>
        <div class="payslip-fact">
            <span>Bank details</span>
            <strong>{{ $bankLine }}</strong>
        </div>
        <div class="payslip-fact">
            <span>Pay period</span>
            <strong>{{ $period }}</strong>
        </div>
    </div>

    <div class="payslip-split">
        <div class="payslip-col earn">
            <h3>Earnings</h3>
            <table class="payslip-table">
                <tbody>
                    @forelse($earnings as $line)
                        <tr>
                            <td>{{ $line['name'] }}</td>
                            <td>{{ \App\Support\Money::ghs($line['amount'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty">No earnings recorded</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td>Gross pay</td>
                        <td>{{ \App\Support\Money::ghs($payslip->gross) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="payslip-col deduct">
            <h3>Deductions</h3>
            <table class="payslip-table">
                <tbody>
                    @forelse($deductions as $line)
                        <tr>
                            <td>{{ $line['name'] }}</td>
                            <td>{{ \App\Support\Money::ghs($line['amount'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty">No deductions recorded</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total deductions</td>
                        <td>{{ \App\Support\Money::ghs($totalDeductions) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="payslip-totals">
        <div class="payslip-notes">
            <div>Employer SSNIT contribution: <strong>{{ \App\Support\Money::ghs($payslip->ssnit_employer) }}</strong> (not deducted from net pay)</div>
            @if($payslip->unpaid_leave_days)
                <div style="margin-top:6px;">Unpaid leave days this period: <strong>{{ $payslip->unpaid_leave_days }}</strong></div>
            @endif
            <div style="margin-top:6px;">Confirm SSNIT and PAYE against current GRA notices. This document is computer generated.</div>
        </div>
        <div class="payslip-net">
            <div class="label">Net pay</div>
            <div class="amount">{{ \App\Support\Money::ghs($payslip->net) }}</div>
            <div class="hint">Gross {{ \App\Support\Money::ghs($payslip->gross) }} − deductions</div>
        </div>
    </div>

    <div class="payslip-sigs">
        <div class="payslip-sig"><span>Prepared by</span></div>
        <div class="payslip-sig"><span>Authorised by</span></div>
        <div class="payslip-sig"><span>Employee acknowledgement</span></div>
    </div>

    <p class="confidential">Confidential payroll document &nbsp;&bull;&nbsp; {{ $school->name ?: 'EasySchool' }} &nbsp;&bull;&nbsp; {{ $slipNo }}</p>
</div>
@endsection
