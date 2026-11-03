@php
    $pageName = "timetable";
    $subpageName = "timetable-periods";
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ]);
    $dayName = $days[$day] ?? 'Monday';
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .tt-day {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .tt-day-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
    .tt-day-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        color: #475569;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        background: #fff;
    }
    .tt-day-tab:hover { border-color: #25A194; color: #25A194; }
    .tt-day-tab.is-active {
        background: #25A194;
        border-color: #25A194;
        color: #fff;
    }
    .tt-day-tab .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #cbd5e1;
    }
    .tt-day-tab.is-active .dot,
    .tt-day-tab.has-saved .dot { background: #34d399; }
    .tt-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #f8fffd, #fff);
    }
    .tt-start { width: 140px; }
    .tt-board {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        min-height: 360px;
    }
    .tt-track { padding: 16px 20px; }
    .tt-cols {
        display: grid;
        grid-template-columns: 78px minmax(0, 1.1fr) 200px 70px 108px;
        gap: 10px;
        padding: 0 6px 8px 6px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .tt-slot {
        display: grid;
        grid-template-columns: 78px minmax(0, 1.1fr) 200px 70px 108px;
        gap: 10px;
        align-items: center;
        padding: 10px 12px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        position: relative;
    }
    .tt-slot::before {
        content: "";
        position: absolute;
        inset-inline-start: 0;
        top: 8px;
        bottom: 8px;
        width: 4px;
        border-radius: 999px;
    }
    .tt-slot.is-lesson { background: #f8fffd; border-color: #ccfbf1; }
    .tt-slot.is-lesson::before { background: #25A194; }
    .tt-slot.is-break { background: #fffbeb; border-color: #fde68a; }
    .tt-slot.is-break::before { background: #d97706; }
    .tt-slot.is-assembly { background: #eef2ff; border-color: #c7d2fe; }
    .tt-slot.is-assembly::before { background: #4f46e5; }
    .tt-slot-time {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        font-size: 12px;
        color: #0f172a;
        line-height: 1.2;
        padding-inline-start: 8px;
    }
    .tt-slot-time small {
        display: block;
        margin-top: 2px;
        font-weight: 500;
        color: #64748b;
        font-size: 11px;
    }
    .tt-slot-name {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .tt-slot-ico {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .tt-slot.is-lesson .tt-slot-ico { background: rgba(37, 161, 148, .14); color: #0f766e; }
    .tt-slot.is-break .tt-slot-ico { background: rgba(217, 119, 6, .14); color: #b45309; }
    .tt-slot.is-assembly .tt-slot-ico { background: rgba(79, 70, 229, .14); color: #4338ca; }
    .tt-slot-name .form-control { min-width: 0; }
    .tt-slot-subject .form-select { width: 100%; }
    .tt-slot-muted {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
    }
    .tt-slot-mins .form-control { text-align: center; padding-inline: 6px; }
    .tt-slot-actions { display: flex; gap: 4px; justify-content: flex-end; }
    .tt-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        line-height: 1;
    }
    .tt-icon-btn:hover { border-color: #25A194; color: #25A194; }
    .tt-icon-btn.is-danger:hover { border-color: #e11d48; color: #e11d48; }
    .tt-icon-btn:disabled { opacity: .35; }
    .tt-rail {
        border-inline-start: 1px solid #e5e7eb;
        background: #f8fafc;
        padding: 18px 18px 20px;
    }
    .tt-rail h6 { font-size: 13px; }
    .tt-rail-list { list-style: none; margin: 14px 0 0; padding: 0; }
    .tt-rail-list li {
        display: grid;
        grid-template-columns: 64px 10px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        padding-bottom: 12px;
        position: relative;
        font-size: 12px;
    }
    .tt-rail-list li:last-child { padding-bottom: 0; }
    .tt-rail-list .when {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        color: #64748b;
        font-size: 11px;
        padding-top: 1px;
    }
    .tt-rail-list .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-top: 4px;
        background: #25A194;
        box-shadow: 0 0 0 3px rgba(37, 161, 148, .16);
        position: relative;
        z-index: 1;
    }
    .tt-rail-list li::before {
        content: "";
        position: absolute;
        inset-inline-start: 68px;
        top: 14px;
        bottom: -2px;
        width: 2px;
        background: #e2e8f0;
    }
    .tt-rail-list li:last-child::before { display: none; }
    .tt-rail-list .is-break .dot { background: #d97706; box-shadow: 0 0 0 3px rgba(217, 119, 6, .16); }
    .tt-rail-list .is-assembly .dot { background: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, .16); }
    .tt-rail-list .what { font-weight: 700; color: #0f172a; }
    .tt-rail-list .mins { color: #94a3b8; font-weight: 500; }
    .tt-rail-end {
        margin-top: 16px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #e5e7eb;
        font-size: 12px;
        font-weight: 700;
        color: #0f766e;
    }
    .tt-apply {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .tt-apply label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        padding: 6px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        background: #fff;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
    }
    .tt-apply input { margin: 0; }
    .tt-empty-slots {
        text-align: center;
        padding: 36px 12px;
        color: #64748b;
    }
    .tt-empty-slots i { font-size: 28px; color: #25A194; display: block; margin-bottom: 8px; }
    @media (max-width: 1199px) {
        .tt-board { grid-template-columns: 1fr; }
        .tt-rail { border-inline-start: 0; border-top: 1px solid #e5e7eb; }
    }
    @media (max-width: 767px) {
        .tt-cols { display: none; }
        .tt-slot, .tt-slot-name {
            display: flex;
            flex-wrap: wrap;
        }
        .tt-slot {
            grid-template-columns: none;
        }
        .tt-slot-time { width: 100%; }
        .tt-slot-name { flex: 1 1 160px; }
        .tt-slot-subject, .tt-slot-mins { flex: 1 1 120px; }
        .tt-slot-actions { width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Timetable',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Class Timetables', 'url' => route('timetable')],
            ['label' => 'Period times', 'url' => null, 'active' => true],
        ],
        'title' => $class ? $class->name.' · '.$dayName : 'Period times',
        'subtitle' => $class
            ? 'You are setting '.$class->name.' for '.$dayName.'. Switch days to give each weekday its own subjects.'
            : 'Choose a class, then set Monday to Friday one day at a time.',
        'actions' => $class
            ? '<a href="'.route('timetable-show', ['class' => $class->id] + $periodQuery).'" class="btn btn-outline-primary-600"><i class="ri-calendar-schedule-line"></i> View week</a>'
            : '<a href="'.route('timetable').'" class="btn btn-outline-primary-600"><i class="ri-calendar-schedule-line"></i> Class timetables</a>',
    ])

    <div class="card ac-list-wrapper mb-24">
        <div class="card-header py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-12">
            <h6 class="mb-0 fw-semibold">Class and academic period</h6>
            <form method="GET" action="{{ route('timetable-periods') }}" class="ah-period-filter d-flex flex-wrap align-items-end gap-3" id="ttContextFilter">
                <input type="hidden" name="day" value="{{ $day }}">
                <div>
                    <label class="form-label text-xs fw-semibold text-secondary-light mb-4">Class</label>
                    <select name="school_class_id" class="form-select radius-4" style="min-width:180px;" @disabled($classes->isEmpty())>
                        @forelse($classes as $option)
                            <option value="{{ $option->id }}" @selected($class && (int) $class->id === (int) $option->id)>{{ $option->name }}</option>
                        @empty
                            <option value="">No active classes</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs fw-semibold text-secondary-light mb-4">Academic Year</label>
                    <select name="academic_year_id" class="form-select radius-4" style="min-width:160px;">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) ($period['year_id'] ?? '') === (string) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs fw-semibold text-secondary-light mb-4">Term</label>
                    <select name="academic_term_id" class="form-select radius-4" style="min-width:140px;">
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}" @selected((string) ($period['term_id'] ?? '') === (string) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        @if($class)
            <div class="px-24 py-16">
                <div class="tt-day-tabs">
                    @foreach($days as $num => $name)
                        <a class="tt-day-tab {{ (int) $day === (int) $num ? 'is-active' : '' }} {{ ! empty($savedDays[$num]) ? 'has-saved' : '' }}"
                           href="{{ route('timetable-periods', ['school_class_id' => $class->id, 'day' => $num] + $periodQuery) }}">
                            <span class="dot"></span> {{ $name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if(! $class)
        <div class="card ac-list-wrapper">
            <div class="card-body p-24 text-center text-secondary-light">
                <i class="ri-layout-grid-line" style="font-size:32px;color:#25A194;"></i>
                <p class="mt-12 mb-0">Add an active class first, then set its Monday to Friday timetable here.</p>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('timetable-periods-process') }}" class="tt-day" id="dayForm">
            @csrf
            <input type="hidden" name="school_class_id" value="{{ $class->id }}">
            <input type="hidden" name="day" value="{{ $day }}">
            <input type="hidden" name="academic_year_id" value="{{ $period['year_id'] }}">
            <input type="hidden" name="academic_term_id" value="{{ $period['term_id'] }}">

            <div class="tt-toolbar">
                <div class="d-flex flex-wrap align-items-end gap-12">
                    <div class="tt-start">
                        <label class="text-xs fw-semibold text-secondary-light mb-4">Day starts</label>
                        <input class="form-control" type="time" name="start_time" id="startTime" value="{{ $dayStart }}" required>
                    </div>
                    <div class="pb-1 text-secondary-light text-sm">
                        {{ $class->category?->name ?: 'Class' }}{{ $class->classTeacher ? ' · '.$class->classTeacher->full_name : '' }}
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-8">
                    <button type="button" class="btn btn-outline-primary-600 btn-sm" data-add="assembly"><i class="ri-flag-line"></i> Assembly</button>
                    <button type="button" class="btn btn-primary-600 btn-sm" data-add="lesson"><i class="ri-add-line"></i> Period</button>
                    <button type="button" class="btn btn-outline-primary-600 btn-sm" data-add="break"><i class="ri-cup-line"></i> Break</button>
                </div>
            </div>

            <div class="tt-board">
                <div class="tt-track">
                    @if($courses->isEmpty())
                        <p class="ac-pill ac-pill-amber mb-16">No subjects are registered for {{ $class->name }} in this year and term. Register this class’s courses first.</p>
                    @endif
                    <div class="tt-cols">
                        <span>Time</span>
                        <span>Slot</span>
                        <span>Subject</span>
                        <span>Min</span>
                        <span></span>
                    </div>
                    <div id="slotList"></div>
                </div>
                <aside class="tt-rail">
                    <h6 class="mb-0 fw-semibold">{{ $dayName }} clock</h6>
                    <small class="text-secondary-light">Live preview for {{ $class->name }}</small>
                    <div id="clockPreview"></div>
                </aside>
            </div>

            <div class="card-footer py-16 px-24 bg-white">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-12">
                    <div>
                        <div class="text-xs fw-semibold text-secondary-light mb-8">Also apply these subjects to</div>
                        <div class="tt-apply">
                            @foreach($days as $num => $name)
                                @if((int) $num !== (int) $day)
                                    <label>
                                        <input type="checkbox" name="apply_days[]" value="{{ $num }}"> {{ $name }}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-8">
                        <a class="btn btn-outline-primary-600" href="{{ route('timetable-show', ['class' => $class->id] + $periodQuery) }}">View week</a>
                        <button class="btn btn-primary-600" type="submit"><i class="ri-save-line"></i> Save {{ $class->name }} · {{ $dayName }}</button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('#ttContextFilter select').forEach(function (select) {
        select.addEventListener('change', function () {
            document.getElementById('ttContextFilter').submit();
        });
    });

    (function () {
        const list = document.getElementById('slotList');
        const preview = document.getElementById('clockPreview');
        const startInput = document.getElementById('startTime');
        if (!list || !startInput) return;

        const courses = @json($courses);
        const slots = @json($slots);

        function pad(n) { return String(n).padStart(2, '0'); }
        function addMinutes(time, minutes) {
            const parts = (time || '07:30').split(':');
            let total = (parseInt(parts[0], 10) * 60) + parseInt(parts[1] || '0', 10) + (parseInt(minutes, 10) || 0);
            total = ((total % (24 * 60)) + (24 * 60)) % (24 * 60);
            return pad(Math.floor(total / 60)) + ':' + pad(total % 60);
        }
        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char];
            });
        }
        function courseName(id) {
            const course = courses.find(function (item) { return String(item.id) === String(id); });
            return course ? course.name : '';
        }
        function courseOptions(selected) {
            let html = '<option value="">Select subject</option>';
            courses.forEach(function (course) {
                html += '<option value="' + course.id + '"' + (String(selected || '') === String(course.id) ? ' selected' : '') + '>' + escapeHtml(course.name) + '</option>';
            });
            return html;
        }
        function iconFor(kind) {
            if (kind === 'break') return 'ri-cup-line';
            if (kind === 'assembly') return 'ri-flag-line';
            return 'ri-book-open-line';
        }

        function render() {
            list.innerHTML = '';
            if (!slots.length) {
                list.innerHTML = '<div class="tt-empty-slots"><i class="ri-calendar-schedule-line"></i>Add a period, break, or assembly to start the day.</div>';
                renderClock();
                return;
            }

            slots.forEach(function (slot, index) {
                const card = document.createElement('div');
                card.className = 'tt-slot is-' + slot.kind;
                const subjectCell = slot.kind === 'lesson'
                    ? '<div class="tt-slot-subject"><select class="form-control form-select" name="slots[' + index + '][course_id]">' + courseOptions(slot.course_id) + '</select></div>'
                    : '<div class="tt-slot-muted">No subject</div>';

                card.innerHTML =
                    '<input type="hidden" name="slots[' + index + '][id]" value="' + (slot.id || '') + '">' +
                    '<input type="hidden" name="slots[' + index + '][kind]" value="' + slot.kind + '">' +
                    '<div class="tt-slot-time">—<small>—</small></div>' +
                    '<div class="tt-slot-name">' +
                        '<span class="tt-slot-ico"><i class="' + iconFor(slot.kind) + '"></i></span>' +
                        '<input class="form-control" name="slots[' + index + '][label]" value="' + escapeHtml(slot.label || '') + '" placeholder="' + (slot.kind === 'lesson' ? 'Period' : (slot.kind === 'break' ? 'Break' : 'Assembly')) + '">' +
                    '</div>' +
                    subjectCell +
                    '<div class="tt-slot-mins"><input class="form-control" type="number" min="5" max="90" name="slots[' + index + '][duration_minutes]" value="' + (slot.duration_minutes || 50) + '"></div>' +
                    '<div class="tt-slot-actions">' +
                        '<button type="button" class="tt-icon-btn" data-move="' + index + '" data-dir="-1"' + (index === 0 ? ' disabled' : '') + ' title="Move up"><i class="ri-arrow-up-s-line"></i></button>' +
                        '<button type="button" class="tt-icon-btn" data-move="' + index + '" data-dir="1"' + (index === slots.length - 1 ? ' disabled' : '') + ' title="Move down"><i class="ri-arrow-down-s-line"></i></button>' +
                        '<button type="button" class="tt-icon-btn is-danger" data-remove="' + index + '" title="Remove"><i class="ri-delete-bin-line"></i></button>' +
                    '</div>';
                list.appendChild(card);
            });

            list.querySelectorAll('input, select').forEach(function (field) {
                field.addEventListener('input', sync);
                field.addEventListener('change', sync);
            });
            list.querySelectorAll('[data-remove]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    sync();
                    slots.splice(parseInt(this.getAttribute('data-remove'), 10), 1);
                    render();
                });
            });
            list.querySelectorAll('[data-move]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    sync();
                    const i = parseInt(this.getAttribute('data-move'), 10);
                    const j = i + parseInt(this.getAttribute('data-dir'), 10);
                    if (j < 0 || j >= slots.length) return;
                    const tmp = slots[i];
                    slots[i] = slots[j];
                    slots[j] = tmp;
                    render();
                });
            });
            renderClock();
        }

        function sync() {
            list.querySelectorAll('.tt-slot').forEach(function (card, index) {
                const kind = card.querySelector('input[name$="[kind]"]').value;
                const course = card.querySelector('select[name$="[course_id]"]');
                slots[index] = {
                    id: card.querySelector('input[name$="[id]"]').value || null,
                    kind: kind,
                    label: card.querySelector('input[name$="[label]"]').value,
                    duration_minutes: parseInt(card.querySelector('input[name$="[duration_minutes]"]').value, 10) || 50,
                    course_id: course ? (course.value || null) : null
                };
            });
            renderClock();
        }

        function renderClock() {
            let cursor = startInput.value || '07:30';
            const items = [];
            const cards = list.querySelectorAll('.tt-slot');
            slots.forEach(function (slot, index) {
                const end = addMinutes(cursor, slot.duration_minutes || 50);
                if (cards[index]) {
                    cards[index].querySelector('.tt-slot-time').innerHTML = cursor + '<small>' + end + '</small>';
                }
                const name = slot.kind === 'lesson'
                    ? (courseName(slot.course_id) || slot.label || 'Period')
                    : (slot.label || slot.kind);
                items.push(
                    '<li class="is-' + slot.kind + '">' +
                        '<span class="when">' + cursor + '</span>' +
                        '<span class="dot"></span>' +
                        '<span><span class="what">' + escapeHtml(name) + '</span><span class="mins"> · ' + (slot.duration_minutes || 0) + ' min</span></span>' +
                    '</li>'
                );
                cursor = end;
            });
            preview.innerHTML =
                '<ul class="tt-rail-list">' + (items.join('') || '<li><span class="when"></span><span class="dot"></span><span class="what">Nothing on the day yet</span></li>') + '</ul>' +
                '<div class="tt-rail-end">Closes ' + cursor + '</div>';
        }

        document.querySelectorAll('[data-add]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                sync();
                const kind = this.getAttribute('data-add');
                slots.push({
                    id: null,
                    kind: kind,
                    label: kind === 'lesson' ? '' : (kind === 'break' ? 'Break' : 'Assembly & Registration'),
                    duration_minutes: kind === 'lesson' ? 50 : 30,
                    course_id: null
                });
                render();
            });
        });
        startInput.addEventListener('input', renderClock);
        render();
    })();
</script>
@endsection
