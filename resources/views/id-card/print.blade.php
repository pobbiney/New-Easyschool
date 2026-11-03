@php
    $pageName = 'id-card';
    $subpageName = 'print-id-cards';
    $audience = $audience ?? 'student';
    $search = $search ?? '';
    $palette = $palette ?? ['dark' => '#3B0A5C', 'accent' => '#F5C518', 'ink' => '#1A0B2E'];
    $logoUrl = $logoUrl ?? ($school && method_exists($school, 'logoUrl') ? $school->logoUrl() : asset('assets/images/logo-icon.png'));
    $fallbackPhoto = $audience === 'employee'
        ? asset('assets/images/id-card-employee.png')
        : asset('assets/images/id-card-school-girl.png');
    $people = $audience === 'employee' ? $staff : $students;
@endphp
@extends('layouts.app')

@section('css')
<style>
    .print-page {
        --card-dark: {{ $palette['dark'] }};
        --card-accent: {{ $palette['accent'] }};
        --card-ink: {{ $palette['ink'] }};
    }
    .print-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        background: linear-gradient(135deg, var(--card-dark) 0%, var(--card-ink) 70%);
        color: #fff;
        border-radius: 20px;
        padding: 22px 26px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .print-hero::after {
        content: "";
        position: absolute;
        right: -30px; top: -40px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: color-mix(in srgb, var(--card-accent) 28%, transparent);
    }
    .print-hero h2 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
    .print-hero p { margin: 0; opacity: .86; font-size: 13px; }
    .print-hero-logo {
        width: 64px; height: 64px; border-radius: 16px; background: #fff;
        display: flex; align-items: center; justify-content: center; overflow: hidden; z-index: 1;
    }
    .print-hero-logo img { width: 86%; height: 86%; object-fit: contain; }
    .idcard-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
        padding: 4px;
        background: #fff;
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 14px;
        width: fit-content;
    }
    .idcard-tab {
        display: inline-flex;
        border: 0;
        background: transparent;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
        text-decoration: none;
    }
    .idcard-tab.active { background: var(--card-dark); color: #fff; }
    .print-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        margin-bottom: 16px;
    }
    .print-search {
        position: relative;
        flex: 1;
        min-width: 220px;
    }
    .print-search input {
        padding-left: 40px;
        height: 44px;
        border-radius: 12px;
    }
    .print-search i {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .print-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .print-chip {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        color: #475569;
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .print-chip.active {
        background: var(--card-accent);
        border-color: var(--card-accent);
        color: var(--card-ink);
    }
    .print-people {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 14px;
        padding-bottom: 88px;
    }
    .print-person {
        display: flex;
        gap: 12px;
        align-items: center;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px;
        cursor: pointer;
        margin: 0;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .print-person:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(15,23,42,.08); }
    .print-person.is-on {
        border-color: var(--card-accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--card-accent) 28%, transparent);
    }
    .print-person input { margin: 0; flex-shrink: 0; width: 18px; height: 18px; }
    .print-avatar {
        width: 52px; height: 52px; border-radius: 14px; overflow: hidden;
        background: #f1f5f9; flex-shrink: 0;
    }
    .print-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .print-person strong { display: block; font-size: 14px; color: #0f172a; line-height: 1.25; }
    .print-person small { display: block; color: #64748b; font-size: 12px; margin-top: 2px; }
    .print-empty {
        text-align: center;
        padding: 48px 20px;
        background: #fff;
        border-radius: 16px;
        border: 1px dashed #cbd5e1;
        color: #64748b;
    }
    .print-bar {
        position: sticky;
        bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: var(--card-dark);
        color: #fff;
        border-radius: 16px;
        padding: 14px 18px;
        box-shadow: 0 16px 40px rgba(15,23,42,.28);
        z-index: 8;
    }
    .print-bar strong { color: var(--card-accent); }
</style>
@endsection

@section('content')
<div class="dashboard-main-body print-page">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-16">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">ID Card</h1>
            <div>
                <a href="{{ route('dashboard', [], false) }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Print ID Cards</span>
            </div>
        </div>
        <a href="{{ route('id-card-settings', ['tab' => $audience], false) }}" class="btn btn-outline-primary-600">Card settings</a>
    </div>

    <div class="print-hero" style="background: white;">
        <div >
            <h2>Print ID cards</h2>
            <p style="color:#0f172a;">Pick {{ $audience === 'employee' ? 'staff' : 'students' }}, then print using the saved card layout.</p>
        </div>
        <div class="print-hero-logo"><img src="{{ $logoUrl }}" alt=""></div>
    </div>

    <div class="idcard-tabs">
        <a class="idcard-tab {{ $audience === 'student' ? 'active' : '' }}" href="{{ route('print-id-cards', ['tab' => 'student'], false) }}">Student</a>
        <a class="idcard-tab {{ $audience === 'employee' ? 'active' : '' }}" href="{{ route('print-id-cards', ['tab' => 'employee'], false) }}">Employee</a>
    </div>

    <form method="GET" action="{{ route('print-id-cards') }}" class="print-toolbar">
        <input type="hidden" name="tab" value="{{ $audience }}">
        @if ($audience === 'student' && $classId)
            <input type="hidden" name="class_id" value="{{ $classId }}">
        @endif
        <div class="print-search">
            <i class="ri-search-line"></i>
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search name or ID...">
        </div>
        <button type="submit" class="btn btn-primary-600">Search</button>
        <button type="button" class="btn btn-outline-neutral-400" id="printSelectAllBtn">Select all</button>
        <button type="button" class="btn btn-outline-neutral-400" id="printClearBtn">Clear</button>
    </form>

    @if ($audience === 'student' && $classes->isNotEmpty())
        <div class="print-chips mb-16">
            <a class="print-chip {{ ! $classId ? 'active' : '' }}" href="{{ route('print-id-cards', array_filter(['tab' => 'student', 'q' => $search ?: null]), false) }}">All classes</a>
            @foreach ($classes as $class)
                <a class="print-chip {{ (string) $classId === (string) $class->id ? 'active' : '' }}" href="{{ route('print-id-cards', array_filter(['tab' => 'student', 'class_id' => $class->id, 'q' => $search ?: null]), false) }}">{{ $class->name }}</a>
            @endforeach
        </div>
    @endif

    @if ($people->isEmpty())
        <div class="print-empty">
            <i class="ri-id-card-line" style="font-size:36px;color:var(--card-accent);"></i>
            <p class="mt-12 mb-0">No {{ $audience === 'employee' ? 'employees' : 'students' }} match this filter.</p>
        </div>
    @else
        <form id="printIdCardsForm" method="GET" action="{{ route('print-id-cards-sheet') }}" target="_blank">
            <input type="hidden" name="tab" value="{{ $audience }}">
            <div class="print-people">
            @if ($audience === 'student')
                @foreach ($people as $student)
                    @php
                        $photo = $student->picture ? asset($student->picture) : $fallbackPhoto;
                    @endphp
                    <label class="print-person">
                        <input type="checkbox" class="form-check-input print-row" name="ids[]" value="{{ $student->id }}">
                        <span class="print-avatar"><img src="{{ $photo }}" alt=""></span>
                        <span>
                            <strong>{{ $student->full_name }}</strong>
                            <small>{{ $student->student_id }} · {{ $student->schoolClass->name ?? $student->class_name ?? 'No class' }}</small>
                        </span>
                    </label>
                @endforeach
            @else
                @foreach ($people as $member)
                    @php
                        $photo = $member->picture ? asset($member->picture) : $fallbackPhoto;
                    @endphp
                    <label class="print-person">
                        <input type="checkbox" class="form-check-input print-row" name="ids[]" value="{{ $member->id }}">
                        <span class="print-avatar"><img src="{{ $photo }}" alt=""></span>
                        <span>
                            <strong>{{ $member->full_name }}</strong>
                            <small>{{ $member->employee_id }} · {{ $member->hrPosition->name ?? $member->position ?? 'Staff' }}</small>
                        </span>
                    </label>
                @endforeach
            @endif
            </div>
        </form>
    @endif

    <div class="print-bar">
        <div><strong id="printSelectedCount">0</strong> selected of {{ $people->count() }}</div>
        <button type="button" class="btn btn-sm" style="background:var(--card-accent);color:var(--card-ink);font-weight:800;" id="printIdCardsBtn">Print selected cards</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const rows = () => Array.from(document.querySelectorAll('.print-row'));
    const countEl = document.getElementById('printSelectedCount');

    function paint() {
        rows().forEach((el) => {
            el.closest('.print-person')?.classList.toggle('is-on', el.checked);
        });
        if (countEl) countEl.textContent = String(rows().filter((el) => el.checked).length);
    }

    document.getElementById('printSelectAllBtn')?.addEventListener('click', function () {
        rows().forEach((el) => { el.checked = true; });
        paint();
    });
    document.getElementById('printClearBtn')?.addEventListener('click', function () {
        rows().forEach((el) => { el.checked = false; });
        paint();
    });
    document.querySelector('.print-people')?.addEventListener('change', paint);

    document.getElementById('printIdCardsBtn')?.addEventListener('click', function () {
        const ids = rows().filter((el) => el.checked).map((el) => el.value);
        if (!ids.length) {
            if (typeof showAppToast === 'function') showAppToast('warning', 'Select at least one person.');
            return;
        }
        document.getElementById('printIdCardsForm')?.submit();
    });

    paint();
})();
</script>
@endsection
