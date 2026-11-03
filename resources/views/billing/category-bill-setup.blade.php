@php $pageName = "bill-management"; $subpageName = "category-bill-setup"; @endphp
@extends('layouts.app')

@section('css')
<style>
    .setup-progress-bar {
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

    .setup-progress-bar.is-active { opacity: 1; }

    .setup-progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 35%;
        background: linear-gradient(90deg, var(--primary-600, #25A194), #6366f1);
        animation: setupProgressSlide 1.1s ease-in-out infinite;
    }

    @keyframes setupProgressSlide {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(320%); }
    }

    .setup-hero {
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12) 0%, rgba(99, 102, 241, 0.08) 100%);
        border: 1px solid rgba(37, 161, 148, 0.15);
        margin-bottom: 24px;
    }

    .setup-hero-icon {
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

    .setup-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 20px 22px;
        background: var(--white, #fff);
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .setup-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .setup-stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .setup-filter-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px;
        background: var(--white, #fff);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .filter-field { position: relative; }

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

    .setup-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid var(--neutral-200, #e5e7eb);
    }

    .setup-status-badge {
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

    .setup-status-badge.is-loading {
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .setup-status-badge.is-ready {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .setup-list-wrapper {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--neutral-200, #e5e7eb);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .setup-list-wrapper .card-header {
        background: linear-gradient(180deg, #fff 0%, var(--neutral-50, #f9fafb) 100%);
    }

    .setup-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
        min-height: 280px;
    }

    .setup-table-overlay {
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

    .setup-table-overlay.is-visible { display: flex; }

    .setup-spinner {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 3px solid rgba(37, 161, 148, 0.15);
        border-top-color: var(--primary-600, #25A194);
        animation: setupSpin 0.8s linear infinite;
    }

    @keyframes setupSpin { to { transform: rotate(360deg); } }

    .bill-item-row {
        transition: background-color 0.15s ease;
    }

    .bill-item-row:hover {
        background: rgba(37, 161, 148, 0.03);
    }

    .bill-item-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bill-item-avatar {
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

    .compulsory-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(234, 88, 12, 0.1);
        color: #c2410c;
        white-space: nowrap;
    }

    .optional-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
        white-space: nowrap;
    }

    .setup-amount.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
    }

    .amount-input-wrap {
        position: relative;
        max-width: 180px;
    }

    .amount-input-wrap .currency-prefix {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--neutral-500, #6b7280);
        font-size: 13px;
        font-weight: 600;
        pointer-events: none;
    }

    .amount-input-wrap .form-control {
        padding-left: 34px;
        border-radius: 10px;
        min-height: 42px;
        font-weight: 600;
    }

    .setup-empty-state {
        text-align: center;
        padding: 56px 24px;
        color: var(--neutral-500, #6b7280);
    }

    .setup-empty-state-icon {
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

    .setup-total-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid var(--neutral-200, #e5e7eb);
        background: var(--neutral-50, #f9fafb);
    }

    .setup-total-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--primary-600, #25A194);
    }

    .setup-result-card {
        margin-top: 18px;
        padding: 16px 18px;
        border-radius: 14px;
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.18);
    }

    .setup-result-card .result-stat {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #fff;
        font-size: 12px;
        font-weight: 600;
        color: #15803d;
        margin-right: 8px;
        margin-top: 8px;
    }

    .skeleton-row td { padding-top: 18px; padding-bottom: 18px; }

    .skeleton-block {
        height: 14px;
        border-radius: 999px;
        background: linear-gradient(90deg, #eef2f7 25%, #f8fafc 50%, #eef2f7 75%);
        background-size: 200% 100%;
        animation: skeletonShimmer 1.2s ease-in-out infinite;
    }

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
</style>
@endsection

@section('content')

<div id="setupProgressBar" class="setup-progress-bar" aria-hidden="true"></div>

<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Category Bill Setup</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('billing-items') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-price-tag-3-line"></i>
                Billing Items
            </a>
            <a href="{{ route('student-bills') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-wallet-3-line"></i>
                Student Bills
            </a>
        </div>
    </div>

    <div class="setup-hero d-flex align-items-start gap-16">
        <span class="setup-hero-icon"><i class="ri-settings-3-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Category Bill Setup</h5>
            <p class="text-sm text-secondary-light mb-0">Set fee amounts for a class category, term, and year. All students in that category automatically inherit these bills when you save.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-4">
            <div class="setup-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Billing Items</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['billing_items'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-price-tag-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="setup-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Saved Setups</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['setups'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="setup-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Class Categories</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['categories'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-folder-3-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="setup-filter-card mb-24">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-16">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Setup Filters</h6>
                <p class="text-sm text-secondary-light mb-0">All three selections are required before bill items appear.</p>
            </div>
            <span id="setupStatusBadge" class="setup-status-badge">
                <i class="ri-information-line"></i>
                Waiting for selections
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="filter-field">
                    <label for="filter_academic_term_id" class="form-label text-sm fw-medium">Academic Term</label>
                    <i class="ri-calendar-event-line filter-field-icon"></i>
                    <select id="filter_academic_term_id" class="form-select setup-filter" data-chip="term" data-label="Term">
                        <option value="">Select term</option>
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-field">
                    <label for="filter_academic_year_id" class="form-label text-sm fw-medium">Academic Year</label>
                    <i class="ri-calendar-2-line filter-field-icon"></i>
                    <select id="filter_academic_year_id" class="form-select setup-filter" data-chip="year" data-label="Year">
                        <option value="">Select year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-field">
                    <label for="filter_class_category_id" class="form-label text-sm fw-medium">Class Category</label>
                    <i class="ri-folder-3-line filter-field-icon"></i>
                    <select id="filter_class_category_id" class="form-select setup-filter" data-chip="category" data-label="Category">
                        <option value="">Select category</option>
                        @foreach($classCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div id="selectionSummary" class="selection-summary">
            <span class="selection-chip" data-chip="term"><i class="ri-calendar-event-line"></i><span>Term: Not selected</span></span>
            <span class="selection-chip" data-chip="year"><i class="ri-calendar-2-line"></i><span>Year: Not selected</span></span>
            <span class="selection-chip" data-chip="category"><i class="ri-folder-3-line"></i><span>Category: Not selected</span></span>
        </div>

        <div class="setup-actions">
            <button type="button" id="loadSetupBtn" class="btn btn-primary-600 d-inline-flex align-items-center gap-6">
                <i class="ri-search-line"></i>
                Load Bill Items
            </button>
            <button type="button" id="saveSetupBtn" class="btn btn-success-600 d-inline-flex align-items-center gap-6" disabled>
                <i class="ri-save-line"></i>
                Save & Sync Students
            </button>
        </div>

        <div id="setupResult" class="setup-result-card d-none">
            <p class="fw-semibold mb-4 text-success-600" id="setupResultMessage">Setup saved successfully.</p>
            <div id="setupResultStats"></div>
        </div>
    </div>

    <div class="card setup-list-wrapper">
        <div class="card-header border-bottom py-16 px-24">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">Bill Amounts</h6>
                    <p class="text-sm text-secondary-light mb-0">Enter an amount for each billing item. Leave blank or 0 to skip.</p>
                </div>
                <span id="itemCountBadge" class="setup-status-badge d-none">
                    <i class="ri-list-check-2"></i>
                    <span id="itemCountText">0 items</span>
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="setup-list-scroll">
                <div id="setupTableOverlay" class="setup-table-overlay" aria-hidden="true">
                    <div class="setup-spinner"></div>
                    <p class="text-sm fw-medium text-secondary-light mb-0">Loading bill items...</p>
                </div>

                <div id="setupPlaceholder" class="setup-empty-state">
                    <div class="setup-empty-state-icon"><i class="ri-filter-3-line"></i></div>
                    <h6 class="fw-semibold">Start by selecting filters</h6>
                    <p class="mb-0 text-sm">Pick term, year, and class category above, then click Load Bill Items.</p>
                </div>

                <div id="setupTableWrap" class="d-none">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th>Billing Item</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="setupItemsBody"></tbody>
                    </table>
                    <div class="setup-total-bar">
                        <div>
                            <p class="text-sm text-secondary-light mb-4">Total bill for this setup</p>
                            <div class="setup-total-value" id="setupTotalValue">0.00</div>
                        </div>
                        <span class="setup-status-badge is-ready d-none" id="setupLoadedBadge">
                            <i class="ri-check-line"></i> Ready to save
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const loadUrl = @json(route('category-bill-setup-load'));
    const saveUrl = @json(route('category-bill-setup-save'));
    const csrfToken = @json(csrf_token());
    const defaultAcademicYearId = @json($defaultAcademicYearId);
    const defaultAcademicTermId = @json($defaultAcademicTermId);

    let loadedItems = [];
    let activeRequest = null;

    const $progressBar = $('#setupProgressBar');
    const $overlay = $('#setupTableOverlay');
    const $statusBadge = $('#setupStatusBadge');

    function setLoading(isLoading) {
        $progressBar.toggleClass('is-active', isLoading);
        $overlay.toggleClass('is-visible', isLoading);
        $overlay.attr('aria-hidden', isLoading ? 'false' : 'true');
        $('.setup-filter, #loadSetupBtn').prop('disabled', isLoading);

        if (isLoading) {
            $statusBadge.removeClass('is-ready').addClass('is-loading')
                .html('<span class="setup-spinner" style="width:14px;height:14px;border-width:2px;"></span> Loading items...');
        } else if (allFiltersSelected()) {
            $statusBadge.removeClass('is-loading').addClass('is-ready')
                .html('<i class="ri-check-line"></i> Items loaded');
        } else {
            updateFilterState();
        }
    }

    function allFiltersSelected() {
        return $('#filter_academic_term_id').val()
            && $('#filter_academic_year_id').val()
            && $('#filter_class_category_id').val();
    }

    function updateFilterState() {
        $('.setup-filter').each(function () {
            const hasValue = !!$(this).val();
            $(this).toggleClass('is-selected', hasValue);

            const chip = $(this).data('chip');
            const label = $(this).data('label');
            const text = hasValue ? $(this).find('option:selected').text().trim() : 'Not selected';
            const $chip = $('[data-chip="' + chip + '"]');

            $chip.toggleClass('is-ready', hasValue);
            $chip.find('span').text(label + ': ' + text);
        });

        if (!allFiltersSelected()) {
            $statusBadge.removeClass('is-loading is-ready')
                .html('<i class="ri-information-line"></i> Waiting for selections');
        }
    }

    function updateTotal() {
        const total = loadedItems.reduce(function (sum, item) {
            return sum + (parseFloat(item.amount) || 0);
        }, 0);

        $('#setupTotalValue').text(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    function buildSkeletonRows(count) {
        let rows = '';
        for (let i = 0; i < count; i++) {
            rows += '<tr class="skeleton-row">' +
                '<td><div class="d-flex align-items-center gap-12"><div class="skeleton-circle"></div><div class="flex-grow-1"><div class="skeleton-block" style="width:50%;"></div></div></div></td>' +
                '<td><div class="skeleton-block" style="width:70%;"></div></td>' +
                '<td><div class="skeleton-block" style="width:80px;"></div></td>' +
                '<td><div class="skeleton-block" style="width:100px;"></div></td>' +
            '</tr>';
        }
        return rows;
    }

    function renderSetupRows() {
        let html = '';

        loadedItems.forEach(function (item, index) {
            const initials = (item.name || '').substring(0, 2).toUpperCase();
            const typeBadge = item.is_compulsory
                ? '<span class="compulsory-badge"><i class="ri-lock-line"></i> Compulsory</span>'
                : '<span class="optional-badge">Optional</span>';
            html += '<tr class="bill-item-row">' +
                '<td><div class="bill-item-cell"><span class="bill-item-avatar">' + initials + '</span><span class="fw-semibold">' + $('<div>').text(item.name).html() + '</span></div></td>' +
                '<td class="text-secondary-light">' + $('<div>').text(item.description || '—').html() + '</td>' +
                '<td>' + typeBadge + '</td>' +
                '<td><div class="amount-input-wrap"><span class="currency-prefix">₵</span><input type="number" min="0" step="0.01" class="form-control setup-amount" data-index="' + index + '" value="' + (item.amount ?? '') + '" placeholder="0.00"' + (item.is_compulsory ? ' required' : '') + '></div></td>' +
            '</tr>';
        });

        $('#setupItemsBody').html(html);
        $('#itemCountBadge').removeClass('d-none');
        $('#itemCountText').text(loadedItems.length + ' item' + (loadedItems.length === 1 ? '' : 's'));
        $('#setupLoadedBadge').removeClass('d-none');
        updateTotal();
    }

    function showPlaceholder(title, message, icon) {
        $('#setupPlaceholder').removeClass('d-none').html(
            '<div class="setup-empty-state-icon"><i class="' + icon + '"></i></div>' +
            '<h6 class="fw-semibold">' + title + '</h6>' +
            '<p class="mb-0 text-sm">' + message + '</p>'
        );
        $('#setupTableWrap').addClass('d-none');
        $('#saveSetupBtn').prop('disabled', true);
        $('#itemCountBadge, #setupLoadedBadge').addClass('d-none');
    }

    $('#loadSetupBtn').on('click', function () {
        updateFilterState();

        if (!allFiltersSelected()) {
            showAppToast('error', 'Select term, year, and class category.');
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        setLoading(true);
        $('#setupPlaceholder').addClass('d-none');
        $('#setupTableWrap').removeClass('d-none');
        $('#setupItemsBody').html(buildSkeletonRows(4));
        $('#setupResult').addClass('d-none');

        activeRequest = $.ajax({
            url: loadUrl,
            method: 'GET',
            data: {
                academic_term_id: $('#filter_academic_term_id').val(),
                academic_year_id: $('#filter_academic_year_id').val(),
                class_category_id: $('#filter_class_category_id').val(),
            },
        }).done(function (data) {
            loadedItems = data.items || [];

            if (!loadedItems.length) {
                showPlaceholder('No billing items found', 'Add active billing items before creating a category bill setup.', 'ri-price-tag-3-line');
                return;
            }

            renderSetupRows();
            $('#setupPlaceholder').addClass('d-none');
            $('#setupTableWrap').removeClass('d-none');
            $('#saveSetupBtn').prop('disabled', false);
        }).fail(function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

            showPlaceholder('Unable to load bill items', 'Something went wrong while fetching the setup. Please try again.', 'ri-error-warning-line');
            showAppToast('error', 'Unable to load bill setup.');
        }).always(function () {
            activeRequest = null;
            setLoading(false);
        });
    });

    $('body').on('input', '.setup-amount', function () {
        loadedItems[$(this).data('index')].amount = $(this).val();
        updateTotal();
    });

    $('#saveSetupBtn').on('click', function () {
        if (!allFiltersSelected() || !loadedItems.length) {
            return;
        }

        $('.setup-amount').removeClass('is-invalid');

        const invalidCompulsory = loadedItems.find(function (item) {
            return item.is_compulsory && !(parseFloat(item.amount) > 0);
        });

        if (invalidCompulsory) {
            $('.setup-amount').each(function () {
                const item = loadedItems[$(this).data('index')];
                if (item.is_compulsory && !(parseFloat(item.amount) > 0)) {
                    $(this).addClass('is-invalid');
                }
            });
            showAppToast('error', 'Compulsory billing item "' + invalidCompulsory.name + '" must have an amount greater than zero.');
            return;
        }

        const $btn = $(this).prop('disabled', true).html('<span class="setup-spinner" style="width:14px;height:14px;border-width:2px;"></span> Saving...');

        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                academic_term_id: $('#filter_academic_term_id').val(),
                academic_year_id: $('#filter_academic_year_id').val(),
                class_category_id: $('#filter_class_category_id').val(),
                amounts: loadedItems.map(function (item) {
                    return { billing_item_id: item.id, amount: item.amount || 0 };
                }),
            },
            success: function (res) {
                const stats = res.stats || {};
                $('#setupResult').removeClass('d-none');
                $('#setupResultMessage').text(res.message || 'Category bill setup saved and student bills synced.');
                $('#setupResultStats').html(
                    '<span class="result-stat"><i class="ri-group-line"></i> ' + (stats.students_matched || 0) + ' students</span>' +
                    '<span class="result-stat"><i class="ri-add-circle-line"></i> ' + (stats.bills_created || 0) + ' created</span>' +
                    '<span class="result-stat"><i class="ri-refresh-line"></i> ' + (stats.bills_updated || 0) + ' updated</span>'
                );
                showAppToast('success', res.message || 'Bill setup saved.');
            },
            error: function (xhr) {
                showAppToast('error', xhr.responseJSON?.message || 'Unable to save bill setup.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ri-save-line"></i> Save & Sync Students');
            },
        });
    });

    $('.setup-filter').on('change', function () {
        updateFilterState();
        $('#setupResult').addClass('d-none');
    });

    if (defaultAcademicYearId) { $('#filter_academic_year_id').val(String(defaultAcademicYearId)); }
    if (defaultAcademicTermId) { $('#filter_academic_term_id').val(String(defaultAcademicTermId)); }
    updateFilterState();
</script>
@endsection
