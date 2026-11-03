@extends('layouts.print')

@section('content')
@php
    $logoUrl = $school->logoUrl();
    $title = 'Student Bill Ledger';
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
                @if(!empty($school->address))<span><i class="ri-map-pin-line"></i> {{ $school->address }}</span>@endif
                @if(!empty($school->phone))<span><i class="ri-phone-line"></i> {{ $school->phone }}</span>@endif
            </div>
        </div>
    </header>

    <div class="doc-head">
        <h1>Student Bill Ledger</h1>
        <p>Printed {{ $printedAt->format('d M Y g:i A') }}
            @if(!empty($filterLabels))
                &nbsp;&bull;&nbsp; {{ implode(' · ', $filterLabels) }}
            @endif
        </p>
    </div>

    <div class="print-content">
        <table class="info-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Student</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Class</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Category</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Total Due</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Paid</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Balance</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Credit</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                        <strong>{{ $row->student->full_name }}</strong><br>
                        <small>{{ $row->student->student_id }}</small>
                    </td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $row->student->class_name }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $row->student->schoolClass?->category?->name ?: '—' }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format($row->total_due, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format($row->total_paid, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format($row->balance, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;color:#15803d;">{{ number_format($row->credit_balance, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $row->status }}@if($row->credit_balance > 0) · Credit @endif</td>
                </tr>
                @empty
                <tr><td colspan="8" style="padding:24px;text-align:center;color:#6b7280;">No students match the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
