@extends('layouts.parent')

@section('title', 'Payments — '.$student->full_name)
@section('page-title', 'Payment History')
@section('page-subtitle', $student->full_name)

@section('css')
<style>
    .pay {
        --p-teal: #25A194;
        --p-teal-d: #0f766e;
        --p-ink: #0f172a;
        --p-muted: #64748b;
        --p-border: #e2e8f0;
        --p-green: #10b981;
    }

    .pay-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .pay-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .pay-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .pay-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }
    .pay-hero-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .pay-hero-amount {
        font-size: clamp(2.2rem, 6vw, 3rem);
        font-weight: 800;
        letter-spacing: -.04em;
        line-height: 1;
    }
    .pay-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .pay-hero-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .pay-hero-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .pay-student-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        padding: 10px 16px 10px 10px;
        border: 1px solid rgba(255,255,255,.2);
    }
    .pay-student-chip img,
    .pay-student-chip .av {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        background: rgba(255,255,255,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
    }
    .pay-student-chip b { display: block; font-size: 14px; }
    .pay-student-chip small { opacity: .8; font-size: 12px; }

    .pay-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .pay-actions-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid var(--p-border);
        background: #fff;
        color: var(--p-teal-d);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: all .15s;
    }
    .pay-actions-link:hover {
        border-color: var(--p-teal);
        background: #f0fdfa;
        color: var(--p-teal-d);
    }

    .pay-card {
        background: #fff;
        border: 1px solid var(--p-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .pay-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--p-border);
        background: #fafafa;
    }
    .pay-card-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--p-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pay-card-head h3 i { color: var(--p-teal); }
    .pay-card-head .count {
        font-size: 12px;
        font-weight: 700;
        color: var(--p-muted);
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .pay-table-wrap { overflow-x: auto; }
    .pay-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .pay-table thead th {
        padding: 14px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--p-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--p-border);
        white-space: nowrap;
    }
    .pay-table thead th.text-end { text-align: right; }
    .pay-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .pay-table tbody tr:hover { background: #fafafa; }
    .pay-table tbody tr:last-child td { border-bottom: none; }
    .pay-table .text-muted { color: var(--p-muted); font-size: 13px; }
    .pay-table .text-end { text-align: right; }
    .pay-table .amt {
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        color: var(--p-teal-d);
    }
    .pay-receipt {
        font-family: ui-monospace, monospace;
        font-size: 13px;
        font-weight: 700;
        color: var(--p-ink);
    }
    .pay-method {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: var(--p-ink);
    }
    .pay-method i { color: var(--p-teal); font-size: 16px; }
    .pay-items {
        font-size: 12px;
        color: var(--p-muted);
        margin-top: 4px;
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pay-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1.5px solid var(--p-teal);
        background: #f0fdfa;
        color: var(--p-teal-d);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: all .12s;
        white-space: nowrap;
    }
    .pay-btn:hover {
        background: var(--p-teal);
        color: #fff;
    }

    .pay-empty {
        padding: 48px 24px;
        text-align: center;
    }
    .pay-empty i {
        font-size: 48px;
        color: #cbd5e1;
        display: block;
        margin-bottom: 12px;
    }
    .pay-empty h3 {
        font-size: 18px;
        font-weight: 800;
        color: var(--p-ink);
        margin: 0 0 6px;
    }
    .pay-empty p {
        color: var(--p-muted);
        margin: 0 0 20px;
        font-size: 14px;
    }
    .pay-empty .pay-btn {
        display: inline-flex;
    }

    @media (max-width: 640px) {
        .pay-table thead { display: none; }
        .pay-table tbody tr {
            display: block;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .pay-table tbody tr:hover { background: #fff; }
        .pay-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
        }
        .pay-table tbody td::before {
            content: attr(data-label);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--p-muted);
        }
        .pay-table tbody td:last-child {
            justify-content: flex-end;
            padding-top: 12px;
        }
        .pay-table tbody td:last-child::before { display: none; }
        .pay-items { max-width: 160px; }
    }
</style>
@endsection

@section('content')
@php
    $initials = strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1));
    $totalPaid = $payments->sum(fn ($p) => (float) $p->amount + (float) $p->credit_applied);
    $cashPaid = $payments->sum(fn ($p) => (float) $p->amount);
    $lastPayment = $payments->first();
@endphp

<div class="pay">
    <div class="pay-hero">
        <div class="pay-hero-inner">
            <div>
                <div class="pay-hero-label">Total paid</div>
                <div class="pay-hero-amount">GHS {{ number_format($totalPaid, 2) }}</div>
                <div class="pay-hero-meta">
                    <div>Payments made<strong>{{ $payments->count() }}</strong></div>
                    <div>Cash / online<strong>GHS {{ number_format($cashPaid, 2) }}</strong></div>
                    @if($lastPayment)
                        <div>Last payment<strong>{{ $lastPayment->paid_at->format('d M Y') }}</strong></div>
                    @endif
                </div>
            </div>
            <div class="pay-student-chip">
                @if($student->picture)
                    <img src="{{ asset($student->picture) }}" alt="">
                @else
                    <div class="av">{{ $initials }}</div>
                @endif
                <div>
                    <b>{{ $student->full_name }}</b>
                    <small>{{ $student->schoolClass?->name ?? $student->class_name }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="pay-actions">
        <a href="{{ route('parent.bills', $student) }}" class="pay-actions-link">
            <i class="ri-bill-line"></i> View fees & bills
        </a>
    </div>

    <div class="pay-card">
        <div class="pay-card-head">
            <h3><i class="ri-receipt-line"></i> Payment history</h3>
            <span class="count">{{ $payments->count() }} {{ Str::plural('record', $payments->count()) }}</span>
        </div>

        @if($payments->isEmpty())
            <div class="pay-empty">
                <i class="ri-wallet-3-line"></i>
                <h3>No payments yet</h3>
                <p>When you pay school fees, your receipts will appear here.</p>
                <a href="{{ route('parent.bills', $student) }}" class="pay-btn">
                    <i class="ri-bank-card-line"></i> Go to fees & bills
                </a>
            </div>
        @else
            <div class="pay-table-wrap">
                <table class="pay-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Receipt</th>
                            <th>Method</th>
                            <th>Details</th>
                            <th class="text-end">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $total = (float) $payment->amount + (float) $payment->credit_applied;
                                $items = $payment->allocations
                                    ->map(fn ($a) => $a->studentBill?->billingItem?->name)
                                    ->filter()
                                    ->take(3)
                                    ->implode(', ');
                                $methodIcon = str_contains(strtolower($payment->payment_method), 'paystack')
                                    ? 'ri-bank-card-line'
                                    : 'ri-money-dollar-circle-line';
                            @endphp
                            <tr>
                                <td data-label="Date">
                                    <div>{{ $payment->paid_at->format('d M Y') }}</div>
                                    <div class="text-muted">{{ $payment->paid_at->format('g:i A') }}</div>
                                </td>
                                <td data-label="Receipt">
                                    <span class="pay-receipt">{{ $payment->receipt_no }}</span>
                                </td>
                                <td data-label="Method">
                                    <span class="pay-method">
                                        <i class="{{ $methodIcon }}"></i>
                                        {{ $payment->payment_method }}
                                    </span>
                                    @if($payment->payment_channel)
                                        <div class="text-muted">{{ ucwords(str_replace('_', ' ', $payment->payment_channel)) }}</div>
                                    @endif
                                </td>
                                <td data-label="Details">
                                    @if($items)
                                        <div class="pay-items" title="{{ $items }}">{{ $items }}</div>
                                    @endif
                                    @if($payment->credit_applied > 0)
                                        <div class="text-muted">Credit used: GHS {{ number_format($payment->credit_applied, 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-end" data-label="Amount">
                                    <span class="amt">GHS {{ number_format($total, 2) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('parent.payment.receipt', [$student, $payment]) }}" target="_blank" class="pay-btn">
                                        <i class="ri-printer-line"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
