@php $pageName = "bill-management"; $subpageName = "edit-student-bills"; @endphp
@extends('layouts.app')

@section('css')
<style>
    .esb-progress { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999; opacity: 0; pointer-events: none; transition: opacity .2s; background: rgba(37,161,148,.1); overflow: hidden; }
    .esb-progress.is-active { opacity: 1; }
    .esb-progress::after { content: ''; position: absolute; top: 0; left: 0; height: 100%; width: 35%; background: linear-gradient(90deg, #25A194, #6366f1); animation: esbSlide 1.1s ease-in-out infinite; }
    @keyframes esbSlide { 0% { transform: translateX(-120%); } 100% { transform: translateX(320%); } }

    .esb-hero { border-radius: 16px; padding: 22px 26px; background: linear-gradient(135deg, rgba(37,161,148,.1), rgba(99,102,241,.06)); border: 1px solid rgba(37,161,148,.12); margin-bottom: 20px; }
    .esb-hero-icon { width: 48px; height: 48px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; background: #25A194; color: #fff; font-size: 22px; box-shadow: 0 4px 12px rgba(37,161,148,.25); }

    .esb-steps { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .esb-step { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; font-size: 12px; font-weight: 600; color: #9ca3af; transition: all .15s; }
    .esb-step .num { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; background: #f3f4f6; color: #9ca3af; }
    .esb-step.is-done { border-color: rgba(37,161,148,.25); color: #25A194; background: rgba(37,161,148,.04); }
    .esb-step.is-done .num { background: #25A194; color: #fff; }
    .esb-step.is-active { border-color: #25A194; color: #111827; box-shadow: 0 0 0 1px rgba(37,161,148,.15); }
    .esb-step.is-active .num { background: rgba(37,161,148,.15); color: #25A194; }

    .esb-period-card { border: 1px solid #e5e7eb; border-radius: 14px; padding: 20px 22px; background: #fff; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .esb-period-card h6 { font-size: 13px; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .esb-filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 576px) { .esb-filter-grid { grid-template-columns: 1fr; } }
    .esb-filter-field label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
    .esb-filter-field .form-select { border-radius: 11px; min-height: 44px; font-size: 13px; border-color: #e5e7eb; transition: border-color .15s, box-shadow .15s; }
    .esb-filter-field .form-select:focus, .esb-filter-field .form-select.is-selected { border-color: rgba(37,161,148,.45); box-shadow: 0 0 0 3px rgba(37,161,148,.1); }
    .esb-period-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #e5e7eb; }
    .esb-period-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; }
    .esb-period-chip.is-ready { background: rgba(37,161,148,.08); border-color: rgba(37,161,148,.2); color: #25A194; }

    .esb-workspace { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 991px) { .esb-workspace { grid-template-columns: 1fr; } }

    .esb-search-panel { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .esb-search-head { padding: 16px 18px; border-bottom: 1px solid #f3f4f6; }
    .esb-search-head h6 { font-size: 13px; font-weight: 700; margin: 0 0 10px; }
    .esb-search-wrap { position: relative; }
    .esb-search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #25A194; font-size: 16px; pointer-events: none; }
    .esb-search-wrap .form-control { padding-left: 38px; border-radius: 10px; min-height: 42px; font-size: 13px; }
    .esb-search-wrap .form-control:disabled { background: #f9fafb; cursor: not-allowed; }
    .esb-result-count { font-size: 11px; font-weight: 700; color: #25A194; background: rgba(37,161,148,.08); padding: 2px 8px; border-radius: 999px; }
    .esb-results { max-height: 420px; overflow-y: auto; padding: 8px 10px 12px; }
    .esb-empty-search { text-align: center; padding: 32px 16px; font-size: 12px; color: #d1d5db; }
    .esb-empty-search i { font-size: 24px; display: block; margin-bottom: 8px; }
    .esb-result-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 11px; border: 1px solid transparent; cursor: pointer; margin-bottom: 4px; transition: all .12s; }
    .esb-result-card:hover { background: #fafafa; border-color: #e5e7eb; }
    .esb-result-card.is-selected { background: rgba(37,161,148,.05); border-color: rgba(37,161,148,.3); box-shadow: 0 0 0 1px rgba(37,161,148,.1); }
    .esb-avatar { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; flex-shrink: 0; background: rgba(37,161,148,.1); color: #25A194; }

    .esb-bills-panel { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,.04); min-height: 420px; position: relative; }
    .esb-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 80px 32px; color: #6b7280; min-height: 420px; }
    .esb-empty-ring { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #f9fafb; border: 1px solid #e5e7eb; color: #d1d5db; font-size: 26px; margin-bottom: 14px; }

    .esb-student-head { padding: 20px 22px; border-bottom: 1px solid #f3f4f6; background: linear-gradient(180deg, #fff, #fafafa); }
    .esb-student-name { font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 4px; letter-spacing: -.01em; }
    .esb-student-meta { font-size: 12px; color: #6b7280; margin: 0; }
    .esb-stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border-bottom: 1px solid #f3f4f6; }
    @media (max-width: 576px) { .esb-stat-row { grid-template-columns: 1fr; } }
    .esb-stat { padding: 14px 18px; border-right: 1px solid #f3f4f6; text-align: center; }
    .esb-stat:last-child { border-right: none; }
    @media (max-width: 576px) { .esb-stat { border-right: none; border-bottom: 1px solid #f3f4f6; } .esb-stat:last-child { border-bottom: none; } }
    .esb-stat .lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; margin-bottom: 2px; }
    .esb-stat .val { font-size: 17px; font-weight: 700; color: #111827; }
    .esb-stat.paid .val { color: #15803d; }
    .esb-stat.balance .val { color: #dc2626; }

    .esb-bills-list { padding: 12px 14px 16px; display: flex; flex-direction: column; gap: 8px; max-height: 380px; overflow-y: auto; }
    .esb-bill-card { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-radius: 12px; border: 1px solid #f3f4f6; background: #fafafa; transition: border-color .12s, box-shadow .12s; }
    .esb-bill-card:hover { border-color: rgba(37,161,148,.2); background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04); }
    .esb-bill-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: rgba(37,161,148,.1); color: #25A194; font-size: 18px; flex-shrink: 0; }
    .esb-bill-info { flex: 1; min-width: 0; }
    .esb-bill-name { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 2px; }
    .esb-bill-sub { font-size: 11px; color: #9ca3af; }
    .esb-bill-amounts { text-align: right; flex-shrink: 0; }
    .esb-bill-due { font-size: 14px; font-weight: 700; color: #111827; }
    .esb-bill-subamt { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    .esb-bill-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

    .status-pill { padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .status-pill.Pending { background: rgba(245,158,11,.14); color: #b45309; }
    .status-pill.Partial { background: rgba(59,130,246,.14); color: #1d4ed8; }
    .status-pill.Paid { background: rgba(34,197,94,.14); color: #15803d; }
    .type-badge-compulsory, .type-badge-optional { padding: 1px 7px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
    .type-badge-compulsory { background: rgba(234,88,12,.08); color: #c2410c; }
    .type-badge-optional { background: rgba(99,102,241,.08); color: #4338ca; }

    .esb-loading { position: absolute; inset: 0; background: rgba(255,255,255,.82); backdrop-filter: blur(3px); display: none; align-items: center; justify-content: center; z-index: 5; flex-direction: column; gap: 8px; }
    .esb-loading.is-visible { display: flex; }
    .esb-spinner { width: 28px; height: 28px; border-radius: 50%; border: 3px solid rgba(37,161,148,.12); border-top-color: #25A194; animation: esbSpin .7s linear infinite; }
    @keyframes esbSpin { to { transform: rotate(360deg); } }

    .esb-modal-preview { padding: 14px; border-radius: 10px; background: #f9fafb; border: 1px solid #e5e7eb; margin-top: 12px; }
    .esb-modal-preview-row { display: flex; justify-content: space-between; font-size: 12px; padding: 4px 0; }
    .esb-modal-preview-row.total { font-weight: 700; font-size: 13px; border-top: 1px dashed #e5e7eb; margin-top: 6px; padding-top: 8px; }
</style>
@endsection

@section('content')
<div class="esb-progress" id="esbProgress"></div>

<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-20">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Edit Student Bills</span>
            </div>
        </div>
        <a href="{{ route('student-bills') }}" class="btn btn-outline-primary-600 btn-sm d-flex align-items-center gap-6"><i class="ri-file-list-3-line"></i> Bill Ledger</a>
    </div>

    <div class="esb-hero d-flex align-items-start gap-14 mb-20">
        <span class="esb-hero-icon"><i class="ri-edit-box-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-4">Edit Student Bills</h5>
            <p class="text-sm text-secondary-light mb-0">Select an academic period, find a student, and adjust individual bill amounts for that term.</p>
        </div>
    </div>

    <div class="esb-steps">
        <span class="esb-step" id="stepPeriod"><span class="num">1</span> Select Period</span>
        <span class="esb-step" id="stepStudent"><span class="num">2</span> Find Student</span>
        <span class="esb-step" id="stepEdit"><span class="num">3</span> Edit Bills</span>
    </div>

    <div class="esb-period-card">
        <h6><i class="ri-calendar-2-line text-primary-600"></i> Billing Period</h6>
        <div class="esb-filter-grid">
            <div class="esb-filter-field">
                <label><i class="ri-calendar-2-line"></i> Academic Year</label>
                <select id="filterYear" class="form-select">
                    <option value="">Select year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="esb-filter-field">
                <label><i class="ri-calendar-event-line"></i> Academic Term</label>
                <select id="filterTerm" class="form-select">
                    <option value="">Select term</option>
                    @foreach($academicTerms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="esb-period-chips" id="periodChips">
            <span class="esb-period-chip" id="chipYear"><i class="ri-calendar-2-line"></i> No year selected</span>
            <span class="esb-period-chip" id="chipTerm"><i class="ri-calendar-event-line"></i> No term selected</span>
        </div>
    </div>

    <div class="esb-workspace">
        <div class="esb-search-panel">
            <div class="esb-search-head">
                <div class="d-flex align-items-center justify-content-between mb-0">
                    <h6>Students</h6>
                    <span id="resultCount" class="esb-result-count d-none">0</span>
                </div>
                <div class="esb-search-wrap mt-10">
                    <i class="ri-search-line"></i>
                    <input type="text" id="studentSearchInput" class="form-control" placeholder="Name or student ID..." autocomplete="off" disabled>
                </div>
            </div>
            <div id="searchResults" class="esb-results">
                <div class="esb-empty-search"><i class="ri-user-search-line"></i>Select year & term to search</div>
            </div>
        </div>

        <div class="esb-bills-panel">
            <div class="esb-loading" id="billsLoading">
                <div class="esb-spinner"></div>
                <p class="text-sm text-secondary-light mb-0">Loading bills...</p>
            </div>

            <div id="workspaceEmpty" class="esb-empty">
                <div class="esb-empty-ring"><i class="ri-bill-line"></i></div>
                <h6 class="fw-semibold mb-6">No student selected</h6>
                <p class="text-sm mb-0" style="max-width:280px;">Search and select a student to view and edit their bills for the chosen period.</p>
            </div>

            <div id="workspaceContent" class="d-none">
                <div class="esb-student-head">
                    <h4 class="esb-student-name" id="profileName"></h4>
                    <p class="esb-student-meta" id="profileMeta"></p>
                </div>
                <div class="esb-stat-row" id="profileSummary"></div>
                <div class="px-18 py-10 border-bottom d-flex align-items-center justify-content-between">
                    <span class="text-xs fw-semibold text-secondary-light" id="billCountLabel"></span>
                </div>
                <div class="esb-bills-list" id="billsList"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12">
            <div class="modal-header border-bottom">
                <div>
                    <h6 class="modal-title fw-semibold mb-2">Edit Bill Amount</h6>
                    <p class="text-xs text-secondary-light mb-0" id="editBillPeriodLabel"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBillForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="student_bill_id" id="edit_bill_id">
                    <p class="text-sm fw-semibold mb-12" id="editBillItemLabel"></p>
                    <label class="form-label text-sm fw-medium">Amount Due <span class="text-danger-600">*</span></label>
                    <div class="input-group mb-0">
                        <span class="input-group-text">₵</span>
                        <input type="number" name="amount_due" id="edit_amount_due" class="form-control" min="0" step="0.01" required>
                    </div>
                    <p class="text-xs text-secondary-light mt-6 mb-0">Cannot be less than amount already paid.</p>
                    <div class="esb-modal-preview">
                        <div class="esb-modal-preview-row"><span>Amount Paid</span><span id="previewPaid">—</span></div>
                        <div class="esb-modal-preview-row total"><span>New Balance</span><span id="previewBalance" class="text-warning-600">—</span></div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-600" id="editBillSubmitBtn"><i class="ri-check-line"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const searchUrl = @json(route('edit-student-bills-search'));
    const billsUrl = @json(url('get-student-bills'));
    const updateUrl = @json(route('update-student-bill-process'));
    const csrfToken = @json(csrf_token());
    const yearOptions = @json($academicYears->pluck('name', 'id'));
    const termOptions = @json($academicTerms->pluck('name', 'id'));
    const defaultAcademicYearId = @json($defaultAcademicYearId);
    const defaultAcademicTermId = @json($defaultAcademicTermId);

    let selectedStudentId = null, searchTimer = null, currentBills = [], editingBill = null;

    function getPeriodFilters() {
        return { academic_year_id: $('#filterYear').val(), academic_term_id: $('#filterTerm').val() };
    }

    function periodIsReady() {
        const p = getPeriodFilters();
        return p.academic_year_id && p.academic_term_id;
    }

    function updateSteps() {
        const ready = periodIsReady();
        const hasStudent = !!selectedStudentId;
        $('#stepPeriod').toggleClass('is-done', ready).toggleClass('is-active', !ready);
        $('#stepStudent').toggleClass('is-done', hasStudent).toggleClass('is-active', ready && !hasStudent);
        $('#stepEdit').toggleClass('is-active', hasStudent);
    }

    function updatePeriodChips() {
        const yearId = $('#filterYear').val(), termId = $('#filterTerm').val();
        const yearName = yearId ? (yearOptions[yearId] || 'Selected') : 'No year selected';
        const termName = termId ? (termOptions[termId] || 'Selected') : 'No term selected';
        $('#chipYear').toggleClass('is-ready', !!yearId).html('<i class="ri-calendar-2-line"></i> ' + yearName);
        $('#chipTerm').toggleClass('is-ready', !!termId).html('<i class="ri-calendar-event-line"></i> ' + termName);
        $('#filterYear').toggleClass('is-selected', !!yearId);
        $('#filterTerm').toggleClass('is-selected', !!termId);
    }

    function syncPeriodUi() {
        const ready = periodIsReady();
        $('#studentSearchInput').prop('disabled', !ready);
        updatePeriodChips();
        updateSteps();
        if (!ready) {
            selectedStudentId = null;
            $('#workspaceContent').addClass('d-none');
            $('#workspaceEmpty').removeClass('d-none');
            $('#searchResults').html('<div class="esb-empty-search"><i class="ri-user-search-line"></i>Select year & term to search</div>');
            $('#resultCount').addClass('d-none');
            $('#studentSearchInput').val('');
        }
    }

    function escapeHtml(v) { return $('<div>').text(v ?? '').html(); }
    function formatMoney(v) { return parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function statusPill(s) { return '<span class="status-pill ' + escapeHtml(s) + '">' + escapeHtml(s) + '</span>'; }

    function setLoading(on) {
        $('#esbProgress').toggleClass('is-active', on);
        $('#billsLoading').toggleClass('is-visible', on);
    }

    function renderSearchResults(students) {
        const container = $('#searchResults'), count = $('#resultCount');
        if (!students.length) {
            count.addClass('d-none');
            container.html('<div class="esb-empty-search"><i class="ri-user-unfollow-line"></i>No students found</div>');
            return;
        }
        count.removeClass('d-none').text(students.length);
        let html = '';
        students.forEach(function (s) {
            const sel = selectedStudentId === s.id ? ' is-selected' : '';
            html += '<div class="esb-result-card' + sel + '" data-id="' + s.id + '">'
                + '<span class="esb-avatar">' + escapeHtml(s.initials) + '</span>'
                + '<div class="flex-grow-1 min-w-0">'
                + '<div class="fw-semibold text-sm text-truncate">' + escapeHtml(s.full_name) + '</div>'
                + '<div class="text-xs text-secondary-light">' + escapeHtml(s.student_id) + '</div>'
                + '<div class="text-xs mt-1 text-primary-600">' + s.bill_count + ' bills · ₵' + formatMoney(s.balance) + '</div>'
                + '</div>'
                + (sel ? '<i class="ri-check-line text-primary-600"></i>' : '')
                + '</div>';
        });
        container.html(html);
    }

    function runSearch() {
        if (!periodIsReady()) return;
        const q = $('#studentSearchInput').val().trim();
        if (q.length < 2) {
            $('#searchResults').html('<div class="esb-empty-search"><i class="ri-search-line"></i>Type at least 2 characters</div>');
            $('#resultCount').addClass('d-none');
            return;
        }
        $.get(searchUrl, Object.assign({ q: q }, getPeriodFilters()))
            .done(function (data) { renderSearchResults(data.students || []); });
    }

    function renderBillsList(data) {
        currentBills = data.bills || [];
        const s = data.student, summary = data.summary;

        $('#profileName').text(s.full_name);
        $('#profileMeta').text(s.student_id + ' · ' + (s.class_name || '—') + (s.category_name ? ' · ' + s.category_name : ''));
        $('#profileSummary').html(
            '<div class="esb-stat"><div class="lbl">Total Due</div><div class="val">₵' + formatMoney(summary.total_due) + '</div></div>'
            + '<div class="esb-stat paid"><div class="lbl">Paid</div><div class="val">₵' + formatMoney(summary.total_paid) + '</div></div>'
            + '<div class="esb-stat balance"><div class="lbl">Balance</div><div class="val">₵' + formatMoney(summary.balance) + '</div></div>'
        );
        $('#billCountLabel').text(currentBills.length + ' bill line(s) for this period');

        if (!currentBills.length) {
            $('#billsList').html('<div class="esb-empty-search py-40"><i class="ri-file-list-3-line"></i>No bills for this period</div>');
            return;
        }

        let html = '';
        currentBills.forEach(function (b) {
            const tb = b.is_compulsory ? '<span class="type-badge-compulsory ms-4">Comp</span>' : '<span class="type-badge-optional ms-4">Opt</span>';
            html += '<div class="esb-bill-card" data-bill-id="' + b.id + '">'
                + '<span class="esb-bill-icon"><i class="ri-price-tag-3-line"></i></span>'
                + '<div class="esb-bill-info">'
                + '<div class="esb-bill-name">' + escapeHtml(b.item_name) + tb + '</div>'
                + '<div class="esb-bill-sub">' + escapeHtml(b.category_name || '—') + '</div>'
                + '</div>'
                + '<div class="esb-bill-amounts">'
                + '<div class="esb-bill-due bill-due">₵' + formatMoney(b.amount_due) + '</div>'
                + '<div class="esb-bill-subamt">Paid ₵' + formatMoney(b.amount_paid) + ' · Bal ₵' + formatMoney(b.balance) + '</div>'
                + '</div>'
                + '<div class="esb-bill-actions">'
                + '<span class="bill-status">' + statusPill(b.status) + '</span>'
                + '<button type="button" class="btn btn-sm btn-outline-primary-600 edit-bill-btn" data-id="' + b.id + '"><i class="ri-edit-line"></i></button>'
                + '</div></div>';
        });
        $('#billsList').html(html);
    }

    function loadStudentBills(id) {
        if (!periodIsReady()) { showAppToast('error', 'Select academic year and term first.'); return; }
        selectedStudentId = id;
        setLoading(true);
        $.get(billsUrl + '/' + id, getPeriodFilters(), function (data) {
            $('#workspaceEmpty').addClass('d-none');
            $('#workspaceContent').removeClass('d-none');
            renderBillsList(data);
            updateSteps();
            runSearch();
        }).fail(function () { showAppToast('error', 'Unable to load student bills.'); })
        .always(function () { setLoading(false); });
    }

    function updateModalPreview() {
        if (!editingBill) return;
        const due = parseFloat($('#edit_amount_due').val() || 0);
        const paid = parseFloat(editingBill.amount_paid || 0);
        const balance = Math.max(due - paid, 0);
        $('#previewPaid').text('₵' + formatMoney(paid));
        $('#previewBalance').text('₵' + formatMoney(balance));
    }

    function openEditModal(billId) {
        const bill = currentBills.find(function (b) { return b.id === billId; });
        if (!bill) return;
        editingBill = bill;
        const period = (bill.term_name || '') + ' · ' + (bill.year_name || '');
        $('#edit_bill_id').val(bill.id);
        $('#editBillItemLabel').text(bill.item_name + (bill.is_compulsory ? ' (Compulsory)' : ''));
        $('#editBillPeriodLabel').text((bill.category_name || '') + ' · ' + period);
        $('#edit_amount_due').val(parseFloat(bill.amount_due).toFixed(2)).attr('min', parseFloat(bill.amount_paid).toFixed(2));
        updateModalPreview();
        $('#editBillModal').modal('show');
    }

    function updateSummaryFromBills() {
        let totalDue = 0, totalPaid = 0, balance = 0;
        currentBills.forEach(function (b) {
            totalDue += parseFloat(b.amount_due || 0);
            totalPaid += parseFloat(b.amount_paid || 0);
            balance += parseFloat(b.balance || 0);
        });
        $('#profileSummary').html(
            '<div class="esb-stat"><div class="lbl">Total Due</div><div class="val">₵' + formatMoney(totalDue) + '</div></div>'
            + '<div class="esb-stat paid"><div class="lbl">Paid</div><div class="val">₵' + formatMoney(totalPaid) + '</div></div>'
            + '<div class="esb-stat balance"><div class="lbl">Balance</div><div class="val">₵' + formatMoney(balance) + '</div></div>'
        );
    }

    $('#studentSearchInput').on('input', function () { clearTimeout(searchTimer); searchTimer = setTimeout(runSearch, 300); });
    $('#filterYear, #filterTerm').on('change', function () {
        syncPeriodUi();
        if (periodIsReady() && selectedStudentId) loadStudentBills(selectedStudentId);
        else if (periodIsReady() && $('#studentSearchInput').val().trim().length >= 2) runSearch();
    });
    $('#edit_amount_due').on('input', updateModalPreview);

    if (defaultAcademicYearId) { $('#filterYear').val(String(defaultAcademicYearId)); }
    if (defaultAcademicTermId) { $('#filterTerm').val(String(defaultAcademicTermId)); }
    syncPeriodUi();

    $('body').on('click', '.esb-result-card', function () { loadStudentBills(parseInt($(this).data('id'), 10)); });
    $('body').on('click', '.edit-bill-btn', function () { openEditModal(parseInt($(this).data('id'), 10)); });

    $('#editBillForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#editBillSubmitBtn').prop('disabled', true).html('<span class="esb-spinner d-inline-block" style="width:14px;height:14px;border-width:2px;"></span> Saving...');
        $.ajax({
            url: updateUrl, method: 'POST',
            data: Object.assign({ _token: csrfToken, student_bill_id: $('#edit_bill_id').val(), amount_due: $('#edit_amount_due').val() }, getPeriodFilters()),
            headers: { Accept: 'application/json' },
        }).done(function (res) {
            showAppToast('success', res.message || 'Bill updated.');
            $('#editBillModal').modal('hide');
            const idx = currentBills.findIndex(function (b) { return b.id === res.bill.id; });
            if (idx >= 0) currentBills[idx] = res.bill;
            const $card = $('.esb-bill-card[data-bill-id="' + res.bill.id + '"]');
            $card.find('.bill-due').text('₵' + formatMoney(res.bill.amount_due));
            $card.find('.esb-bill-subamt').text('Paid ₵' + formatMoney(res.bill.amount_paid) + ' · Bal ₵' + formatMoney(res.bill.balance));
            $card.find('.bill-status').html(statusPill(res.bill.status));
            updateSummaryFromBills();
            runSearch();
        }).fail(function (xhr) {
            showAppToast('error', (xhr.responseJSON || {}).message || 'Unable to update bill.');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="ri-check-line"></i> Save Changes');
        });
    });
</script>
@endsection
