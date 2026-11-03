@php $pageName = "class-setup"; $subpageName = "student-class-assignment"; @endphp
@extends('layouts.app')

@section('css')
<style>
    .sca-progress {
        position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999;
        opacity: 0; pointer-events: none; transition: opacity .2s;
        background: rgba(37,161,148,.1); overflow: hidden;
    }
    .sca-progress.is-active { opacity: 1; }
    .sca-progress::after {
        content: ''; position: absolute; top: 0; left: 0; height: 100%; width: 35%;
        background: linear-gradient(90deg, #25A194, #6366f1);
        animation: scaSlide 1.1s ease-in-out infinite;
    }
    @keyframes scaSlide { 0% { transform: translateX(-120%); } 100% { transform: translateX(320%); } }

    /* Hero */
    .sca-hero {
        border-radius: 16px; padding: 22px 26px;
        background: linear-gradient(135deg, rgba(37,161,148,.1) 0%, rgba(99,102,241,.06) 100%);
        border: 1px solid rgba(37,161,148,.12);
    }
    .sca-hero-icon {
        width: 48px; height: 48px; border-radius: 13px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #25A194; color: #fff; font-size: 22px; flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(37,161,148,.25);
    }

    /* Stat cards */
    .sca-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb); border-radius: 14px;
        padding: 18px 20px; background: #fff; height: 100%;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .sca-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15,23,42,.06);
    }
    .sca-stat-card .stat-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: inline-flex; align-items: center; justify-content: center; font-size: 19px;
    }

    /* Shell */
    .sca-shell {
        border: 1px solid var(--neutral-200, #e5e7eb); border-radius: 16px;
        background: #fff; overflow: hidden;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }

    /* Search — top row + results below */
    .sca-search-section {
        padding: 20px 22px 16px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fff 0%, #f9fafb 100%);
    }
    .sca-search-row {
        display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;
    }
    .sca-search-label {
        display: flex; align-items: center; gap: 10px; flex-shrink: 0;
        padding-bottom: 10px;
    }
    .sca-search-label h6 { font-size: 14px; font-weight: 700; margin: 0; white-space: nowrap; }
    .sca-search-wrap { position: relative; flex: 1; min-width: 200px; }
    .sca-search-wrap i {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #25A194; font-size: 17px; pointer-events: none;
    }
    .sca-search-wrap .form-control {
        padding-left: 42px; border-radius: 12px; min-height: 44px; font-size: 13px;
        border-color: var(--neutral-200, #e5e7eb);
        transition: border-color .15s, box-shadow .15s;
    }
    .sca-search-wrap .form-control:focus {
        border-color: rgba(37,161,148,.45);
        box-shadow: 0 0 0 3px rgba(37,161,148,.1);
    }
    .sca-filter-field { min-width: 150px; flex: 0 1 160px; }
    .sca-filter-field .form-select {
        border-radius: 12px; min-height: 44px; font-size: 13px;
        border-color: var(--neutral-200, #e5e7eb);
    }
    .sca-result-count {
        padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
        background: rgba(37,161,148,.1); color: #25A194; white-space: nowrap;
    }
    .sca-results {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px;
        margin-top: 14px;
        max-height: 200px;
        overflow-y: auto;
        padding: 2px;
    }
    .sca-results.is-empty-state {
        display: block; max-height: none;
    }
    .sca-results.is-empty-state .sca-empty-search {
        text-align: center; padding: 24px 16px;
        font-size: 13px; color: #9ca3af;
    }
    @media (max-width: 768px) {
        .sca-search-row { flex-direction: column; align-items: stretch; }
        .sca-search-label { padding-bottom: 0; }
        .sca-filter-field { flex: 1 1 100%; min-width: 0; }
    }

    .sca-result-card {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 12px;
        border: 1px solid var(--neutral-200, #e5e7eb); cursor: pointer;
        transition: all .15s ease; background: #fff;
    }
    .sca-result-card:hover {
        border-color: rgba(37,161,148,.18);
        box-shadow: 0 3px 10px rgba(15,23,42,.05);
    }
    .sca-result-card.is-selected {
        border-color: rgba(37,161,148,.35);
        background: rgba(37,161,148,.05);
        box-shadow: 0 0 0 1px rgba(37,161,148,.12);
    }
    .sca-avatar {
        width: 44px; height: 44px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px; flex-shrink: 0; overflow: hidden;
        background: rgba(37,161,148,.1); color: #25A194;
    }
    .sca-avatar img { width: 100%; height: 100%; object-fit: cover; }

    /* Main panel */
    .sca-main-col { display: flex; flex-direction: column; min-width: 0; position: relative; min-height: 420px; }
    .sca-empty {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center; text-align: center;
        padding: 72px 32px; color: #6b7280;
    }
    .sca-empty-icon {
        width: 68px; height: 68px; border-radius: 18px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 30px; background: #f3f4f6; color: #9ca3af; margin-bottom: 16px;
    }

    /* Student profile */
    .sca-profile-card {
        margin: 20px 22px 0;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .sca-profile-accent {
        height: 4px;
        background: linear-gradient(90deg, #25A194, #17897e);
    }
    .sca-profile-top {
        display: flex; flex-wrap: wrap; align-items: center; gap: 20px;
        padding: 22px 24px;
    }
    .sca-profile-photo {
        width: 88px; height: 88px; border-radius: 14px; overflow: hidden;
        flex-shrink: 0; display: flex; align-items: center; justify-content: center;
        font-size: 24px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #25A194, #17897e);
        border: 3px solid rgba(37,161,148,.15);
        box-shadow: 0 4px 14px rgba(37,161,148,.2);
    }
    .sca-profile-photo img { width: 100%; height: 100%; object-fit: cover; }
    .sca-profile-meta { flex: 1; min-width: 180px; }
    .sca-profile-name { font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 4px; letter-spacing: -.02em; }
    .sca-profile-id { font-size: 13px; color: #6b7280; margin: 0; }
    .sca-profile-stats {
        display: flex; flex-wrap: wrap; gap: 10px; flex: 1 1 100%;
    }
    @media (min-width: 768px) {
        .sca-profile-stats { flex: 0 1 auto; margin-left: auto; justify-content: flex-end; }
    }
    .sca-stat-mini {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 14px; border-radius: 11px;
        border: 1px solid var(--neutral-200, #e5e7eb);
        background: #fafafa; min-width: 130px;
    }
    .sca-stat-mini-icon {
        width: 34px; height: 34px; border-radius: 9px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(37,161,148,.1); color: #25A194; font-size: 16px; flex-shrink: 0;
    }
    .sca-stat-mini .lbl { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; line-height: 1.2; }
    .sca-stat-mini .val { font-size: 13px; font-weight: 700; color: #111827; line-height: 1.3; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Detail tabs area */
    .sca-detail-wrap { padding: 18px 22px 0; background: #f9fafb; }
    .sca-pill-tabs {
        display: flex; flex-wrap: nowrap; gap: 7px;
        padding: 0 0 14px; overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .sca-pill-tab {
        border: 1px solid var(--neutral-200, #e5e7eb); background: #fff;
        color: #6b7280; padding: 7px 14px; border-radius: 999px;
        font-size: 12px; font-weight: 600; white-space: nowrap; cursor: pointer;
        transition: all .15s ease; display: inline-flex; align-items: center; gap: 6px;
    }
    .sca-pill-tab:hover { border-color: rgba(37,161,148,.25); color: #25A194; }
    .sca-pill-tab.active {
        background: #25A194; border-color: #25A194; color: #fff;
        box-shadow: 0 2px 8px rgba(37,161,148,.25);
    }
    .sca-pill-tab.active .sca-tab-badge { background: #fff; color: #E5484D; }
    .sca-tab-badge {
        min-width: 17px; height: 17px; padding: 0 5px; border-radius: 999px;
        background: #E5484D; color: #fff; font-size: 10px; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }

    .sca-tab-body { padding: 0 22px 22px; flex: 1; background: #f9fafb; }
    .sca-detail-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;
    }
    .sca-detail-card {
        border: 1px solid var(--neutral-200, #e5e7eb); border-radius: 12px;
        background: #fff; overflow: hidden; height: 100%;
    }
    .sca-detail-card-head {
        padding: 14px 18px; border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fff, #fafafa);
        font-size: 13px; font-weight: 700; color: #374151;
        display: flex; align-items: center; gap: 8px;
    }
    .sca-detail-card-head i { color: #25A194; font-size: 17px; }
    .sca-detail-card-body { padding: 6px 18px 14px; }
    .sca-detail-row {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 10px 0; border-bottom: 1px dashed #f3f4f6;
    }
    .sca-detail-row:last-child { border-bottom: none; padding-bottom: 4px; }
    .sca-detail-label {
        min-width: 120px; flex-shrink: 0;
        font-size: 12px; font-weight: 600; color: #9ca3af;
    }
    .sca-detail-value {
        font-size: 13px; font-weight: 600; color: #111827;
        word-break: break-word; flex: 1;
    }
    .sca-detail-value.is-empty { color: #d1d5db; font-weight: 400; }

    .sca-tab-empty {
        text-align: center; padding: 48px 24px;
        border: 1px dashed var(--neutral-200, #e5e7eb); border-radius: 12px;
        background: #fff; color: #9ca3af; font-size: 13px;
    }
    .sca-tab-empty i { font-size: 32px; display: block; margin-bottom: 10px; color: #e5e7eb; }

    .sca-doc-card {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 14px; border-radius: 10px;
        border: 1px solid var(--neutral-200, #e5e7eb); background: #fafafa;
        text-decoration: none; color: inherit;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .sca-doc-card:hover {
        border-color: rgba(37,161,148,.35); background: #fff;
        box-shadow: 0 4px 12px rgba(15,23,42,.05);
    }
    .sca-doc-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(37,161,148,.1); color: #25A194; font-size: 17px;
    }

    /* Bills */
    .sca-bills-card {
        border: 1px solid var(--neutral-200, #e5e7eb); border-radius: 12px;
        background: #fff; overflow: hidden;
    }
    .sca-bill-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; }
    @media (max-width: 576px) { .sca-bill-summary { grid-template-columns: 1fr; } }
    .sca-bill-sum-card {
        padding: 16px 18px; border-right: 1px solid var(--neutral-200, #e5e7eb);
    }
    .sca-bill-sum-card:last-child { border-right: none; }
    @media (max-width: 576px) {
        .sca-bill-sum-card { border-right: none; border-bottom: 1px solid var(--neutral-200, #e5e7eb); }
        .sca-bill-sum-card:last-child { border-bottom: none; }
    }
    .sca-bill-sum-card.due { background: rgba(99,102,241,.03); }
    .sca-bill-sum-card.paid { background: rgba(34,197,94,.03); }
    .sca-bill-sum-card.balance { background: rgba(229,72,77,.03); }
    .sca-bills-table { padding: 0; }

    /* Assign card */
    .sca-assign-card {
        margin: 0 22px 22px; padding: 22px 24px; border-radius: 14px;
        border: 1px solid rgba(37,161,148,.18);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .sca-assign-card h6 { display: flex; align-items: center; gap: 8px; font-size: 14px; }
    .sca-field label {
        font-size: 12px; font-weight: 600; color: #4b5563;
        margin-bottom: 6px; display: flex; align-items: center; gap: 6px;
    }
    .sca-field .form-select {
        border-radius: 12px; min-height: 44px; font-size: 13px;
        border-color: var(--neutral-200, #e5e7eb);
        transition: border-color .15s, box-shadow .15s;
    }
    .sca-field .form-select:focus {
        border-color: rgba(37,161,148,.45);
        box-shadow: 0 0 0 3px rgba(37,161,148,.1);
    }

    .sca-preview {
        margin-top: 16px; padding: 16px; border-radius: 12px;
        border: 1px solid var(--neutral-200, #e5e7eb); background: #fff;
    }
    .sca-preview.is-found { border-color: rgba(34,197,94,.3); background: rgba(34,197,94,.03); }
    .sca-preview.is-missing { border-color: rgba(234,179,8,.35); background: rgba(234,179,8,.05); }
    .sca-preview-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0; border-bottom: 1px dashed var(--neutral-200, #e5e7eb); font-size: 13px;
    }
    .sca-preview-row:last-child { border-bottom: none; }

    /* Status badges */
    .sca-status-active, .sca-status-draft, .sca-status-inactive {
        padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
    }
    .sca-status-active { background: rgba(34,197,94,.15); color: #15803d; }
    .sca-status-draft { background: rgba(234,179,8,.15); color: #a16207; }
    .sca-status-inactive { background: rgba(239,68,68,.12); color: #b91c1c; }
    .type-badge-compulsory, .type-badge-optional {
        padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600;
    }
    .type-badge-compulsory { background: rgba(234,88,12,.1); color: #c2410c; }
    .type-badge-optional { background: rgba(99,102,241,.1); color: #4338ca; }
    .bill-status-paid { color: #15803d; font-weight: 600; }
    .bill-status-partial { color: #a16207; font-weight: 600; }
    .bill-status-pending { color: #b91c1c; font-weight: 600; }

    /* Loading */
    .sca-loading {
        position: absolute; inset: 0;
        background: rgba(255,255,255,.78); backdrop-filter: blur(3px);
        display: none; align-items: center; justify-content: center;
        z-index: 5; flex-direction: column; gap: 10px;
    }
    .sca-loading.is-visible { display: flex; }
    .sca-spinner {
        width: 30px; height: 30px; border-radius: 50%;
        border: 3px solid rgba(37,161,148,.12); border-top-color: #25A194;
        animation: scaSpin .7s linear infinite;
    }
    @keyframes scaSpin { to { transform: rotate(360deg); } }

    .sca-mode-tabs {
        display: inline-flex; gap: 8px; padding: 6px;
        border: 1px solid var(--neutral-200, #e5e7eb); border-radius: 999px;
        background: #fff; margin-bottom: 16px;
    }
    .sca-mode-tab {
        border: none; background: transparent; color: #6b7280;
        padding: 8px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
        transition: all .15s ease;
    }
    .sca-mode-tab.is-active {
        background: #25A194; color: #fff;
        box-shadow: 0 2px 8px rgba(37,161,148,.25);
    }
    .sca-bulk-shell {
        border: 1px solid var(--neutral-200, #e5e7eb); border-radius: 16px;
        background: #fff; overflow: hidden;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .sca-bulk-toolbar {
        padding: 20px 22px; border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fff 0%, #f9fafb 100%);
    }
    .sca-bulk-table-wrap { max-height: 420px; overflow: auto; }
    .sca-bulk-table thead th {
        position: sticky; top: 0; z-index: 2;
        background: #25A194; color: #fff; border: none; font-size: 12px;
    }
    .sca-bulk-table tbody tr.is-selected { background: rgba(37,161,148,.04); }
    .sca-bulk-footer {
        padding: 18px 22px; border-top: 1px solid var(--neutral-200, #e5e7eb);
        background: #fafafa;
    }
    .sca-bulk-count {
        padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700;
        background: rgba(37,161,148,.1); color: #25A194;
    }
</style>
@endsection

@section('content')
<div class="sca-progress" id="scaProgress"></div>

<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">CLASS SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Student Class Assignment</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('school-classes') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6"><i class="ri-layout-grid-line"></i> Classes</a>
            <a href="{{ route('category-bill-setup') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6"><i class="ri-price-tag-3-line"></i> Bill Setup</a>
        </div>
    </div>

    <div class="sca-hero d-flex align-items-start gap-16 mb-24">
        <span class="sca-hero-icon"><i class="ri-user-shared-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Student Class Assignment</h5>
            <p class="text-sm text-secondary-light mb-0">Search students for single assignment, or use bulk mode to assign many students to one class at once.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-4">
            <div class="sca-stat-card d-flex align-items-center justify-content-between gap-14">
                <div>
                    <p class="text-sm text-secondary-light mb-4">Active Students</p>
                    <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                </div>
                <span class="stat-icon" style="background:rgba(37,161,148,.1);color:#25A194;"><i class="ri-group-line"></i></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="sca-stat-card d-flex align-items-center justify-content-between gap-14">
                <div>
                    <p class="text-sm text-secondary-light mb-4">Fully Assigned</p>
                    <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['assigned'] }}</h4>
                </div>
                <span class="stat-icon" style="background:rgba(34,197,94,.1);color:#15803d;"><i class="ri-checkbox-circle-line"></i></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="sca-stat-card d-flex align-items-center justify-content-between gap-14">
                <div>
                    <p class="text-sm text-secondary-light mb-4">Unassigned</p>
                    <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['unassigned'] }}</h4>
                </div>
                <span class="stat-icon" style="background:rgba(234,179,8,.12);color:#a16207;"><i class="ri-user-unfollow-line"></i></span>
            </div>
        </div>
    </div>

    <div class="sca-mode-tabs">
        <button type="button" class="sca-mode-tab is-active" data-mode="single"><i class="ri-user-line"></i> Single Student</button>
        <button type="button" class="sca-mode-tab" data-mode="bulk"><i class="ri-group-2-line"></i> Bulk Assignment</button>
    </div>

    <div id="singleAssignmentPanel">
    <div class="sca-shell">
        <div class="sca-search-section">
            <div class="sca-search-row">
                <div class="sca-search-label">
                    <h6>Find Student</h6>
                    <span id="resultCount" class="sca-result-count d-none">0</span>
                </div>
                <div class="sca-search-wrap">
                    <i class="ri-search-line"></i>
                    <input type="text" id="studentSearchInput" class="form-control" placeholder="Name or student ID..." autocomplete="off">
                </div>
                <div class="sca-filter-field">
                    <select id="filterStatus" class="form-select">
                        <option value="">All statuses</option>
                        <option value="Active">Active</option>
                        <option value="Draft">Draft</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="sca-filter-field">
                    <select id="filterClass" class="form-select">
                        <option value="">All classes</option>
                        @foreach($schoolClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div id="searchResults" class="sca-results is-empty-state">
                <div class="sca-empty-search">Type at least 2 characters or apply filters to see results.</div>
            </div>
        </div>

        <div class="sca-main-col">
                <div class="sca-loading" id="profileLoading">
                    <div class="sca-spinner"></div>
                    <p class="text-sm text-secondary-light mb-0">Loading profile...</p>
                </div>

                <div id="workspaceEmpty" class="sca-empty">
                    <div class="sca-empty-icon"><i class="ri-user-search-line"></i></div>
                    <h6 class="fw-semibold mb-8">Select a student</h6>
                    <p class="text-sm mb-0" style="max-width:360px;">Search above, then click a student to view their profile and assign a class.</p>
                </div>

                <div id="workspaceContent" class="d-none flex-column flex-grow-1" style="background:#f9fafb;">
                    <div class="sca-profile-card">
                        <div class="sca-profile-accent"></div>
                        <div class="sca-profile-top">
                            <div class="sca-profile-photo" id="heroPhoto"></div>
                            <div class="sca-profile-meta">
                                <div class="d-flex flex-wrap align-items-center gap-8 mb-4">
                                    <h4 class="sca-profile-name mb-0" id="heroName"></h4>
                                    <span id="heroStatus"></span>
                                </div>
                                <p class="sca-profile-id" id="heroStudentId"></p>
                            </div>
                            <div class="sca-profile-stats" id="heroStats"></div>
                        </div>
                    </div>

                    <div class="sca-detail-wrap">
                        <div class="sca-pill-tabs" id="pillTabs">
                            <button type="button" class="sca-pill-tab active" data-tab="tabOverview"><i class="ri-dashboard-line"></i> Overview</button>
                            <button type="button" class="sca-pill-tab" data-tab="tabPersonal"><i class="ri-id-card-line"></i> Personal</button>
                            <button type="button" class="sca-pill-tab" data-tab="tabParents"><i class="ri-parent-line"></i> Parents</button>
                            <button type="button" class="sca-pill-tab" data-tab="tabMedical"><i class="ri-heart-pulse-line"></i> Medical</button>
                            <button type="button" class="sca-pill-tab" data-tab="tabDocuments"><i class="ri-file-upload-line"></i> Documents</button>
                            <button type="button" class="sca-pill-tab" data-tab="tabBills"><i class="ri-bill-line"></i> Bills <span id="billsTabBadge" class="sca-tab-badge d-none">0</span></button>
                            <button type="button" class="sca-pill-tab" data-tab="tabDormitory"><i class="ri-hotel-bed-line"></i> Dormitory</button>
                        </div>
                    </div>

                    <div class="sca-tab-body">
                        <div id="tabOverview" class="sca-tab-pane"></div>
                        <div id="tabPersonal" class="sca-tab-pane d-none"></div>
                        <div id="tabParents" class="sca-tab-pane d-none"></div>
                        <div id="tabMedical" class="sca-tab-pane d-none"></div>
                        <div id="tabDocuments" class="sca-tab-pane d-none"></div>
                        <div id="tabBills" class="sca-tab-pane d-none"></div>
                        <div id="tabDormitory" class="sca-tab-pane d-none"></div>
                    </div>

                    <div class="sca-assign-card">
                        <h6 class="fw-semibold mb-4"><i class="ri-user-shared-2-line text-primary-600"></i> Assign Class & Inherit Bills</h6>
                        <p class="text-sm text-secondary-light mb-16">Set the student's class, year, and term. Bills from the matching category bill setup will sync to their ledger.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="sca-field">
                                    <label><i class="ri-calendar-2-line"></i> Academic Year</label>
                                    <select id="assignYear" class="form-select">
                                        <option value="">Select year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sca-field">
                                    <label><i class="ri-calendar-event-line"></i> Academic Term</label>
                                    <select id="assignTerm" class="form-select">
                                        <option value="">Select term</option>
                                        @foreach($academicTerms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sca-field">
                                    <label><i class="ri-book-open-line"></i> Class</label>
                                    <select id="assignClass" class="form-select">
                                        <option value="">Select class</option>
                                        @foreach($schoolClasses as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}@if($class->category) · {{ $class->category->name }}@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="billPreview" class="sca-preview d-none"></div>

                        <div class="d-flex flex-wrap gap-2 mt-16">
                            <button type="button" id="assignBtn" class="btn btn-primary-600 d-inline-flex align-items-center gap-6">
                                <i class="ri-check-double-line"></i> Assign & Sync Bills
                            </button>
                            <a href="{{ route('category-bill-setup') }}" id="setupLink" class="btn btn-outline-primary-600 d-none">
                                <i class="ri-settings-3-line"></i> Configure Bill Setup
                            </a>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    </div>

    <div id="bulkAssignmentPanel" class="d-none">
        <div class="sca-bulk-shell">
            <div class="sca-bulk-toolbar">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-16">
                    <div>
                        <h6 class="fw-semibold mb-4"><i class="ri-group-2-line text-primary-600"></i> Bulk Assign to Class</h6>
                        <p class="text-sm text-secondary-light mb-0">Select students, choose target class/year/term, then assign all at once.</p>
                    </div>
                    <span id="bulkSelectedCount" class="sca-bulk-count">0 selected</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="sca-field">
                            <label><i class="ri-calendar-2-line"></i> Academic Year</label>
                            <select id="bulkAssignYear" class="form-select">
                                <option value="">Select year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sca-field">
                            <label><i class="ri-calendar-event-line"></i> Academic Term</label>
                            <select id="bulkAssignTerm" class="form-select">
                                <option value="">Select term</option>
                                @foreach($academicTerms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sca-field">
                            <label><i class="ri-book-open-line"></i> Target Class</label>
                            <select id="bulkAssignClass" class="form-select">
                                <option value="">Select class</option>
                                @foreach($schoolClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}@if($class->category) · {{ $class->category->name }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div id="bulkBillPreview" class="sca-preview d-none mt-16"></div>
            </div>

            <div class="sca-bulk-toolbar border-top-0 pt-0">
                <div class="sca-search-row">
                    <div class="sca-search-wrap">
                        <i class="ri-search-line"></i>
                        <input type="text" id="bulkSearchInput" class="form-control" placeholder="Search students by name or ID..." autocomplete="off">
                    </div>
                    <div class="sca-filter-field">
                        <select id="bulkFilterClass" class="form-select">
                            <option value="">All current classes</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sca-filter-field d-flex align-items-end">
                        <div class="form-check mb-10">
                            <input class="form-check-input" type="checkbox" id="bulkUnassignedOnly" checked>
                            <label class="form-check-label text-sm" for="bulkUnassignedOnly">Unassigned only</label>
                        </div>
                    </div>
                    <button type="button" id="bulkLoadBtn" class="btn btn-outline-primary-600"><i class="ri-refresh-line"></i> Load Students</button>
                </div>
            </div>

            <div class="sca-bulk-table-wrap">
                <table class="table bordered-table mb-0 sca-bulk-table">
                    <thead>
                        <tr>
                            <th style="width:44px;"><input type="checkbox" id="bulkSelectAll" class="form-check-input m-0"></th>
                            <th>Student</th>
                            <th>ID</th>
                            <th>Current Class</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="bulkStudentsBody">
                        <tr><td colspan="5" class="text-center py-40 text-secondary-light text-sm">Click “Load Students” to show candidates for bulk assignment.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="sca-bulk-footer d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="text-sm text-secondary-light" id="bulkLoadedSummary">No students loaded.</div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="bulkClearSelectionBtn" class="btn btn-outline-secondary-600">Clear Selection</button>
                    <button type="button" id="bulkAssignBtn" class="btn btn-primary-600 d-inline-flex align-items-center gap-6">
                        <i class="ri-check-double-line"></i> Assign Selected Students
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const searchUrl = @json(route('student-class-assignment-search'));
    const showUrlTemplate = @json(route('get-student-class-assignment', ['id' => '__ID__']));
    const previewUrl = @json(route('student-class-assignment-preview'));
    const assignUrl = @json(route('assign-student-class-process'));
    const bulkCandidatesUrl = @json(route('student-class-assignment-bulk-candidates'));
    const bulkAssignUrl = @json(route('assign-student-class-bulk-process'));
    const csrfToken = @json(csrf_token());
    const defaultAcademicYearId = @json($defaultAcademicYearId);
    const defaultAcademicTermId = @json($defaultAcademicTermId);

    let selectedStudentId = null, currentStudent = null, searchTimer = null, activeTab = 'tabOverview';
    let bulkStudents = [], bulkSelectedIds = new Set();

    function setLoading(on) { $('#scaProgress').toggleClass('is-active', on); $('#profileLoading').toggleClass('is-visible', on); }
    function escapeHtml(v) { return $('<div>').text(v ?? '').html(); }
    function formatMoney(v) { return parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function statusBadge(s) { const c = s === 'Active' ? 'sca-status-active' : (s === 'Draft' ? 'sca-status-draft' : 'sca-status-inactive'); return '<span class="' + c + '">' + escapeHtml(s) + '</span>'; }

    function detailValue(v) {
        return v ? escapeHtml(v) : '<span class="is-empty">—</span>';
    }
    function detailRow(label, value) {
        return '<div class="sca-detail-row"><span class="sca-detail-label">' + escapeHtml(label) + '</span><span class="sca-detail-value">' + detailValue(value) + '</span></div>';
    }
    function detailCard(title, icon, rows) {
        return '<div class="sca-detail-card"><div class="sca-detail-card-head"><i class="' + icon + '"></i> ' + escapeHtml(title) + '</div><div class="sca-detail-card-body">' + rows.join('') + '</div></div>';
    }
    function detailGrid(cards) { return '<div class="sca-detail-grid">' + cards.join('') + '</div>'; }
    function tabEmpty(icon, msg) { return '<div class="sca-tab-empty"><i class="' + icon + '"></i>' + escapeHtml(msg) + '</div>'; }
    function statMini(icon, label, value) {
        return '<div class="sca-stat-mini"><span class="sca-stat-mini-icon"><i class="' + icon + '"></i></span><div><div class="lbl">' + escapeHtml(label) + '</div><div class="val" title="' + escapeHtml(value || '—') + '">' + escapeHtml(value || '—') + '</div></div></div>';
    }

    function switchTab(tabId) {
        activeTab = tabId;
        $('.sca-pill-tab').removeClass('active');
        $('.sca-pill-tab[data-tab="' + tabId + '"]').addClass('active');
        $('.sca-tab-pane').addClass('d-none');
        $('#' + tabId).removeClass('d-none');
    }

    function renderHero(student) {
        $('#heroPhoto').html(student.picture ? '<img src="' + student.picture + '" alt="">' : escapeHtml(student.initials));
        $('#heroName').text(student.full_name);
        $('#heroStudentId').text('ID: ' + student.student_id);
        $('#heroStatus').html(statusBadge(student.status));
        $('#heroStats').html([
            statMini('ri-book-open-line', 'Class', student.class_name || 'Unassigned'),
            statMini('ri-calendar-2-line', 'Year', student.academic_year),
            statMini('ri-calendar-event-line', 'Term', student.academic_term),
            statMini('ri-folder-3-line', 'Category', student.category_name),
        ].join(''));
    }

    function renderTabs(data) {
        const s = data.student;
        $('#tabOverview').html(detailGrid([
            detailCard('Basic Information', 'ri-user-3-line', [
                detailRow('Gender', s.gender),
                detailRow('Date of Birth', s.dob),
                detailRow('Category', s.category),
                detailRow('Notes', s.notes),
            ]),
            detailCard('Contact', 'ri-phone-line', [
                detailRow('Phone', s.phone),
                detailRow('Email', s.email),
            ]),
        ]));
        $('#tabPersonal').html(detailGrid([
            detailCard('Enrollment', 'ri-graduation-cap-line', [
                detailRow('Class', s.class_name),
                detailRow('Class Category', s.category_name),
                detailRow('Academic Year', s.academic_year),
                detailRow('Academic Term', s.academic_term),
                detailRow('Section', s.section),
                detailRow('Roll Number', s.roll_number),
            ]),
        ]));
        $('#tabParents').html(detailGrid([
            detailCard('Father', 'ri-user-line', [
                detailRow('Name', s.father_name),
                detailRow('Phone', s.father_phone),
            ]),
            detailCard('Mother', 'ri-user-line', [
                detailRow('Name', s.mother_name),
                detailRow('Phone', s.mother_phone),
            ]),
            detailCard('Guardian', 'ri-shield-user-line', [
                detailRow('Name', s.guardian_name),
                detailRow('Phone', s.guardian_phone),
                detailRow('Type', s.guardian_type),
            ]),
        ]));
        $('#tabMedical').html(detailGrid([
            detailCard('Health & Background', 'ri-heart-pulse-line', [
                detailRow('Blood Group', s.blood_group),
                detailRow('Height', s.height),
                detailRow('Weight', s.weight),
                detailRow('NHIS', s.has_nhis ? 'Yes' : 'No'),
                detailRow('NHIS Number', s.nhis_number),
                detailRow('Address', s.current_address),
                detailRow('Previous School', s.previous_school_name),
            ]),
        ]));
        if (!data.documents.length) {
            $('#tabDocuments').html(tabEmpty('ri-file-upload-line', 'No documents uploaded.'));
        } else {
            let docs = '<div class="sca-detail-grid">';
            data.documents.forEach(function (d) {
                docs += '<a href="' + d.url + '" target="_blank" class="sca-doc-card"><span class="sca-doc-icon"><i class="ri-file-text-line"></i></span><span><span class="fw-semibold text-sm d-block">' + escapeHtml(d.name) + '</span><span class="text-xs text-secondary-light">Click to download</span></span></a>';
            });
            $('#tabDocuments').html(docs + '</div>');
        }
        renderBillsTab(data);
        $('#tabDormitory').html(detailGrid([
            detailCard('Boarding', 'ri-hotel-bed-line', [
                detailRow('House', s.house_name),
                detailRow('Dormitory', s.dormitory_name),
                detailRow('Bed', s.bed_label),
            ]),
        ]));
        switchTab(activeTab);
    }

    function renderBillsTab(data) {
        const summary = data.bill_summary, badge = $('#billsTabBadge');
        summary.outstanding_count > 0 ? badge.removeClass('d-none').text(summary.outstanding_count) : badge.addClass('d-none');
        if (!data.bills.length) { $('#tabBills').html(tabEmpty('ri-bill-line', 'No bills recorded.')); return; }
        let html = '<div class="sca-bills-card"><div class="sca-bill-summary">';
        html += '<div class="sca-bill-sum-card due"><div class="text-xs text-secondary-light mb-4">Total Due</div><div class="fw-bold fs-5">₵' + formatMoney(summary.total_due) + '</div></div>';
        html += '<div class="sca-bill-sum-card paid"><div class="text-xs text-secondary-light mb-4">Total Paid</div><div class="fw-bold fs-5 text-success-600">₵' + formatMoney(summary.total_paid) + '</div></div>';
        html += '<div class="sca-bill-sum-card balance"><div class="text-xs text-secondary-light mb-4">Balance</div><div class="fw-bold fs-5 text-danger-600">₵' + formatMoney(summary.balance) + '</div></div></div>';
        html += '<div class="sca-bills-table table-responsive"><table class="table bordered-table mb-0"><thead><tr><th>Item</th><th>Period</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead><tbody>';
        data.bills.forEach(function (b) {
            const sc = b.status === 'Paid' ? 'bill-status-paid' : (b.status === 'Partial' ? 'bill-status-partial' : 'bill-status-pending');
            const tb = b.is_compulsory ? '<span class="type-badge-compulsory ms-4">Compulsory</span>' : '<span class="type-badge-optional ms-4">Optional</span>';
            html += '<tr><td class="fw-semibold text-sm">' + escapeHtml(b.item_name) + tb + '</td><td class="text-secondary-light text-sm">' + escapeHtml((b.year_name||'')+' · '+(b.term_name||'')) + '</td><td class="text-sm">₵' + formatMoney(b.amount_due) + '</td><td class="text-sm">₵' + formatMoney(b.amount_paid) + '</td><td class="fw-semibold text-sm">₵' + formatMoney(b.balance) + '</td><td><span class="' + sc + '">' + escapeHtml(b.status) + '</span></td></tr>';
        });
        $('#tabBills').html(html + '</tbody></table></div></div>');
    }

    function setAssignmentFields(student) {
        $('#assignYear').val(student.academic_year_id || defaultAcademicYearId || '');
        $('#assignTerm').val(student.academic_term_id || defaultAcademicTermId || '');
        $('#assignClass').val(student.school_class_id || '');
        loadBillPreview();
    }

    function loadBillPreview() {
        const classId = $('#assignClass').val(), yearId = $('#assignYear').val(), termId = $('#assignTerm').val();
        const preview = $('#billPreview'), setupLink = $('#setupLink');
        if (!classId || !yearId || !termId) { preview.addClass('d-none').removeClass('is-found is-missing').empty(); setupLink.addClass('d-none'); return; }
        preview.removeClass('d-none is-found is-missing').html('<div class="text-sm text-secondary-light"><span class="sca-spinner d-inline-block" style="width:16px;height:16px;border-width:2px;vertical-align:middle;margin-right:6px;"></span> Checking bill setup...</div>');
        $.get(previewUrl, { school_class_id: classId, academic_year_id: yearId, academic_term_id: termId }).done(function (data) {
            preview.removeClass('is-found is-missing');
            if (data.setup_found) {
                preview.addClass('is-found');
                let html = '<div class="fw-semibold text-success-600 mb-10"><i class="ri-checkbox-circle-line"></i> Bills to inherit · ' + escapeHtml(data.category_name) + '</div>';
                data.items.forEach(function (item) {
                    html += '<div class="sca-preview-row"><span>' + escapeHtml(item.name) + (item.is_compulsory ? ' <span class="type-badge-compulsory">Compulsory</span>' : '') + '</span><span class="fw-semibold">₵' + formatMoney(item.amount) + '</span></div>';
                });
                html += '<div class="sca-preview-row mt-4 pt-8" style="border-top:1px solid rgba(34,197,94,.2);"><span class="fw-bold">Total</span><span class="fw-bold text-success-600">₵' + formatMoney(data.total) + '</span></div>';
                preview.html(html); setupLink.addClass('d-none');
            } else {
                preview.addClass('is-missing');
                preview.html('<div class="fw-semibold text-warning-600 mb-4"><i class="ri-error-warning-line"></i> No bill setup</div><p class="text-sm mb-0">' + escapeHtml(data.message || 'No bills will be created.') + '</p>');
                setupLink.removeClass('d-none');
            }
        });
    }

    function renderSearchResults(students) {
        const container = $('#searchResults'), count = $('#resultCount');
        if (!students.length) {
            count.addClass('d-none');
            container.addClass('is-empty-state').html('<div class="sca-empty-search">No students found.</div>');
            return;
        }
        count.removeClass('d-none').text(students.length + ' found');
        container.removeClass('is-empty-state');
        let html = '';
        students.forEach(function (s) {
            const sel = selectedStudentId === s.id ? ' is-selected' : '';
            const photo = s.picture ? '<img src="' + s.picture + '" alt="">' : escapeHtml(s.initials);
            html += '<div class="sca-result-card' + sel + '" data-id="' + s.id + '">'
                + '<span class="sca-avatar">' + photo + '</span>'
                + '<div class="flex-grow-1 min-w-0">'
                + '<div class="fw-semibold text-truncate text-sm">' + escapeHtml(s.full_name) + '</div>'
                + '<div class="text-xs text-secondary-light">' + escapeHtml(s.student_id) + '</div>'
                + '<div class="text-xs mt-2 text-primary-600 fw-medium text-truncate">' + escapeHtml(s.class_name) + '</div>'
                + '</div>'
                + (sel ? '<i class="ri-check-line text-primary-600 flex-shrink-0"></i>' : '')
                + '</div>';
        });
        container.html(html);
    }

    function runSearch() {
        $.get(searchUrl, { q: $('#studentSearchInput').val().trim(), status: $('#filterStatus').val(), school_class_id: $('#filterClass').val() })
            .done(function (data) { renderSearchResults(data.students || []); });
    }

    function loadStudent(id) {
        selectedStudentId = id;
        setLoading(true);
        $.get(showUrlTemplate.replace('__ID__', id), function (data) {
            currentStudent = data;
            $('#workspaceEmpty').addClass('d-none');
            $('#workspaceContent').removeClass('d-none').addClass('d-flex');
            renderHero(data.student);
            renderTabs(data);
            setAssignmentFields(data.student);
            runSearch();
        }).fail(function () { showAppToast('error', 'Unable to load student profile.'); })
        .always(function () { setLoading(false); });
    }

    $('#pillTabs').on('click', '.sca-pill-tab', function () { switchTab($(this).data('tab')); });
    $('#studentSearchInput, #filterStatus, #filterClass').on('input change', function () { clearTimeout(searchTimer); searchTimer = setTimeout(runSearch, 300); });
    $('body').on('click', '.sca-result-card', function () { loadStudent(parseInt($(this).data('id'), 10)); });
    $('#assignYear, #assignTerm, #assignClass').on('change', loadBillPreview);

    $('#assignBtn').on('click', function () {
        if (!selectedStudentId) return;
        const payload = { _token: csrfToken, student_id: selectedStudentId, school_class_id: $('#assignClass').val(), academic_year_id: $('#assignYear').val(), academic_term_id: $('#assignTerm').val() };
        if (!payload.school_class_id || !payload.academic_year_id || !payload.academic_term_id) { showAppToast('error', 'Select academic year, term, and class.'); return; }
        const $btn = $(this).prop('disabled', true).html('<span class="sca-spinner d-inline-block" style="width:14px;height:14px;border-width:2px;"></span> Assigning...');
        $.ajax({ url: assignUrl, method: 'POST', data: payload, headers: { Accept: 'application/json' } })
            .done(function (res) { showAppToast('success', res.message || 'Student assigned.'); loadStudent(selectedStudentId); })
            .fail(function (xhr) { showAppToast('error', (xhr.responseJSON || {}).message || 'Unable to assign student.'); })
            .always(function () { $btn.prop('disabled', false).html('<i class="ri-check-double-line"></i> Assign & Sync Bills'); });
    });

    function switchAssignmentMode(mode) {
        $('.sca-mode-tab').removeClass('is-active');
        $('.sca-mode-tab[data-mode="' + mode + '"]').addClass('is-active');
        if (mode === 'bulk') {
            $('#singleAssignmentPanel').addClass('d-none');
            $('#bulkAssignmentPanel').removeClass('d-none');
            if (!bulkStudents.length) {
                loadBulkCandidates();
            }
        } else {
            $('#bulkAssignmentPanel').addClass('d-none');
            $('#singleAssignmentPanel').removeClass('d-none');
        }
    }

    function updateBulkSelectedCount() {
        $('#bulkSelectedCount').text(bulkSelectedIds.size + ' selected');
        $('#bulkSelectAll').prop('checked', bulkStudents.length > 0 && bulkSelectedIds.size === bulkStudents.length);
    }

    function renderBulkStudents(students) {
        bulkStudents = students || [];
        bulkSelectedIds = new Set(bulkStudents.map(function (s) { return s.id; }));
        updateBulkSelectedCount();
        $('#bulkLoadedSummary').text(bulkStudents.length + ' student(s) loaded.');

        if (!bulkStudents.length) {
            $('#bulkStudentsBody').html('<tr><td colspan="5" class="text-center py-40 text-secondary-light text-sm">No students match your filters.</td></tr>');
            return;
        }

        let html = '';
        bulkStudents.forEach(function (s) {
            const checked = bulkSelectedIds.has(s.id) ? ' checked' : '';
            html += '<tr class="bulk-student-row' + (checked ? ' is-selected' : '') + '" data-id="' + s.id + '">'
                + '<td><input type="checkbox" class="form-check-input bulk-student-check m-0" value="' + s.id + '"' + checked + '></td>'
                + '<td class="fw-semibold text-sm">' + escapeHtml(s.full_name) + '</td>'
                + '<td class="text-sm text-secondary-light">' + escapeHtml(s.student_id) + '</td>'
                + '<td class="text-sm">' + escapeHtml(s.class_name || 'Unassigned') + '</td>'
                + '<td>' + statusBadge(s.status) + '</td>'
                + '</tr>';
        });
        $('#bulkStudentsBody').html(html);
    }

    function loadBulkBillPreview() {
        const classId = $('#bulkAssignClass').val();
        const yearId = $('#bulkAssignYear').val();
        const termId = $('#bulkAssignTerm').val();
        const preview = $('#bulkBillPreview');

        if (!classId || !yearId || !termId) {
            preview.addClass('d-none').removeClass('is-found is-missing').empty();
            return;
        }

        preview.removeClass('d-none is-found is-missing').html('<div class="text-sm text-secondary-light"><span class="sca-spinner d-inline-block" style="width:16px;height:16px;border-width:2px;vertical-align:middle;margin-right:6px;"></span> Checking bill setup...</div>');

        $.get(previewUrl, { school_class_id: classId, academic_year_id: yearId, academic_term_id: termId }).done(function (data) {
            preview.removeClass('is-found is-missing');
            if (data.setup_found) {
                preview.addClass('is-found');
                let html = '<div class="fw-semibold text-success-600 mb-10"><i class="ri-checkbox-circle-line"></i> Bills to inherit · ' + escapeHtml(data.category_name) + '</div>';
                data.items.forEach(function (item) {
                    html += '<div class="sca-preview-row"><span>' + escapeHtml(item.name) + '</span><span class="fw-semibold">₵' + formatMoney(item.amount) + '</span></div>';
                });
                html += '<div class="sca-preview-row mt-4 pt-8" style="border-top:1px solid rgba(34,197,94,.2);"><span class="fw-bold">Total per student</span><span class="fw-bold text-success-600">₵' + formatMoney(data.total) + '</span></div>';
                preview.html(html);
            } else {
                preview.addClass('is-missing');
                preview.html('<div class="fw-semibold text-warning-600 mb-4"><i class="ri-error-warning-line"></i> No bill setup</div><p class="text-sm mb-0">' + escapeHtml(data.message || 'No bills will be created.') + '</p>');
            }
        });
    }

    function loadBulkCandidates() {
        setLoading(true);
        $.get(bulkCandidatesUrl, {
            q: $('#bulkSearchInput').val().trim(),
            school_class_id: $('#bulkFilterClass').val(),
            unassigned_only: $('#bulkUnassignedOnly').is(':checked') ? 1 : 0,
        }).done(function (data) {
            renderBulkStudents(data.students || []);
        }).fail(function () {
            showAppToast('error', 'Unable to load students for bulk assignment.');
        }).always(function () {
            setLoading(false);
        });
    }

    $('.sca-mode-tab').on('click', function () {
        switchAssignmentMode($(this).data('mode'));
    });

    if (defaultAcademicYearId) {
        $('#bulkAssignYear').val(String(defaultAcademicYearId));
    }
    if (defaultAcademicTermId) {
        $('#bulkAssignTerm').val(String(defaultAcademicTermId));
    }

    $('#bulkAssignYear, #bulkAssignTerm, #bulkAssignClass').on('change', loadBulkBillPreview);
    $('#bulkLoadBtn').on('click', loadBulkCandidates);
    $('#bulkSearchInput, #bulkFilterClass, #bulkUnassignedOnly').on('change input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadBulkCandidates, 350);
    });

    $('#bulkSelectAll').on('change', function () {
        const checked = $(this).is(':checked');
        bulkSelectedIds = checked ? new Set(bulkStudents.map(function (s) { return s.id; })) : new Set();
        $('.bulk-student-check').prop('checked', checked);
        $('.bulk-student-row').toggleClass('is-selected', checked);
        updateBulkSelectedCount();
    });

    $('body').on('change', '.bulk-student-check', function () {
        const id = parseInt($(this).val(), 10);
        if ($(this).is(':checked')) {
            bulkSelectedIds.add(id);
            $(this).closest('tr').addClass('is-selected');
        } else {
            bulkSelectedIds.delete(id);
            $(this).closest('tr').removeClass('is-selected');
        }
        updateBulkSelectedCount();
    });

    $('#bulkClearSelectionBtn').on('click', function () {
        bulkSelectedIds.clear();
        $('.bulk-student-check').prop('checked', false);
        $('.bulk-student-row').removeClass('is-selected');
        updateBulkSelectedCount();
    });

    $('#bulkAssignBtn').on('click', function () {
        const studentIds = Array.from(bulkSelectedIds);
        const payload = {
            _token: csrfToken,
            student_ids: studentIds,
            school_class_id: $('#bulkAssignClass').val(),
            academic_year_id: $('#bulkAssignYear').val(),
            academic_term_id: $('#bulkAssignTerm').val(),
        };

        if (!studentIds.length) {
            showAppToast('error', 'Select at least one student.');
            return;
        }
        if (!payload.school_class_id || !payload.academic_year_id || !payload.academic_term_id) {
            showAppToast('error', 'Select academic year, term, and target class.');
            return;
        }

        const className = $('#bulkAssignClass option:selected').text().trim();
        if (!confirm('Assign ' + studentIds.length + ' student(s) to ' + className + '?')) {
            return;
        }

        const $btn = $(this).prop('disabled', true).html('<span class="sca-spinner d-inline-block" style="width:14px;height:14px;border-width:2px;"></span> Assigning...');
        $.ajax({
            url: bulkAssignUrl,
            method: 'POST',
            data: payload,
            headers: { Accept: 'application/json' },
        }).done(function (res) {
            showAppToast('success', res.message || 'Students assigned.');
            loadBulkCandidates();
        }).fail(function (xhr) {
            showAppToast('error', (xhr.responseJSON || {}).message || 'Unable to bulk assign students.');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="ri-check-double-line"></i> Assign Selected Students');
        });
    });
</script>
@endsection
