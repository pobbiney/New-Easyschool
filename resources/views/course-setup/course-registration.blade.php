@php $pageName = "course"; $subpageName = "course-registration"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .registration-hero {
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12) 0%, rgba(99, 102, 241, 0.08) 100%);
        border: 1px solid rgba(37, 161, 148, 0.15);
        margin-bottom: 24px;
    }

    .registration-hero-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-600, #25A194);
        color: #fff;
        font-size: 24px;
        flex-shrink: 0;
    }

    .registration-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        background: rgba(37, 161, 148, 0.12);
        overflow: hidden;
    }

    .registration-progress-bar.is-active {
        opacity: 1;
    }

    .registration-progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 35%;
        background: linear-gradient(90deg, var(--primary-600, #25A194), #6366f1);
        animation: registrationProgressSlide 1.1s ease-in-out infinite;
    }

    @keyframes registrationProgressSlide {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(320%); }
    }

    .registration-filter-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px;
        background: var(--white, #fff);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .filter-field {
        position: relative;
    }

    .filter-field-icon {
        position: absolute;
        left: 14px;
        top: 38px;
        color: var(--primary-600, #25A194);
        font-size: 18px;
        pointer-events: none;
        z-index: 2;
    }

    .filter-field .form-select {
        padding-left: 42px;
        border-radius: 12px;
        min-height: 46px;
        border-color: var(--neutral-200, #e5e7eb);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .filter-field .form-select:focus,
    .filter-field .form-select.is-selected {
        border-color: rgba(37, 161, 148, 0.45);
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.1);
    }

    .filter-field .form-select.is-loading {
        opacity: 0.65;
        pointer-events: none;
    }

    .selection-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed var(--neutral-200, #e5e7eb);
    }

    .selection-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: var(--neutral-50, #f9fafb);
        border: 1px solid var(--neutral-200, #e5e7eb);
        font-size: 13px;
        font-weight: 600;
        color: var(--neutral-600, #4b5563);
    }

    .selection-chip.is-ready {
        background: rgba(37, 161, 148, 0.08);
        border-color: rgba(37, 161, 148, 0.2);
        color: var(--primary-600, #25A194);
    }

    .selection-chip i {
        font-size: 15px;
    }

    .registration-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 20px 22px;
        background: var(--white, #fff);
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .registration-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .registration-stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .registration-progress-ring {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: conic-gradient(var(--primary-600, #25A194) calc(var(--progress, 0) * 1%), var(--neutral-100, #f3f4f6) 0);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .registration-progress-ring::after {
        content: attr(data-label);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: var(--primary-600, #25A194);
    }

    .registration-list-wrapper {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--neutral-200, #e5e7eb);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .registration-list-wrapper .card-header {
        background: linear-gradient(180deg, #fff 0%, var(--neutral-50, #f9fafb) 100%);
    }

    .registration-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .registration-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-600, #4b5563);
    }

    .registration-status-badge.is-loading {
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .registration-status-badge.is-ready {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .registration-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
        min-height: 280px;
    }

    .registration-list-wrapper table.dataTable {
        min-width: 860px;
    }

    .registration-table-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(2px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 5;
        flex-direction: column;
        gap: 12px;
    }

    .registration-table-overlay.is-visible {
        display: flex;
    }

    .registration-spinner {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 3px solid rgba(37, 161, 148, 0.15);
        border-top-color: var(--primary-600, #25A194);
        animation: registrationSpin 0.8s linear infinite;
    }

    @keyframes registrationSpin {
        to { transform: rotate(360deg); }
    }

    .course-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .course-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .course-registration-row {
        transition: background-color 0.15s ease;
    }

    .course-registration-row:hover {
        background: rgba(37, 161, 148, 0.03);
    }

    .course-registration-row.is-registered {
        background: rgba(34, 197, 94, 0.04);
    }

    .parent-hint {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #6366f1;
        font-weight: 500;
    }

    .type-badge,
    .category-badge,
    .registered-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .type-badge {
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
    }

    .category-badge {
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-600, #4b5563);
    }

    .registered-badge {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
        gap: 4px;
    }

    .btn-pill {
        border-radius: 999px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 108px;
        justify-content: center;
    }

    .btn-pill-primary {
        background: var(--primary-600, #25A194);
        border: 1px solid var(--primary-600, #25A194);
        color: #fff;
    }

    .btn-pill-primary:hover:not(:disabled) {
        background: var(--primary-700, #1f8a7f);
        border-color: var(--primary-700, #1f8a7f);
        color: #fff;
    }

    .btn-pill-danger {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #dc2626;
    }

    .btn-pill-danger:hover:not(:disabled) {
        background: rgba(239, 68, 68, 0.14);
        color: #b91c1c;
    }

    .btn-pill:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid currentColor;
        border-top-color: transparent;
        animation: registrationSpin 0.7s linear infinite;
        display: inline-block;
    }

    .registration-empty-state {
        text-align: center;
        padding: 56px 24px;
        color: var(--neutral-500, #6b7280);
    }

    .registration-empty-state-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 16px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-400, #9ca3af);
        font-size: 32px;
    }

    .registration-empty-state h6 {
        color: var(--neutral-700, #374151);
        margin-bottom: 6px;
    }

    .skeleton-row td {
        padding-top: 18px;
        padding-bottom: 18px;
    }

    .skeleton-block {
        height: 14px;
        border-radius: 999px;
        background: linear-gradient(90deg, #eef2f7 25%, #f8fafc 50%, #eef2f7 75%);
        background-size: 200% 100%;
        animation: skeletonShimmer 1.2s ease-in-out infinite;
    }

    .skeleton-block.w-70 { width: 70%; }
    .skeleton-block.w-50 { width: 50%; }
    .skeleton-block.w-40 { width: 40%; }
    .skeleton-block.w-30 { width: 30%; }
    .skeleton-circle {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(90deg, #eef2f7 25%, #f8fafc 50%, #eef2f7 75%);
        background-size: 200% 100%;
        animation: skeletonShimmer 1.2s ease-in-out infinite;
    }

    @keyframes skeletonShimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .stat-value-loading {
        opacity: 0.35;
    }

    .registration-mode-tabs {
        display: inline-flex;
        gap: 8px;
        padding: 6px;
        border-radius: 14px;
        background: var(--neutral-100, #f3f4f6);
        border: 1px solid var(--neutral-200, #e5e7eb);
        margin-bottom: 24px;
    }

    .registration-mode-tab {
        border: none;
        background: transparent;
        color: var(--neutral-600, #4b5563);
        font-size: 14px;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .registration-mode-tab.is-active {
        background: #fff;
        color: var(--primary-600, #25A194);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .registration-mode-tab .tab-count {
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: rgba(37, 161, 148, 0.12);
        color: var(--primary-600, #25A194);
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .view-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed var(--neutral-200, #e5e7eb);
    }

    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-600, #4b5563);
    }

    .meta-badge.class-badge {
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
    }

    .meta-badge.term-badge {
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .meta-badge.year-badge {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .teacher-name-cell {
        display: flex;
        align-items: center;
    }

    .teacher-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px 4px 4px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .teacher-pill-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 10px;
        flex-shrink: 0;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.55);
    }

    .teacher-pill-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .teacher-pill-0 {
        background: rgba(37, 161, 148, 0.14);
        color: #0f766e;
    }

    .teacher-pill-1 {
        background: rgba(99, 102, 241, 0.14);
        color: #4338ca;
    }

    .teacher-pill-2 {
        background: rgba(236, 72, 153, 0.14);
        color: #be185d;
    }

    .teacher-pill-3 {
        background: rgba(245, 158, 11, 0.16);
        color: #b45309;
    }

    .teacher-pill-4 {
        background: rgba(59, 130, 246, 0.14);
        color: #1d4ed8;
    }

    .teacher-pill-5 {
        background: rgba(168, 85, 247, 0.14);
        color: #7e22ce;
    }

    .teacher-pill-6 {
        background: rgba(239, 68, 68, 0.14);
        color: #b91c1c;
    }

    .teacher-pill-7 {
        background: rgba(20, 184, 166, 0.14);
        color: #0d9488;
    }

    .no-teacher-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-500, #6b7280);
    }
</style>
@endsection

@section('content')

<div id="registrationProgressBar" class="registration-progress-bar" aria-hidden="true"></div>

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">COURSE SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('add-course') }}" class="text-secondary-light hover-text-primary hover-underline"> / Courses</a>
                <span class="text-secondary-light"> / Course Registration</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('course-teacher-assignment') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-user-follow-line"></i>
                Course Teachers
            </a>
            <a href="{{ route('add-course') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-book-open-line"></i>
                Manage Courses
            </a>
        </div>
    </div>

    <div class="registration-hero d-flex align-items-start gap-16">
        <span class="registration-hero-icon"><i class="ri-clipboard-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Course Registration</h5>
            <p class="text-sm text-secondary-light mb-0">Register courses for a class, term, and year — or browse everything already registered using flexible filters.</p>
        </div>
    </div>

    <div class="registration-mode-tabs" role="tablist" aria-label="Course registration modes">
        <button type="button" class="registration-mode-tab is-active" data-mode="register" role="tab" aria-selected="true">
            <i class="ri-add-circle-line"></i>
            Register Courses
        </button>
        <button type="button" class="registration-mode-tab" data-mode="view" role="tab" aria-selected="false">
            <i class="ri-list-check-2"></i>
            View Registrations
            <span class="tab-count">{{ $totalSavedRegistrations }}</span>
        </button>
    </div>

    <div id="registerCoursesPanel">
    <div class="registration-filter-card mb-24">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-16">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Registration Filters</h6>
                <p class="text-sm text-secondary-light mb-0">All three selections are required before courses appear.</p>
            </div>
            <span id="registrationStatusBadge" class="registration-status-badge">
                <i class="ri-information-line"></i>
                Waiting for selections
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="filter-field">
                    <label for="filter_school_class_id" class="form-label text-sm fw-medium">Class</label>
                    <i class="ri-group-line filter-field-icon"></i>
                    <select id="filter_school_class_id" class="form-select registration-filter" data-filter-label="Class">
                        <option value="">Select class</option>
                        @foreach($schoolClasses as $schoolClass)
                            <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-field">
                    <label for="filter_academic_term_id" class="form-label text-sm fw-medium">Academic Term</label>
                    <i class="ri-calendar-event-line filter-field-icon"></i>
                    <select id="filter_academic_term_id" class="form-select registration-filter" data-filter-label="Term">
                        <option value="">Select term</option>
                        @foreach($academicTerms as $academicTerm)
                            <option value="{{ $academicTerm->id }}">{{ $academicTerm->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-field">
                    <label for="filter_academic_year_id" class="form-label text-sm fw-medium">Academic Year</label>
                    <i class="ri-calendar-2-line filter-field-icon"></i>
                    <select id="filter_academic_year_id" class="form-select registration-filter" data-filter-label="Year">
                        <option value="">Select year</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div id="selectionSummary" class="selection-summary">
            <span class="selection-chip" data-chip="class"><i class="ri-group-line"></i><span>Class: Not selected</span></span>
            <span class="selection-chip" data-chip="term"><i class="ri-calendar-event-line"></i><span>Term: Not selected</span></span>
            <span class="selection-chip" data-chip="year"><i class="ri-calendar-2-line"></i><span>Year: Not selected</span></span>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="registration-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Courses</p>
                        <h4 class="fw-semibold mb-0" id="stat_total">0</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="registration-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Registered</p>
                        <h4 class="fw-semibold mb-0 text-success-600" id="stat_registered">0</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="registration-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Unregistered</p>
                        <h4 class="fw-semibold mb-0 text-warning-600" id="stat_unregistered">0</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="registration-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Completion</p>
                        <h4 class="fw-semibold mb-0" id="stat_completion">0%</h4>
                    </div>
                    <span id="stat_progress_ring" class="registration-progress-ring" style="--progress: 0;" data-label="0%"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 registration-list-wrapper">
        <div class="card-header border-bottom py-16 px-24">
            <div class="registration-toolbar">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">Available Courses</h6>
                    <p class="text-sm text-secondary-light mb-0">Register or remove courses for the current selection.</p>
                </div>
                <form class="navbar-search dt-search m-0">
                    <input type="text" id="registrationSearchInput" class="dt-input bg-transparent radius-4" aria-controls="registrationTable" name="search" placeholder="Search courses..." disabled>
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="registration-list-scroll p-0">
                <div id="registrationTableOverlay" class="registration-table-overlay" aria-hidden="true">
                    <div class="registration-spinner"></div>
                    <p class="text-sm fw-medium text-secondary-light mb-0">Loading courses...</p>
                </div>
                <table class="table bordered-table mb-0" id="registrationTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Course / Sub-Course</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="courseRegistrationTableBody">
                        <tr id="courseRegistrationPlaceholder">
                            <td colspan="5">
                                <div class="registration-empty-state">
                                    <div class="registration-empty-state-icon"><i class="ri-filter-3-line"></i></div>
                                    <h6 class="fw-semibold">Start by selecting filters</h6>
                                    <p class="mb-0 text-sm">Pick a class, academic term, and year above to load the course list.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <div id="viewRegistrationsPanel" class="d-none">
        <div class="registration-filter-card mb-24">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-16">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">Browse Registered Courses</h6>
                    <p class="text-sm text-secondary-light mb-0">Select one or more filters to view saved registrations. Leave a filter on "All" to include every value for that field.</p>
                </div>
                <span id="viewRegistrationStatusBadge" class="registration-status-badge">
                    <i class="ri-information-line"></i>
                    Select at least one filter
                </span>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="filter-field">
                        <label for="view_filter_school_class_id" class="form-label text-sm fw-medium">Class</label>
                        <i class="ri-group-line filter-field-icon"></i>
                        <select id="view_filter_school_class_id" class="form-select view-registration-filter">
                            <option value="">All classes</option>
                            @foreach($schoolClasses as $schoolClass)
                                <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="filter-field">
                        <label for="view_filter_academic_term_id" class="form-label text-sm fw-medium">Academic Term</label>
                        <i class="ri-calendar-event-line filter-field-icon"></i>
                        <select id="view_filter_academic_term_id" class="form-select view-registration-filter">
                            <option value="">All terms</option>
                            @foreach($academicTerms as $academicTerm)
                                <option value="{{ $academicTerm->id }}">{{ $academicTerm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="filter-field">
                        <label for="view_filter_academic_year_id" class="form-label text-sm fw-medium">Academic Year</label>
                        <i class="ri-calendar-2-line filter-field-icon"></i>
                        <select id="view_filter_academic_year_id" class="form-select view-registration-filter">
                            <option value="">All years</option>
                            @foreach($academicYears as $academicYear)
                                <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="view-filter-actions">
                <button type="button" id="viewRegistrationsSearchBtn" class="btn btn-primary-600 d-inline-flex align-items-center gap-6">
                    <i class="ri-search-line"></i>
                    View Registrations
                </button>
                <button type="button" id="viewRegistrationsResetBtn" class="btn btn-outline-secondary d-inline-flex align-items-center gap-6">
                    <i class="ri-restart-line"></i>
                    Reset Filters
                </button>
            </div>
        </div>

        <div class="row gy-4 mb-24">
            <div class="col-sm-6 col-xl-4">
                <div class="registration-stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <p class="text-secondary-light text-sm mb-4">Matching Registrations</p>
                            <h4 class="fw-semibold mb-0" id="view_stat_total">0</h4>
                        </div>
                        <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-list-check-2"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="registration-stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <p class="text-secondary-light text-sm mb-4">Saved in System</p>
                            <h4 class="fw-semibold mb-0 text-success-600">{{ $totalSavedRegistrations }}</h4>
                        </div>
                        <span class="stat-icon bg-success-100 text-success-600"><i class="ri-database-2-line"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="registration-stat-card">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <p class="text-secondary-light text-sm mb-4">Active Filters</p>
                            <h4 class="fw-semibold mb-0" id="view_stat_filters">0</h4>
                        </div>
                        <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-filter-3-line"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card h-100 registration-list-wrapper">
            <div class="card-header border-bottom py-16 px-24">
                <div class="registration-toolbar">
                    <div>
                        <h6 class="text-lg fw-semibold mb-4">Registered Courses</h6>
                        <p class="text-sm text-secondary-light mb-0">All courses currently registered for your selected filters.</p>
                    </div>
                    <form class="navbar-search dt-search m-0">
                        <input type="text" id="viewRegistrationSearchInput" class="dt-input bg-transparent radius-4" aria-controls="viewRegistrationTable" name="search" placeholder="Search registrations..." disabled>
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>
                </div>
            </div>
            <div class="card-body p-0 dataTable-wrapper">
                <div class="registration-list-scroll p-0">
                    <div id="viewRegistrationTableOverlay" class="registration-table-overlay" aria-hidden="true">
                        <div class="registration-spinner"></div>
                        <p class="text-sm fw-medium text-secondary-light mb-0">Loading registrations...</p>
                    </div>
                    <table class="table bordered-table mb-0" id="viewRegistrationTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th>Course / Sub-Course</th>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Term</th>
                                <th>Year</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody id="viewRegistrationTableBody">
                            <tr id="viewRegistrationPlaceholder">
                                <td colspan="6">
                                    <div class="registration-empty-state">
                                        <div class="registration-empty-state-icon"><i class="ri-search-line"></i></div>
                                        <h6 class="fw-semibold">Search registered courses</h6>
                                        <p class="mb-0 text-sm">Choose class, term, and/or year above, then click View Registrations.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="courseRegistrationForm" class="d-none">
    @csrf
    <input type="hidden" name="school_class_id" id="registration_school_class_id">
    <input type="hidden" name="academic_term_id" id="registration_academic_term_id">
    <input type="hidden" name="academic_year_id" id="registration_academic_year_id">
</form>

@endsection

@section('scripts')
<script>
    (function () {
        const coursesUrl = @json(route('course-registration-courses'));
        const registeredUrl = @json(route('course-registration-registered'));
        const registerUrl = @json(route('course-registration-register'));
        const unregisterUrl = @json(route('course-registration-unregister'));
        const csrfToken = @json(csrf_token());
        const defaultAcademicYearId = @json($defaultAcademicYearId);
        const defaultAcademicTermId = @json($defaultAcademicTermId);

        let registrationTable = null;
        let viewRegistrationTable = null;
        let activeRequest = null;
        let activeViewRequest = null;

        const $progressBar = $('#registrationProgressBar');
        const $tableOverlay = $('#registrationTableOverlay');
        const $statusBadge = $('#registrationStatusBadge');
        const $searchInput = $('#registrationSearchInput');
        const $viewTableOverlay = $('#viewRegistrationTableOverlay');
        const $viewStatusBadge = $('#viewRegistrationStatusBadge');
        const $viewSearchInput = $('#viewRegistrationSearchInput');

        function showToast(type, message) {
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(type, message);
            }
        }

        function setLoading(isLoading) {
            $progressBar.toggleClass('is-active', isLoading);
            $tableOverlay.toggleClass('is-visible', isLoading);
            $tableOverlay.attr('aria-hidden', isLoading ? 'false' : 'true');
            $('.registration-filter').toggleClass('is-loading', isLoading);
            $searchInput.prop('disabled', isLoading || !allFiltersSelected());
            $('.stat-value-loading').removeClass('stat-value-loading');

            if (isLoading) {
                $statusBadge
                    .removeClass('is-ready')
                    .addClass('is-loading')
                    .html('<span class="btn-spinner"></span> Loading courses...');
                $('#stat_total, #stat_registered, #stat_unregistered, #stat_completion').addClass('stat-value-loading');
            } else if (allFiltersSelected()) {
                $statusBadge
                    .removeClass('is-loading')
                    .addClass('is-ready')
                    .html('<i class="ri-check-line"></i> Courses loaded');
            } else {
                $statusBadge
                    .removeClass('is-loading is-ready')
                    .html('<i class="ri-information-line"></i> Waiting for selections');
            }
        }

        function getFilterValues() {
            return {
                school_class_id: $('#filter_school_class_id').val(),
                academic_term_id: $('#filter_academic_term_id').val(),
                academic_year_id: $('#filter_academic_year_id').val(),
            };
        }

        function allFiltersSelected() {
            const filters = getFilterValues();
            return filters.school_class_id && filters.academic_term_id && filters.academic_year_id;
        }

        function syncHiddenFilterFields() {
            const filters = getFilterValues();
            $('#registration_school_class_id').val(filters.school_class_id);
            $('#registration_academic_term_id').val(filters.academic_term_id);
            $('#registration_academic_year_id').val(filters.academic_year_id);
        }

        function updateSelectionSummary() {
            const mappings = [
                { id: '#filter_school_class_id', chip: 'class', label: 'Class' },
                { id: '#filter_academic_term_id', chip: 'term', label: 'Term' },
                { id: '#filter_academic_year_id', chip: 'year', label: 'Year' },
            ];

            mappings.forEach(function (item) {
                const $select = $(item.id);
                const text = $select.find('option:selected').text().trim();
                const hasValue = !!$select.val();
                const $chip = $('[data-chip="' + item.chip + '"]');

                $select.toggleClass('is-selected', hasValue);
                $chip.toggleClass('is-ready', hasValue);
                $chip.find('span').text(item.label + ': ' + (hasValue ? text : 'Not selected'));
            });
        }

        function updateStats(stats) {
            const total = stats.total || 0;
            const registered = stats.registered || 0;
            const unregistered = stats.unregistered || 0;
            const completion = total > 0 ? Math.round((registered / total) * 100) : 0;

            $('#stat_total').text(total);
            $('#stat_registered').text(registered);
            $('#stat_unregistered').text(unregistered);
            $('#stat_completion').text(completion + '%');

            $('#stat_progress_ring')
                .css('--progress', completion)
                .attr('data-label', completion + '%');
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function buttonLoadingHtml(label) {
            return '<span class="btn-spinner"></span> ' + label;
        }

        function buildActionButton(course) {
            if (course.is_registered) {
                return (
                    '<button type="button" class="btn btn-pill btn-pill-danger course-registration-toggle-btn"' +
                        ' data-action="remove"' +
                        ' data-course-id="' + course.id + '"' +
                        ' data-registration-id="' + course.registration_id + '">' +
                        '<i class="ri-subtract-line"></i> Remove' +
                    '</button>'
                );
            }

            return (
                '<button type="button" class="btn btn-pill btn-pill-primary course-registration-toggle-btn"' +
                    ' data-action="add"' +
                    ' data-course-id="' + course.id + '">' +
                    '<i class="ri-add-line"></i> Add' +
                '</button>'
            );
        }

        function buildCourseRow(course) {
            const parentHint = course.parent_name
                ? '<span class="parent-hint">' + escapeHtml(course.parent_name) + '</span>'
                : '';

            const statusBadge = course.is_registered
                ? '<span class="registered-badge"><i class="ri-check-line"></i> Registered</span>'
                : '<span class="category-badge">Not registered</span>';

            return (
                '<tr class="course-registration-row' + (course.is_registered ? ' is-registered' : '') + '" data-course-id="' + course.id + '">' +
                    '<td>' +
                        '<div class="course-name-cell">' +
                            '<span class="course-avatar"><i class="ri-book-open-line"></i></span>' +
                            '<div>' +
                                '<span class="fw-medium">' + escapeHtml(course.name) + '</span>' +
                                parentHint +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td><span class="type-badge">' + (course.is_sub_course ? 'Sub-Course' : 'Course') + '</span></td>' +
                    '<td><span class="category-badge">' + escapeHtml(course.category || '—') + '</span></td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td class="registration-action-cell">' + buildActionButton(course) + '</td>' +
                '</tr>'
            );
        }

        function buildSkeletonRows(count) {
            let rows = '';

            for (let i = 0; i < count; i++) {
                rows +=
                    '<tr class="skeleton-row">' +
                        '<td><div class="d-flex align-items-center gap-12"><div class="skeleton-circle"></div><div class="flex-grow-1"><div class="skeleton-block w-50 mb-8"></div><div class="skeleton-block w-30"></div></div></div></td>' +
                        '<td><div class="skeleton-block w-40"></div></td>' +
                        '<td><div class="skeleton-block w-50"></div></td>' +
                        '<td><div class="skeleton-block w-40"></div></td>' +
                        '<td><div class="skeleton-block w-30"></div></td>' +
                    '</tr>';
            }

            return rows;
        }

        function buildViewSkeletonRows(count) {
            let rows = '';

            for (let i = 0; i < count; i++) {
                rows +=
                    '<tr class="skeleton-row">' +
                        '<td><div class="d-flex align-items-center gap-12"><div class="skeleton-circle"></div><div class="flex-grow-1"><div class="skeleton-block w-50 mb-8"></div><div class="skeleton-block w-30"></div></div></div></td>' +
                        '<td><div class="skeleton-block w-40"></div></td>' +
                        '<td><div class="d-flex align-items-center gap-12"><div class="skeleton-circle" style="width:32px;height:32px;border-radius:50%;"></div><div class="skeleton-block w-50"></div></div></td>' +
                        '<td><div class="skeleton-block w-40"></div></td>' +
                        '<td><div class="skeleton-block w-30"></div></td>' +
                        '<td><div class="skeleton-block w-40"></div></td>' +
                    '</tr>';
            }

            return rows;
        }

        function showPlaceholder(title, message, icon) {
            destroyDataTable();
            $searchInput.prop('disabled', true).val('');

            $('#courseRegistrationTableBody').html(
                '<tr id="courseRegistrationPlaceholder">' +
                    '<td colspan="5">' +
                        '<div class="registration-empty-state">' +
                            '<div class="registration-empty-state-icon"><i class="' + icon + '"></i></div>' +
                            '<h6 class="fw-semibold">' + escapeHtml(title) + '</h6>' +
                            '<p class="mb-0 text-sm">' + escapeHtml(message) + '</p>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            );
        }

        function showLoadingSkeleton() {
            destroyDataTable();
            $('#courseRegistrationTableBody').html(buildSkeletonRows(6));
        }

        function destroyDataTable() {
            if (!registrationTable) {
                return;
            }

            registrationTable.destroy();
            registrationTable = null;
        }

        function initDataTable() {
            destroyDataTable();

            const tableEl = document.getElementById('registrationTable');
            if (!tableEl) {
                return;
            }

            registrationTable = new DataTable(tableEl, {
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: [4] }],
            });

            $searchInput
                .prop('disabled', false)
                .off('keyup.registrationTable')
                .on('keyup.registrationTable', function () {
                    registrationTable.search(this.value).draw();
                });
        }

        function renderCourses(data) {
            const courses = data.courses || [];

            updateStats(data.stats || { total: 0, registered: 0, unregistered: 0 });

            if (!courses.length) {
                showPlaceholder(
                    'No eligible subjects',
                    data.message || 'No subjects are linked to the selected class category.',
                    'ri-book-open-line'
                );
                return;
            }

            destroyDataTable();

            const rows = courses.map(buildCourseRow).join('');
            $('#courseRegistrationTableBody').html(rows);
            initDataTable();
        }

        function loadCourses() {
            updateSelectionSummary();

            if (activeRequest) {
                activeRequest.abort();
                activeRequest = null;
            }

            if (!allFiltersSelected()) {
                updateStats({ total: 0, registered: 0, unregistered: 0 });
                showPlaceholder(
                    'Start by selecting filters',
                    'Pick a class, academic term, and year above to load the course list.',
                    'ri-filter-3-line'
                );
                setLoading(false);
                return;
            }

            syncHiddenFilterFields();
            setLoading(true);
            showLoadingSkeleton();

            const filters = getFilterValues();

            activeRequest = $.ajax({
                url: coursesUrl,
                method: 'GET',
                data: filters,
                dataType: 'json',
            })
                .done(function (data) {
                    renderCourses(data);
                })
                .fail(function (xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }

                    showPlaceholder(
                        'Unable to load courses',
                        'Something went wrong while fetching courses. Please try again.',
                        'ri-error-warning-line'
                    );
                    showToast('error', 'Unable to load courses.');
                })
                .always(function () {
                    activeRequest = null;
                    setLoading(false);
                });
        }

        function updateRowState($row, courseId, registrationId, isRegistered) {
            const course = {
                id: courseId,
                is_registered: isRegistered,
                registration_id: registrationId,
            };

            $row.toggleClass('is-registered', isRegistered);
            $row.find('td').eq(3).html(
                isRegistered
                    ? '<span class="registered-badge"><i class="ri-check-line"></i> Registered</span>'
                    : '<span class="category-badge">Not registered</span>'
            );
            $row.find('.registration-action-cell').html(buildActionButton(course));
        }

        function adjustStats(deltaRegistered) {
            const total = parseInt($('#stat_total').text(), 10) || 0;
            const registered = Math.max((parseInt($('#stat_registered').text(), 10) || 0) + deltaRegistered, 0);
            const unregistered = Math.max(total - registered, 0);

            updateStats({
                total: total,
                registered: registered,
                unregistered: unregistered,
            });
        }

        $('.registration-filter').on('change', loadCourses);

        if (defaultAcademicYearId) {
            $('#filter_academic_year_id').val(String(defaultAcademicYearId));
            $('#view_filter_academic_year_id').val(String(defaultAcademicYearId));
        }
        if (defaultAcademicTermId) {
            $('#filter_academic_term_id').val(String(defaultAcademicTermId));
            $('#view_filter_academic_term_id').val(String(defaultAcademicTermId));
        }
        updateSelectionSummary();

        function setViewLoading(isLoading) {
            $viewTableOverlay.toggleClass('is-visible', isLoading);
            $viewTableOverlay.attr('aria-hidden', isLoading ? 'false' : 'true');
            $('.view-registration-filter, #viewRegistrationsSearchBtn, #viewRegistrationsResetBtn').prop('disabled', isLoading);
            $viewSearchInput.prop('disabled', isLoading || !viewRegistrationTable);

            if (isLoading) {
                $viewStatusBadge
                    .removeClass('is-ready')
                    .addClass('is-loading')
                    .html('<span class="btn-spinner"></span> Loading registrations...');
            } else if (anyViewFiltersSelected()) {
                $viewStatusBadge
                    .removeClass('is-loading')
                    .addClass('is-ready')
                    .html('<i class="ri-check-line"></i> Results loaded');
            } else {
                $viewStatusBadge
                    .removeClass('is-loading is-ready')
                    .html('<i class="ri-information-line"></i> Select at least one filter');
            }
        }

        function getViewFilterValues() {
            return {
                school_class_id: $('#view_filter_school_class_id').val(),
                academic_term_id: $('#view_filter_academic_term_id').val(),
                academic_year_id: $('#view_filter_academic_year_id').val(),
            };
        }

        function anyViewFiltersSelected() {
            const filters = getViewFilterValues();
            return !!(filters.school_class_id || filters.academic_term_id || filters.academic_year_id);
        }

        function countActiveViewFilters() {
            const filters = getViewFilterValues();
            return [filters.school_class_id, filters.academic_term_id, filters.academic_year_id].filter(Boolean).length;
        }

        function updateViewFilterState() {
            const activeCount = countActiveViewFilters();
            $('#view_stat_filters').text(activeCount);

            $('.view-registration-filter').each(function () {
                $(this).toggleClass('is-selected', !!$(this).val());
            });

            if (!anyViewFiltersSelected()) {
                $viewStatusBadge
                    .removeClass('is-loading is-ready')
                    .html('<i class="ri-information-line"></i> Select at least one filter');
            }
        }

        function teacherPillClass(teacherName) {
            const paletteSize = 8;
            let hash = 0;
            const name = (teacherName || '').trim();

            for (let i = 0; i < name.length; i++) {
                hash = ((hash << 5) - hash) + name.charCodeAt(i);
                hash |= 0;
            }

            return 'teacher-pill-' + (Math.abs(hash) % paletteSize);
        }

        function teacherAvatarHtml(teacherName, picture) {
            if (picture) {
                return '<img src="' + escapeHtml(picture) + '" alt="' + escapeHtml(teacherName) + '">';
            }

            const parts = (teacherName || '').trim().split(/\s+/);
            const initials = ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '');

            return escapeHtml(initials.toUpperCase());
        }

        function buildRegisteredRow(item) {
            const parentHint = item.parent_name
                ? '<span class="parent-hint">' + escapeHtml(item.parent_name) + '</span>'
                : '';

            const teacherCell = item.has_teacher
                ? (
                    '<div class="teacher-name-cell">' +
                        '<span class="teacher-pill ' + teacherPillClass(item.teacher_name) + '">' +
                            '<span class="teacher-pill-avatar">' + teacherAvatarHtml(item.teacher_name, item.teacher_picture) + '</span>' +
                            escapeHtml(item.teacher_name) +
                        '</span>' +
                    '</div>'
                )
                : '<span class="no-teacher-badge"><i class="ri-user-unfollow-line"></i> No teacher assigned</span>';

            return (
                '<tr class="course-registration-row is-registered">' +
                    '<td>' +
                        '<div class="course-name-cell">' +
                            '<span class="course-avatar"><i class="ri-book-open-line"></i></span>' +
                            '<div>' +
                                '<span class="fw-medium">' + escapeHtml(item.course_name) + '</span>' +
                                parentHint +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td><span class="meta-badge class-badge"><i class="ri-group-line"></i> ' + escapeHtml(item.class_name) + '</span></td>' +
                    '<td>' + teacherCell + '</td>' +
                    '<td><span class="meta-badge term-badge"><i class="ri-calendar-event-line"></i> ' + escapeHtml(item.term_name) + '</span></td>' +
                    '<td><span class="meta-badge year-badge"><i class="ri-calendar-2-line"></i> ' + escapeHtml(item.year_name) + '</span></td>' +
                    '<td><span class="category-badge">' + escapeHtml(item.registered_at || '—') + '</span></td>' +
                '</tr>'
            );
        }

        function showViewPlaceholder(title, message, icon) {
            destroyViewDataTable();
            $viewSearchInput.prop('disabled', true).val('');

            $('#viewRegistrationTableBody').html(
                '<tr id="viewRegistrationPlaceholder">' +
                    '<td colspan="6">' +
                        '<div class="registration-empty-state">' +
                            '<div class="registration-empty-state-icon"><i class="' + icon + '"></i></div>' +
                            '<h6 class="fw-semibold">' + escapeHtml(title) + '</h6>' +
                            '<p class="mb-0 text-sm">' + escapeHtml(message) + '</p>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            );
        }

        function showViewLoadingSkeleton() {
            destroyViewDataTable();
            $('#viewRegistrationTableBody').html(buildViewSkeletonRows(6));
        }

        function destroyViewDataTable() {
            if (!viewRegistrationTable) {
                return;
            }

            viewRegistrationTable.destroy();
            viewRegistrationTable = null;
        }

        function initViewDataTable() {
            destroyViewDataTable();

            const tableEl = document.getElementById('viewRegistrationTable');
            if (!tableEl) {
                return;
            }

            viewRegistrationTable = new DataTable(tableEl, {
                pageLength: 10,
                order: [[5, 'desc']],
            });

            $viewSearchInput
                .prop('disabled', false)
                .off('keyup.viewRegistrationTable')
                .on('keyup.viewRegistrationTable', function () {
                    viewRegistrationTable.search(this.value).draw();
                });
        }

        function renderRegisteredCourses(data) {
            const registrations = data.registrations || [];

            $('#view_stat_total').text((data.stats && data.stats.total) || 0);
            updateViewFilterState();

            if (!registrations.length) {
                showViewPlaceholder(
                    'No registrations found',
                    'No courses match the selected filters. Try changing class, term, or year.',
                    'ri-inbox-line'
                );
                return;
            }

            destroyViewDataTable();
            $('#viewRegistrationTableBody').html(registrations.map(buildRegisteredRow).join(''));
            initViewDataTable();
        }

        function loadRegisteredCourses() {
            updateViewFilterState();

            if (activeViewRequest) {
                activeViewRequest.abort();
                activeViewRequest = null;
            }

            if (!anyViewFiltersSelected()) {
                $('#view_stat_total').text('0');
                showViewPlaceholder(
                    'Search registered courses',
                    'Choose class, term, and/or year above, then click View Registrations.',
                    'ri-search-line'
                );
                setViewLoading(false);
                return;
            }

            setViewLoading(true);
            showViewLoadingSkeleton();

            const filters = getViewFilterValues();

            activeViewRequest = $.ajax({
                url: registeredUrl,
                method: 'GET',
                data: filters,
                dataType: 'json',
            })
                .done(function (data) {
                    renderRegisteredCourses(data);
                })
                .fail(function (xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }

                    const message = xhr.responseJSON?.message || 'Unable to load registrations.';
                    showViewPlaceholder('Unable to load registrations', message, 'ri-error-warning-line');
                    showToast('error', message);
                })
                .always(function () {
                    activeViewRequest = null;
                    setViewLoading(false);
                });
        }

        function resetViewFilters() {
            $('.view-registration-filter').val('').removeClass('is-selected');
            $('#view_stat_total').text('0');
            updateViewFilterState();
            showViewPlaceholder(
                'Search registered courses',
                'Choose class, term, and/or year above, then click View Registrations.',
                'ri-search-line'
            );
        }

        $('.registration-mode-tab').on('click', function () {
            const mode = $(this).data('mode');

            $('.registration-mode-tab')
                .removeClass('is-active')
                .attr('aria-selected', 'false');
            $(this).addClass('is-active').attr('aria-selected', 'true');

            if (mode === 'view') {
                $('#registerCoursesPanel').addClass('d-none');
                $('#viewRegistrationsPanel').removeClass('d-none');
                updateViewFilterState();
                return;
            }

            $('#viewRegistrationsPanel').addClass('d-none');
            $('#registerCoursesPanel').removeClass('d-none');
        });

        $('#viewRegistrationsSearchBtn').on('click', loadRegisteredCourses);
        $('#viewRegistrationsResetBtn').on('click', resetViewFilters);
        $('.view-registration-filter').on('change', updateViewFilterState);

        function submitRegistrationToggle($button, action, courseId, originalHtml) {
            const $row = $button.closest('tr.course-registration-row');

            $button.prop('disabled', true).html(buttonLoadingHtml(action === 'add' ? 'Adding...' : 'Removing...'));

            const filters = getFilterValues();
            const payload = {
                _token: csrfToken,
                course_id: courseId,
                school_class_id: filters.school_class_id,
                academic_term_id: filters.academic_term_id,
                academic_year_id: filters.academic_year_id,
            };

            if (action === 'remove') {
                payload.registration_id = $button.data('registration-id');
            }

            $.ajax({
                url: action === 'add' ? registerUrl : unregisterUrl,
                method: 'POST',
                data: payload,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    if (action === 'add') {
                        updateRowState($row, courseId, response.registration_id, true);
                        adjustStats(1);
                        $('.registration-mode-tab[data-mode="view"] .tab-count').text(function (index, value) {
                            return (parseInt(value, 10) || 0) + 1;
                        });
                    } else {
                        updateRowState($row, courseId, null, false);
                        adjustStats(-1);
                        $('.registration-mode-tab[data-mode="view"] .tab-count').text(function (index, value) {
                            return Math.max((parseInt(value, 10) || 0) - 1, 0);
                        });
                    }

                    if (!$('#viewRegistrationsPanel').hasClass('d-none') && anyViewFiltersSelected()) {
                        loadRegisteredCourses();
                    }

                    showToast('success', response.message || 'Course registration updated successfully.');
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message
                        || xhr.responseJSON?.errors?.course_id?.[0]
                        || 'Unable to update course registration.';

                    showToast('error', message);
                    $button.prop('disabled', false).html(originalHtml);
                },
            });
        }

        $('body').on('click', '.course-registration-toggle-btn', function () {
            if (!allFiltersSelected()) {
                showToast('error', 'Select class, term, and year first.');
                return;
            }

            const $button = $(this);
            const action = $button.data('action');
            const courseId = $button.data('course-id');
            const originalHtml = $button.html();

            syncHiddenFilterFields();

            if (action === 'remove') {
                confirmAppAction({
                    title: 'Remove this course registration?',
                    text: 'This cannot be undone.',
                    confirmButtonText: 'Yes, remove',
                }).then(function (ok) {
                    if (ok) {
                        submitRegistrationToggle($button, action, courseId, originalHtml);
                    }
                });
                return;
            }

            submitRegistrationToggle($button, action, courseId, originalHtml);
        });
    })();
</script>
@endsection
