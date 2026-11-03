@extends('layouts.print')

@section('css')
<style>
    .print-sheet { max-width: 1100px; }
    .rpt-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
        justify-content: center;
        font-size: 12px;
        color: var(--muted);
        margin-top: 6px;
    }
    .rpt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .rpt-table th,
    .rpt-table td {
        border: 1px solid var(--line);
        padding: 7px 8px;
        text-align: left;
        vertical-align: top;
    }
    .rpt-table th {
        background: var(--brand-light);
        color: var(--brand-dark);
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .rpt-table td.money { font-weight: 700; white-space: nowrap; color: var(--brand-dark); }
    .rpt-totals {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 0 40px 18px;
    }
    .rpt-total {
        border: 1px solid var(--brand-border);
        border-radius: 10px;
        padding: 8px 12px;
        min-width: 130px;
        background: var(--brand-light);
    }
    .rpt-total small { display: block; font-size: 10px; font-weight: 700; color: var(--brand-dark); text-transform: uppercase; }
    .rpt-total strong { font-size: 14px; }
    @media print {
        @page { size: A4 landscape; margin: 12mm; }
        .rpt-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endsection

@section('content')
@php
    $logoUrl = $school->logoUrl();
@endphp
<div class="print-sheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-primary">
            <i class="ri-printer-line"></i> Print
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-secondary">
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
                @if(!empty($school->email))<span><i class="ri-mail-line"></i> {{ $school->email }}</span>@endif
            </div>
        </div>
        <div></div>
    </header>

    <div class="doc-head">
        <h1>{{ $report['title'] }}</h1>
        <p>{{ $report['subtitle'] }}</p>
        <div class="rpt-meta">Generated {{ $report['printed_at'] }} · {{ number_format(count($report['rows'])) }} rows</div>
    </div>

    @if($report['totals'])
        <div class="rpt-totals">
            @foreach($report['totals'] as $total)
                <div class="rpt-total">
                    <small>{{ $total['label'] }}</small>
                    <strong>{{ $total['value'] }}</strong>
                </div>
            @endforeach
        </div>
    @endif

    <div class="print-content">
        @if(count($report['rows']) === 0)
            <p class="text-center" style="color:#64748b;">No records match these filters.</p>
        @else
            <table class="rpt-table">
                <thead>
                    <tr>
                        @foreach($report['columns'] as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['rows'] as $row)
                        <tr>
                            @foreach($report['columns'] as $column)
                                <td class="{{ !empty($column['money']) ? 'money' : '' }}">{{ $row[$column['key']] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <div class="print-footnote">Printed from EasySchool reports · {{ $school->name ?: 'School' }}</div>
    </div>
</div>
@endsection
