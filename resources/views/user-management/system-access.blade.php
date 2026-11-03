@php
    $pageName = "user-management";
    $subpageName = "system-access";
    $withLogin = $staffMembers->filter(fn ($member) => $member->user)->count();
    $withoutLogin = $staffMembers->count() - $withLogin;

    $staffPhoto = function ($member) {
        if (! empty($member->picture)) {
            return asset($member->picture);
        }

        return asset(strtolower((string) $member->gender) === 'male'
            ? 'assets/images/thumbs/guardian-img1.png'
            : 'assets/images/thumbs/guardian-img2.png');
    };
@endphp

@extends('layouts.app')

@section('css')
@include('staff.partials._extra-screens-styles')
<style>
    .sa-stat {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 14px;
        padding: 18px 20px;
        background: #fff;
        height: 100%;
    }
    .sa-stat .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .sa-workspace {
        display: grid;
        grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }
    .sa-directory,
    .sa-editor,
    .sa-empty {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .sa-directory-head,
    .sa-profile,
    .sa-footer {
        padding: 16px 18px;
    }
    .sa-directory-head {
        border-bottom: 1px solid #eef2f6;
        background: linear-gradient(180deg, #f8fffd, #fff);
    }
    .sa-search {
        position: relative;
    }
    .sa-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .sa-search input {
        padding-left: 36px;
    }
    .sa-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }
    .sa-filter {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #475569;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .sa-filter.is-active {
        background: #25A194;
        border-color: #25A194;
        color: #fff;
    }
    .sa-people {
        max-height: calc(100vh - 280px);
        min-height: 320px;
        overflow: auto;
        padding: 8px;
    }
    .sa-person {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        border: 1px solid transparent;
    }
    .sa-person:hover {
        background: #f8fafc;
    }
    .sa-person.is-selected {
        background: rgba(37, 161, 148, 0.08);
        border-color: rgba(37, 161, 148, 0.28);
    }
    .sa-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: #f1f5f9;
    }
    .sa-person-copy {
        min-width: 0;
        flex: 1;
    }
    .sa-person-copy strong,
    .sa-person-copy span {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sa-person-copy strong {
        font-size: 14px;
        color: #0f172a;
    }
    .sa-person-copy span {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }
    .sa-pill {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 8px;
    }
    .sa-pill.is-on {
        background: #dcfce7;
        color: #15803d;
    }
    .sa-pill.is-off {
        background: #f1f5f9;
        color: #64748b;
    }
    .sa-none {
        padding: 28px 16px;
        text-align: center;
        color: #64748b;
        font-size: 13px;
    }
    .sa-profile {
        display: flex;
        align-items: center;
        gap: 14px;
        background: linear-gradient(180deg, #f8fffd, #fff);
        border-bottom: 1px solid #eef2f6;
    }
    .sa-profile-photo {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        object-fit: cover;
        background: #f1f5f9;
        flex-shrink: 0;
    }
    .sa-profile h5 {
        margin: 0;
        font-size: 18px;
        color: #0f172a;
    }
    .sa-kicker {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #25A194;
        margin-bottom: 2px;
    }
    .sa-editor .staff-access-card {
        margin-top: 0;
        box-shadow: none;
        border-radius: 0;
    }
    .sa-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-top: 1px solid #eef2f6;
        background: #fff;
    }
    .sa-empty {
        min-height: 420px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 40px 24px;
    }
    .sa-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.12);
        color: #25A194;
        font-size: 28px;
        margin-bottom: 14px;
    }
    .screen-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        margin: 0 6px 6px 0;
    }
    .inherited-screens-box {
        min-height: 56px;
        background: var(--neutral-50, #f9fafb);
    }
    .system-access-fields.is-disabled {
        opacity: 0.55;
        pointer-events: none;
    }
    @media (max-width: 991px) {
        .sa-workspace { grid-template-columns: 1fr; }
        .sa-people { max-height: 360px; min-height: 0; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">USER MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / System Access</span>
            </div>
        </div>
    </div>

    <div class="row gy-3 mb-24">
        <div class="col-sm-4">
            <div class="sa-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Staff</p>
                        <h4 class="fw-semibold mb-0">{{ $staffMembers->count() }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-team-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="sa-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Logins enabled</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $withLogin }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-shield-check-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="sa-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">No login</p>
                        <h4 class="fw-semibold mb-0">{{ $withoutLogin }}</h4>
                    </div>
                    <span class="stat-icon bg-neutral-200 text-secondary-light"><i class="ri-lock-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="sa-workspace">
        <aside class="sa-directory">
            <div class="sa-directory-head">
                <div class="sa-search">
                    <i class="ri-search-line"></i>
                    <input type="search" id="system-access-search" class="form-control" placeholder="Search name, ID, or email">
                </div>
                <div class="sa-filters" id="system-access-filters">
                    <button type="button" class="sa-filter is-active" data-filter="all">All</button>
                    <button type="button" class="sa-filter" data-filter="on">Enabled</button>
                    <button type="button" class="sa-filter" data-filter="off">No login</button>
                </div>
            </div>
            <div class="sa-people" id="system-access-people">
                @forelse($staffMembers as $member)
                    <a href="{{ route('system-access', ['staff' => $member->id]) }}"
                        class="sa-person {{ $selectedStaff && $selectedStaff->id === $member->id ? 'is-selected' : '' }}"
                        data-login="{{ $member->user ? 'on' : 'off' }}"
                        data-search="{{ strtolower($member->full_name.' '.$member->employee_id.' '.$member->email.' '.($member->user?->category?->cat_name ?? '')) }}">
                        <img class="sa-avatar" src="{{ $staffPhoto($member) }}" alt="">
                        <div class="sa-person-copy">
                            <strong>{{ $member->full_name }}</strong>
                            <span>{{ $member->employee_id }}{{ $member->user?->category ? ' · '.$member->user->category->cat_name : '' }}</span>
                        </div>
                        <span class="sa-pill {{ $member->user ? 'is-on' : 'is-off' }}">{{ $member->user ? 'On' : 'Off' }}</span>
                    </a>
                @empty
                    <div class="sa-none">No staff records yet.</div>
                @endforelse
                <div class="sa-none d-none" id="system-access-empty">No staff match this search.</div>
            </div>
        </aside>

        @if($selectedStaff)
            <form action="{{ route('system-access-process') }}" method="POST" class="sa-editor">
                @csrf
                <input type="hidden" name="staff_id" value="{{ $selectedStaff->id }}">

                <div class="sa-profile">
                    <img class="sa-profile-photo" src="{{ $staffPhoto($selectedStaff) }}" alt="">
                    <div class="min-w-0 flex-grow-1">
                        <div class="sa-kicker">System access</div>
                        <h5>{{ $selectedStaff->full_name }}</h5>
                        <p class="text-sm text-secondary-light mb-0">
                            {{ $selectedStaff->employee_id }}
                            @if($selectedStaff->email)
                                · {{ $selectedStaff->email }}
                            @endif
                            @if($selectedStaff->position)
                                · {{ $selectedStaff->position }}
                            @endif
                        </p>
                    </div>
                    <span class="sa-pill {{ $staffUser ? 'is-on' : 'is-off' }}">{{ $staffUser ? 'Login on' : 'No login' }}</span>
                </div>

                @include('staff.partials._section-system-access')

                <div class="sa-footer">
                    <p class="text-sm text-secondary-light mb-0">A password is required only when this person does not have a login yet.</p>
                    <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6">
                        <i class="ri-save-line"></i>
                        Save access
                    </button>
                </div>
            </form>
        @else
            <div class="sa-empty">
                <span class="sa-empty-icon"><i class="ri-shield-keyhole-line"></i></span>
                <h5 class="fw-semibold mb-8">Choose a staff member</h5>
                <p class="text-sm text-secondary-light mb-0" style="max-width: 360px;">
                    Select someone from the directory to turn on their login, assign a user category, and grant extra screens.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
@if($selectedStaff)
    @include('staff.partials._extra-screens-scripts')
    <script>
        (function () {
            initStaffExtraScreens({
                accessToggle: document.getElementById('enable_system_access'),
                accessFields: document.getElementById('system-access-fields'),
                categorySelect: document.getElementById('staff_user_cat'),
                previewBox: document.getElementById('inherited-screens-preview'),
                categoryLinksUrl: @json(url('get-user-category-id')),
            });
        })();
    </script>
@endif
<script>
    (function () {
        const input = document.getElementById('system-access-search');
        const people = document.querySelectorAll('#system-access-people .sa-person');
        const empty = document.getElementById('system-access-empty');
        const filters = document.querySelectorAll('#system-access-filters .sa-filter');
        let mode = 'all';

        function apply() {
            const term = (input.value || '').trim().toLowerCase();
            let visible = 0;
            people.forEach(function (person) {
                const matchesFilter = mode === 'all' || person.getAttribute('data-login') === mode;
                const matchesSearch = !term || (person.getAttribute('data-search') || '').indexOf(term) !== -1;
                const show = matchesFilter && matchesSearch;
                person.style.display = show ? '' : 'none';
                if (show) visible += 1;
            });
            if (empty) empty.classList.toggle('d-none', visible !== 0);
        }

        if (input) input.addEventListener('input', apply);
        filters.forEach(function (button) {
            button.addEventListener('click', function () {
                mode = this.getAttribute('data-filter');
                filters.forEach(function (item) { item.classList.toggle('is-active', item === button); });
                apply();
            });
        });

        const selected = document.querySelector('.sa-person.is-selected');
        if (selected) selected.scrollIntoView({ block: 'nearest' });
    })();
</script>
@endsection
