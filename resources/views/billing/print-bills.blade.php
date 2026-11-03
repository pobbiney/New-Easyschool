@php
    $pageName = 'bill-management';
    $subpageName = 'print-bills';
    $activeTab = in_array(request('tab'), ['student', 'class'], true) ? request('tab') : 'student';
    $tabMeta = [
        'student' => [
            'title' => 'Print Student Bill',
            'desc' => 'Select an academic period, find a student, and print their bill statement.',
            'breadcrumb' => 'Print Student Bill',
        ],
        'class' => [
            'title' => 'Print Class Bills',
            'desc' => 'Print bill statements for all active students in a class for the selected period.',
            'breadcrumb' => 'Print Class Bills',
        ],
    ];
    $currentTab = $tabMeta[$activeTab];
@endphp
@extends('layouts.app')

@section('css')
<style>
    .bp-hero { border-radius: 16px; padding: 22px 26px; background: linear-gradient(135deg, rgba(37,161,148,.1), rgba(99,102,241,.06)); border: 1px solid rgba(37,161,148,.12); margin-bottom: 20px; }
    .bp-hero-icon { width: 48px; height: 48px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; background: #25A194; color: #fff; font-size: 22px; }
    .bp-tabs { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 0; }
    .bp-tab {
        display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px;
        border: none; background: none; font-size: 14px; font-weight: 600; color: #6b7280;
        border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; transition: all .15s;
    }
    .bp-tab:hover { color: #25A194; }
    .bp-tab.is-active { color: #25A194; border-bottom-color: #25A194; }
    .bp-tab i { font-size: 18px; }
    .bp-panel { display: none; }
    .bp-panel.is-active { display: block; }
    .bp-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; padding: 20px 22px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .bp-card h6 { font-size: 13px; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .bp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 576px) { .bp-grid { grid-template-columns: 1fr; } }
    .bp-field label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; margin-bottom: 6px; display: block; }
    .bp-field .form-select, .bp-field .form-control { border-radius: 11px; min-height: 44px; font-size: 13px; }
    .bp-workspace { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 991px) { .bp-workspace { grid-template-columns: 1fr; } }
    .bp-search-panel, .bp-preview-panel { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .bp-search-head { padding: 16px 18px; border-bottom: 1px solid #f3f4f6; }
    .bp-search-wrap { position: relative; }
    .bp-search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #25A194; pointer-events: none; }
    .bp-search-wrap .form-control { padding-left: 38px; border-radius: 10px; min-height: 42px; }
    .bp-results { max-height: 360px; overflow-y: auto; padding: 8px 10px 12px; }
    .bp-result { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 11px; cursor: pointer; margin-bottom: 4px; border: 1px solid transparent; }
    .bp-result:hover { background: #fafafa; border-color: #e5e7eb; }
    .bp-result.is-selected { background: rgba(37,161,148,.05); border-color: rgba(37,161,148,.3); }
    .bp-avatar { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; background: rgba(37,161,148,.1); color: #25A194; flex-shrink: 0; }
    .bp-empty { text-align: center; padding: 48px 24px; color: #9ca3af; }
    .bp-empty i { font-size: 32px; display: block; margin-bottom: 10px; opacity: .5; }
    .bp-preview-head { padding: 20px 22px; border-bottom: 1px solid #f3f4f6; background: linear-gradient(180deg, #fff, #fafafa); }
    .bp-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border-bottom: 1px solid #f3f4f6; }
    .bp-stat { padding: 14px; text-align: center; border-right: 1px solid #f3f4f6; }
    .bp-stat:last-child { border-right: none; }
    .bp-stat .lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #9ca3af; }
    .bp-stat .val { font-size: 17px; font-weight: 700; color: #111827; margin-top: 2px; }
    .bp-actions { padding: 18px 22px; }
    .bp-class-card { max-width: 720px; }
    .bp-note { margin-top: 14px; padding: 12px 14px; border-radius: 10px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 13px; color: #6b7280; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-20">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div>
                <a href="{{ url('/dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light" id="bp_breadcrumb"> / {{ $currentTab['breadcrumb'] }}</span>
            </div>
        </div>
        <a href="{{ url('/student-bills') }}" class="btn btn-outline-primary-600 btn-sm d-flex align-items-center gap-6"><i class="ri-file-list-3-line"></i> Bill Ledger</a>
    </div>

    <div class="bp-hero d-flex align-items-start gap-14 mb-20">
        <span class="bp-hero-icon"><i class="ri-printer-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-4" id="bp_page_title">{{ $currentTab['title'] }}</h5>
            <p class="text-sm text-secondary-light mb-0" id="bp_page_desc">{{ $currentTab['desc'] }}</p>
        </div>
    </div>

    <div class="bp-tabs" role="tablist">
        <button type="button" class="bp-tab {{ $activeTab === 'student' ? 'is-active' : '' }}" data-bp-tab="student" data-bp-title="Print Student Bill" data-bp-desc="Select an academic period, find a student, and print their bill statement." data-bp-breadcrumb="Print Student Bill" role="tab">
            <i class="ri-user-line"></i> Single Student
        </button>
        <button type="button" class="bp-tab {{ $activeTab === 'class' ? 'is-active' : '' }}" data-bp-tab="class" data-bp-title="Print Class Bills" data-bp-desc="Print bill statements for all active students in a class for the selected period." data-bp-breadcrumb="Print Class Bills" role="tab">
            <i class="ri-group-line"></i> Whole Class
        </button>
    </div>

    {{-- Student tab --}}
    <div class="bp-panel {{ $activeTab === 'student' ? 'is-active' : '' }}" id="bp_panel_student" role="tabpanel">
        <div class="bp-card">
            <h6><i class="ri-calendar-line text-primary-600"></i> Academic Period</h6>
            <div class="bp-grid">
                <div class="bp-field">
                    <label>Academic Year</label>
                    <select id="bp_year" class="form-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) $filters['academic_year_id'] === (string) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bp-field">
                    <label>Academic Term</label>
                    <select id="bp_term" class="form-select">
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}" @selected((string) $filters['academic_term_id'] === (string) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bp-workspace">
            <div class="bp-search-panel">
                <div class="bp-search-head">
                    <h6 class="mb-10">Find Student</h6>
                    <div class="bp-search-wrap">
                        <i class="ri-search-line"></i>
                        <input type="text" id="bp_search" class="form-control" placeholder="Search name or student ID..." autocomplete="off">
                    </div>
                </div>
                <div class="bp-results" id="bp_results">
                    <div class="bp-empty"><i class="ri-user-search-line"></i>Type at least 2 characters to search.</div>
                </div>
            </div>

            <div class="bp-preview-panel">
                <div id="bp_preview_empty" class="bp-empty">
                    <i class="ri-file-list-3-line"></i>
                    <strong>Select a student</strong>
                    <p class="mb-0 mt-2 text-sm">Search and click a student to preview their bill before printing.</p>
                </div>
                <div id="bp_preview_content" style="display:none;">
                    <div class="bp-preview-head">
                        <h5 class="fw-semibold mb-4" id="bp_student_name">—</h5>
                        <p class="text-sm text-secondary-light mb-0" id="bp_student_meta">—</p>
                    </div>
                    <div class="bp-stats">
                        <div class="bp-stat"><div class="lbl">Total Due</div><div class="val" id="bp_total_due">—</div></div>
                        <div class="bp-stat"><div class="lbl">Total Paid</div><div class="val" id="bp_total_paid" style="color:#15803d;">—</div></div>
                        <div class="bp-stat"><div class="lbl">Balance</div><div class="val" id="bp_balance" style="color:#dc2626;">—</div></div>
                    </div>
                    <div class="bp-actions">
                        <button type="button" class="btn btn-primary-600 w-100 d-flex align-items-center justify-content-center gap-6" id="bp_print_btn" disabled>
                            <i class="ri-printer-line"></i> Print Bill Statement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Class tab --}}
    <div class="bp-panel {{ $activeTab === 'class' ? 'is-active' : '' }}" id="bp_panel_class" role="tabpanel">
        <div class="bp-card bp-class-card">
            <h6><i class="ri-filter-3-line text-primary-600"></i> Class Print Options</h6>
            <form id="bp_class_form" method="GET" action="{{ url('/print-class-bills/output') }}" target="_blank">
                <div class="bp-grid">
                    <div class="bp-field">
                        <label>Academic Year</label>
                        <select name="academic_year_id" class="form-select" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" @selected((string) $filters['academic_year_id'] === (string) $year->id)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bp-field">
                        <label>Academic Term</label>
                        <select name="academic_term_id" class="form-select" required>
                            @foreach($academicTerms as $term)
                                <option value="{{ $term->id }}" @selected((string) $filters['academic_term_id'] === (string) $term->id)>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="bp-field mt-14">
                    <label>Class</label>
                    <select name="school_class_id" id="bp_class" class="form-select" required>
                        <option value="">Select a class</option>
                        @foreach($schoolClasses as $class)
                            <option value="{{ $class->id }}" data-count="{{ $class->students_count ?? 0 }}">{{ $class->name }}@if($class->category) ({{ $class->category->name }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="bp-note" id="bp_class_note">Select a class to see how many students will be included.</div>
                <div class="bp-actions" style="padding:18px 0 0;margin:0;">
                    <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6">
                        <i class="ri-printer-line"></i> Print Class Bills
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const searchUrl = @json(url('/edit-student-bills-search'));
    const printBaseUrl = @json(url('/student-bills/print'));
    let selectedStudentId = null;
    let searchTimer = null;

    $('.bp-tab').on('click', function() {
        const $tab = $(this);
        const tab = $tab.data('bp-tab');
        $('.bp-tab').removeClass('is-active');
        $tab.addClass('is-active');
        $('.bp-panel').removeClass('is-active');
        $('#bp_panel_' + tab).addClass('is-active');

        $('#bp_page_title').text($tab.data('bp-title'));
        $('#bp_page_desc').text($tab.data('bp-desc'));
        $('#bp_breadcrumb').text(' / ' + $tab.data('bp-breadcrumb'));

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    });

    function periodParams() {
        return {
            academic_year_id: $('#bp_year').val(),
            academic_term_id: $('#bp_term').val(),
        };
    }

    function formatMoney(v) {
        return parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function clearSelection() {
        selectedStudentId = null;
        $('#bp_preview_empty').show();
        $('#bp_preview_content').hide();
        $('#bp_print_btn').prop('disabled', true);
        $('.bp-result').removeClass('is-selected');
    }

    function renderResults(students) {
        const $box = $('#bp_results');
        if (!students.length) {
            $box.html('<div class="bp-empty"><i class="ri-user-unfollow-line"></i>No students found.</div>');
            return;
        }

        $box.empty();
        students.forEach(function(student) {
            const $row = $('<div class="bp-result"></div>')
                .attr('data-student-id', student.id)
                .toggleClass('is-selected', selectedStudentId === student.id)
                .append(
                    $('<span class="bp-avatar"></span>').text(student.initials),
                    $('<div></div>').append(
                        $('<div class="fw-semibold text-sm"></div>').text(student.full_name),
                        $('<div class="text-xs text-secondary-light"></div>').text(student.student_id + ' · ' + student.class_name)
                    )
                )
                .on('click', function() {
                    selectStudent(student);
                });
            $box.append($row);
        });
    }

    function selectStudent(student) {
        selectedStudentId = student.id;
        $('.bp-result').removeClass('is-selected');
        $('.bp-result[data-student-id="' + student.id + '"]').addClass('is-selected');

        $('#bp_preview_empty').hide();
        $('#bp_preview_content').show();
        $('#bp_student_name').text(student.full_name);
        $('#bp_student_meta').text(student.student_id + ' · ' + student.class_name + (student.category_name ? ' · ' + student.category_name : ''));
        $('#bp_print_btn').prop('disabled', false);

        $.get(@json(url('/get-student-bills')) + '/' + student.id, periodParams(), function(res) {
            $('#bp_total_due').text('₵' + formatMoney(res.summary.total_due));
            $('#bp_total_paid').text('₵' + formatMoney(res.summary.total_paid));
            $('#bp_balance').text('₵' + formatMoney(res.summary.balance));
        });
    }

    function runSearch() {
        const q = $('#bp_search').val().trim();
        if (q.length < 2) {
            $('#bp_results').html('<div class="bp-empty"><i class="ri-user-search-line"></i>Type at least 2 characters to search.</div>');
            clearSelection();
            return;
        }

        $.get(searchUrl, Object.assign({ q: q }, periodParams()), function(res) {
            renderResults(res.students || []);
        });
    }

    $('#bp_search').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 300);
    });

    $('#bp_year, #bp_term').on('change', function() {
        clearSelection();
        if ($('#bp_search').val().trim().length >= 2) {
            runSearch();
        }
    });

    $('#bp_print_btn').on('click', function() {
        if (!selectedStudentId) return;
        const params = new URLSearchParams(periodParams());
        window.open(printBaseUrl + '/' + selectedStudentId + '?' + params.toString(), '_blank');
    });

    $('#bp_class').on('change', function() {
        const $opt = $(this).find('option:selected');
        const count = parseInt($opt.data('count') || 0, 10);
        const name = $opt.text().trim();
        if (!$(this).val()) {
            $('#bp_class_note').text('Select a class to see how many students will be included.');
            return;
        }
        $('#bp_class_note').text(count + ' active student' + (count === 1 ? '' : 's') + ' in ' + name + ' will be printed.');
    });
</script>
@endsection
