@extends('layouts.parent')

@section('title', 'Communications — '.($student ? $student->full_name : 'Parent Portal'))
@section('page-title', 'Communications')
@section('page-subtitle', $student ? 'Messages for '.$student->full_name : 'All messages for your account')

@section('css')
<style>
    .com {
        --c-teal: #25A194;
        --c-teal-d: #0f766e;
        --c-ink: #0f172a;
        --c-muted: #64748b;
        --c-border: #e2e8f0;
        --c-green: #10b981;
        --c-amber: #f59e0b;
        --c-blue: #3b82f6;
    }

    .com-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .com-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .com-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .com-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }
    .com-hero-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .com-hero-title {
        font-size: clamp(1.6rem, 4vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        margin-bottom: 6px;
    }
    .com-hero-sub {
        font-size: 14px;
        opacity: .9;
        font-weight: 600;
        max-width: 420px;
    }
    .com-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .com-hero-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .com-hero-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .com-student-chip {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.18);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        padding: 10px 16px 10px 10px;
        border: 1px solid rgba(255,255,255,.2);
    }
    .com-student-chip img,
    .com-student-chip .av {
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
    .com-student-chip b { display: block; font-size: 14px; }
    .com-student-chip small { opacity: .8; font-size: 12px; }

    .com-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .com-grid { grid-template-columns: 1fr; }
    }

    .com-card {
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .com-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--c-border);
        background: #fafafa;
    }
    .com-card-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--c-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .com-card-head h3 i { color: var(--c-teal); }
    .com-card-head .count {
        font-size: 12px;
        font-weight: 700;
        color: var(--c-muted);
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 999px;
    }
    .com-card-body { padding: 20px; }

    .com-compose label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--c-muted);
        margin-bottom: 8px;
    }
    .com-compose select,
    .com-compose textarea {
        width: 100%;
        border: 1.5px solid var(--c-border);
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 14px;
        color: var(--c-ink);
        background: #fff;
        transition: border-color .12s, box-shadow .12s;
    }
    .com-compose select { margin-bottom: 14px; font-weight: 600; }
    .com-compose textarea {
        min-height: 120px;
        resize: vertical;
        line-height: 1.5;
        margin-bottom: 14px;
    }
    .com-compose select:focus,
    .com-compose textarea:focus {
        outline: none;
        border-color: var(--c-teal);
        box-shadow: 0 0 0 3px rgba(37,161,148,.12);
    }
    .com-compose textarea.is-invalid { border-color: #ef4444; }
    .com-send-btn {
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
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(37,161,148,.28);
        transition: transform .12s, box-shadow .12s;
    }
    .com-send-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(37,161,148,.35);
    }
    .com-hint {
        margin-top: 14px;
        font-size: 12px;
        color: var(--c-muted);
        line-height: 1.5;
    }

    .com-feed {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-height: 640px;
        overflow-y: auto;
        padding: 4px 2px;
    }
    .com-item {
        display: flex;
        gap: 14px;
        padding: 16px;
        border: 1px solid var(--c-border);
        border-radius: 16px;
        background: #fff;
        transition: box-shadow .12s;
    }
    .com-item:hover { box-shadow: 0 8px 24px rgba(15,23,42,.06); }
    .com-item-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .com-item-icon.sms { background: #ecfdf5; color: var(--c-teal-d); }
    .com-item-icon.portal { background: #eff6ff; color: var(--c-blue); }
    .com-item-icon.default { background: #f8fafc; color: var(--c-muted); }
    .com-item-body { flex: 1; min-width: 0; }
    .com-item-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .com-channel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 4px 10px;
        border-radius: 999px;
    }
    .com-channel.sms { background: #e6f7f5; color: var(--c-teal-d); }
    .com-channel.portal { background: #dbeafe; color: #1e40af; }
    .com-channel.default { background: #f1f5f9; color: var(--c-muted); }
    .com-date {
        font-size: 12px;
        font-weight: 600;
        color: var(--c-muted);
        white-space: nowrap;
    }
    .com-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--c-teal-d);
        margin-bottom: 6px;
    }
    .com-text {
        font-size: 14px;
        color: var(--c-ink);
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .com-empty {
        padding: 48px 24px;
        text-align: center;
    }
    .com-empty i {
        font-size: 48px;
        color: #cbd5e1;
        display: block;
        margin-bottom: 12px;
    }
    .com-empty h3 {
        font-size: 18px;
        font-weight: 800;
        color: var(--c-ink);
        margin: 0 0 6px;
    }
    .com-empty p {
        color: var(--c-muted);
        margin: 0;
        font-size: 14px;
    }

    .com-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 14px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 600;
    }
    .com-alert.success {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        color: #047857;
    }
    .com-alert.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }
</style>
@endsection

@section('content')
@php
    $initials = $student
        ? strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1))
        : strtoupper(substr($parent->guardian_name ?? 'P', 0, 1));
    $lastMessage = $timeline->first();
    $smsCount = $timeline->where('channel', 'sms')->count();
    $otherCount = $timeline->count() - $smsCount;
@endphp

<div class="com">
    <div class="com-hero">
        <div class="com-hero-inner">
            <div>
                <div class="com-hero-label">School communications</div>
                <div class="com-hero-title">
                    @if($student)
                        {{ $student->firstname }}'s messages
                    @else
                        Your inbox
                    @endif
                </div>
                <div class="com-hero-sub">
                    @if($student)
                        SMS alerts and school notices linked to {{ $student->firstname }}.
                    @else
                        All SMS alerts and school notices for your linked children.
                    @endif
                </div>
                <div class="com-hero-meta">
                    <div>Total messages<strong>{{ $timeline->count() }}</strong></div>
                    @if($smsCount > 0)
                        <div>SMS alerts<strong>{{ $smsCount }}</strong></div>
                    @endif
                    @if($lastMessage)
                        <div>Latest<strong>{{ optional($lastMessage['sent_at'])->format('d M Y') }}</strong></div>
                    @endif
                </div>
            </div>
            @if($student)
                <div class="com-student-chip">
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
            @else
                <div class="com-student-chip">
                    <div class="av">{{ $initials }}</div>
                    <div>
                        <b>{{ $parent->guardian_name ?? 'Parent' }}</b>
                        <small>{{ $children->count() }} {{ Str::plural('child', $children->count()) }} linked</small>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="com-alert error">
            <i class="ri-error-warning-fill"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="com-grid">
        <div class="com-card">
            <div class="com-card-head">
                <h3><i class="ri-edit-line"></i> Message the school</h3>
            </div>
            <div class="com-card-body com-compose">
                <form method="POST" action="{{ route('parent.messages.store') }}">
                    @csrf
                    @if($student)
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <label>Regarding</label>
                        <div class="mb-3 p-3 rounded-3" style="background:#f0fdfa;border:1px solid #ccfbf1;font-size:14px;font-weight:600;color:#0f766e;">
                            <i class="ri-user-smile-line"></i> {{ $student->full_name }}
                        </div>
                    @else
                        <label for="student_id">Regarding</label>
                        <select name="student_id" id="student_id">
                            <option value="">General message</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}" @selected(old('student_id') == $child->id)>About {{ $child->full_name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <label for="message">Your message</label>
                    <textarea name="message"
                              id="message"
                              required
                              minlength="5"
                              maxlength="1000"
                              placeholder="Write your question or request to the school..."
                              class="@error('message') is-invalid @enderror">{{ old('message') }}</textarea>

                    <button type="submit" class="com-send-btn">
                        <i class="ri-send-plane-fill"></i> Send message
                    </button>
                </form>
                <div class="com-hint">
                    <i class="ri-information-line"></i>
                    The school office will receive your message and respond when available. For urgent fee matters, you can also pay online from the Fees & Bills page.
                </div>
            </div>
        </div>

        <div class="com-card">
            <div class="com-card-head">
                <h3><i class="ri-inbox-line"></i> Received messages</h3>
                <span class="count">{{ $timeline->count() }}</span>
            </div>
            <div class="com-card-body">
                @if($timeline->isEmpty())
                    <div class="com-empty">
                        <i class="ri-mail-open-line"></i>
                        <h3>No messages yet</h3>
                        <p>SMS alerts and school notices will appear here when the school sends them.</p>
                    </div>
                @else
                    <div class="com-feed">
                        @foreach($timeline as $item)
                            @php
                                $channel = strtolower($item['channel'] ?? 'notice');
                                $isSms = $channel === 'sms' || ($item['type'] ?? '') === 'sms';
                                $iconClass = $isSms ? 'sms' : ($channel === 'portal' ? 'portal' : 'default');
                                $channelClass = $isSms ? 'sms' : ($channel === 'portal' ? 'portal' : 'default');
                                $icon = $isSms ? 'ri-message-2-line' : 'ri-notification-3-line';
                                $channelLabel = $isSms ? 'SMS' : ucwords(str_replace('_', ' ', $channel));
                            @endphp
                            <article class="com-item">
                                <div class="com-item-icon {{ $iconClass }}">
                                    <i class="{{ $icon }}"></i>
                                </div>
                                <div class="com-item-body">
                                    <div class="com-item-top">
                                        <span class="com-channel {{ $channelClass }}">{{ $channelLabel }}</span>
                                        <span class="com-date">{{ optional($item['sent_at'])->format('d M Y, g:i A') }}</span>
                                    </div>
                                    @if(!empty($item['label']))
                                        <div class="com-label">{{ $item['label'] }}</div>
                                    @endif
                                    <div class="com-text">{{ $item['message'] }}</div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
