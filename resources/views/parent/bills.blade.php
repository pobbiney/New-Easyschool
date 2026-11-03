@extends('layouts.parent')

@section('title', 'Fees — '.$student->full_name)
@section('page-title', 'Fees & Bills')
@section('page-subtitle', $student->full_name)

@section('css')
@if($paystackConfigured)
<script src="https://js.paystack.co/v1/inline.js"></script>
@endif
<style>
    .fees {
        --f-teal: #25A194;
        --f-teal-d: #0f766e;
        --f-ink: #0f172a;
        --f-muted: #64748b;
        --f-border: #e2e8f0;
        --f-red: #ef4444;
        --f-green: #10b981;
    }

    .fees-balance {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .fees-balance::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .fees-balance::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .fees-balance-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }
    .fees-balance-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .fees-balance-amount {
        font-size: clamp(2.2rem, 6vw, 3rem);
        font-weight: 800;
        letter-spacing: -.04em;
        line-height: 1;
    }
    .fees-balance-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .fees-balance-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .fees-balance-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .fees-student-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        padding: 10px 16px 10px 10px;
        border: 1px solid rgba(255,255,255,.2);
    }
    .fees-student-chip img,
    .fees-student-chip .av {
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
    .fees-student-chip b { display: block; font-size: 14px; }
    .fees-student-chip small { opacity: .8; font-size: 12px; }

    .fees-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .fees-bar select {
        border: 1px solid var(--f-border);
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        color: var(--f-ink);
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .fees-bar-filters { display: flex; gap: 10px; flex-wrap: wrap; }

    /* Tabs */
    .fees-tabs-wrap {
        background: #fff;
        border: 1px solid var(--f-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .fees-tabs {
        display: flex;
        border-bottom: 1px solid var(--f-border);
        background: #fafafa;
    }
    .fees-tab {
        flex: 1;
        padding: 14px 20px;
        border: none;
        background: transparent;
        font-size: 14px;
        font-weight: 700;
        color: var(--f-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .15s;
        border-bottom: 3px solid transparent;
        margin-bottom: -1px;
    }
    .fees-tab:hover { color: var(--f-teal-d); background: #fff; }
    .fees-tab.active {
        color: var(--f-teal-d);
        background: #fff;
        border-bottom-color: var(--f-teal);
    }
    .fees-tab .count {
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        color: var(--f-muted);
    }
    .fees-tab.active .count {
        background: #e6f7f5;
        color: var(--f-teal-d);
    }
    .fees-tab-pane { display: none; padding: 0; }
    .fees-tab-pane.active { display: block; }

    /* Tables */
    .fees-table-wrap { overflow-x: auto; }
    .fees-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .fees-table thead th {
        padding: 14px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--f-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--f-border);
        white-space: nowrap;
    }
    .fees-table thead th.text-end { text-align: right; }
    .fees-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .fees-table tbody tr:hover { background: #fafafa; }
    .fees-table tbody tr:last-child td { border-bottom: none; }
    .fees-table tfoot td {
        padding: 16px 20px;
        background: #f8fafc;
        font-weight: 800;
        border-top: 2px solid var(--f-border);
    }
    .fees-table .item-name { font-weight: 700; color: var(--f-ink); }
    .fees-table .item-sub { font-size: 12px; color: var(--f-muted); margin-top: 2px; }
    .fees-table .amt { font-weight: 800; font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .fees-table .amt.due { color: var(--f-red); }
    .fees-table .amt.ok { color: var(--f-green); }
    .fees-table .text-muted { color: var(--f-muted); font-size: 13px; }
    .fees-table .text-end { text-align: right; }
    .fees-req {
        display: inline-block;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #fef3c7;
        color: #b45309;
        padding: 2px 8px;
        border-radius: 999px;
        margin-left: 6px;
        vertical-align: middle;
    }
    .fees-status {
        display: inline-block;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 4px 10px;
        border-radius: 999px;
    }
    .fees-status-paid { background: #dcfce7; color: #166534; }
    .fees-status-partial { background: #dbeafe; color: #1e40af; }
    .fees-status-pending { background: #fee2e2; color: #991b1b; }
    .fees-mini-bar {
        height: 4px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 6px;
        max-width: 120px;
    }
    .fees-mini-bar span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, var(--f-teal), #6ee7b7);
        border-radius: 999px;
    }

    .fees-lines { padding: 8px 0; }
    .fees-line {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        user-select: none;
    }
    .fees-line:last-child { border-bottom: 0; }
    .fees-line.is-locked { cursor: default; }
    .fees-line.is-selected { background: #f0fdfa; }
    .fees-line:hover { background: #f8fffe; }
    .fees-check {
        width: 28px; height: 28px; border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        display: flex; align-items: center; justify-content: center;
        color: #fff; background: #fff; font-size: 16px;
    }
    .fees-line.is-selected .fees-check {
        background: var(--f-teal); border-color: var(--f-teal);
    }
    .fees-line.is-locked .fees-check {
        background: #0f766e; border-color: #0f766e;
    }
    .fees-line-name { font-weight: 800; color: var(--f-ink); }
    .fees-line-meta { font-size: 12px; color: var(--f-muted); font-weight: 600; margin-top: 2px; }
    .fees-line-bal { font-weight: 800; color: var(--f-red); white-space: nowrap; text-align: right; }
    .fees-line-bal small { display: block; font-size: 11px; color: var(--f-muted); font-weight: 700; }

    .fees-credit {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 12px 14px; border-radius: 14px; background: #fffbeb; border: 1px solid #fde68a;
        margin-bottom: 16px;
    }
    .fees-credit strong { display: block; font-size: 14px; color: #92400e; }
    .fees-credit span { font-size: 12px; color: #b45309; font-weight: 600; }
    .fees-credit input { width: 18px; height: 18px; accent-color: #25A194; }
    .fees-credit-amt { margin-bottom: 16px; display: none; }

    .fees-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .fees-grid { grid-template-columns: 1fr; }
    }

    .fees-block-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--f-ink);
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fees-block-title i { color: var(--f-teal); }
    .fees-block-title .n {
        margin-left: auto;
        font-size: 12px;
        font-weight: 700;
        color: var(--f-muted);
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .fees-all-clear {
        background: #fff;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 40px 24px;
        text-align: center;
        margin-bottom: 28px;
    }
    .fees-all-clear i {
        font-size: 48px;
        color: var(--f-green);
        display: block;
        margin-bottom: 12px;
    }
    .fees-all-clear h3 {
        font-size: 18px;
        font-weight: 800;
        color: var(--f-ink);
        margin: 0 0 6px;
    }
    .fees-all-clear p { color: var(--f-muted); margin: 0; font-size: 14px; }

    .fees-pay {
        background: #fff;
        border: 1px solid var(--f-border);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(15,23,42,.07);
        position: sticky;
        top: 76px;
    }
    .fees-pay-head {
        background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
        padding: 24px 24px 20px;
        border-bottom: 1px solid #ccfbf1;
        text-align: center;
    }
    .fees-pay-head i {
        font-size: 32px;
        color: var(--f-teal);
        display: block;
        margin-bottom: 10px;
    }
    .fees-pay-head h3 {
        margin: 0 0 4px;
        font-size: 17px;
        font-weight: 800;
        color: var(--f-ink);
    }
    .fees-pay-head p {
        margin: 0;
        font-size: 13px;
        color: var(--f-muted);
    }
    .fees-pay-body { padding: 24px; }
    .fees-pay-total {
        text-align: center;
        margin-bottom: 20px;
    }
    .fees-pay-total label {
        font-size: 12px;
        font-weight: 700;
        color: var(--f-muted);
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .fees-pay-total div {
        font-size: 36px;
        font-weight: 800;
        color: var(--f-teal-d);
        letter-spacing: -.03em;
        line-height: 1.1;
        margin-top: 4px;
    }
    .fees-pay-total div small {
        font-size: 16px;
        font-weight: 700;
        color: var(--f-muted);
    }
    .fees-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
        justify-content: center;
    }
    .fees-chip {
        padding: 8px 14px;
        border-radius: 999px;
        border: 1.5px solid var(--f-border);
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        color: var(--f-ink);
        cursor: pointer;
        transition: all .12s;
    }
    .fees-chip:hover, .fees-chip.on {
        border-color: var(--f-teal);
        background: #f0fdfa;
        color: var(--f-teal-d);
    }
    .fees-input-wrap {
        position: relative;
        margin-bottom: 16px;
    }
    .fees-input-wrap span {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        font-weight: 700;
        color: var(--f-muted);
    }
    .fees-input-wrap input {
        width: 100%;
        padding: 14px 14px 14px 52px;
        border: 1.5px solid var(--f-border);
        border-radius: 14px;
        font-size: 18px;
        font-weight: 800;
        color: var(--f-ink);
    }
    .fees-input-wrap input:focus {
        outline: none;
        border-color: var(--f-teal);
        box-shadow: 0 0 0 3px rgba(37,161,148,.12);
    }
    .fees-pay-btn {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--f-teal-d), var(--f-teal));
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 8px 24px rgba(37,161,148,.3);
        transition: transform .12s, box-shadow .12s;
    }
    .fees-pay-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(37,161,148,.35);
    }
    .fees-pay-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .fees-pay-trust {
        text-align: center;
        margin-top: 14px;
        font-size: 12px;
        color: var(--f-muted);
        font-weight: 600;
    }
    .fees-msg {
        margin-top: 12px;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        display: none;
    }
    .fees-msg.show { display: block; }
    .fees-msg.err { background: #fef2f2; color: #b91c1c; }
    .fees-msg.ok { background: #ecfdf5; color: #047857; }

    .fees-note {
        margin-top: 16px;
        padding: 16px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid var(--f-border);
        font-size: 13px;
        color: var(--f-muted);
        line-height: 1.5;
    }
    .fees-note a { color: var(--f-teal-d); font-weight: 700; text-decoration: none; }
</style>
@endsection

@section('content')
@php
    $initials = strtoupper(substr($student->firstname,0,1).substr($student->surname,0,1));
@endphp

<div class="fees">
    <div class="fees-balance">
        <div class="fees-balance-inner">
            <div>
                <div class="fees-balance-label">Total outstanding</div>
                <div class="fees-balance-amount">GHS {{ number_format($totalOutstanding, 2) }}</div>
                <div class="fees-balance-meta">
                    <div>Credit available<strong>GHS {{ number_format($creditBalance, 2) }}</strong></div>
                    <div>Amount to pay<strong>GHS {{ number_format($netPayable, 2) }}</strong></div>
                </div>
            </div>
            <div class="fees-student-chip">
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

    <div class="fees-bar">
        <form method="GET" class="fees-bar-filters">
            <select name="academic_year_id" onchange="this.form.submit()">
                <option value="">All years</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected($period['year_id'] == $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
            <select name="academic_term_id" onchange="this.form.submit()">
                <option value="">All terms</option>
                @foreach($academicTerms as $term)
                    <option value="{{ $term->id }}" @selected($period['term_id'] == $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="fees-grid">
        <div>
            <div class="fees-tabs-wrap">
                <div class="fees-tabs" role="tablist">
                    <button type="button" class="fees-tab active" data-tab="due" role="tab">
                        <i class="ri-error-warning-line"></i>
                        Bills to pay
                        <span class="count">{{ $outstandingBills->count() }}</span>
                    </button>
                    <button type="button" class="fees-tab" data-tab="statement" role="tab">
                        <i class="ri-file-list-3-line"></i>
                        Statement
                        <span class="count">{{ $bills->count() }}</span>
                    </button>
                </div>

                {{-- Tab: Bills to pay --}}
                <div class="fees-tab-pane active" id="tab-due" role="tabpanel">
                    @if($outstandingBills->isEmpty())
                        <div class="fees-all-clear" style="margin:0;border:none;border-radius:0;">
                            <i class="ri-checkbox-circle-fill"></i>
                            <h3>All fees paid</h3>
                            <p>{{ $student->firstname }} has no outstanding bills. Thank you!</p>
                        </div>
                    @else
                        <div class="fees-lines" id="feesLines">
                            @foreach($outstandingBills as $bill)
                                <div class="fees-line is-selected {{ $bill['is_compulsory'] ? 'is-locked' : '' }}"
                                     data-bill-id="{{ $bill['id'] }}"
                                     data-balance="{{ number_format($bill['balance'], 2, '.', '') }}"
                                     data-compulsory="{{ $bill['is_compulsory'] ? '1' : '0' }}">
                                    <span class="fees-check"><i class="{{ $bill['is_compulsory'] ? 'ri-lock-fill' : 'ri-check-line' }}"></i></span>
                                    <div>
                                        <div class="fees-line-name">
                                            {{ $bill['item_name'] }}
                                            @if($bill['is_compulsory'])<span class="fees-req">Required</span>@endif
                                        </div>
                                        <div class="fees-line-meta">
                                            {{ trim(($bill['term_name'] ?? '').' '.($bill['year_name'] ?? '')) ?: 'Current' }}
                                            · Due GHS {{ number_format($bill['amount_due'], 2) }}
                                            · Paid GHS {{ number_format($bill['amount_paid'], 2) }}
                                        </div>
                                    </div>
                                    <div class="fees-line-bal">
                                        GHS {{ number_format($bill['balance'], 2) }}
                                        <small>to pay</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tab: Statement --}}
                <div class="fees-tab-pane" id="tab-statement" role="tabpanel">
                    @if($bills->isEmpty())
                        <div class="fees-all-clear" style="margin:0;border:none;border-radius:0;">
                            <i class="ri-file-search-line" style="color:#94a3b8;"></i>
                            <h3>No records</h3>
                            <p>No bills found for the selected period.</p>
                        </div>
                    @else
                        <div class="fees-table-wrap">
                            <table class="fees-table">
                                <thead>
                                    <tr>
                                        <th>Fee item</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th class="text-end">Due</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bills as $bill)
                                        @php
                                            $st = strtolower($bill->status);
                                            $stClass = match($st) {
                                                'paid' => 'fees-status-paid',
                                                'partial' => 'fees-status-partial',
                                                default => 'fees-status-pending',
                                            };
                                        @endphp
                                        <tr>
                                            <td><div class="item-name">{{ $bill->billingItem?->name ?? 'Bill' }}</div></td>
                                            <td class="text-muted">
                                                {{ trim(($bill->setup?->academicTerm?->name ?? '').' '.($bill->setup?->academicYear?->name ?? '')) ?: '—' }}
                                            </td>
                                            <td><span class="fees-status {{ $stClass }}">{{ $bill->status }}</span></td>
                                            <td class="amt">GHS {{ number_format($bill->amount_due, 2) }}</td>
                                            <td class="amt ok">GHS {{ number_format($bill->amount_paid, 2) }}</td>
                                            <td class="amt {{ $bill->balance > 0 ? 'due' : 'ok' }}">GHS {{ number_format($bill->balance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            @if($outstandingBills->isNotEmpty())
                <form class="fees-pay" id="parentPayForm" onsubmit="return false;">
                    @csrf
                    <div class="fees-pay-head">
                        <i class="ri-bank-card-line"></i>
                        <h3>Pay school fees</h3>
                        <p>{{ $paystackConfigured ? 'Mobile Money or card via Paystack' : 'Enter the amount to pay for the selected bills' }}</p>
                    </div>
                    <div class="fees-pay-body">
                        <div class="fees-pay-total">
                            <label>You pay</label>
                            <div><small>GHS </small><span id="showAmt">{{ number_format($netPayable > 0 ? $netPayable : $totalOutstanding, 2) }}</span></div>
                        </div>

                        @if($creditBalance > 0)
                            <label class="fees-credit">
                                <div>
                                    <strong>Use credit GHS {{ number_format($creditBalance, 2) }}</strong>
                                    <span>Apply remaining credit to this payment</span>
                                </div>
                                <input type="checkbox" id="useCredit" {{ $netPayable <= 0 ? 'checked' : '' }}>
                            </label>
                            <div class="fees-input-wrap fees-credit-amt" id="creditAmtWrap">
                                <span>GHS</span>
                                <input type="number" id="creditAmount" min="0" step="0.01" max="{{ $creditBalance }}" value="{{ number_format(min($creditBalance, $totalOutstanding), 2, '.', '') }}">
                            </div>
                        @endif

                        <div class="fees-chips" id="payChips">
                            <button type="button" class="fees-chip on" data-mode="full">Full balance</button>
                            <button type="button" class="fees-chip" data-mode="half">Half</button>
                            @if($netPayable >= 100 || $totalOutstanding >= 100)<button type="button" class="fees-chip" data-v="100">100</button>@endif
                            @if($netPayable >= 200 || $totalOutstanding >= 200)<button type="button" class="fees-chip" data-v="200">200</button>@endif
                            @if($netPayable >= 500 || $totalOutstanding >= 500)<button type="button" class="fees-chip" data-v="500">500</button>@endif
                        </div>
                        <div class="fees-input-wrap">
                            <span>GHS</span>
                            <input type="number" id="payAmount" min="0" step="0.01" value="{{ number_format($netPayable, 2, '.', '') }}">
                        </div>
                        <button type="button" id="paystackBtn" class="fees-pay-btn">
                            <i class="ri-lock-unlock-line"></i>
                            <span id="payBtnLabel">{{ $netPayable > 0 ? 'Pay now' : 'Apply credit' }}</span>
                        </button>
                        <div class="fees-pay-trust">
                            <i class="ri-shield-check-line"></i>
                            {{ $paystackConfigured ? 'Secure Paystack checkout' : 'Compulsory fees are paid first' }}
                        </div>
                        <div id="payMessage" class="fees-msg"></div>
                    </div>
                </form>
            @else
                <div class="fees-pay">
                    <div class="fees-pay-body fees-all-clear" style="border:none;box-shadow:none;margin:0;">
                        <i class="ri-emotion-happy-line"></i>
                        <h3>Nothing to pay</h3>
                        <p>Account is fully settled.</p>
                    </div>
                </div>
            @endif

            <div class="fees-note">
                <i class="ri-information-line"></i> Compulsory fees are paid first. Need help?
                <a href="{{ route('parent.payments', $student) }}">View payment history</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    document.querySelectorAll('.fees-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.fees-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.fees-tab-pane').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab)?.classList.add('active');
        });
    });
})();
</script>
@if($outstandingBills->isNotEmpty())
<script>
(function () {
    const creditBalance = {{ json_encode((float) $creditBalance) }};
    const paystackConfigured = {{ json_encode((bool) $paystackConfigured) }};
    const csrf = @json(csrf_token());
    const initUrl = @json(route('parent.paystack.initialize', $student));
    const verifyUrl = @json(route('parent.paystack.verify', $student));
    const creditUrl = @json(route('parent.pay-credit', $student));
    const input = document.getElementById('payAmount');
    const show = document.getElementById('showAmt');
    const msg = document.getElementById('payMessage');
    const btn = document.getElementById('paystackBtn');
    const btnLabel = document.getElementById('payBtnLabel');
    const useCredit = document.getElementById('useCredit');
    const creditInput = document.getElementById('creditAmount');
    const creditWrap = document.getElementById('creditAmtWrap');

    function round2(n) { return Math.round((n + Number.EPSILON) * 100) / 100; }
    function fmt(n) { return Number(n || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

    function selectedBills() {
        return Array.from(document.querySelectorAll('.fees-line.is-selected')).map(function (el) {
            return {
                id: parseInt(el.dataset.billId, 10),
                balance: parseFloat(el.dataset.balance),
                compulsory: el.dataset.compulsory === '1',
            };
        });
    }

    function selectedTotal() {
        return round2(selectedBills().reduce((sum, bill) => sum + bill.balance, 0));
    }

    function creditApplied() {
        if (!useCredit || !useCredit.checked) return 0;
        const requested = parseFloat(creditInput?.value || '0') || 0;
        return round2(Math.min(Math.max(requested, 0), creditBalance, selectedTotal()));
    }

    function cashAmount() {
        return round2(Math.max(selectedTotal() - creditApplied(), 0));
    }

    function allocations(totalFunding) {
        let remaining = round2(totalFunding);
        const rows = [];
        selectedBills().forEach(function (bill) {
            if (remaining <= 0) return;
            const amount = round2(Math.min(bill.balance, remaining));
            if (amount > 0) {
                rows.push({ student_bill_id: bill.id, amount: amount });
                remaining = round2(remaining - amount);
            }
        });
        return rows;
    }

    function sync() {
        const cash = cashAmount();
        const credit = creditApplied();
        if (input && document.activeElement !== input) {
            input.value = cash.toFixed(2);
        }
        if (show) show.textContent = fmt(cash > 0 ? cash : credit);
        if (creditWrap) creditWrap.style.display = useCredit?.checked ? 'block' : 'none';
        if (btnLabel) btnLabel.textContent = cash > 0.009 ? 'Pay now' : 'Apply credit';
        document.querySelectorAll('.fees-chip').forEach(c => c.classList.remove('on'));
    }

    document.querySelectorAll('.fees-line').forEach(function (line) {
        line.addEventListener('click', function () {
            if (line.classList.contains('is-locked')) return;
            line.classList.toggle('is-selected');
            const icon = line.querySelector('.fees-check i');
            if (icon) icon.className = line.classList.contains('is-selected') ? 'ri-check-line' : 'ri-subtract-line';
            if (useCredit?.checked && creditInput) {
                creditInput.value = Math.min(creditBalance, selectedTotal()).toFixed(2);
            }
            sync();
        });
    });

    useCredit?.addEventListener('change', function () {
        if (creditInput) creditInput.value = Math.min(creditBalance, selectedTotal()).toFixed(2);
        sync();
    });
    creditInput?.addEventListener('input', sync);

    document.querySelectorAll('.fees-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            const total = selectedTotal();
            const credit = creditApplied();
            let value = total - credit;
            if (chip.dataset.mode === 'half') value = round2((total - credit) / 2);
            if (chip.dataset.v) value = parseFloat(chip.dataset.v);
            input.value = Math.max(value, 0).toFixed(2);
            show.textContent = fmt(input.value);
            document.querySelectorAll('.fees-chip').forEach(c => c.classList.remove('on'));
            chip.classList.add('on');
            if (btnLabel) btnLabel.textContent = parseFloat(input.value) > 0.009 ? 'Pay now' : 'Apply credit';
        });
    });

    input?.addEventListener('input', function () {
        show.textContent = fmt(input.value);
        document.querySelectorAll('.fees-chip').forEach(c => c.classList.remove('on'));
        if (btnLabel) btnLabel.textContent = parseFloat(input.value || '0') > 0.009 ? 'Pay now' : 'Apply credit';
    });

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Request failed.');
        return data;
    }

    function finish(data) {
        if (data.receipt_url) {
            location.href = data.receipt_url;
            return;
        }
        msg.textContent = data.message || 'Payment successful!';
        msg.className = 'fees-msg show ok';
        setTimeout(() => location.reload(), 900);
    }

    btn?.addEventListener('click', async function () {
        const selected = selectedBills();
        if (!selected.length) {
            msg.textContent = 'Select at least one bill to pay.';
            msg.className = 'fees-msg show err';
            return;
        }

        const cash = round2(parseFloat(input.value || '0') || 0);
        const credit = creditApplied();
        const funding = round2(cash + credit);

        if (funding <= 0) {
            msg.textContent = 'Enter an amount to pay, or apply credit.';
            msg.className = 'fees-msg show err';
            return;
        }

        const alloc = allocations(funding);
        btn.disabled = true;
        msg.textContent = cash > 0 ? 'Opening payment…' : 'Applying credit…';
        msg.className = 'fees-msg show';

        try {
            if (cash <= 0) {
                const data = await postJson(creditUrl, { credit_applied: credit, allocations: alloc });
                finish(data);
                return;
            }

            if (!paystackConfigured || typeof PaystackPop === 'undefined') {
                throw new Error('Online payment is not available yet. Please pay at the school office.');
            }

            const data = await postJson(initUrl, { amount: cash, credit_applied: credit, allocations: alloc });
            PaystackPop.setup({
                key: data.public_key,
                email: data.email,
                amount: data.amount,
                currency: data.currency || 'GHS',
                ref: data.reference,
                label: data.label,
                callback(response) {
                    msg.textContent = 'Verifying…';
                    postJson(verifyUrl, { reference: response.reference })
                        .then(finish)
                        .catch(function (err) {
                            msg.textContent = err.message || 'Verification failed.';
                            msg.className = 'fees-msg show err';
                            btn.disabled = false;
                        });
                },
                onClose() {
                    btn.disabled = false;
                    msg.className = 'fees-msg';
                },
            }).openIframe();
        } catch (err) {
            msg.textContent = err.message;
            msg.className = 'fees-msg show err';
            btn.disabled = false;
        }
    });

    sync();
})();
</script>
@endif
@endsection
