@php
    $pageName = 'bill-management';
    $subpageName = 'student-bills';
    $initials = strtoupper(substr($student->firstname, 0, 1) . substr($student->surname, 0, 1));
    $photoUrl = $student->picture ? asset($student->picture) : null;
@endphp
@extends('layouts.app')

@section('css')
<style>
    .fc-page { --fc-teal: #25A194; --fc-indigo: #6366f1; --fc-dark: #0f172a; --fc-red: #dc2626; min-height: calc(100vh - 120px); }

    .fc-topbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
        margin-bottom: 20px;
    }
    .fc-back {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
        border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; color: #374151;
        font-size: 13px; font-weight: 600; text-decoration: none; transition: all .15s;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .fc-back:hover { border-color: var(--fc-teal); color: var(--fc-teal); background: rgba(37,161,148,.04); }
    .fc-clock {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
        border-radius: 12px; background: var(--fc-dark); color: #fff; font-size: 13px; font-weight: 600;
    }
    .fc-clock i { color: #34d399; }

    .fc-profile {
        position: relative; border-radius: 20px; margin-bottom: 20px; background: #fff;
        border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 8px 30px rgba(15,23,42,.06);
    }
    .fc-profile-accent { height: 5px; background: linear-gradient(90deg, var(--fc-teal), var(--fc-indigo)); }
    .fc-profile-inner {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 24px; padding: 24px 28px;
    }
    .fc-profile-student { display: flex; align-items: center; gap: 20px; min-width: 0; flex: 1; }
    .fc-profile-photo {
        width: 96px; height: 96px; border-radius: 18px; flex-shrink: 0; overflow: hidden;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 28px; font-weight: 800; color: #fff;
        background: linear-gradient(135deg, var(--fc-teal), #17897e);
        border: 4px solid rgba(37,161,148,.12); box-shadow: 0 8px 24px rgba(37,161,148,.22);
    }
    .fc-profile-photo img { width: 100%; height: 100%; object-fit: cover; }
    .fc-profile-meta { min-width: 0; }
    .fc-profile-eyebrow {
        display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em; color: var(--fc-teal); margin-bottom: 8px;
    }
    .fc-profile-name {
        font-size: 28px; font-weight: 800; color: #111827; margin: 0 0 6px;
        letter-spacing: -.02em; line-height: 1.15;
    }
    .fc-profile-id { font-size: 14px; color: #6b7280; margin: 0 0 14px; font-weight: 500; }
    .fc-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .fc-tag {
        display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px;
        border-radius: 999px; font-size: 12px; font-weight: 600; color: #374151;
        background: #f9fafb; border: 1px solid #e5e7eb;
    }
    .fc-tag i { color: var(--fc-teal); font-size: 14px; }

    .fc-profile-total {
        flex-shrink: 0; min-width: 220px; padding: 20px 22px; border-radius: 16px;
        background: linear-gradient(135deg, #fff7ed, #fffbeb); border: 1px solid #fde68a;
        text-align: center; box-shadow: 0 4px 16px rgba(245,158,11,.08);
    }
    .fc-profile-total-lbl {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em;
        color: #b45309; margin-bottom: 8px;
    }
    .fc-profile-total-val {
        font-size: 34px; font-weight: 800; color: #dc2626; letter-spacing: -.03em; line-height: 1;
    }
    .fc-profile-total-sub { font-size: 12px; color: #92400e; margin-top: 8px; font-weight: 600; }

    @media (max-width: 768px) {
        .fc-profile-inner { padding: 20px; }
        .fc-profile-student { flex-direction: column; align-items: flex-start; }
        .fc-profile-photo { width: 84px; height: 84px; }
        .fc-profile-name { font-size: 24px; }
        .fc-profile-total { width: 100%; min-width: 0; }
    }

    .fc-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(340px, 420px); gap: 20px; align-items: start; }
    @media (max-width: 991px) { .fc-grid { grid-template-columns: 1fr; } }

    .fc-card {
        background: #fff; border-radius: 18px; border: 1px solid #e5e7eb;
        box-shadow: 0 4px 20px rgba(15,23,42,.05); overflow: hidden;
    }
    .fc-card-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 18px 22px; border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fff, #fafafa);
    }
    .fc-card-head h6 {
        font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        color: #6b7280; margin: 0; display: flex; align-items: center; gap: 8px;
    }
    .fc-card-head h6 i { color: var(--fc-teal); font-size: 16px; }
    .fc-count {
        font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px;
        background: rgba(37,161,148,.1); color: var(--fc-teal);
    }

    .fc-actions { display: flex; gap: 8px; }
    .fc-mini-btn {
        padding: 7px 12px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff;
        font-size: 11px; font-weight: 700; color: #374151; cursor: pointer; transition: all .12s;
    }
    .fc-mini-btn:hover { border-color: var(--fc-teal); color: var(--fc-teal); background: rgba(37,161,148,.04); }

    .fc-lines { padding: 14px 18px 18px; max-height: 460px; overflow-y: auto; }
    .fc-line {
        display: flex; align-items: center; gap: 14px; padding: 16px 18px;
        border-radius: 16px; margin-bottom: 10px; cursor: pointer;
        border: 1px solid #f3f4f6; background: linear-gradient(135deg, #fafafa, #fff);
        transition: all .15s;
    }
    .fc-line:hover { border-color: rgba(37,161,148,.25); box-shadow: 0 4px 16px rgba(15,23,42,.06); transform: translateY(-1px); }
    .fc-line.is-selected {
        border-color: rgba(37,161,148,.4); background: linear-gradient(135deg, rgba(37,161,148,.06), rgba(99,102,241,.04));
        box-shadow: 0 0 0 1px rgba(37,161,148,.15), 0 4px 16px rgba(37,161,148,.08);
    }
    .fc-line.is-required.is-selected { border-left: 3px solid #f59e0b; }
    .fc-line.is-locked.is-selected:hover { transform: none; }

    .fc-check {
        width: 24px; height: 24px; border-radius: 8px; flex-shrink: 0;
        border: 2px solid #d1d5db; display: inline-flex; align-items: center; justify-content: center;
        font-size: 14px; color: transparent; transition: all .12s;
    }
    .fc-line.is-selected .fc-check { background: linear-gradient(135deg, var(--fc-teal), #1d8a80); border-color: var(--fc-teal); color: #fff; }
    .fc-line.is-required.is-selected .fc-check { background: #f59e0b; border-color: #f59e0b; }

    .fc-line-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 20px;
        background: rgba(99,102,241,.1); color: var(--fc-indigo);
    }
    .fc-line.is-selected .fc-line-icon { background: rgba(37,161,148,.12); color: var(--fc-teal); }
    .fc-line-info { flex: 1; min-width: 0; }
    .fc-line-name { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 3px; }
    .fc-line-meta { font-size: 11px; color: #9ca3af; }
    .fc-required {
        font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
        padding: 2px 7px; border-radius: 5px; margin-left: 8px;
        background: rgba(245,158,11,.12); color: #b45309; vertical-align: middle;
    }
    .fc-line-amt { text-align: right; flex-shrink: 0; }
    .fc-line-balance { font-size: 17px; font-weight: 800; color: #111827; }
    .fc-line-due { font-size: 10px; color: #9ca3af; margin-top: 2px; }

    .fc-cart-foot {
        padding: 18px 22px; border-top: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #fafafa, #fff);
    }
    .fc-cart-row { display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; margin-bottom: 8px; }
    .fc-cart-row.total {
        font-size: 15px; font-weight: 800; color: #111827; margin: 0; padding-top: 12px;
        border-top: 1px dashed #e5e7eb;
    }
    .fc-cart-row.total span:last-child { font-size: 22px; color: #dc2626; }

    .fc-terminal { position: sticky; top: 20px; }
    .fc-display {
        margin: 18px 18px 0; padding: 22px 24px; border-radius: 18px;
        background: linear-gradient(145deg, #0f172a, #1e293b);
        color: #fff; position: relative; overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 8px 24px rgba(15,23,42,.2);
    }
    .fc-display::before {
        content: ''; position: absolute; top: 0; right: 0; width: 120px; height: 120px;
        background: radial-gradient(circle, rgba(52,211,153,.15), transparent 70%);
    }
    .fc-display-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; opacity: .55; margin-bottom: 6px; }
    .fc-display-amt { font-size: 38px; font-weight: 800; letter-spacing: -.03em; line-height: 1; color: #34d399; position: relative; }
    .fc-display-row {
        display: flex; justify-content: space-between; margin-top: 14px; padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,.1); font-size: 12px; opacity: .75; position: relative;
    }
    .fc-display-change { color: #fbbf24; font-weight: 800; font-size: 14px; }

    .fc-panel-body { padding: 18px; }
    .fc-quick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
    .fc-quick-btn {
        padding: 10px 8px; border-radius: 11px; border: 1px solid #e5e7eb; background: #fff;
        font-size: 11px; font-weight: 800; color: #374151; cursor: pointer; transition: all .12s;
    }
    .fc-quick-btn:hover { border-color: var(--fc-teal); color: var(--fc-teal); background: rgba(37,161,148,.05); }

    .fc-field { margin-bottom: 14px; }
    .fc-field label {
        font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        color: #6b7280; margin-bottom: 7px; display: block;
    }
    .fc-field .form-control { border-radius: 12px; min-height: 46px; font-size: 14px; border-color: #e5e7eb; }
    .fc-field .form-control:focus { border-color: var(--fc-teal); box-shadow: 0 0 0 3px rgba(37,161,148,.12); }
    .fc-amt-wrap { position: relative; }
    .fc-amt-prefix {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        font-size: 18px; font-weight: 800; color: var(--fc-teal); pointer-events: none;
    }
    .fc-amt-wrap .form-control { padding-left: 40px; font-size: 20px; font-weight: 800; min-height: 54px; }

    .fc-methods { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
    @media (max-width: 575px) { .fc-methods { grid-template-columns: 1fr; } }
    .fc-method {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 16px 10px; border-radius: 14px; border: 2px solid #e5e7eb; background: #fff;
        cursor: pointer; transition: all .12s;
    }
    .fc-method i { font-size: 26px; transition: color .12s; }
    .fc-method span { font-size: 11px; font-weight: 800; }
    .fc-method[data-method="Cash"] {
        background: #f0fdf4; border-color: #bbf7d0;
    }
    .fc-method[data-method="Cash"] i { color: #16a34a; }
    .fc-method[data-method="Cash"] span { color: #15803d; }
    .fc-method[data-method="Cash"].is-active {
        border-color: #16a34a; background: #dcfce7;
        box-shadow: 0 0 0 1px rgba(22,163,74,.2);
    }
    .fc-method[data-method="Bank"] {
        background: #eff6ff; border-color: #bfdbfe;
    }
    .fc-method[data-method="Bank"] i { color: #2563eb; }
    .fc-method[data-method="Bank"] span { color: #1d4ed8; }
    .fc-method[data-method="Bank"].is-active {
        border-color: #2563eb; background: #dbeafe;
        box-shadow: 0 0 0 1px rgba(37,99,235,.2);
    }
    .fc-method[data-method="Paystack"] {
        background: #f5f3ff; border-color: #ddd6fe;
    }
    .fc-method[data-method="Paystack"] i { color: #7c3aed; }
    .fc-method[data-method="Paystack"] span { color: #6d28d9; }
    .fc-method[data-method="Paystack"].is-active {
        border-color: #7c3aed; background: #ede9fe;
        box-shadow: 0 0 0 1px rgba(124,58,237,.2);
    }
    .fc-method.is-disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }

    .fc-ref-field { display: none; }
    .fc-ref-field.is-visible { display: block; }

    .fc-credit-box {
        background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 12px;
        padding: 12px 14px; margin-bottom: 14px;
    }
    .fc-credit-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .fc-credit-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #15803d; }
    .fc-credit-val { font-size: 16px; font-weight: 800; color: #15803d; }
    .fc-credit-note { font-size: 11px; color: #6b7280; margin: 8px 0 0; line-height: 1.4; }

    .fc-excess-option {
        background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
        padding: 10px 12px; margin-bottom: 14px;
    }
    .fc-excess-option label { font-size: 12px; color: #92400e; margin: 0; cursor: pointer; }
    .fc-surplus-hint { font-size: 11px; color: #b45309; margin-top: 6px; }

    .fc-submit-wrap { padding: 0 18px 20px; }
    .fc-submit {
        width: 100%; min-height: 56px; border: none; border-radius: 14px;
        background: var(--fc-red); color: #fff; font-size: 16px; font-weight: 800; letter-spacing: .02em;
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        box-shadow: 0 8px 24px rgba(220,38,38,.3); transition: transform .12s, box-shadow .12s, background .12s;
    }
    .fc-submit:hover:not(:disabled) { transform: translateY(-2px); background: #b91c1c; box-shadow: 0 12px 28px rgba(220,38,38,.35); color: #fff; }
    .fc-submit:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

    .fc-hint { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 10px; }
    .fc-no-bills { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 14px; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body fc-page">
    <div class="fc-topbar">
        <a href="{{ route('student-bills') }}" class="fc-back"><i class="ri-arrow-left-line"></i> Back to Student Bills</a>
        <div class="fc-clock"><i class="ri-time-line"></i> <span id="fc_clock">{{ now()->format('D, M j Y · g:i A') }}</span></div>
    </div>

    <div class="fc-profile">
        <div class="fc-profile-accent"></div>
        <div class="fc-profile-inner">
            <div class="fc-profile-student">
                <div class="fc-profile-photo">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $student->full_name }}">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="fc-profile-meta">
                    <div class="fc-profile-eyebrow"><i class="ri-store-2-line"></i> Fee Cashier</div>
                    <h1 class="fc-profile-name">{{ $student->full_name }}</h1>
                    <p class="fc-profile-id">Student ID · {{ $student->student_id }}</p>
                    <div class="fc-tags">
                        @if($student->class_name)
                            <span class="fc-tag"><i class="ri-book-open-line"></i> {{ $student->class_name }}</span>
                        @endif
                        @if($student->schoolClass?->category?->name)
                            <span class="fc-tag"><i class="ri-stack-line"></i> {{ $student->schoolClass->category->name }}</span>
                        @endif
                        @if($student->gender)
                            <span class="fc-tag"><i class="ri-user-line"></i> {{ $student->gender }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="fc-profile-total">
                <div class="fc-profile-total-lbl">Total outstanding</div>
                <div class="fc-profile-total-val">₵{{ number_format($totalOutstanding, 2) }}</div>
                <div class="fc-profile-total-sub">{{ $bills->count() }} bill item{{ $bills->count() === 1 ? '' : 's' }} due</div>
            </div>
        </div>
    </div>

    <form id="recordPaymentForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="payment_method" id="fc_payment_method" value="Cash">

        <div class="fc-grid">
            <div class="fc-card">
                <div class="fc-card-head">
                    <h6><i class="ri-shopping-cart-2-line"></i> Outstanding items <span class="fc-count" id="fc_selected_count">{{ $bills->count() }} selected</span></h6>
                    <div class="fc-actions">
                        <button type="button" class="fc-mini-btn" id="fc_select_all">Select all</button>
                        <button type="button" class="fc-mini-btn" id="fc_clear_all">Clear optional</button>
                    </div>
                </div>
                <div class="fc-lines" id="fc_bill_lines">
                    @forelse($bills as $bill)
                        @php $period = collect([$bill['term_name'], $bill['year_name']])->filter()->join(' · '); @endphp
                        <div class="fc-line is-selected {{ $bill['is_compulsory'] ? 'is-required is-locked' : '' }}"
                             data-bill-id="{{ $bill['id'] }}"
                             data-balance="{{ $bill['balance'] }}"
                             data-compulsory="{{ $bill['is_compulsory'] ? '1' : '0' }}">
                            <span class="fc-check">
                                @if($bill['is_compulsory'])
                                    <i class="ri-lock-fill"></i>
                                @else
                                    <i class="ri-check-line"></i>
                                @endif
                            </span>
                            <div class="fc-line-icon"><i class="ri-bill-line"></i></div>
                            <div class="fc-line-info">
                                <div class="fc-line-name">
                                    {{ $bill['item_name'] }}
                                    @if($bill['is_compulsory'])<span class="fc-required">Required</span>@endif
                                </div>
                                <div class="fc-line-meta">{{ $period ?: 'Outstanding balance' }} · Due ₵{{ number_format($bill['amount_due'], 2) }} · Paid ₵{{ number_format($bill['amount_paid'], 2) }}</div>
                            </div>
                            <div class="fc-line-amt">
                                <div class="fc-line-balance">₵{{ number_format($bill['balance'], 2) }}</div>
                                <div class="fc-line-due">balance</div>
                            </div>
                        </div>
                    @empty
                        <div class="fc-no-bills">
                            @if(($creditBalance ?? 0) > 0)
                                No outstanding bills. Credit balance: <strong>₵{{ number_format($creditBalance, 2) }}</strong>
                            @else
                                All bills are paid for this student.
                            @endif
                        </div>
                    @endforelse
                </div>
                @if($bills->isNotEmpty())
                <div class="fc-cart-foot">
                    <div class="fc-cart-row"><span>Selected total</span><span id="fc_cart_subtotal">₵{{ number_format($totalOutstanding, 2) }}</span></div>
                    <div class="fc-cart-row total"><span>Amount to collect</span><span id="fc_cart_total">₵{{ number_format($totalOutstanding, 2) }}</span></div>
                </div>
                @endif
            </div>

            <div class="fc-terminal">
                <div class="fc-card">
                    <div class="fc-display">
                        <div class="fc-display-lbl">Payment amount</div>
                        <div class="fc-display-amt" id="fc_display_amount">₵{{ number_format($totalOutstanding, 2) }}</div>
                        <div class="fc-display-row">
                            <span>Change due</span>
                            <span class="fc-display-change" id="fc_display_change">—</span>
                        </div>
                    </div>

                    <div class="fc-panel-body">
                        <div class="fc-quick">
                            <button type="button" class="fc-quick-btn" data-fc-quick="full">Pay full</button>
                            <button type="button" class="fc-quick-btn" data-fc-quick="half">Half</button>
                            <button type="button" class="fc-quick-btn" data-fc-quick="clear">Clear</button>
                        </div>

                        <div class="fc-credit-box" id="fc_credit_box">
                            <div class="fc-credit-top">
                                <div>
                                    <div class="fc-credit-label">Remaining credit</div>
                                    <div class="fc-credit-val" id="fc_credit_balance_display">₵{{ number_format($creditBalance ?? 0, 2) }}</div>
                                </div>
                                @if(($creditBalance ?? 0) > 0)
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="fc_use_credit">
                                    <label class="form-check-label" for="fc_use_credit">Use credit</label>
                                </div>
                                @endif
                            </div>
                            @if(($creditBalance ?? 0) > 0)
                            <div class="fc-field mb-0" id="fc_credit_amount_field" style="display:none;">
                                <label>Credit to apply</label>
                                <div class="fc-amt-wrap">
                                    <span class="fc-amt-prefix">₵</span>
                                    <input type="number" min="0" step="0.01" id="fc_credit_applied" class="form-control" value="0">
                                </div>
                            </div>
                            @else
                            <p class="fc-credit-note mb-0">Overpaying any bill saves the extra amount here. It can be applied to reduce the next payment.</p>
                            @endif
                        </div>

                        <div class="fc-field">
                            <label id="fc_amount_label">Amount to collect</label>
                            <div class="fc-amt-wrap">
                                <span class="fc-amt-prefix">₵</span>
                                <input type="number" min="0" step="0.01" name="amount" id="fc_payment_amount" class="form-control" value="{{ number_format($totalOutstanding, 2, '.', '') }}" inputmode="decimal" autocomplete="off">
                            </div>
                            <p class="fc-surplus-hint" id="fc_surplus_hint" style="display:none;"></p>
                        </div>

                        <div class="fc-field" id="fc_tendered_field">
                            <label>Amount received (cash)</label>
                            <div class="fc-amt-wrap">
                                <span class="fc-amt-prefix">₵</span>
                                <input type="number" min="0" step="0.01" id="fc_amount_tendered" class="form-control" value="{{ number_format($totalOutstanding, 2, '.', '') }}">
                            </div>
                        </div>

                        <div class="fc-excess-option" id="fc_excess_credit_wrap" style="display:none;">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="fc_save_excess_as_credit" checked>
                                <label class="form-check-label" for="fc_save_excess_as_credit" id="fc_save_excess_label">
                                    Save excess received as remaining credit
                                </label>
                            </div>
                        </div>

                        <div class="fc-field">
                            <label>Payment method</label>
                            <div class="fc-methods">
                                <div class="fc-method is-active" data-method="Cash"><i class="ri-money-dollar-box-line"></i><span>Cash</span></div>
                                <div class="fc-method" data-method="Bank"><i class="ri-bank-line"></i><span>Bank</span></div>
                            </div>
                        </div>

                        <div id="fc_bank_fields" style="display:none;">
                            <div class="fc-field">
                                <label>Paying by</label>
                                <select name="bank_instrument" id="fc_bank_instrument" class="form-control form-select">
                                    <option value="">Select Cheque or Bankers Draft</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Bankers Draft">Bankers Draft</option>
                                </select>
                            </div>
                            <div class="fc-field">
                                <label>Receipt / cheque number</label>
                                <input type="text" name="reference" id="fc_bank_reference" class="form-control" placeholder="Enter receipt or cheque number" maxlength="100">
                            </div>
                            <div class="fc-field">
                                <label>Upload copy of receipt</label>
                                <input type="file" name="receipt_file" id="fc_receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                            </div>
                        </div>

                        <div class="fc-field fc-ref-field" id="fc_ref_field">
                            <label>Reference / transaction ID</label>
                            <input type="text" id="fc_generic_reference" class="form-control" placeholder="Enter reference number">
                        </div>

                        <div class="fc-field">
                            <label>Payment date</label>
                            <input type="datetime-local" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d\\TH:i') }}" required>
                        </div>

                        <div class="fc-field mb-0">
                            <label>Notes (optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Any note for this payment">
                        </div>
                    </div>

                    <div class="fc-submit-wrap">
                        <button type="submit" class="fc-submit" id="fc_submit_btn">
                            <i class="ri-printer-line"></i> Complete payment &amp; print receipt
                        </button>
                        <p class="fc-hint">Receipt opens automatically after successful payment</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const paymentUrl = @json(route('record-bill-payment-process', [], false));
    const paystackInitializeUrl = @json(route('paystack-bill-payment-initialize', [], false));
    const paystackVerifyUrl = @json(route('paystack-bill-payment-verify', [], false));
    const paystackPublicKey = @json($paystackPublicKey ?? '');
    const paystackConfigured = @json($paystackConfigured ?? false);
    const studentBillsUrl = @json(route('student-bills', [], false));
    const billData = @json($bills->values());
    const hasOutstandingBills = @json($hasOutstandingBills ?? $bills->isNotEmpty());
    let selectedIds = new Set(billData.map(function(b){ return b.id; }));

    function isCompulsoryBill(id) {
        const bill = billData.find(function(b){ return b.id === id; });
        return !!(bill && bill.is_compulsory);
    }

    function compulsoryBillIds() {
        return billData.filter(function(b){ return b.is_compulsory; }).map(function(b){ return b.id; });
    }

    function ensureCompulsorySelected() {
        compulsoryBillIds().forEach(function(id) {
            selectedIds.add(id);
        });
        syncLineSelectionClasses();
    }

    function syncLineSelectionClasses() {
        $('.fc-line').each(function() {
            const id = parseInt($(this).data('bill-id'), 10);
            const isSelected = selectedIds.has(id);
            const isRequired = $(this).data('compulsory') === 1 || $(this).data('compulsory') === '1';
            $(this).toggleClass('is-selected', isSelected);
            if (isRequired) {
                $(this).addClass('is-required');
                if (isSelected) {
                    $(this).addClass('is-locked');
                }
            }
        });
    }

    function resolveEffectivePayAmount(payAmount, tendered, total, method) {
        payAmount = parseFloat(payAmount || 0);
        tendered = parseFloat(tendered || 0);
        total = parseFloat(total || 0);

        if (method === 'Cash' && $('#fc_save_excess_as_credit').is(':checked') && tendered > total + 0.009) {
            return Math.max(payAmount, tendered);
        }

        return payAmount;
    }

    function syncPayAmountFromTendered() {
        if ($('#fc_payment_method').val() !== 'Cash' || !$('#fc_save_excess_as_credit').is(':checked')) {
            return;
        }

        const total = selectedTotal();
        const tendered = parseFloat($('#fc_amount_tendered').val() || 0);

        if (total > 0 && tendered > total + 0.009) {
            $('#fc_payment_amount').val(tendered.toFixed(2));
        }
    }

    function formatMoney(value) {
        return parseFloat(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function selectedBills() {
        return billData.filter(function(b){ return selectedIds.has(b.id); });
    }

    function selectedTotal() {
        return selectedBills().reduce(function(sum, b){ return sum + parseFloat(b.balance || 0); }, 0);
    }

    const creditBalance = parseFloat(@json($creditBalance ?? 0));

    function creditAppliedAmount() {
        if (!$('#fc_use_credit').length || !$('#fc_use_credit').is(':checked')) {
            return 0;
        }
        return parseFloat($('#fc_credit_applied').val() || 0);
    }

    function totalFundingAmount() {
        return parseFloat($('#fc_payment_amount').val() || 0) + creditAppliedAmount();
    }

    function buildAllocations(totalApplied) {
        let remaining = parseFloat(totalApplied || 0);
        const allocations = [];
        selectedBills().forEach(function(bill) {
            if (remaining <= 0) return;
            const amount = Math.min(parseFloat(bill.balance || 0), remaining);
            if (amount > 0) {
                allocations.push({ student_bill_id: bill.id, amount: amount.toFixed(2) });
                remaining -= amount;
            }
        });
        return allocations;
    }

    function syncPaymentAmountWithSelection(forceFullAmount) {
        const total = selectedTotal();
        const $paymentAmount = $('#fc_payment_amount');
        const currentPay = parseFloat($paymentAmount.val() || 0);

        if (forceFullAmount) {
            $paymentAmount.val(total > 0 ? total.toFixed(2) : '');
            $('#fc_amount_tendered').val(total > 0 ? total.toFixed(2) : '');
            return;
        }

        if (total <= 0) {
            $paymentAmount.val('');
            return;
        }

        if (!Number.isFinite(currentPay) || currentPay <= 0) {
            $paymentAmount.val(total.toFixed(2));
            return;
        }
    }

    function updateDisplay() {
        const total = selectedTotal();
        const enteredPayAmount = parseFloat($('#fc_payment_amount').val() || 0);
        const creditApplied = creditAppliedAmount();
        const tendered = parseFloat($('#fc_amount_tendered').val() || 0);
        const method = $('#fc_payment_method').val();
        const payAmount = resolveEffectivePayAmount(enteredPayAmount, tendered, total, method);
        const totalApplied = payAmount + creditApplied;
        const saveExcessAsCredit = method === 'Cash' && $('#fc_save_excess_as_credit').is(':checked');
        const excessReceived = method === 'Cash' ? Math.max(tendered - total, 0) : 0;
        const change = method === 'Cash' && !saveExcessAsCredit && tendered > payAmount ? tendered - payAmount : 0;
        const surplus = Math.max(totalApplied - total, 0);
        const creditToApply = creditAppliedAmount();
        const netToCollect = Math.max(total - Math.min(creditBalance, total), 0);

        $('#fc_display_amount').text('₵' + formatMoney(totalApplied));
        $('#fc_selected_count').text(selectedBills().length + ' selected');
        $('#fc_cart_subtotal').text('₵' + formatMoney(total));
        $('#fc_cart_total').text('₵' + formatMoney(Math.min(totalApplied, total)));
        $('#fc_display_change').text(change > 0 ? '₵' + formatMoney(change) : (surplus > 0.009 ? '₵0.00 credit' : '—'));

        if (!hasOutstandingBills) {
            $('#fc_submit_btn').prop('disabled', true);
        } else {
            $('#fc_submit_btn').prop('disabled', totalApplied <= 0 || selectedBills().length === 0);
        }

        if (surplus > 0.009) {
            $('#fc_surplus_hint').text('₵' + formatMoney(surplus) + ' will be saved as remaining credit and shown on the bill printout.').show();
        } else {
            $('#fc_surplus_hint').hide();
        }

        if (method === 'Cash' && excessReceived > 0.009) {
            $('#fc_excess_credit_wrap').show();
            $('#fc_save_excess_label').text(
                saveExcessAsCredit
                    ? 'Save ₵' + formatMoney(excessReceived) + ' excess as remaining credit'
                    : 'Return ₵' + formatMoney(excessReceived) + ' excess as change to customer'
            );
        } else {
            $('#fc_excess_credit_wrap').hide();
        }

        if ($('#fc_use_credit').is(':checked')) {
            $('#fc_amount_label').text('Cash to collect');
        } else {
            $('#fc_amount_label').text('Amount to collect');
        }

        if (method === 'Cash') {
            $('#fc_ref_field').removeClass('is-visible');
            $('#fc_tendered_field').show();
            $('#fc_bank_fields').hide();
        } else if (method === 'Paystack') {
            $('#fc_ref_field').removeClass('is-visible');
            $('#fc_tendered_field').hide();
            $('#fc_bank_fields').hide();
        } else if (method === 'Bank') {
            $('#fc_ref_field').removeClass('is-visible');
            $('#fc_tendered_field').hide();
            $('#fc_bank_fields').show();
        } else {
            $('#fc_ref_field').addClass('is-visible');
            $('#fc_tendered_field').hide();
            $('#fc_bank_fields').hide();
        }

        if (method === 'Paystack') {
            $('#fc_submit_btn').html('<i class="ri-secure-payment-line"></i> Pay with Paystack');
        } else if (method === 'Bank') {
            $('#fc_submit_btn').html('<i class="ri-time-line"></i> Submit for approval');
        } else {
            $('#fc_submit_btn').html('<i class="ri-printer-line"></i> Complete payment &amp; print receipt');
        }
    }

    $('.fc-line').on('click', function() {
        const id = parseInt($(this).data('bill-id'), 10);
        const isRequired = $(this).data('compulsory') === 1 || $(this).data('compulsory') === '1';

        if (selectedIds.has(id)) {
            if (isRequired) {
                showAppToast('error', 'Required bill items cannot be removed.');
                return;
            }
            selectedIds.delete(id);
        } else {
            selectedIds.add(id);
        }
        syncLineSelectionClasses();
        syncPaymentAmountWithSelection(false);
        updateDisplay();
    });

    $('#fc_select_all').on('click', function() {
        selectedIds = new Set(billData.map(function(b){ return b.id; }));
        syncLineSelectionClasses();
        syncPaymentAmountWithSelection(true);
        updateDisplay();
    });

    $('#fc_clear_all').on('click', function() {
        selectedIds = new Set(compulsoryBillIds());
        syncLineSelectionClasses();
        const total = selectedTotal();
        if (total > 0) {
            $('#fc_payment_amount').val(total.toFixed(2));
            $('#fc_amount_tendered').val(total.toFixed(2));
        } else {
            $('#fc_payment_amount').val('');
            $('#fc_amount_tendered').val('');
        }
        updateDisplay();
    });

    $('.fc-method').on('click', function() {
        if ($(this).hasClass('is-disabled')) {
            return;
        }

        $('.fc-method').removeClass('is-active');
        $(this).addClass('is-active');
        $('#fc_payment_method').val($(this).data('method'));
        updateDisplay();
    });

    $('[data-fc-quick]').on('click', function() {
        const action = $(this).data('fc-quick');
        const total = selectedTotal();
        if (action === 'full') {
            syncPaymentAmountWithSelection(true);
        } else if (action === 'half') {
            const half = (total / 2).toFixed(2);
            $('#fc_payment_amount').val(half);
            $('#fc_amount_tendered').val(half);
        } else {
            $('#fc_payment_amount').val('');
            $('#fc_amount_tendered').val('');
        }
        updateDisplay();
    });

    $('#fc_payment_amount').on('input', updateDisplay);
    $('#fc_amount_tendered').on('input', function() {
        syncPayAmountFromTendered();
        updateDisplay();
    });
    $('#fc_credit_applied').on('input', updateDisplay);
    $('#fc_save_excess_as_credit').on('change', function() {
        if (!$(this).is(':checked')) {
            const total = selectedTotal();
            const currentPay = parseFloat($('#fc_payment_amount').val() || 0);
            if (total > 0 && currentPay > total + 0.009) {
                $('#fc_payment_amount').val(total.toFixed(2));
            }
        } else {
            syncPayAmountFromTendered();
        }
        updateDisplay();
    });
    $('#fc_payment_amount').on('focus', function() {
        $(this).select();
    });

    $('#fc_use_credit').on('change', function() {
        const checked = $(this).is(':checked');
        $('#fc_credit_amount_field').toggle(checked);
        if (checked) {
            const defaultCredit = Math.min(creditBalance, selectedTotal()).toFixed(2);
            $('#fc_credit_applied').val(defaultCredit);
            const cashNeeded = Math.max(selectedTotal() - parseFloat(defaultCredit), 0).toFixed(2);
            $('#fc_payment_amount').val(cashNeeded);
            $('#fc_amount_tendered').val(cashNeeded);
        } else {
            $('#fc_credit_applied').val('0');
            const total = selectedTotal();
            $('#fc_payment_amount').val(total > 0 ? total.toFixed(2) : '');
            $('#fc_amount_tendered').val(total > 0 ? total.toFixed(2) : '');
        }
        updateDisplay();
    });

    function resetSubmitButton() {
        const method = $('#fc_payment_method').val();
        if (method === 'Paystack') {
            $('#fc_submit_btn').prop('disabled', false).html('<i class="ri-secure-payment-line"></i> Pay with Paystack');
        } else if (method === 'Bank') {
            $('#fc_submit_btn').prop('disabled', false).html('<i class="ri-time-line"></i> Submit for approval');
        } else {
            $('#fc_submit_btn').prop('disabled', false).html('<i class="ri-printer-line"></i> Complete payment &amp; print receipt');
        }
    }

    function openPaymentDocuments(res) {
        if (res.receipt_url) {
            window.open(res.receipt_url, '_blank');
        }
        if (res.statement_url && (parseFloat(res.credit_generated || 0) > 0 || parseFloat(res.credit_balance || 0) > 0)) {
            window.open(res.statement_url, '_blank');
        }
    }

    function showSmsFeedback(res) {
        if (!res || !res.sms) {
            return;
        }

        if (res.sms.sent) {
            showAppToast('success', res.sms.message || 'SMS sent to parent.');
            return;
        }

        showAppToast('error', res.sms.message || 'SMS could not be sent to parent.');
    }

    function submitManualPayment(payload, isBank) {
        const $btn = $('#fc_submit_btn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        const ajax = {
            url: paymentUrl,
            method: 'POST',
            success: function(res) {
                showAppToast('success', res.message || 'Payment recorded.');
                if (res.pending) {
                    setTimeout(function(){ window.location.href = res.redirect_url || studentBillsUrl; }, 900);
                    return;
                }
                showSmsFeedback(res);
                openPaymentDocuments(res);
                setTimeout(function(){ window.location.href = studentBillsUrl; }, 900);
            },
            error: function(xhr) {
                showAppToast('error', xhr.responseJSON?.message || 'Unable to record payment.');
                resetSubmitButton();
            }
        };

        if (isBank) {
            ajax.data = payload;
            ajax.processData = false;
            ajax.contentType = false;
        } else {
            ajax.data = $.param(payload);
        }

        $.ajax(ajax);
    }

    function verifyPaystackPayment(reference) {
        const $btn = $('#fc_submit_btn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verifying payment...');

        $.ajax({
            url: paystackVerifyUrl,
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                reference: reference
            },
            success: function(res) {
                showAppToast('success', res.message || 'Paystack payment verified.');
                showSmsFeedback(res);
                openPaymentDocuments(res);
                setTimeout(function(){ window.location.href = studentBillsUrl; }, 900);
            },
            error: function(xhr) {
                showAppToast('error', xhr.responseJSON?.message || 'Unable to verify Paystack payment.');
                resetSubmitButton();
            }
        });
    }

    function startPaystackPayment(payAmount, creditApplied, allocations) {
        const $btn = $('#fc_submit_btn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Initializing Paystack...');

        const payload = [
            { name: '_token', value: $('input[name="_token"]').val() },
            { name: 'student_id', value: $('input[name="student_id"]').val() },
            { name: 'amount', value: payAmount.toFixed(2) },
            { name: 'credit_applied', value: creditApplied.toFixed(2) }
        ];

        allocations.forEach(function(a, i) {
            payload.push({ name: 'allocations[' + i + '][student_bill_id]', value: a.student_bill_id });
            payload.push({ name: 'allocations[' + i + '][amount]', value: a.amount });
        });

        $.ajax({
            url: paystackInitializeUrl,
            method: 'POST',
            data: $.param(payload),
            success: function(res) {
                if (res.paid_with_credit_only) {
                    showAppToast('success', res.message || 'Payment completed using credit.');
                    showSmsFeedback(res);
                    openPaymentDocuments(res);
                    setTimeout(function(){ window.location.href = studentBillsUrl; }, 900);
                    return;
                }

                if (typeof PaystackPop === 'undefined') {
                    showAppToast('error', 'Paystack checkout could not be loaded.');
                    resetSubmitButton();
                    return;
                }

                const amount = Number(res.amount);

                if (!amount || amount < 1) {
                    showAppToast('error', 'Paystack did not receive a payment amount. Try again.');
                    resetSubmitButton();
                    return;
                }

                const handler = PaystackPop.setup({
                    key: res.public_key || paystackPublicKey,
                    email: res.email,
                    label: res.label || undefined,
                    amount: amount,
                    currency: res.currency || 'GHS',
                    ref: res.reference,
                    callback: function(response) {
                        verifyPaystackPayment(response.reference);
                    },
                    onClose: function() {
                        showAppToast('error', 'Paystack payment was cancelled.');
                        resetSubmitButton();
                    }
                });

                $btn.html('<i class="ri-secure-payment-line"></i> Opening Paystack...');
                handler.openIframe();
            },
            error: function(xhr) {
                showAppToast('error', xhr.responseJSON?.message || 'Unable to initialize Paystack payment.');
                resetSubmitButton();
            }
        });
    }

    $('#recordPaymentForm').on('submit', function(e) {
        e.preventDefault();

        if (!hasOutstandingBills) {
            showAppToast('error', 'This student has no outstanding bills to pay.');
            return;
        }

        const enteredPayAmount = parseFloat($('#fc_payment_amount').val() || 0);
        const tendered = parseFloat($('#fc_amount_tendered').val() || 0);
        const total = selectedTotal();
        const method = $('#fc_payment_method').val();
        const payAmount = resolveEffectivePayAmount(enteredPayAmount, tendered, total, method);
        const creditApplied = creditAppliedAmount();
        const totalApplied = payAmount + creditApplied;
        const allocations = buildAllocations(totalApplied);

        if (!allocations.length) {
            showAppToast('error', 'Select at least one bill to pay.');
            return;
        }

        if (totalApplied <= 0) {
            showAppToast('error', 'Enter a payment amount or apply credit.');
            return;
        }

        if (creditApplied > creditBalance + 0.009) {
            showAppToast('error', 'Credit applied exceeds available credit balance.');
            return;
        }

        if (method === 'Paystack') {
            if (!paystackConfigured) {
                showAppToast('error', 'Paystack is not configured.');
                return;
            }

            startPaystackPayment(payAmount, creditApplied, allocations);
            return;
        }

        if (method === 'Bank') {
            if (!$('#fc_bank_instrument').val()) {
                showAppToast('error', 'Select Cheque or Bankers Draft.');
                return;
            }
            if (!$.trim($('#fc_bank_reference').val() || '')) {
                showAppToast('error', 'Enter the receipt or cheque number.');
                return;
            }
            if (!$('#fc_receipt_file')[0].files.length) {
                showAppToast('error', 'Upload a copy of the receipt.');
                return;
            }

            const formData = new FormData(this);
            formData.set('amount', payAmount.toFixed(2));
            formData.set('credit_applied', creditApplied.toFixed(2));
            formData.set('payment_method', 'Bank');
            allocations.forEach(function(a, i) {
                formData.append('allocations[' + i + '][student_bill_id]', a.student_bill_id);
                formData.append('allocations[' + i + '][amount]', a.amount);
            });
            submitManualPayment(formData, true);
            return;
        }

        const payload = $(this).serializeArray().filter(function(item) {
            return item.name !== 'amount';
        });
        payload.push({ name: 'amount', value: payAmount.toFixed(2) });
        payload.push({ name: 'credit_applied', value: creditApplied.toFixed(2) });
        allocations.forEach(function(a, i) {
            payload.push({ name: 'allocations[' + i + '][student_bill_id]', value: a.student_bill_id });
            payload.push({ name: 'allocations[' + i + '][amount]', value: a.amount });
        });

        submitManualPayment(payload, false);
    });

    setInterval(function() {
        const now = new Date();
        $('#fc_clock').text(now.toLocaleString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }));
    }, 30000);

    ensureCompulsorySelected();
    updateDisplay();
</script>
@endsection
