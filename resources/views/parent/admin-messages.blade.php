@php
    $pageName = 'sms';
    $subpageName = 'parent-messages';
    $statusPills = [
        'new' => ['label' => 'New', 'class' => 'ac-pill-amber', 'icon' => 'ri-mail-unread-line'],
        'read' => ['label' => 'Read', 'class' => 'ac-pill-sky', 'icon' => 'ri-mail-open-line'],
        'replied' => ['label' => 'Replied', 'class' => 'ac-pill-emerald', 'icon' => 'ri-chat-check-line'],
    ];
    $filterTabs = [
        ['key' => '', 'label' => 'All', 'count' => $counts['all'], 'icon' => 'ri-inbox-2-line'],
        ['key' => 'new', 'label' => 'New', 'count' => $counts['new'], 'icon' => 'ri-mail-unread-line'],
        ['key' => 'read', 'label' => 'Read', 'count' => $counts['read'], 'icon' => 'ri-mail-open-line'],
        ['key' => 'replied', 'label' => 'Replied', 'count' => $counts['replied'], 'icon' => 'ri-chat-check-line'],
    ];
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .pm-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        border: 1px solid rgba(37, 161, 148, .18);
        background:
            radial-gradient(circle at top right, rgba(99, 102, 241, .12), transparent 42%),
            linear-gradient(135deg, rgba(37, 161, 148, .14) 0%, rgba(255, 255, 255, .96) 52%, rgba(245, 158, 11, .1) 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
    }
    .pm-hero::after {
        content: "";
        position: absolute;
        right: -24px;
        bottom: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(37, 161, 148, .08);
        pointer-events: none;
    }
    .pm-hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #25A194, #17897e);
        color: #fff;
        font-size: 26px;
        flex-shrink: 0;
        box-shadow: 0 12px 24px rgba(37, 161, 148, .28);
    }
    .pm-hero-title { font-size: 1.35rem; font-weight: 800; letter-spacing: -.02em; margin: 0 0 6px; color: #0f172a; }
    .pm-hero-sub { margin: 0; color: #64748b; font-weight: 600; max-width: 520px; }
    .pm-stat {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px 20px;
        background: #fff;
        height: 100%;
        text-decoration: none;
        display: block;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        position: relative;
        overflow: hidden;
    }
    .pm-stat:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(15, 23, 42, .08); border-color: rgba(37, 161, 148, .22); }
    .pm-stat.is-active { border-color: #25A194; box-shadow: 0 0 0 3px rgba(37, 161, 148, .12); }
    .pm-stat .stat-icon {
        width: 44px; height: 44px; border-radius: 14px;
        display: inline-flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;
    }
    .pm-stat-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -.03em; line-height: 1; }
    .pm-stat-label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-top: 4px; }
    .pm-board {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
    }
    .pm-board-head {
        padding: 18px 22px;
        border-bottom: 1px solid #eef2f6;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .pm-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
    .pm-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 999px;
        border: 1px solid #e5e7eb; background: #fff;
        color: #475569; font-size: 13px; font-weight: 700; text-decoration: none;
    }
    .pm-tab:hover { border-color: #25A194; color: #0f766e; }
    .pm-tab.is-active { background: #0f766e; border-color: #0f766e; color: #fff; }
    .pm-tab .count {
        min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 11px; background: rgba(15, 23, 42, .08);
    }
    .pm-tab.is-active .count { background: rgba(255,255,255,.2); color: #fff; }
    .pm-search { position: relative; min-width: 240px; }
    .pm-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .pm-search input {
        width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 13px;
    }
    .pm-search input:focus { outline: none; border-color: #25A194; box-shadow: 0 0 0 3px rgba(37,161,148,.12); }
    .pm-item {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr) auto;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid #f1f5f9;
        align-items: start;
        transition: background .12s ease;
    }
    .pm-item:last-child { border-bottom: 0; }
    .pm-item:hover { background: #f8fffe; }
    .pm-item.is-new { background: linear-gradient(90deg, rgba(245, 158, 11, .08), transparent 48%); }
    .pm-avatar {
        width: 48px; height: 48px; border-radius: 14px;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; color: #fff; font-size: 15px;
        background: linear-gradient(135deg, #0f766e, #25A194);
        box-shadow: 0 8px 16px rgba(37, 161, 148, .2);
    }
    .pm-name { font-weight: 800; color: #0f172a; margin-bottom: 2px; }
    .pm-meta { font-size: 12px; color: #64748b; font-weight: 600; display: flex; flex-wrap: wrap; gap: 8px 12px; }
    .pm-meta span { display: inline-flex; align-items: center; gap: 4px; }
    .pm-preview { margin: 8px 0 0; color: #334155; font-size: 14px; line-height: 1.5; }
    .pm-empty { padding: 48px 24px; text-align: center; color: #64748b; }
    .pm-empty i { font-size: 42px; color: #25A194; display: block; margin-bottom: 10px; }
    .pm-modal .modal-content { border: 0; border-radius: 20px; overflow: hidden; }
    .pm-modal-head {
        background: linear-gradient(135deg, #0f766e, #25A194);
        color: #fff; padding: 20px 22px;
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    }
    .pm-modal-head h5 { margin: 0; font-weight: 800; }
    .pm-modal-head p { margin: 4px 0 0; opacity: .9; font-size: 13px; }
    .pm-modal-head .btn-close { filter: invert(1); opacity: .85; }
    .pm-bubble {
        border-radius: 16px; padding: 14px 16px; line-height: 1.55; font-size: 14px;
    }
    .pm-bubble-in { background: #f0fdfa; border: 1px solid #ccfbf1; color: #134e4a; }
    .pm-bubble-out { background: #eef2ff; border: 1px solid #c7d2fe; color: #312e81; }
    .pm-modal textarea {
        border-radius: 14px; border: 1.5px solid #e2e8f0; min-height: 96px;
    }
    .pm-modal textarea:focus { border-color: #25A194; box-shadow: 0 0 0 3px rgba(37,161,148,.12); }
    .pm-save {
        background: linear-gradient(135deg, #0f766e, #25A194);
        border: 0; color: #fff; font-weight: 800; border-radius: 12px; padding: 10px 18px;
        box-shadow: 0 10px 22px rgba(37,161,148,.28);
    }
    @media (max-width: 767px) {
        .pm-item { grid-template-columns: 44px minmax(0, 1fr); }
        .pm-item .pm-actions { grid-column: 2; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Communications',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Parent Messages', 'active' => true],
        ],
        'title' => 'Parent Messages',
        'subtitle' => 'Inbox for questions parents send from the parent portal.',
    ])

    <div class="pm-hero d-flex flex-wrap align-items-center gap-3">
        <div class="pm-hero-icon"><i class="ri-mail-unread-line"></i></div>
        <div class="flex-grow-1">
            <h2 class="pm-hero-title">Parent inbox</h2>
            <p class="pm-hero-sub">New messages stay on top. Open a message to mark it read or save an internal reply note.</p>
        </div>
        @if($counts['new'] > 0)
            <span class="ac-pill ac-pill-amber"><i class="ri-notification-3-line"></i> {{ $counts['new'] }} waiting</span>
        @endif
    </div>

    <div class="row g-3 mb-24">
        <div class="col-6 col-xl-3">
            <a href="{{ route('parent-messages') }}" class="pm-stat {{ $status === '' ? 'is-active' : '' }}">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-600 text-white"><i class="ri-inbox-2-line"></i></span>
                    <div>
                        <div class="pm-stat-value">{{ number_format($counts['all']) }}</div>
                        <div class="pm-stat-label">All messages</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('parent-messages', ['status' => 'new']) }}" class="pm-stat {{ $status === 'new' ? 'is-active' : '' }}">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-600 text-white"><i class="ri-mail-unread-line"></i></span>
                    <div>
                        <div class="pm-stat-value">{{ number_format($counts['new']) }}</div>
                        <div class="pm-stat-label">New</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('parent-messages', ['status' => 'read']) }}" class="pm-stat {{ $status === 'read' ? 'is-active' : '' }}">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon bg-info-600 text-white"><i class="ri-mail-open-line"></i></span>
                    <div>
                        <div class="pm-stat-value">{{ number_format($counts['read']) }}</div>
                        <div class="pm-stat-label">Read</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('parent-messages', ['status' => 'replied']) }}" class="pm-stat {{ $status === 'replied' ? 'is-active' : '' }}">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-600 text-white"><i class="ri-chat-check-line"></i></span>
                    <div>
                        <div class="pm-stat-value">{{ number_format($counts['replied']) }}</div>
                        <div class="pm-stat-label">Replied</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="pm-board">
        <div class="pm-board-head d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="pm-tabs">
                @foreach($filterTabs as $tab)
                    <a href="{{ route('parent-messages', array_filter(['status' => $tab['key'] ?: null, 'q' => $search ?: null])) }}"
                       class="pm-tab {{ $status === $tab['key'] ? 'is-active' : '' }}">
                        <i class="{{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                        <span class="count">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('parent-messages') }}" class="pm-search">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <i class="ri-search-line"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search parent, student, or message">
            </form>
        </div>

        @forelse($messages as $message)
            @php
                $parentName = $message->parentAccount?->guardian_name ?: ($message->parentAccount?->phone ?: 'Parent');
                $parts = preg_split('/\s+/', trim($parentName)) ?: [];
                $initials = strtoupper(mb_substr($parts[0] ?? 'P', 0, 1).mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
                $pill = $statusPills[$message->status] ?? ['label' => ucfirst($message->status), 'class' => 'ac-pill-slate', 'icon' => 'ri-mail-line'];
            @endphp
            <div class="pm-item {{ $message->status === 'new' ? 'is-new' : '' }}">
                <div class="pm-avatar">{{ $initials }}</div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <div class="pm-name">{{ $parentName }}</div>
                        <span class="ac-pill {{ $pill['class'] }}"><i class="{{ $pill['icon'] }}"></i> {{ $pill['label'] }}</span>
                    </div>
                    <div class="pm-meta">
                        <span><i class="ri-phone-line"></i> {{ $message->parentAccount?->phone ?: '—' }}</span>
                        <span><i class="ri-user-3-line"></i> {{ $message->student?->full_name ?? 'General enquiry' }}</span>
                        <span><i class="ri-time-line"></i> {{ $message->created_at->format('d M Y, g:i A') }}</span>
                    </div>
                    <p class="pm-preview">{{ \Illuminate\Support\Str::limit($message->message, 160) }}</p>
                </div>
                <div class="pm-actions">
                    <button type="button" class="btn btn-sm btn-primary-600 radius-8" data-bs-toggle="modal" data-bs-target="#msgModal{{ $message->id }}">
                        <i class="ri-eye-line"></i> Open
                    </button>
                </div>
            </div>
        @empty
            <div class="pm-empty">
                <i class="ri-chat-off-line"></i>
                <strong class="d-block text-dark-1 mb-1">No messages here</strong>
                {{ $search || $status ? 'Try another filter or search.' : 'Parents have not sent any portal messages yet.' }}
            </div>
        @endforelse

        @if($messages->hasPages())
            <div class="p-20 border-top">{{ $messages->links() }}</div>
        @endif
    </div>
</div>

@foreach($messages as $message)
    @php
        $parentName = $message->parentAccount?->guardian_name ?: ($message->parentAccount?->phone ?: 'Parent');
    @endphp
    <div class="modal fade pm-modal" id="msgModal{{ $message->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="pm-modal-head">
                    <div>
                        <h5>Message from {{ $parentName }}</h5>
                        <p>{{ $message->parentAccount?->phone }} · {{ $message->created_at->format('d M Y, g:i A') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-24">
                    <div class="mb-16">
                        <div class="text-xs fw-bold text-secondary-light mb-8 text-uppercase">About</div>
                        <span class="ac-pill ac-pill-teal"><i class="ri-user-3-line"></i> {{ $message->student?->full_name ?? 'General enquiry' }}</span>
                    </div>
                    <div class="mb-16">
                        <div class="text-xs fw-bold text-secondary-light mb-8 text-uppercase">Parent wrote</div>
                        <div class="pm-bubble pm-bubble-in">{{ $message->message }}</div>
                    </div>
                    @if($message->admin_reply)
                        <div class="mb-16">
                            <div class="text-xs fw-bold text-secondary-light mb-8 text-uppercase">Internal note</div>
                            <div class="pm-bubble pm-bubble-out">{{ $message->admin_reply }}</div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('parent-messages-read', $message) }}">
                        @csrf
                        <label class="form-label fw-bold">Internal reply note</label>
                        <textarea name="admin_reply" class="form-control mb-16" rows="3" placeholder="Optional note for the office. This is not sent to the parent.">{{ old('admin_reply', $message->admin_reply) }}</textarea>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="pm-save"><i class="ri-check-line"></i> Save & mark handled</button>
                            <button type="button" class="btn btn-outline-secondary radius-8" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
