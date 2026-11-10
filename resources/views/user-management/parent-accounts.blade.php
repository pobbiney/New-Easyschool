@php $pageName = "user-management"; $subpageName = "parent-accounts"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .pa-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 18px 20px;
        background: var(--white, #fff);
        height: 100%;
    }

    .pa-stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .pa-student-results {
        position: absolute;
        z-index: 20;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        background: #fff;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        max-height: 280px;
        overflow-y: auto;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    }

    .pa-student-result {
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }

    .pa-student-result:hover,
    .pa-student-result.is-active {
        background: rgba(37, 161, 148, 0.08);
    }

    .pa-student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }

    .pa-student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pa-selected-student {
        border: 1px solid rgba(37, 161, 148, 0.25);
        background: rgba(37, 161, 148, 0.06);
        border-radius: 12px;
        padding: 14px 16px;
    }

    .pa-child-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        margin: 0 6px 6px 0;
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
                <span class="text-secondary-light"> / Parent Account</span>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-4">
            <div class="pa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Accounts</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-parent-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="pa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Active</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['active'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-50 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="pa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Linked to Children</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['with_children'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-50 text-info-600"><i class="ri-user-heart-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-1 radius-12 mb-24">
        <div class="card-body p-24">
            <div class="mb-20">
                <h6 class="fw-semibold mb-4">Create parent account</h6>
                <p class="text-sm text-secondary-light mb-0">
                    Select a child, confirm the guardian name and phone, then create the portal login.
                    Login uses the guardian phone. New accounts get the default password <strong>{{ $defaultPassword }}</strong>.
                </p>
            </div>

            <form method="POST" action="{{ route('parent-accounts-process') }}" id="parentAccountForm">
                @csrf
                <input type="hidden" name="student_id" id="pa_student_id" value="{{ old('student_id') }}" required>

                <div class="row gy-3">
                    <div class="col-lg-6">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Search student <span class="text-danger-600">*</span></label>
                        <div class="position-relative">
                            <input type="search" id="pa_student_search" class="form-control" placeholder="Search by name, student ID, class, or guardian phone" autocomplete="off">
                            <div id="pa_student_results" class="pa-student-results d-none"></div>
                        </div>
                        <div id="pa_selected_student" class="pa-selected-student mt-12 d-none"></div>
                        @error('student_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Guardian name</label>
                        <input type="text" name="guardian_name" id="pa_guardian_name" class="form-control" value="{{ old('guardian_name') }}" placeholder="Guardian full name">
                        @error('guardian_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Guardian phone <span class="text-danger-600">*</span></label>
                        <input type="tel" name="guardian_phone" id="pa_guardian_phone" class="form-control" value="{{ old('guardian_phone') }}" placeholder="e.g. 0244123456" required>
                        @error('guardian_phone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-20">
                    <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6" id="pa_submit_btn" disabled>
                        <i class="ri-user-add-line"></i>
                        Create / Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-1 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-12">
            <div>
                <h6 class="fw-semibold mb-0">Parent accounts</h6>
                <p class="text-sm text-secondary-light mb-0">Accounts are linked to Active students that share the same guardian phone.</p>
            </div>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table mb-0" id="parentAccountsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Guardian</th>
                            <th>Phone (login)</th>
                            <th>Linked children</th>
                            <th>Status</th>
                            <th>Last login</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="d-block fw-semibold">{{ $account['guardian_name'] }}</span>
                                    <span class="text-xs text-secondary-light">Created {{ $account['created_at'] ?? '—' }}</span>
                                </td>
                                <td>{{ $account['phone'] }}</td>
                                <td>
                                    @if($account['children_count'] > 0)
                                        @foreach($account['children'] as $child)
                                            <span class="pa-child-chip" title="{{ $child['student_id'] }}">
                                                {{ $child['full_name'] }}
                                                <span class="text-secondary-light">· {{ $child['class_name'] }}</span>
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-secondary-light">No Active children linked</span>
                                    @endif
                                </td>
                                <td>
                                    @if($account['status'] === 'Active')
                                        <span class="bg-success-100 text-success-600 px-16 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-16 py-4 radius-4 fw-medium text-sm">{{ $account['status'] }}</span>
                                    @endif
                                    @if($account['must_change_password'])
                                        <div class="text-xs text-warning-600 mt-4">Must change password</div>
                                    @endif
                                </td>
                                <td>{{ $account['last_login_at'] ?? 'Never' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('parent-accounts-reset-process') }}" class="pa-reset-form">
                                        @csrf
                                        <input type="hidden" name="phone" value="{{ $account['phone'] }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning-600">
                                            <i class="ri-lock-password-line"></i> Reset password
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary-light py-32">No parent accounts yet. Create one from a student above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
    const searchInput = document.getElementById('pa_student_search');
    const resultsBox = document.getElementById('pa_student_results');
    const selectedBox = document.getElementById('pa_selected_student');
    const studentIdInput = document.getElementById('pa_student_id');
    const guardianNameInput = document.getElementById('pa_guardian_name');
    const guardianPhoneInput = document.getElementById('pa_guardian_phone');
    const submitBtn = document.getElementById('pa_submit_btn');
    const searchUrl = @json(route('parent-accounts-student-search'));
    let searchTimer = null;
    let activeIndex = -1;
    let currentResults = [];

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function syncSubmitState() {
        submitBtn.disabled = !(studentIdInput.value && guardianPhoneInput.value.trim());
    }

    function selectStudent(student) {
        studentIdInput.value = student.id;
        guardianNameInput.value = student.guardian_name
            || student.mother_name
            || student.father_name
            || '';
        guardianPhoneInput.value = student.guardian_phone
            || student.mother_phone
            || student.father_phone
            || '';

        const avatar = student.picture
            ? '<img src="' + escapeHtml(student.picture) + '" alt="">'
            : escapeHtml(student.initials || 'ST');

        selectedBox.innerHTML =
            '<div class="d-flex align-items-center gap-12">' +
                '<span class="pa-student-avatar">' + avatar + '</span>' +
                '<div>' +
                    '<span class="d-block fw-semibold">' + escapeHtml(student.full_name) + '</span>' +
                    '<span class="d-block text-sm text-secondary-light">' +
                        escapeHtml(student.student_id) + ' · ' + escapeHtml(student.class_name) +
                        (student.has_parent_account ? ' · Parent account exists' : '') +
                    '</span>' +
                '</div>' +
            '</div>';
        selectedBox.classList.remove('d-none');
        resultsBox.classList.add('d-none');
        searchInput.value = student.full_name + ' (' + student.student_id + ')';
        syncSubmitState();
    }

    function renderResults(students) {
        currentResults = students || [];
        activeIndex = -1;

        if (!currentResults.length) {
            resultsBox.innerHTML = '<div class="p-16 text-sm text-secondary-light">No Active students found.</div>';
            resultsBox.classList.remove('d-none');
            return;
        }

        resultsBox.innerHTML = currentResults.map(function (student, index) {
            const avatar = student.picture
                ? '<img src="' + escapeHtml(student.picture) + '" alt="">'
                : escapeHtml(student.initials || 'ST');
            const meta = [
                student.student_id,
                student.class_name,
                student.guardian_phone || 'No guardian phone',
                student.has_parent_account ? 'Account exists' : null
            ].filter(Boolean).join(' · ');

            return '<button type="button" class="pa-student-result" data-index="' + index + '">' +
                '<span class="pa-student-avatar">' + avatar + '</span>' +
                '<span>' +
                    '<span class="d-block fw-semibold text-sm">' + escapeHtml(student.full_name) + '</span>' +
                    '<span class="d-block text-xs text-secondary-light">' + escapeHtml(meta) + '</span>' +
                '</span>' +
            '</button>';
        }).join('');

        resultsBox.classList.remove('d-none');
        resultsBox.querySelectorAll('.pa-student-result').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectStudent(currentResults[Number(btn.dataset.index)]);
            });
        });
    }

    function runSearch(term) {
        if (term.length < 2) {
            resultsBox.classList.add('d-none');
            resultsBox.innerHTML = '';
            return;
        }

        fetch(searchUrl + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) { renderResults(data.students || []); })
            .catch(function () {
                resultsBox.innerHTML = '<div class="p-16 text-sm text-danger">Search failed. Try again.</div>';
                resultsBox.classList.remove('d-none');
            });
    }

    searchInput.addEventListener('input', function () {
        studentIdInput.value = '';
        selectedBox.classList.add('d-none');
        syncSubmitState();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            runSearch(searchInput.value.trim());
        }, 250);
    });

    searchInput.addEventListener('keydown', function (event) {
        const items = resultsBox.querySelectorAll('.pa-student-result');
        if (!items.length || resultsBox.classList.contains('d-none')) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            selectStudent(currentResults[activeIndex]);
            return;
        } else {
            return;
        }

        items.forEach(function (item, index) {
            item.classList.toggle('is-active', index === activeIndex);
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#pa_student_search') && !event.target.closest('#pa_student_results')) {
            resultsBox.classList.add('d-none');
        }
    });

    guardianPhoneInput.addEventListener('input', syncSubmitState);

    document.querySelectorAll('.pa-reset-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const phone = form.querySelector('input[name="phone"]').value;
            confirmAppAction({
                title: 'Reset parent password?',
                text: 'Reset login for ' + phone + ' to the default password?',
                confirmButtonText: 'Yes, reset',
            }).then(function (ok) {
                if (ok) {
                    form.submit();
                }
            });
        });
    });

    syncSubmitState();
})();
</script>
@endsection
