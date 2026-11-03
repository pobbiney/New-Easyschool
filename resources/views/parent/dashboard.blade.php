@extends('layouts.parent')

@section('title', 'Dashboard — Parent Portal')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Hello, '.($parent->guardian_name ?: 'Parent'))

@section('css')
<style>
    .pd {
        --d-teal: #25A194;
        --d-teal-d: #0f766e;
        --d-ink: #0f172a;
        --d-muted: #64748b;
        --d-border: #e2e8f0;
        --d-green: #10b981;
        --d-red: #ef4444;
        --d-amber: #f59e0b;
    }

    .pd-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .pd-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .pd-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .pd-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }
    .pd-welcome-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .pd-welcome-title {
        font-size: clamp(1.6rem, 4vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        margin-bottom: 6px;
    }
    .pd-welcome-sub {
        font-size: 14px;
        opacity: .9;
        font-weight: 600;
        max-width: 480px;
    }
    .pd-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .pd-hero-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .pd-hero-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .pd-school-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        padding: 10px 16px 10px 10px;
        border: 1px solid rgba(255,255,255,.2);
        max-width: 260px;
    }
    .pd-school-chip img {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: contain;
        background: rgba(255,255,255,.95);
        padding: 4px;
    }
    .pd-school-chip b { display: block; font-size: 13px; line-height: 1.3; }
    .pd-school-chip small { opacity: .8; font-size: 11px; }

    .pd-section-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--d-muted);
        margin: 0 0 14px;
    }

    .pd-children {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .pd-child-card {
        background: #fff;
        border: 1px solid var(--d-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
        transition: transform .12s, box-shadow .12s;
    }
    .pd-child-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15,23,42,.08);
    }
    .pd-child-top {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .pd-child-avatar {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        object-fit: cover;
        background: #e6f7f5;
        flex-shrink: 0;
        border: 2px solid #fff;
        box-shadow: 0 4px 12px rgba(15,23,42,.08);
    }
    .pd-child-name {
        font-size: 16px;
        font-weight: 800;
        color: var(--d-ink);
        line-height: 1.25;
        margin-bottom: 4px;
    }
    .pd-child-meta {
        font-size: 12px;
        color: var(--d-muted);
        font-weight: 600;
    }
    .pd-child-fees {
        margin: 0 20px 16px;
        padding: 14px 16px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .pd-child-fees.due {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }
    .pd-child-fees.ok {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
    }
    .pd-child-fees label {
        font-size: 12px;
        font-weight: 700;
        color: var(--d-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .pd-child-fees strong {
        display: block;
        font-size: 18px;
        font-weight: 800;
        margin-top: 2px;
    }
    .pd-child-fees.due strong { color: var(--d-red); }
    .pd-child-fees.ok strong { color: var(--d-green); }
    .pd-child-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 0 20px 20px;
    }
    .pd-child-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        transition: all .12s;
    }
    .pd-child-btn.primary {
        background: linear-gradient(135deg, var(--d-teal-d), var(--d-teal));
        color: #fff;
        box-shadow: 0 4px 14px rgba(37,161,148,.25);
    }
    .pd-child-btn.primary:hover { color: #fff; transform: translateY(-1px); }
    .pd-child-btn.secondary {
        background: #f8fafc;
        border: 1px solid var(--d-border);
        color: var(--d-ink);
    }
    .pd-child-btn.secondary:hover {
        background: #f0fdfa;
        border-color: #99f6e4;
        color: var(--d-teal-d);
    }
    .pd-child-btn.full { grid-column: 1 / -1; }

    .pd-compose {
        background: #fff;
        border: 1px solid var(--d-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .pd-compose-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--d-border);
        background: #fafafa;
    }
    .pd-compose-head h3 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 800;
        color: var(--d-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pd-compose-head h3 i { color: var(--d-teal); }
    .pd-compose-head p {
        margin: 0;
        font-size: 13px;
        color: var(--d-muted);
    }
    .pd-compose-body { padding: 20px; }
    .pd-compose label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--d-muted);
        margin-bottom: 8px;
    }
    .pd-compose select,
    .pd-compose textarea {
        width: 100%;
        border: 1.5px solid var(--d-border);
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 14px;
        color: var(--d-ink);
        background: #fff;
        transition: border-color .12s, box-shadow .12s;
    }
    .pd-compose select { margin-bottom: 14px; font-weight: 600; }
    .pd-compose textarea {
        min-height: 100px;
        resize: vertical;
        line-height: 1.5;
        margin-bottom: 14px;
    }
    .pd-compose select:focus,
    .pd-compose textarea:focus {
        outline: none;
        border-color: var(--d-teal);
        box-shadow: 0 0 0 3px rgba(37,161,148,.12);
    }
    .pd-compose-row {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 14px;
    }
    @media (max-width: 768px) {
        .pd-compose-row { grid-template-columns: 1fr; }
    }
    .pd-send-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #d97706, var(--d-amber));
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(245,158,11,.28);
        transition: transform .12s;
    }
    .pd-send-btn:hover { transform: translateY(-1px); }

    .pd-empty {
        padding: 56px 24px;
        text-align: center;
        background: #fff;
        border: 1px solid var(--d-border);
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .pd-empty i {
        font-size: 52px;
        color: #cbd5e1;
        display: block;
        margin-bottom: 14px;
    }
    .pd-empty h3 {
        font-size: 20px;
        font-weight: 800;
        color: var(--d-ink);
        margin: 0 0 8px;
    }
    .pd-empty p {
        color: var(--d-muted);
        margin: 0;
        font-size: 14px;
        max-width: 420px;
        margin-inline: auto;
        line-height: 1.6;
    }

    .pd-quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
    }
    .pd-quick-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid var(--d-border);
        background: #fff;
        color: var(--d-teal-d);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: all .12s;
    }
    .pd-quick-link:hover {
        border-color: var(--d-teal);
        background: #f0fdfa;
        color: var(--d-teal-d);
    }
</style>
@endsection

@section('content')
@php
    $totalOutstanding = $summaries->sum('outstanding');
    $childrenWithFees = $summaries->where('outstanding', '>', 0)->count();
    $parentInitial = strtoupper(substr($parent->guardian_name ?? 'P', 0, 1));
    $schoolName = $school->name ?? $school->school_name ?? 'School';
@endphp

<div class="pd">
    <div class="pd-hero">
        <div class="pd-hero-inner">
            <div>
                <div class="pd-welcome-label">Parent portal</div>
                <div class="pd-welcome-title">Welcome, {{ $parent->guardian_name ?: 'Parent' }}</div>
                <div class="pd-welcome-sub">
                    @if($summaries->isEmpty())
                        Manage your children's school records from one place.
                    @else
                        Track fees, academics, and messages for {{ $summaries->count() }} linked {{ Str::plural('child', $summaries->count()) }}.
                    @endif
                </div>
                @if($summaries->isNotEmpty())
                    <div class="pd-hero-meta">
                        <div>Children<strong>{{ $summaries->count() }}</strong></div>
                        <div>Total outstanding<strong>GHS {{ number_format($totalOutstanding, 2) }}</strong></div>
                        @if($childrenWithFees > 0)
                            <div>Need payment<strong>{{ $childrenWithFees }}</strong></div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="pd-school-chip">
                @if($school->logoUrl())
                    <img src="{{ $school->logoUrl() }}" alt="">
                @else
                    <div class="pd-child-avatar" style="width:44px;height:44px;font-size:16px;">{{ strtoupper(substr($schoolName, 0, 1)) }}</div>
                @endif
                <div>
                    <b>{{ $schoolName }}</b>
                    <small>Parent portal</small>
                </div>
            </div>
        </div>
    </div>

    @if($summaries->isEmpty())
        <div class="pd-empty">
            <i class="ri-user-search-line"></i>
            <h3>No children linked</h3>
            <p>No active students are linked to your phone number yet. Contact the school office if this is unexpected.</p>
        </div>
    @else
        @if($childrenWithFees > 0)
            <div class="pd-quick-links">
                @foreach($summaries->where('outstanding', '>', 0) as $item)
                    <a href="{{ route('parent.bills', $item['student']) }}" class="pd-quick-link">
                        <i class="ri-bank-card-line"></i>
                        Pay fees — {{ $item['student']->firstname }}
                    </a>
                @endforeach
            </div>
        @endif

        <p class="pd-section-title">Your children</p>
        <div class="pd-children">
            @foreach($summaries as $item)
                @php
                    $student = $item['student'];
                    $hasDue = $item['outstanding'] > 0;
                    $studentPhoto = $student->picture
                        ? asset($student->picture)
                        : asset('assets/images/student-placeholder.svg');
                @endphp
                <article class="pd-child-card">
                    <div class="pd-child-top">
                        <img src="{{ $studentPhoto }}"
                             alt="{{ $student->full_name }}"
                             class="pd-child-avatar"
                             onerror="this.onerror=null;this.src='{{ asset('assets/images/student-placeholder.svg') }}';">
                        <div>
                            <div class="pd-child-name">{{ $student->full_name }}</div>
                            <div class="pd-child-meta">
                                {{ $item['class_name'] ?? '—' }} · {{ $student->student_id }}
                            </div>
                        </div>
                    </div>
                    <div class="pd-child-fees {{ $hasDue ? 'due' : 'ok' }}">
                        <div>
                            <label>Outstanding fees</label>
                            <strong>GHS {{ number_format($item['outstanding'], 2) }}</strong>
                        </div>
                        @if($hasDue)
                            <i class="ri-error-warning-line" style="color:var(--d-red);font-size:22px;"></i>
                        @else
                            <i class="ri-checkbox-circle-fill" style="color:var(--d-green);font-size:22px;"></i>
                        @endif
                    </div>
                    <div class="pd-child-actions">
                        <a href="{{ route('parent.child', $student) }}" class="pd-child-btn primary">
                            <i class="ri-dashboard-line"></i> Overview
                        </a>
                        @if($hasDue)
                            <a href="{{ route('parent.bills', $student) }}" class="pd-child-btn secondary">
                                <i class="ri-bank-card-line"></i> Pay fees
                            </a>
                        @else
                            <a href="{{ route('parent.academics', $student) }}" class="pd-child-btn secondary">
                                <i class="ri-book-read-line"></i> Academics
                            </a>
                        @endif
                        <a href="{{ route('parent.communications.child', $student) }}" class="pd-child-btn secondary full">
                            <i class="ri-message-3-line"></i> Messages
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pd-compose">
            <div class="pd-compose-head">
                <h3><i class="ri-mail-send-line"></i> Message the school</h3>
                <p>Send a note directly to the school office.</p>
            </div>
            <div class="pd-compose-body">
                <form method="POST" action="{{ route('parent.messages.store') }}">
                    @csrf
                    <div class="pd-compose-row">
                        <div>
                            <label for="student_id">Regarding</label>
                            <select name="student_id" id="student_id">
                                <option value="">General message</option>
                                @foreach($summaries as $item)
                                    <option value="{{ $item['student']->id }}" @selected(old('student_id') == $item['student']->id)>
                                        About {{ $item['student']->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="message">Your message</label>
                            <textarea name="message"
                                      id="message"
                                      required
                                      minlength="5"
                                      maxlength="1000"
                                      placeholder="Write your question or request...">{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="pd-send-btn">
                        <i class="ri-send-plane-fill"></i> Send message
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
