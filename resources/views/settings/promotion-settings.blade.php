@php
    $pageName = 'settings';
    $subpageName = 'promotion-settings';

    $configuredCount = $classes->filter(fn ($c) => $c->promotion_minimum_mark !== null)->count();
    $unsetCount = $classes->count() - $configuredCount;
    $avgMark = $classes->filter(fn ($c) => $c->promotion_minimum_mark !== null)->avg('promotion_minimum_mark');
    $categoryColors = ['ac-pill-teal', 'ac-pill-indigo', 'ac-pill-amber', 'ac-pill-violet', 'ac-pill-sky', 'ac-pill-emerald'];
@endphp

@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .ps-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fff 0%, var(--neutral-50, #f9fafb) 100%);
    }

    .ps-toolbar-search {
        position: relative;
        min-width: 220px;
        flex: 1;
        max-width: 320px;
    }

    .ps-toolbar-search input {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 10px;
        font-size: 0.875rem;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .ps-toolbar-search input:focus {
        outline: none;
        border-color: var(--primary-600, #25A194);
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.12);
    }

    .ps-toolbar-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--neutral-400, #9ca3af);
        font-size: 1rem;
    }

    .ps-bulk {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .ps-bulk label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--neutral-600, #4b5563);
        margin: 0;
        white-space: nowrap;
    }

    .ps-bulk-input {
        width: 96px;
        padding: 8px 10px;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 10px;
        font-size: 0.875rem;
        text-align: center;
    }

    .ps-bulk-input:focus {
        outline: none;
        border-color: var(--primary-600, #25A194);
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.12);
    }

    .ps-class-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ps-class-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .ps-mark-cell {
        min-width: 180px;
    }

    .ps-mark-field {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ps-mark-number {
        width: 120px;
        padding: 8px 12px;
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .ps-mark-number:focus {
        outline: none;
        border-color: var(--primary-600, #25A194);
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.12);
    }

    .ps-mark-hint {
        font-size: 0.75rem;
        color: var(--neutral-400, #9ca3af);
        white-space: nowrap;
    }

    .ps-row-hidden {
        display: none !important;
    }

    .ps-save-bar {
        position: sticky;
        bottom: 0;
        z-index: 5;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid var(--neutral-200, #e5e7eb);
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.06);
    }

    .ps-save-hint {
        font-size: 0.8125rem;
        color: var(--neutral-500, #6b7280);
        margin: 0;
    }

    .ps-empty {
        text-align: center;
        padding: 56px 24px;
    }

    .ps-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
        font-size: 28px;
    }

    @media (max-width: 767px) {
        .ps-save-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .ps-save-bar .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">SETTINGS</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Settings / Promotion Settings</span>
            </div>
        </div>
        @if($classes->isNotEmpty())
        <button type="submit" form="promotion-settings-form" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <i class="ri-save-line"></i> Save Settings
        </button>
        @endif
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-arrow-up-circle-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Class Promotion Requirements</h5>
            <p class="text-sm text-secondary-light mb-0">
                Set the minimum total mark a student must obtain before they can be promoted to the next class.
                You decide the pass mark for each class — leave blank if no minimum is required.
            </p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Active Classes</p>
                        <h4 class="fw-semibold mb-0">{{ $classes->count() }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-group-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Thresholds Set</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $configuredCount }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Average Minimum</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $avgMark ? number_format($avgMark, 0) : '—' }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-bar-chart-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('update-promotion-settings-process') }}" method="POST" id="promotion-settings-form">
        @csrf
        <div class="card ac-list-wrapper">
            <div class="ps-toolbar">
                <div class="ps-toolbar-search">
                    <i class="ri-search-line"></i>
                    <input type="text" id="ps-search" placeholder="Search classes or categories..." autocomplete="off">
                </div>
                @if($classes->isNotEmpty())
                <div class="ps-bulk">
                    <label for="ps-bulk-value">Apply to all:</label>
                    <input type="number" id="ps-bulk-value" class="ps-bulk-input" min="0" step="1" placeholder="e.g. 400">
                    <button type="button" class="btn btn-sm btn-outline-primary-600" id="ps-apply-all">
                        <i class="ri-check-double-line"></i> Apply
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-neutral-400 text-secondary-light" id="ps-clear-all">
                        <i class="ri-eraser-line"></i> Clear all
                    </button>
                </div>
                @endif
            </div>

            @if($classes->isEmpty())
            <div class="ps-empty">
                <div class="ps-empty-icon"><i class="ri-school-line"></i></div>
                <h6 class="fw-semibold mb-8">No active classes found</h6>
                <p class="text-secondary-light text-sm mb-16">Add classes under Class Setup before configuring promotion thresholds.</p>
                <a href="{{ route('school-classes') }}" class="btn btn-primary-600 btn-sm">
                    <i class="ri-add-line"></i> Go to Class Setup
                </a>
            </div>
            @else
            <div class="ac-list-scroll">
                <table class="table bordered-table mb-0" id="ps-table">
                    <thead>
                        <tr>
                            <th style="width:56px;">#</th>
                            <th>Class</th>
                            <th>Category</th>
                            <th style="width:120px;">Status</th>
                            <th style="width:300px;">Minimum Total Mark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classes as $class)
                        @php
                            $mark = old('promotion_marks.'.$loop->index.'.promotion_minimum_mark', $class->promotion_minimum_mark);
                            $hasMark = $mark !== null && $mark !== '';
                            $pillClass = $categoryColors[crc32($class->category?->name ?? 'default') % count($categoryColors)];
                        @endphp
                        <tr class="ps-row" data-search="{{ strtolower($class->name.' '.($class->category?->name ?? '')) }}">
                            <td class="text-secondary-light">{{ $loop->iteration }}</td>
                            <td>
                                <div class="ps-class-cell">
                                    <span class="ps-class-avatar">{{ strtoupper(substr($class->name, 0, 1)) }}</span>
                                    <span class="fw-semibold text-primary-light">{{ $class->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($class->category)
                                    <span class="ac-pill {{ $pillClass }}">{{ $class->category->name }}</span>
                                @else
                                    <span class="text-secondary-light">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="ac-pill ps-status-pill {{ $hasMark ? 'ac-pill-emerald' : 'ac-pill-slate' }}">
                                    <i class="ri-{{ $hasMark ? 'shield-check' : 'subtract' }}-line"></i>
                                    {{ $hasMark ? 'Set' : 'Not set' }}
                                </span>
                            </td>
                            <td class="ps-mark-cell">
                                <input type="hidden" name="promotion_marks[{{ $loop->index }}][school_class_id]" value="{{ $class->id }}">
                                <div class="ps-mark-field">
                                    <input type="number"
                                        name="promotion_marks[{{ $loop->index }}][promotion_minimum_mark]"
                                        class="ps-mark-number"
                                        min="0"
                                        step="1"
                                        placeholder="—"
                                        value="{{ $hasMark ? (int) $mark : '' }}"
                                        aria-label="Minimum total mark for {{ $class->name }}">
                                    <span class="ps-mark-hint">marks</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ps-save-bar">
                <p class="ps-save-hint mb-0">
                    <i class="ri-information-line"></i>
                    {{ $unsetCount }} class{{ $unsetCount === 1 ? '' : 'es' }} without a minimum — students in those classes won't be blocked from promotion.
                </p>
                <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6">
                    <i class="ri-save-line"></i> Save Settings
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
(function () {
    const form = document.getElementById('promotion-settings-form');
    if (!form) return;

    function normalizeMark(value) {
        if (value === '' || value === null || Number.isNaN(value)) return '';
        return Math.max(0, parseInt(value, 10));
    }

    function updateField(field, value) {
        const number = field.querySelector('.ps-mark-number');
        const row = field.closest('.ps-row');
        const pill = row?.querySelector('.ps-status-pill');

        const hasValue = value !== '' && value !== null && value !== undefined;
        const numeric = hasValue ? normalizeMark(value) : '';

        number.value = numeric === '' ? '' : numeric;

        if (hasValue && numeric !== '') {
            pill?.classList.remove('ac-pill-slate');
            pill?.classList.add('ac-pill-emerald');
            pill.innerHTML = '<i class="ri-shield-check-line"></i> Set';
        } else {
            pill?.classList.remove('ac-pill-emerald');
            pill?.classList.add('ac-pill-slate');
            pill.innerHTML = '<i class="ri-subtract-line"></i> Not set';
        }
    }

    form.querySelectorAll('.ps-mark-field').forEach(function (field) {
        const number = field.querySelector('.ps-mark-number');

        number.addEventListener('input', function () {
            updateField(field, number.value === '' ? '' : number.value);
        });

        number.addEventListener('blur', function () {
            if (number.value === '') {
                updateField(field, '');
                return;
            }
            updateField(field, normalizeMark(number.value));
        });
    });

    document.getElementById('ps-search')?.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        form.querySelectorAll('.ps-row').forEach(function (row) {
            const haystack = row.dataset.search || '';
            row.classList.toggle('ps-row-hidden', query !== '' && !haystack.includes(query));
        });
    });

    document.getElementById('ps-apply-all')?.addEventListener('click', function () {
        const bulk = document.getElementById('ps-bulk-value');
        const value = normalizeMark(bulk.value);
        if (value === '') {
            bulk.focus();
            return;
        }
        form.querySelectorAll('.ps-mark-field').forEach(function (field) {
            updateField(field, value);
        });
    });

    document.getElementById('ps-clear-all')?.addEventListener('click', function () {
        confirmAppAction({
            title: 'Clear minimum marks?',
            text: 'This will clear minimum marks for all classes.',
            confirmButtonText: 'Yes, clear',
        }).then(function (ok) {
            if (!ok) {
                return;
            }
            form.querySelectorAll('.ps-mark-field').forEach(function (field) {
                updateField(field, '');
            });
            document.getElementById('ps-bulk-value').value = '';
        });
    });
})();
</script>
@endsection
