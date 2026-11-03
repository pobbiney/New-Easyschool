@php
    $pageName = 'class-setup';
    $subpageName = 'student-promotion';
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $schoolClass = $summary['class'];
    $nextClass = $summary['next_class'];
    $minimum = $summary['minimum'];
@endphp

@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .sp-section-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fff 0%, var(--neutral-50, #f9fafb) 100%);
    }

    .sp-student-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sp-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8125rem;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
        flex-shrink: 0;
    }

    .sp-gap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .sp-gap.is-pass { color: #15803d; }
    .sp-gap.is-fail { color: #b91c1c; }
    .sp-gap.is-neutral { color: #64748b; }

    .sp-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .sp-select-cell {
        width: 44px;
        text-align: center;
        vertical-align: middle;
    }

    .sp-select-cell .form-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
        min-height: 18px;
    }

    .sp-promote-table tbody tr.is-selected {
        background: rgba(37, 161, 148, 0.06);
    }

    .sp-override-table tbody tr.is-selected {
        background: rgba(245, 158, 11, 0.08);
    }

    .sp-promote-table tbody tr,
    .sp-override-table tbody tr {
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">CLASS SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('student-promotion', $periodQuery) }}" class="text-secondary-light hover-text-primary hover-underline"> / Student Promotion</a>
                <span class="text-secondary-light"> / {{ $schoolClass->name }}</span>
            </div>
        </div>
        <a href="{{ route('student-promotion', $periodQuery) }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-arrow-left-line"></i> All Classes
        </a>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-graduation-cap-line"></i></span>
        <div class="flex-grow-1">
            <h5 class="fw-semibold mb-6">{{ $schoolClass->name }} Promotion Review</h5>
            <p class="text-sm text-secondary-light mb-8">
                Total subject scores are summed and compared to the class pass mark
                (<strong>{{ $minimum !== null ? number_format($minimum) : 'not set' }}</strong>).
                @if($nextClass)
                    Eligible students will move to <strong>{{ $nextClass->name }}</strong>.
                    Management may also promote students below the pass mark when required.
                @else
                    This is the highest class in its category.
                @endif
            </p>
            @include('teacher.partials._academic-period-filter', [
                'periodFilterAction' => route('student-promotion-class', $schoolClass),
            ])
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">On Roll</p>
                <h4 class="fw-semibold mb-0">{{ $summary['counts']['total'] }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Ready to Promote</p>
                <h4 class="fw-semibold mb-0 text-success-600">{{ $summary['counts']['eligible'] }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Below Pass Mark</p>
                <h4 class="fw-semibold mb-0 text-danger-600">{{ $summary['counts']['below'] }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Promoted</p>
                <h4 class="fw-semibold mb-0 text-primary-600">{{ $summary['counts']['promoted'] }}</h4>
                @if($summary['counts']['promoted_conditional'] > 0)
                    <p class="text-xs text-warning-600 mb-0 mt-4">{{ $summary['counts']['promoted_conditional'] }} conditional</p>
                @endif
            </div>
        </div>
    </div>

    @if($summary['promoted']->isNotEmpty())
    <div class="card ac-list-wrapper mb-24">
        <div class="sp-section-head">
            <div>
                <h6 class="fw-semibold mb-4 text-primary-600"><i class="ri-user-follow-line"></i> Promoted Students</h6>
                <p class="text-sm text-secondary-light mb-0">Students already promoted from {{ $schoolClass->name }} for this academic period.</p>
            </div>
        </div>
        <div class="ac-list-scroll">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Total Score</th>
                        <th>Pass Mark</th>
                        <th>Promoted To</th>
                        <th>Promotion Type</th>
                        <th>Current Class</th>
                        <th>Promoted On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['promoted'] as $log)
                    @php $student = $log->student; @endphp
                    <tr>
                        <td>
                            <div class="sp-student-cell">
                                <span class="sp-avatar">{{ strtoupper(substr($student->firstname ?? '?', 0, 1).substr($student->surname ?? '?', 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $student?->full_name ?? 'Unknown student' }}</span>
                            </div>
                        </td>
                        <td>{{ $student?->student_id ?? '—' }}</td>
                        <td>{{ $log->aggregate_total_score !== null ? number_format($log->aggregate_total_score) : '—' }}</td>
                        <td>{{ $log->promotion_minimum_mark !== null ? number_format($log->promotion_minimum_mark) : '—' }}</td>
                        <td>{{ $log->toClass?->name ?? '—' }}</td>
                        <td>
                            @if($log->isConditional())
                                <span class="ac-pill ac-pill-amber" title="Promoted by management decision despite being below the pass mark">
                                    <i class="ri-shield-star-line"></i> Conditional Promotion
                                </span>
                            @else
                                <span class="ac-pill ac-pill-emerald">
                                    <i class="ri-checkbox-circle-line"></i> Standard Promotion
                                </span>
                            @endif
                        </td>
                        <td>{{ $student?->class_name ?? '—' }}</td>
                        <td class="text-secondary-light text-sm">{{ $log->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($nextClass && $summary['eligible']->isNotEmpty())
    <form action="{{ route('student-promotion-process', $schoolClass) }}" method="POST" id="sp-promote-form" class="card ac-list-wrapper mb-24">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $period['year_id'] }}">
        <input type="hidden" name="academic_term_id" value="{{ $period['term_id'] }}">
        <input type="hidden" name="promotion_type" value="eligible">

        <div class="sp-section-head">
            <div>
                <h6 class="fw-semibold mb-4 text-success-600"><i class="ri-checkbox-circle-line"></i> Ready for Promotion</h6>
                <p class="text-sm text-secondary-light mb-0">Students who meet or exceed the pass mark.</p>
            </div>
            <div class="sp-toolbar-actions">
                <button type="button" class="btn btn-sm btn-outline-primary-600" id="sp-select-all">Select all</button>
                <button type="submit" class="btn btn-sm btn-primary-600" id="sp-promote-btn">
                    <i class="ri-arrow-up-circle-line"></i> Promote Selected
                </button>
            </div>
        </div>

        <div class="ac-list-scroll">
            <table class="table bordered-table mb-0 sp-promote-table">
                <thead>
                    <tr>
                        <th class="sp-select-cell">
                            <div class="form-check style-check d-flex justify-content-center mb-0">
                                <input type="checkbox" id="sp-master-check" class="form-check-input m-0" aria-label="Select all eligible students">
                            </div>
                        </th>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Total Score</th>
                        <th>Pass Mark</th>
                        <th>Margin</th>
                        <th>Promote To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['eligible'] as $row)
                    @php $student = $row['student']; @endphp
                    <tr data-student-id="{{ $student->id }}">
                        <td class="sp-select-cell">
                            <div class="form-check style-check d-flex justify-content-center mb-0">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input sp-row-check m-0" id="sp-student-{{ $student->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="sp-student-cell">
                                <span class="sp-avatar">{{ strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $student->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->student_id }}</td>
                        <td><strong>{{ number_format($row['aggregate_total_score'] ?? 0) }}</strong></td>
                        <td>{{ $minimum !== null ? number_format($minimum) : '—' }}</td>
                        <td>
                            @if($minimum !== null && $row['aggregate_total_score'] !== null)
                                <span class="sp-gap is-pass">+{{ number_format($row['aggregate_total_score'] - $minimum) }}</span>
                            @else
                                <span class="sp-gap is-neutral">No minimum</span>
                            @endif
                        </td>
                        <td>{{ $nextClass->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
    @elseif($nextClass)
    <div class="card ac-list-wrapper mb-24">
        <div class="text-center py-40 px-24">
            <p class="text-secondary-light mb-0">No students meet the promotion pass mark for this class yet.</p>
        </div>
    </div>
    @endif

    @if($nextClass && $summary['below']->isNotEmpty())
    <form action="{{ route('student-promotion-process', $schoolClass) }}" method="POST" id="sp-override-form" class="card ac-list-wrapper mb-24">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $period['year_id'] }}">
        <input type="hidden" name="academic_term_id" value="{{ $period['term_id'] }}">
        <input type="hidden" name="promotion_type" value="override">

        <div class="sp-section-head">
            <div>
                <h6 class="fw-semibold mb-4 text-danger-600"><i class="ri-error-warning-line"></i> Below Pass Mark</h6>
                <p class="text-sm text-secondary-light mb-0">
                    These students did not meet the pass mark. Select any student management wishes to promote anyway.
                </p>
            </div>
            @if($nextClass)
            <div class="sp-toolbar-actions">
                <button type="button" class="btn btn-sm btn-outline-warning-600" id="sp-override-select-all">Select all</button>
                <button type="submit" class="btn btn-sm btn-warning-600 text-white">
                    <i class="ri-shield-star-line"></i> Promote by Management Decision
                </button>
            </div>
            @endif
        </div>

        <div class="ac-list-scroll">
            <table class="table bordered-table mb-0 sp-override-table">
                <thead>
                    <tr>
                        <th class="sp-select-cell">
                            <div class="form-check style-check d-flex justify-content-center mb-0">
                                <input type="checkbox" id="sp-override-master-check" class="form-check-input m-0" aria-label="Select all below pass mark students">
                            </div>
                        </th>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Total Score</th>
                        <th>Pass Mark</th>
                        <th>Shortfall</th>
                        <th>Status</th>
                        <th>Promote To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['below'] as $row)
                    @php $student = $row['student']; @endphp
                    <tr data-student-id="{{ $student->id }}">
                        <td class="sp-select-cell">
                            <div class="form-check style-check d-flex justify-content-center mb-0">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input sp-override-check m-0" id="sp-override-student-{{ $student->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="sp-student-cell">
                                <span class="sp-avatar">{{ strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $student->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $row['aggregate_total_score'] !== null ? number_format($row['aggregate_total_score']) : '—' }}</td>
                        <td>{{ $minimum !== null ? number_format($minimum) : '—' }}</td>
                        <td>
                            @if($minimum !== null && $row['aggregate_total_score'] !== null)
                                <span class="sp-gap is-fail">{{ number_format($row['aggregate_total_score'] - $minimum) }}</span>
                            @else
                                <span class="sp-gap is-neutral">No scores</span>
                            @endif
                        </td>
                        <td><span class="ac-pill ac-pill-rose"><i class="ri-repeat-line"></i> Below pass mark</span></td>
                        <td>{{ $nextClass->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
    @elseif($nextClass)
    <div class="card ac-list-wrapper mb-24">
        <div class="sp-section-head">
            <div>
                <h6 class="fw-semibold mb-4 text-danger-600"><i class="ri-error-warning-line"></i> Below Pass Mark</h6>
                <p class="text-sm text-secondary-light mb-0">No students are below the pass mark.</p>
            </div>
        </div>
        <div class="text-center py-40 px-24">
            <p class="text-secondary-light mb-0">All students either meet the pass mark or have no scores yet.</p>
        </div>
    </div>
    @else
    <div class="card ac-list-wrapper mb-24">
        <div class="sp-section-head">
            <div>
                <h6 class="fw-semibold mb-4 text-danger-600"><i class="ri-error-warning-line"></i> Below Pass Mark</h6>
                <p class="text-sm text-secondary-light mb-0">Students who did not meet the pass mark for this class.</p>
            </div>
        </div>

        @if($summary['below']->isEmpty())
        <div class="text-center py-40 px-24">
            <p class="text-secondary-light mb-0">No students are below the pass mark.</p>
        </div>
        @else
        <div class="ac-list-scroll">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Total Score</th>
                        <th>Pass Mark</th>
                        <th>Shortfall</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['below'] as $row)
                    @php $student = $row['student']; @endphp
                    <tr>
                        <td>
                            <div class="sp-student-cell">
                                <span class="sp-avatar">{{ strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $student->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $row['aggregate_total_score'] !== null ? number_format($row['aggregate_total_score']) : '—' }}</td>
                        <td>{{ $minimum !== null ? number_format($minimum) : '—' }}</td>
                        <td>
                            @if($minimum !== null && $row['aggregate_total_score'] !== null)
                                <span class="sp-gap is-fail">{{ number_format($row['aggregate_total_score'] - $minimum) }}</span>
                            @else
                                <span class="sp-gap is-neutral">No scores</span>
                            @endif
                        </td>
                        <td><span class="ac-pill ac-pill-rose"><i class="ri-repeat-line"></i> Repeat</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    @if($summary['final_class']->isNotEmpty())
    <div class="card ac-list-wrapper mt-24">
        <div class="sp-section-head">
            <div>
                <h6 class="fw-semibold mb-4 text-info-600"><i class="ri-flag-line"></i> Final Class Students</h6>
                <p class="text-sm text-secondary-light mb-0">Highest class in this category — no further class to promote into.</p>
            </div>
        </div>
        <div class="ac-list-scroll">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Total Score</th>
                        <th>Pass Mark</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['final_class'] as $row)
                    @php $student = $row['student']; @endphp
                    <tr>
                        <td>
                            <div class="sp-student-cell">
                                <span class="sp-avatar">{{ strtoupper(substr($student->firstname, 0, 1).substr($student->surname, 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $student->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $row['aggregate_total_score'] !== null ? number_format($row['aggregate_total_score']) : '—' }}</td>
                        <td>{{ $minimum !== null ? number_format($minimum) : '—' }}</td>
                        <td>
                            @if($minimum !== null && $row['aggregate_total_score'] !== null && $row['aggregate_total_score'] < $minimum)
                                <span class="ac-pill ac-pill-rose">Repeat</span>
                            @else
                                <span class="ac-pill ac-pill-emerald">Completed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(function () {
    const nextClassName = @json($nextClass?->name ?? '');

    function initPromotionForm(config) {
        const $form = $(config.form);
        if (!$form.length) {
            return;
        }

        const $master = $(config.master);
        const $rows = () => $form.find(config.rowCheck);

        function syncMasterState() {
            const $all = $rows();
            const checkedCount = $all.filter(':checked').length;
            $master.prop('checked', $all.length > 0 && checkedCount === $all.length);
            $master.prop('indeterminate', checkedCount > 0 && checkedCount < $all.length);
        }

        $master.on('change', function () {
            const checked = $(this).is(':checked');
            $rows().prop('checked', checked);
            $form.find('tbody tr').toggleClass('is-selected', checked);
        });

        $(config.selectAll).on('click', function () {
            $master.prop('checked', true).trigger('change');
        });

        $form.on('change', config.rowCheck, function () {
            $(this).closest('tr').toggleClass('is-selected', $(this).is(':checked'));
            syncMasterState();
        });

        $form.on('click', 'tbody tr', function (event) {
            if ($(event.target).is('input, label, a, button')) {
                return;
            }

            const $checkbox = $(this).find(config.rowCheck);
            if (!$checkbox.length) {
                return;
            }

            $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
        });

        $form.on('submit', function (event) {
            if (!$rows().filter(':checked').length) {
                event.preventDefault();
                alert(config.emptyMessage);
                return;
            }

            if (!confirm(config.confirmMessage)) {
                event.preventDefault();
            }
        });
    }

    initPromotionForm({
        form: '#sp-promote-form',
        master: '#sp-master-check',
        rowCheck: '.sp-row-check',
        selectAll: '#sp-select-all',
        emptyMessage: 'Select at least one student to promote.',
        confirmMessage: 'Promote selected students to ' + nextClassName + '?',
    });

    initPromotionForm({
        form: '#sp-override-form',
        master: '#sp-override-master-check',
        rowCheck: '.sp-override-check',
        selectAll: '#sp-override-select-all',
        emptyMessage: 'Select at least one student for management override promotion.',
        confirmMessage: 'These students are below the pass mark. Promote them to ' + nextClassName + ' by management decision?',
    });
});
</script>
@endsection
