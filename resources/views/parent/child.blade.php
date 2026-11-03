@extends('layouts.parent')

@section('title', $student->full_name.' — Parent Portal')
@section('page-title', $student->full_name)
@section('page-subtitle', ($student->schoolClass?->name ?? $student->class_name).' · ID '.$student->student_id)

@section('css')
<style>
    .ch {
        --c-teal: #25A194;
        --c-teal-d: #0f766e;
        --c-ink: #0f172a;
        --c-muted: #64748b;
        --c-border: #e2e8f0;
        --c-green: #10b981;
        --c-red: #ef4444;
        --c-amber: #f59e0b;
    }

    .ch-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .ch-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .ch-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .ch-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }
    .ch-profile {
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .ch-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        object-fit: cover;
        background: rgba(255,255,255,.25);
        border: 3px solid rgba(255,255,255,.35);
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(0,0,0,.12);
    }
    .ch-name {
        font-size: clamp(1.5rem, 4vw, 2rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        margin-bottom: 6px;
    }
    .ch-meta-line {
        font-size: 14px;
        opacity: .9;
        font-weight: 600;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
    }
    .ch-meta-line span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ch-hero-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }
    .ch-hero-stat {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 14px;
        padding: 12px 18px;
        min-width: 120px;
        border: 1px solid rgba(255,255,255,.12);
    }
    .ch-hero-stat label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        opacity: .85;
        margin-bottom: 4px;
    }
    .ch-hero-stat strong {
        font-size: 20px;
        font-weight: 800;
        line-height: 1;
    }
    .ch-hero-stat.due strong { color: #fecaca; }
    .ch-hero-stat.ok strong { color: #bbf7d0; }

    .ch-pay-banner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        margin-bottom: 20px;
        border-radius: 18px;
        background: linear-gradient(135deg, #fef2f2, #fff7ed);
        border: 1px solid #fecaca;
    }
    .ch-pay-banner-text h3 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 800;
        color: var(--c-ink);
    }
    .ch-pay-banner-text p {
        margin: 0;
        font-size: 13px;
        color: var(--c-muted);
    }
    .ch-pay-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--c-teal-d), var(--c-teal));
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(37,161,148,.28);
        transition: transform .12s;
    }
    .ch-pay-btn:hover {
        transform: translateY(-1px);
        color: #fff;
    }

    .ch-section-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--c-muted);
        margin: 0 0 14px;
    }

    .ch-actions {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    @media (max-width: 1100px) {
        .ch-actions { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 640px) {
        .ch-actions { grid-template-columns: repeat(2, 1fr); }
    }
    .ch-action {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        min-height: 130px;
        padding: 18px 16px;
        border-radius: 18px;
        border: none;
        text-decoration: none;
        color: #fff;
        box-shadow: 0 8px 24px rgba(15,23,42,.12);
        transition: transform .15s, box-shadow .15s;
        position: relative;
        overflow: hidden;
    }
    .ch-action::before {
        content: '';
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        top: -24px;
        right: -24px;
    }
    .ch-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 36px rgba(15,23,42,.18);
        color: #fff;
    }
    .ch-action--blue { background: linear-gradient(145deg, #1d4ed8, #3b82f6); }
    .ch-action--teal { background: linear-gradient(145deg, #0f766e, #25A194); }
    .ch-action--green { background: linear-gradient(145deg, #047857, #10b981); }
    .ch-action--purple { background: linear-gradient(145deg, #6d28d9, #a855f7); }
    .ch-action--rose { background: linear-gradient(145deg, #be123c, #f43f5e); }
    .ch-action--amber { background: linear-gradient(145deg, #b45309, #f59e0b); }
    .ch-action-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        background: rgba(255,255,255,.22);
        color: #fff;
        position: relative;
        z-index: 1;
    }
    .ch-action-body { position: relative; z-index: 1; }
    .ch-action-title {
        font-size: 14px;
        font-weight: 800;
        line-height: 1.3;
        color: #fff;
    }
    .ch-action-sub {
        font-size: 11px;
        color: rgba(255,255,255,.82);
        line-height: 1.4;
        margin-top: 4px;
        font-weight: 600;
    }

    .ch-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
    }
    .ch-info-card {
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .ch-info-card h3 {
        margin: 0 0 14px;
        font-size: 14px;
        font-weight: 800;
        color: var(--c-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ch-info-card h3 i { color: var(--c-teal); }
    .ch-info-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .ch-info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .ch-info-row span:first-child { color: var(--c-muted); font-weight: 600; }
    .ch-info-row span:last-child { font-weight: 700; color: var(--c-ink); text-align: right; }

    @media (max-width: 640px) {
        .ch-hero-inner { flex-direction: column; align-items: flex-start; }
        .ch-hero-stats { width: 100%; }
        .ch-hero-stat { flex: 1; min-width: calc(50% - 7px); }
        .ch-pay-banner { flex-direction: column; align-items: stretch; }
        .ch-pay-btn { justify-content: center; }
    }
</style>
@endsection

@section('content')
@php
    $studentPhoto = $student->picture
        ? asset($student->picture)
        : asset('assets/images/student-placeholder.svg');
    $credit = (float) $student->credit_balance;
    $netPayable = max(0, $outstanding - $credit);
    $className = $student->schoolClass?->name ?? $student->class_name ?? '—';
    $categoryName = $student->schoolClass?->category?->name ?? $student->category ?? '—';
@endphp

<div class="ch">
    <div class="ch-hero">
        <div class="ch-hero-inner">
            <div class="ch-profile">
                <img src="{{ $studentPhoto }}"
                     alt="{{ $student->full_name }}"
                     class="ch-avatar"
                     onerror="this.onerror=null;this.src='{{ asset('assets/images/student-placeholder.svg') }}';">
                <div>
                    <div class="ch-name">{{ $student->full_name }}</div>
                    <div class="ch-meta-line">
                        <span><i class="ri-building-line"></i> {{ $className }}</span>
                        <span><i class="ri-hashtag"></i> {{ $student->student_id }}</span>
                        @if($student->roll_number)
                            <span><i class="ri-list-ordered"></i> Roll {{ $student->roll_number }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="ch-hero-stats">
                <div class="ch-hero-stat {{ $outstanding > 0 ? 'due' : 'ok' }}">
                    <label>Outstanding</label>
                    <strong>GHS {{ number_format($outstanding, 2) }}</strong>
                </div>
                @if($credit > 0)
                    <div class="ch-hero-stat">
                        <label>Credit</label>
                        <strong>GHS {{ number_format($credit, 2) }}</strong>
                    </div>
                @endif
                <div class="ch-hero-stat">
                    <label>Status</label>
                    <strong>{{ $student->status ?? 'Active' }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if($outstanding > 0)
        <div class="ch-pay-banner">
            <div class="ch-pay-banner-text">
                <h3>Fees outstanding</h3>
                <p>
                    @if($credit > 0 && $netPayable < $outstanding)
                        GHS {{ number_format($netPayable, 2) }} to pay after credit (GHS {{ number_format($credit, 2) }} available).
                    @else
                        {{ $student->firstname }} has GHS {{ number_format($outstanding, 2) }} in unpaid bills.
                    @endif
                </p>
            </div>
            <a href="{{ route('parent.bills', $student) }}" class="ch-pay-btn">
                <i class="ri-bank-card-line"></i> Pay fees now
            </a>
        </div>
    @endif

    <p class="ch-section-title">Quick access</p>
    <div class="ch-actions">
        <a href="{{ route('parent.academics', $student) }}" class="ch-action ch-action--blue">
            <div class="ch-action-icon"><i class="ri-book-read-line"></i></div>
            <div class="ch-action-body">
                <div class="ch-action-title">Academics</div>
                <div class="ch-action-sub">Grades & attendance</div>
            </div>
        </a>
        <a href="{{ route('parent.bills', $student) }}" class="ch-action ch-action--teal">
            <div class="ch-action-icon"><i class="ri-bill-line"></i></div>
            <div class="ch-action-body">
                <div class="ch-action-title">Fees & bills</div>
                <div class="ch-action-sub">View & pay online</div>
            </div>
        </a>
        <a href="{{ route('parent.payments', $student) }}" class="ch-action ch-action--green">
            <div class="ch-action-icon"><i class="ri-receipt-line"></i></div>
            <div class="ch-action-body">
                <div class="ch-action-title">Payments</div>
                <div class="ch-action-sub">Receipt history</div>
            </div>
        </a>
        <a href="{{ route('parent.report-card', $student) }}" class="ch-action ch-action--purple">
            <div class="ch-action-icon"><i class="ri-file-chart-line"></i></div>
            <div class="ch-action-body">
                <div class="ch-action-title">Report card</div>
                <div class="ch-action-sub">Print terminal report</div>
            </div>
        </a>
        <a href="{{ route('parent.communications.child', $student) }}" class="ch-action ch-action--rose">
            <div class="ch-action-icon"><i class="ri-message-3-line"></i></div>
            <div class="ch-action-body">
                <div class="ch-action-title">Messages</div>
                <div class="ch-action-sub">Contact the school</div>
            </div>
        </a>
        <a href="{{ route('parent.dashboard') }}" class="ch-action ch-action--amber">
            <div class="ch-action-icon"><i class="ri-arrow-left-line"></i></div>
            <div class="ch-action-body">
                <div class="ch-action-title">All children</div>
                <div class="ch-action-sub">Back to dashboard</div>
            </div>
        </a>
    </div>

    <p class="ch-section-title">Student details</p>
    <div class="ch-info-grid">
        <div class="ch-info-card">
            <h3><i class="ri-graduation-cap-line"></i> School info</h3>
            <div class="ch-info-row"><span>Class</span><span>{{ $className }}</span></div>
            <div class="ch-info-row"><span>Category</span><span>{{ $categoryName }}</span></div>
            @if($student->section)
                <div class="ch-info-row"><span>Section</span><span>{{ $student->section }}</span></div>
            @endif
            <div class="ch-info-row"><span>Student ID</span><span>{{ $student->student_id }}</span></div>
        </div>
        <div class="ch-info-card">
            <h3><i class="ri-user-line"></i> Personal</h3>
            <div class="ch-info-row"><span>Gender</span><span>{{ $student->gender ?? '—' }}</span></div>
            <div class="ch-info-row"><span>Date of birth</span><span>{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M Y') : '—' }}</span></div>
            <div class="ch-info-row"><span>Status</span><span>{{ $student->status ?? 'Active' }}</span></div>
        </div>
        <div class="ch-info-card">
            <h3><i class="ri-wallet-3-line"></i> Fees summary</h3>
            <div class="ch-info-row"><span>Outstanding</span><span>GHS {{ number_format($outstanding, 2) }}</span></div>
            <div class="ch-info-row"><span>Credit balance</span><span>GHS {{ number_format($credit, 2) }}</span></div>
            <div class="ch-info-row"><span>Net payable</span><span>GHS {{ number_format($netPayable, 2) }}</span></div>
        </div>
    </div>
</div>
@endsection
