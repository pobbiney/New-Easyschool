@php
    $pageName = "settings";
    $subpageName = !empty($focusAcademicSession) ? 'academic-session' : 'school-settings';
    $hasLogo = !empty($school->logo_path);
    $hasName = !empty($school->name);
    $hasAddress = !empty($school->address);
    $hasPhone = !empty($school->phone);
    $fieldsFilled = collect([$hasLogo, $hasName, $hasAddress, $hasPhone, !empty($school->email), !empty($school->motto)])->filter()->count();
    $completion = round(($fieldsFilled / 6) * 100);
@endphp

@extends('layouts.app')

@section('css')
<style>
    .firm-page {
        --firm-teal: #25A194;
        --firm-teal-dark: #1a7a70;
        --firm-gold: #f0b429;
        --firm-ink: #0f172a;
        --firm-muted: #64748b;
        --firm-border: rgba(15, 23, 42, 0.08);
        --firm-glass: rgba(255, 255, 255, 0.72);
    }

    .firm-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .firm-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .firm-stat {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid var(--firm-border);
        border-radius: 16px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .firm-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(37, 161, 148, 0.1);
    }

    .firm-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .firm-stat-icon.teal { background: rgba(37, 161, 148, 0.12); color: var(--firm-teal); }
    .firm-stat-icon.gold { background: rgba(240, 180, 41, 0.15); color: #b45309; }
    .firm-stat-icon.slate { background: rgba(100, 116, 139, 0.12); color: var(--firm-muted); }

    .firm-stat-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--firm-ink);
        line-height: 1;
        margin-bottom: 4px;
    }

    .firm-stat-label {
        font-size: 13px;
        color: var(--firm-muted);
        font-weight: 500;
    }

    .firm-workspace {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 24px;
        align-items: start;
    }

    .firm-preview-stack {
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: sticky;
        top: 88px;
    }

    /* Brand card — premium ID style */
    .brand-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(37, 161, 148, 0.18), 0 0 0 1px rgba(255,255,255,0.1) inset;
        position: relative;
    }

    .brand-card-bg {
        background: linear-gradient(145deg, #1a7a70 0%, #25A194 40%, #2ec4b6 100%);
        padding: 28px 28px 32px;
        position: relative;
        overflow: hidden;
    }

    .brand-card-bg::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 90% 10%, rgba(255,255,255,0.15) 0%, transparent 40%),
            radial-gradient(circle at 10% 90%, rgba(0,0,0,0.08) 0%, transparent 40%);
        pointer-events: none;
    }

    .brand-card-pattern {
        position: absolute;
        inset: 0;
        opacity: 0.06;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .brand-card-content {
        position: relative;
        z-index: 1;
        color: #fff;
    }

    .brand-card-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.25);
        padding: 5px 12px;
        border-radius: 100px;
        margin-bottom: 20px;
        backdrop-filter: blur(8px);
    }

    .brand-logo-ring {
        width: 96px;
        height: 96px;
        border-radius: 20px;
        background: #fff;
        padding: 8px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .brand-logo-ring img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 12px;
    }

    .brand-logo-ring .placeholder {
        font-size: 40px;
        color: #cbd5e1;
    }

    .brand-card-name {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .brand-card-motto {
        font-size: 13px;
        opacity: 0.88;
        font-style: italic;
        margin: 0 0 20px;
        padding-left: 12px;
        border-left: 3px solid rgba(255,255,255,0.4);
    }

    .brand-card-contacts {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 13px;
        opacity: 0.92;
    }

    .brand-card-contacts span {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .brand-card-footer {
        background: #fff;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .completion-ring {
        position: relative;
        width: 44px;
        height: 44px;
        flex-shrink: 0;
    }

    .completion-ring svg {
        transform: rotate(-90deg);
    }

    .completion-ring-text {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: var(--firm-teal);
    }

    /* Editor panel */
    .firm-editor {
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--firm-border);
        box-shadow: 0 8px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .firm-editor-header {
        padding: 24px 28px 0;
        border-bottom: none;
    }

    .firm-editor-header h2 {
        font-size: 20px;
        font-weight: 800;
        color: var(--firm-ink);
        margin: 0 0 6px;
        letter-spacing: -0.02em;
    }

    .firm-editor-header p {
        font-size: 14px;
        color: var(--firm-muted);
        margin: 0;
    }

    .firm-tabs {
        display: flex;
        gap: 4px;
        padding: 20px 28px 0;
        border-bottom: 1px solid var(--firm-border);
    }

    .firm-tab {
        padding: 12px 20px;
        border: none;
        background: transparent;
        font-size: 14px;
        font-weight: 600;
        color: var(--firm-muted);
        border-radius: 10px 10px 0 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        position: relative;
    }

    .firm-tab:hover {
        color: var(--firm-teal);
        background: rgba(37, 161, 148, 0.05);
    }

    .firm-tab.active {
        color: var(--firm-teal);
        background: rgba(37, 161, 148, 0.08);
    }

    .firm-tab.active::after {
        content: "";
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--firm-teal);
        border-radius: 3px 3px 0 0;
    }

    .firm-tab-pane {
        display: none;
        padding: 28px;
        animation: fadeUp 0.35s ease;
    }

    .firm-tab-pane.active {
        display: block;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .logo-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 32px;
        text-align: center;
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        position: relative;
        cursor: pointer;
        transition: all 0.25s;
    }

    .logo-dropzone:hover,
    .logo-dropzone.dragover {
        border-color: var(--firm-teal);
        background: rgba(37, 161, 148, 0.04);
        box-shadow: 0 0 0 4px rgba(37, 161, 148, 0.08);
    }

    .logo-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .logo-dropzone-preview {
        width: 140px;
        height: 140px;
        margin: 0 auto 20px;
        border-radius: 24px;
        background: #fff;
        border: 3px solid var(--firm-teal);
        box-shadow: 0 12px 40px rgba(37, 161, 148, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .logo-dropzone-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 12px;
    }

    .logo-dropzone-preview .ph {
        font-size: 48px;
        color: #cbd5e1;
    }

    .logo-dropzone-hint {
        font-size: 15px;
        font-weight: 600;
        color: var(--firm-ink);
        margin-bottom: 6px;
    }

    .logo-dropzone-sub {
        font-size: 13px;
        color: var(--firm-muted);
    }

    .logo-specs {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .logo-spec {
        font-size: 11px;
        font-weight: 600;
        color: var(--firm-muted);
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
    }

    .premium-field {
        margin-bottom: 22px;
    }

    .premium-field label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--firm-ink);
        margin-bottom: 8px;
        letter-spacing: 0.01em;
    }

    .premium-field label span.req {
        color: #ef4444;
    }

    .premium-input-group {
        position: relative;
    }

    .premium-input-group .ico {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
        pointer-events: none;
        transition: color 0.2s;
    }

    .premium-input-group .form-control {
        padding: 14px 16px 14px 46px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .premium-input-group .form-control:focus {
        border-color: var(--firm-teal);
        box-shadow: 0 0 0 4px rgba(37, 161, 148, 0.12);
    }

    .premium-input-group:focus-within .ico {
        color: var(--firm-teal);
    }

    .premium-input-group textarea.form-control {
        padding-left: 16px;
        resize: vertical;
        min-height: 100px;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 20px;
    }

    .firm-editor-footer {
        padding: 20px 28px;
        background: #f8fafc;
        border-top: 1px solid var(--firm-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .firm-editor-footer-note {
        font-size: 13px;
        color: var(--firm-muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .firm-editor-footer-note i {
        color: var(--firm-teal);
        font-size: 18px;
    }

    @media (max-width: 1199px) {
        .firm-workspace {
            grid-template-columns: 1fr;
        }

        .firm-preview-stack {
            position: static;
        }

        .firm-stats {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }

        .firm-tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
        }
    }

    .firm-session-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--firm-border);
        box-shadow: 0 8px 40px rgba(15, 23, 42, 0.06);
        padding: 24px 28px;
        margin-bottom: 28px;
    }

    .firm-session-card.is-highlighted {
        border-color: rgba(37, 161, 148, 0.45);
        box-shadow: 0 0 0 4px rgba(37, 161, 148, 0.12), 0 8px 40px rgba(15, 23, 42, 0.06);
    }

    .firm-session-card h3 {
        font-size: 18px;
        font-weight: 800;
        color: var(--firm-ink);
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .firm-session-card h3 i {
        color: var(--firm-teal);
        font-size: 22px;
    }

    .firm-session-card .session-hint {
        font-size: 14px;
        color: var(--firm-muted);
        margin: 0 0 20px;
    }

    .firm-session-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .session-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .session-field label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: var(--firm-ink);
        margin: 0;
    }

    .session-field label i {
        color: var(--firm-teal);
        font-size: 17px;
        flex-shrink: 0;
    }

    .session-field label .req {
        color: #ef4444;
    }

    .session-select {
        display: block;
        width: 100%;
        min-height: 48px;
        padding: 12px 2.5rem 12px 16px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.4;
        color: var(--firm-ink);
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px 10px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .session-select:focus {
        border-color: var(--firm-teal);
        box-shadow: 0 0 0 4px rgba(37, 161, 148, 0.12);
        outline: none;
    }

    .session-select:invalid,
    .session-select option[value=""]:checked {
        color: #94a3b8;
    }

    .session-date {
        display: block;
        width: 100%;
        min-height: 48px;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.4;
        color: var(--firm-ink);
        background-color: #fff;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .session-date:focus {
        border-color: var(--firm-teal);
        box-shadow: 0 0 0 4px rgba(37, 161, 148, 0.12);
        outline: none;
    }

    .session-date.is-locked {
        background-color: #f8fafc;
        cursor: not-allowed;
        color: #64748b;
    }

    .alert.session-lock-note {
        display: none;
        margin: 0 0 16px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8d7da;
        border: 1px solid #f1aeb5;
        color: #842029;
        font-size: 13px;
        font-weight: 600;
    }

    .alert.session-lock-note.is-visible {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .alert.session-lock-note i {
        color: #dc3545;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    @media (max-width: 767px) {
        .firm-session-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body firm-page">

    <div class="firm-topbar">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">SETTINGS</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / {{ !empty($focusAcademicSession) ? 'Academic Session' : 'School Firm Setup' }}</span>
            </div>
        </div>
        <button type="submit" form="schoolSettingsForm" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">
            <i class="ri-save-line"></i> Save Changes
        </button>
    </div>

    <form action="{{ route('update-school-settings-process') }}" method="POST" enctype="multipart/form-data" id="schoolSettingsForm">
        @csrf

    <div class="firm-session-card{{ !empty($focusAcademicSession) ? ' is-highlighted' : '' }}" id="academicSessionSection">
        <h3><i class="ri-calendar-2-line"></i> Current Academic Session</h3>
        <p class="session-hint">Choose the active year and term used as the default across billing, registration, and student forms. Click <strong>Save Changes</strong> when done.</p>
        <div class="firm-session-grid">
            <div class="session-field">
                <label for="default_academic_year_id">
                    <i class="ri-calendar-2-line"></i>
                    Academic Year <span class="req">*</span>
                </label>
                <select id="default_academic_year_id" name="default_academic_year_id" class="session-select" required>
                    <option value="" disabled {{ ($selectedYearId ?? $school->default_academic_year_id ?? '') ? '' : 'selected' }}>Select academic year</option>
                    @forelse($academicYears ?? [] as $year)
                        <option value="{{ $year->id }}" @selected(($selectedYearId ?? $school->default_academic_year_id ?? '') == $year->id)>{{ $year->name }}</option>
                    @empty
                        <option value="" disabled>No active academic years — add them under Settings first</option>
                    @endforelse
                </select>
                @error('default_academic_year_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="session-field">
                <label for="default_academic_term_id">
                    <i class="ri-calendar-event-line"></i>
                    Academic Term <span class="req">*</span>
                </label>
                <select id="default_academic_term_id" name="default_academic_term_id" class="session-select" required>
                    <option value="" disabled {{ ($selectedTermId ?? $school->default_academic_term_id ?? '') ? '' : 'selected' }}>Select academic term</option>
                    @forelse($academicTerms ?? [] as $term)
                        <option value="{{ $term->id }}" @selected(($selectedTermId ?? $school->default_academic_term_id ?? '') == $term->id)>{{ $term->name }}</option>
                    @empty
                        <option value="" disabled>No active academic terms — add them under Settings first</option>
                    @endforelse
                </select>
                @error('default_academic_term_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>
    </div>

    <div class="firm-stats">
        <div class="firm-stat">
            <div class="firm-stat-icon teal"><i class="ri-image-2-line"></i></div>
            <div>
                <div class="firm-stat-value">{{ $hasLogo ? 'Uploaded' : 'Missing' }}</div>
                <div class="firm-stat-label">School Logo</div>
            </div>
        </div>
        <div class="firm-stat">
            <div class="firm-stat-icon gold"><i class="ri-pie-chart-2-line"></i></div>
            <div>
                <div class="firm-stat-value">{{ $completion }}%</div>
                <div class="firm-stat-label">Profile Complete</div>
            </div>
        </div>
        <div class="firm-stat">
            <div class="firm-stat-icon slate"><i class="ri-printer-line"></i></div>
            <div>
                <div class="firm-stat-value">{{ ($hasName && $hasAddress && $hasPhone) ? 'Ready' : 'Incomplete' }}</div>
                <div class="firm-stat-label">Print Status</div>
            </div>
        </div>
    </div>

        <div class="firm-workspace">
            <div class="firm-preview-stack">
                <div class="brand-card">
                    <div class="brand-card-bg">
                        <div class="brand-card-pattern"></div>
                        <div class="brand-card-content">
                            <div class="brand-card-badge">
                                <i class="ri-verified-badge-line"></i> Official Identity
                            </div>
                            <div class="brand-logo-ring" id="brandLogoRing">
                                <img src="{{ $school->logoUrl() }}" alt="Logo" id="brandLogoImg">
                                <i class="ri-building-4-line placeholder" id="brandLogoPlaceholder" style="display:none;"></i>
                            </div>
                            <h3 class="brand-card-name" id="brandName">{{ $school->name ?: 'Your School Name' }}</h3>
                            <p class="brand-card-motto" id="brandMotto" style="{{ empty($school->motto) ? 'display:none;' : '' }}">"{{ $school->motto }}"</p>
                            <div class="brand-card-contacts">
                                <span><i class="ri-map-pin-line"></i> <span id="brandAddress">{{ $school->address ?: 'School address' }}</span></span>
                                <span><i class="ri-phone-line"></i> <span id="brandPhone">{{ $school->phone ?: 'Telephone' }}</span></span>
                                <span id="brandEmailRow" style="{{ empty($school->email) ? 'display:none;' : '' }}"><i class="ri-mail-line"></i> <span id="brandEmail">{{ $school->email }}</span></span>
                                <span id="brandWebRow" style="{{ empty($school->website) ? 'display:none;' : '' }}"><i class="ri-global-line"></i> <span id="brandWebsite">{{ $school->website }}</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="brand-card-footer">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:#0f172a;">Setup Progress</div>
                            <div style="font-size:12px;color:#64748b;">{{ $fieldsFilled }} of 6 fields completed</div>
                        </div>
                        <div class="completion-ring">
                            <svg width="44" height="44" viewBox="0 0 44 44">
                                <circle cx="22" cy="22" r="18" fill="none" stroke="#e2e8f0" stroke-width="4"/>
                                <circle cx="22" cy="22" r="18" fill="none" stroke="#25A194" stroke-width="4"
                                    stroke-dasharray="{{ 113 * $completion / 100 }} 113"
                                    stroke-linecap="round"/>
                            </svg>
                            <div class="completion-ring-text">{{ $completion }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="firm-editor">
                <div class="firm-editor-header">
                    <h2>Configure School Profile</h2>
                    <p>Manage the official information that appears on all printed documents.</p>
                </div>

                <div class="firm-tabs" role="tablist">
                    <button type="button" class="firm-tab active" data-tab="identity">
                        <i class="ri-palette-line"></i> Brand Identity
                    </button>
                    <button type="button" class="firm-tab" data-tab="contact">
                        <i class="ri-contacts-book-2-line"></i> Contact Details
                    </button>
                </div>

                <div class="firm-tab-pane active" id="tab-identity">
                    <div class="logo-dropzone" id="logoDropzone">
                        <input type="file" name="logo" id="schoolLogoInput" accept="image/*">
                        <div class="logo-dropzone-preview">
                            <img src="{{ $school->logoUrl() }}" alt="Logo" id="uploadPreviewImg">
                            <i class="ri-upload-cloud-2-line ph" id="uploadPlaceholder" style="display:none;"></i>
                        </div>
                        <div class="logo-dropzone-hint">Drop your logo here or click to browse</div>
                        <div class="logo-dropzone-sub">This logo appears on student printouts and official documents</div>
                        <div class="logo-specs">
                            <span class="logo-spec">PNG / JPG</span>
                            <span class="logo-spec">Max 2MB</span>
                            <span class="logo-spec">Square recommended</span>
                        </div>
                    </div>

                    <div class="row mt-28">
                        <div class="col-md-12">
                            <div class="premium-field">
                                <label>School Name <span class="req">*</span></label>
                                <div class="premium-input-group">
                                    <i class="ri-school-line ico"></i>
                                    <input type="text" name="name" id="inputName" class="form-control" value="{{ old('name', $school->name ?? '') }}" placeholder="e.g. Sunrise International Academy">
                                </div>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="premium-field mb-0">
                                <label>School Motto</label>
                                <div class="premium-input-group">
                                    <i class="ri-double-quotes-l ico"></i>
                                    <input type="text" name="motto" id="inputMotto" class="form-control" value="{{ old('motto', $school->motto ?? '') }}" placeholder="e.g. Knowledge, Integrity, Excellence">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="firm-tab-pane" id="tab-contact">
                    <div class="premium-field">
                        <label>Physical Address <span class="req">*</span></label>
                        <div class="premium-input-group">
                            <textarea name="address" id="inputAddress" class="form-control" rows="3" placeholder="Street, city, region, country">{{ old('address', $school->address ?? '') }}</textarea>
                        </div>
                        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="contact-grid">
                        <div class="premium-field">
                            <label>Telephone <span class="req">*</span></label>
                            <div class="premium-input-group">
                                <i class="ri-phone-line ico"></i>
                                <input type="tel" name="phone" id="inputPhone" class="form-control" value="{{ old('phone', $school->phone ?? '') }}" placeholder="+233 30 000 0000">
                            </div>
                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="premium-field">
                            <label>Email Address</label>
                            <div class="premium-input-group">
                                <i class="ri-mail-line ico"></i>
                                <input type="email" name="email" id="inputEmail" class="form-control" value="{{ old('email', $school->email ?? '') }}" placeholder="info@school.com">
                            </div>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="premium-field mb-0" style="grid-column: 1 / -1;">
                            <label>Website</label>
                            <div class="premium-input-group">
                                <i class="ri-global-line ico"></i>
                                <input type="text" name="website" id="inputWebsite" class="form-control" value="{{ old('website', $school->website ?? '') }}" placeholder="https://www.yourschool.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="firm-editor-footer">
                    <div class="firm-editor-footer-note">
                        <i class="ri-information-line"></i>
                        Changes apply instantly to all new student printouts.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="reset" class="border border-neutral-400 bg-hover-neutral-200 text-secondary-light text-md px-24 py-11 radius-8">Reset</button>
                        <button type="submit" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">
                            <i class="ri-save-line"></i> Save School Information
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    @if($errors->any())
        showAppToast('error', @json($errors->first()));

        @if($errors->has('address') || $errors->has('phone') || $errors->has('email') || $errors->has('website'))
            $('.firm-tab').removeClass('active');
            $('.firm-tab[data-tab="contact"]').addClass('active');
            $('.firm-tab-pane').removeClass('active');
            $('#tab-contact').addClass('active');
        @elseif($errors->has('default_academic_year_id') || $errors->has('default_academic_term_id'))
            document.getElementById('academicSessionSection')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        @else
            $('.firm-tab').removeClass('active');
            $('.firm-tab[data-tab="identity"]').addClass('active');
            $('.firm-tab-pane').removeClass('active');
            $('#tab-identity').addClass('active');
        @endif
    @endif

    @if(!empty($focusAcademicSession))
        document.getElementById('academicSessionSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif

    $('.firm-tab').on('click', function () {
        let tab = $(this).data('tab');
        $('.firm-tab').removeClass('active');
        $(this).addClass('active');
        $('.firm-tab-pane').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });

    function updatePreview() {
        let name = $('#inputName').val().trim() || 'Your School Name';
        let motto = $('#inputMotto').val().trim();
        let address = $('#inputAddress').val().trim() || 'School address';
        let phone = $('#inputPhone').val().trim() || 'Telephone';
        let email = $('#inputEmail').val().trim();
        let website = $('#inputWebsite').val().trim();

        $('#brandName').text(name);
        $('#brandAddress').text(address);
        $('#brandPhone').text(phone);

        if (motto) {
            $('#brandMotto').text('"' + motto + '"').show();
        } else {
            $('#brandMotto').hide();
        }

        if (email) {
            $('#brandEmail').text(email);
            $('#brandEmailRow').show();
        } else {
            $('#brandEmailRow').hide();
        }

        if (website) {
            $('#brandWebsite').text(website);
            $('#brandWebRow').show();
        } else {
            $('#brandWebRow').hide();
        }
    }

    $('#inputName, #inputMotto, #inputAddress, #inputPhone, #inputEmail, #inputWebsite').on('input', updatePreview);

    $('#schoolLogoInput').on('change', function () {
        let file = this.files[0];
        if (!file) return;

        let reader = new FileReader();
        reader.onload = function (e) {
            let src = e.target.result;
            $('#uploadPreviewImg, #brandLogoImg').attr('src', src).show();
            $('#uploadPlaceholder, #brandLogoPlaceholder').hide();
        };
        reader.readAsDataURL(file);
    });

    let dropzone = document.getElementById('logoDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });
    }
</script>
@endsection
