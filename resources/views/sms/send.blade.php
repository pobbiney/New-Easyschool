@php
    $pageName = "sms";
    $subpageName = "send-sms";
    $audiences = [
        ['key' => 'teachers', 'label' => 'Teachers', 'help' => 'Active teaching staff', 'icon' => 'ri-user-star-line', 'theme' => 'amber'],
        ['key' => 'staff', 'label' => 'All staff', 'help' => 'Every active employee', 'icon' => 'ri-team-line', 'theme' => 'teal'],
        ['key' => 'class', 'label' => 'A class', 'help' => 'Parents / guardians', 'icon' => 'ri-layout-grid-line', 'theme' => 'sky'],
        ['key' => 'school', 'label' => 'Entire school', 'help' => 'All student guardians', 'icon' => 'ri-building-4-line', 'theme' => 'violet'],
        ['key' => 'individual', 'label' => 'Individual', 'help' => 'Pick one or more people', 'icon' => 'ri-user-heart-line', 'theme' => 'indigo'],
    ];
    $audiencePills = [
        'teachers' => 'ac-pill-amber',
        'staff' => 'ac-pill-teal',
        'class' => 'ac-pill-sky',
        'school' => 'ac-pill-violet',
        'individual' => 'ac-pill-indigo',
    ];
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .sms-stat {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 18px 20px;
        background: #fff;
        height: 100%;
    }
    .sms-stat .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .sms-compose, .sms-side, .sms-history {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }
    .sms-compose { overflow: visible; }
    .sms-compose .card-header,
    .sms-side .card-header,
    .sms-history .card-header {
        background: #fff;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
    }
    .sms-audience {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }
    .sms-audience input { position: absolute; opacity: 0; pointer-events: none; }
    .sms-choice {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 14px;
        padding: 14px 10px 12px;
        background: #fff;
        cursor: pointer;
        text-align: center;
        height: 100%;
        display: block;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease, background .15s ease;
        position: relative;
        overflow: hidden;
    }
    .sms-choice::before {
        content: "";
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--sms-accent);
        opacity: .35;
    }
    .sms-choice:hover { transform: translateY(-1px); }
    .sms-audience input:checked + .sms-choice {
        border-color: var(--sms-accent);
        background: var(--sms-soft);
        box-shadow: 0 0 0 3px var(--sms-soft);
    }
    .sms-choice i {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 8px;
        background: var(--sms-soft);
        color: var(--sms-accent);
    }
    .sms-choice strong { display: block; font-size: 13px; color: #0f172a; }
    .sms-choice small { color: #64748b; font-size: 11px; }
    .sms-theme-teal { --sms-accent: #25A194; --sms-soft: rgba(37, 161, 148, .12); }
    .sms-theme-amber { --sms-accent: #d97706; --sms-soft: rgba(245, 158, 11, .14); }
    .sms-theme-sky { --sms-accent: #0284c7; --sms-soft: rgba(14, 165, 233, .12); }
    .sms-theme-violet { --sms-accent: #7c3aed; --sms-soft: rgba(139, 92, 246, .12); }
    .sms-theme-indigo { --sms-accent: #4f46e5; --sms-soft: rgba(99, 102, 241, .12); }
    .sms-meter {
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .sms-meter span {
        display: block;
        height: 100%;
        width: 0;
        border-radius: 999px;
        background: #25A194;
        transition: width .15s ease, background .15s ease;
    }
    .sms-meter.is-warn span { background: #d97706; }
    .sms-meter.is-full span { background: #e11d48; }
    .sms-phone {
        background: linear-gradient(180deg, #0f766e 0%, #115e59 48%, #0f172a 48%);
        border-radius: 28px;
        padding: 14px 12px 18px;
        color: #fff;
        min-height: 360px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    }
    .sms-phone-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        opacity: .85;
        margin-bottom: 18px;
        padding: 0 8px;
    }
    .sms-phone-screen {
        background: #f8fafc;
        border-radius: 18px;
        min-height: 280px;
        padding: 16px 14px;
        color: #0f172a;
    }
    .sms-phone-from {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 12px;
    }
    .sms-bubble {
        background: #25A194;
        color: #fff;
        border-radius: 16px 16px 4px 16px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
        min-height: 52px;
        box-shadow: 0 8px 18px rgba(37, 161, 148, .25);
    }
    .sms-bubble.is-empty { background: #e2e8f0; color: #94a3b8; box-shadow: none; }
    .sms-reach {
        border: 1px dashed #99f6e4;
        background: #f0fdfa;
        border-radius: 14px;
        padding: 16px;
    }
    .sms-reach-count {
        font-size: 28px;
        font-weight: 700;
        color: #0f766e;
        line-height: 1;
    }
    .sms-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #ccfbf1;
        color: #0f766e;
        font-size: 11px;
        font-weight: 600;
        margin: 0 6px 6px 0;
    }
    .sms-history-row td { vertical-align: middle; }
    .sms-msg {
        max-width: 420px;
        color: #334155;
        font-size: 13px;
    }
    .sms-empty { text-align: center; padding: 48px 16px; color: #64748b; }
    .sms-empty i { font-size: 34px; color: #25A194; display: block; margin-bottom: 8px; }
    .sms-typeahead { position: relative; }
    .sms-selected {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 0;
    }
    .sms-selected:empty { display: none; }
    .sms-chip-pick {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px 4px 10px;
        border-radius: 999px;
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        color: #3730a3;
        font-size: 12px;
        font-weight: 600;
        max-width: 100%;
    }
    .sms-chip-pick button {
        border: 0;
        background: transparent;
        color: #4338ca;
        padding: 0;
        line-height: 1;
        font-size: 16px;
        cursor: pointer;
    }
    .sms-suggest {
        position: absolute;
        inset-inline: 0;
        top: calc(100% + 4px);
        z-index: 30;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        max-height: 260px;
        overflow-y: auto;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .14);
        display: none;
    }
    .sms-suggest.is-open { display: block; }
    .sms-suggest-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: #fff;
        padding: 10px 12px;
        font-size: 13px;
        color: #0f172a;
        cursor: pointer;
    }
    .sms-suggest-item:hover,
    .sms-suggest-item.is-active { background: #eef2ff; }
    .sms-suggest-meta { color: #64748b; font-weight: 500; }
    .sms-suggest-empty {
        padding: 14px 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }
    .sms-modal {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .45);
    }
    .sms-modal.is-open { display: flex; }
    .sms-modal-card {
        width: min(460px, 100%);
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .25);
    }
    @media (max-width: 1199px) {
        .sms-audience { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .sms-audience { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sms-phone { min-height: 0; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Messaging',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Send SMS', 'url' => null, 'active' => true],
        ],
        'title' => 'Send SMS',
        'subtitle' => 'Compose once. Reach teachers, staff, a class, the whole school, or selected people.',
        'actions' => $configured
            ? '<span class="ac-pill ac-pill-emerald"><i class="ri-checkbox-circle-line"></i> Ready · '.e($senderId).'</span>'
            : '<span class="ac-pill ac-pill-rose"><i class="ri-error-warning-line"></i> Gateway not ready</span>',
    ])

    <div class="row g-3 mb-24">
        <div class="col-md-3 col-sm-6">
            <div class="sms-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Sent today</div>
                        <h4 class="fw-semibold mb-0">{{ number_format($stats['today']) }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-send-plane-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="sms-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">This month</div>
                        <h4 class="fw-semibold mb-0">{{ number_format($stats['month']) }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-calendar-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="sms-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Campaigns</div>
                        <h4 class="fw-semibold mb-0">{{ number_format($stats['campaigns']) }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-chat-history-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="sms-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Sender ID</div>
                        <h4 class="fw-semibold mb-0">{{ $senderId ?: '—' }}</h4>
                    </div>
                    <span class="stat-icon {{ $configured ? 'bg-success-100 text-success-600' : 'bg-danger-100 text-danger-600' }}">
                        <i class="{{ $configured ? 'ri-wifi-line' : 'ri-wifi-off-line' }}"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('send-sms-process') }}" id="smsForm">
        @csrf
        <div class="row g-3 mb-24">
            <div class="col-xl-7">
                <div class="sms-compose">
                    <div class="card-header py-16 px-24 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-semibold">Compose</h6>
                            <small class="text-secondary-light">Staff get employee phones. Classes and the school use parent numbers.</small>
                        </div>
                    </div>
                    <div class="card-body p-24">
                        <p class="text-sm fw-semibold mb-12">Who should receive this?</p>
                        <div class="sms-audience mb-20">
                            @foreach($audiences as $audience)
                                <div>
                                    <input type="radio" name="audience" id="aud_{{ $audience['key'] }}" value="{{ $audience['key'] }}" {{ old('audience', 'teachers') === $audience['key'] ? 'checked' : '' }}>
                                    <label for="aud_{{ $audience['key'] }}" class="sms-choice sms-theme-{{ $audience['theme'] }}">
                                        <i class="{{ $audience['icon'] }}"></i>
                                        <strong>{{ $audience['label'] }}</strong>
                                        <small>{{ $audience['help'] }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="row gy-3 mb-16" id="audienceExtras">
                            <div class="col-md-12 extra extra-class d-none">
                                <label class="text-sm fw-semibold mb-8">Class</label>
                                <select name="school_class_id" class="form-control form-select">
                                    <option value="">Select class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" @selected((string) old('school_class_id') === (string) $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 extra extra-individual d-none">
                                <label class="text-sm fw-semibold mb-8">Send to</label>
                                <select name="target_type" id="targetType" class="form-control form-select">
                                    <option value="staff" @selected(old('target_type', 'staff') === 'staff')>Staff member</option>
                                    <option value="student" @selected(old('target_type') === 'student')>Student / guardian</option>
                                </select>
                            </div>
                            <div class="col-md-8 extra extra-individual d-none">
                                <div class="d-flex justify-content-between align-items-center mb-8">
                                    <label class="text-sm fw-semibold mb-0" for="personSearch">People</label>
                                    <small class="text-secondary-light" id="selectedCount">0 selected</small>
                                </div>
                                <div class="sms-typeahead mb-8" id="personPicker">
                                    <input type="search" id="personSearch" class="form-control" autocomplete="off" placeholder="Type a name, then pick from the list…">
                                    <div class="sms-suggest" id="personSuggest" role="listbox"></div>
                                </div>
                                <div id="selectedPeople" class="sms-selected"></div>
                                <div id="targetIds"></div>
                            </div>
                        </div>

                        <div class="mb-8 d-flex justify-content-between align-items-center">
                            <label class="text-sm fw-semibold mb-0" for="smsMessage">Message</label>
                            <small class="text-secondary-light"><span id="charCount">0</span> / 1000 · <span id="smsParts">1</span> SMS part<span id="smsPartsPlural">s</span></small>
                        </div>
                        <textarea name="message" id="smsMessage" class="form-control mb-10" rows="6" maxlength="1000" required placeholder="Type the SMS parents or staff will read on their phones…">{{ old('message') }}</textarea>
                        <div class="sms-meter mb-20" id="smsMeter"><span></span></div>

                        <div class="d-flex flex-wrap align-items-center gap-12">
                            <button type="submit" class="btn btn-primary-600" id="sendBtn" @disabled(! $configured)>
                                <i class="ri-send-plane-line"></i> Send SMS
                            </button>
                            @unless($configured)
                                <span class="text-sm text-danger-600">Add the mNotify API key in .env before sending.</span>
                            @endunless
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="sms-side mb-16">
                    <div class="card-header py-16 px-24">
                        <h6 class="mb-0 fw-semibold">Live preview</h6>
                    </div>
                    <div class="card-body p-24">
                        <div class="sms-phone">
                            <div class="sms-phone-bar">
                                <span>9:41</span>
                                <span><i class="ri-signal-wifi-line"></i> <i class="ri-battery-2-charge-line"></i></span>
                            </div>
                            <div class="sms-phone-screen">
                                <div class="sms-phone-from">From {{ $senderId ?: 'School' }}</div>
                                <div class="sms-bubble is-empty" id="smsBubble">Your message will appear here.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sms-side">
                    <div class="card-header py-16 px-24">
                        <h6 class="mb-0 fw-semibold">Recipients</h6>
                    </div>
                    <div class="card-body p-24">
                        <div class="sms-reach" id="smsPreview">
                            <div class="d-flex align-items-end justify-content-between gap-12 mb-8">
                                <div>
                                    <div class="text-sm text-secondary-light mb-6">Will be reached</div>
                                    <div class="sms-reach-count" id="previewCount">—</div>
                                </div>
                                <span class="ac-pill ac-pill-teal" id="previewLabel">Choose an audience</span>
                            </div>
                            <p class="text-sm mb-10" id="previewNote">Pick who should receive this message to see the count.</p>
                            <div id="previewSample"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="sms-history">
        <div class="card-header py-16 px-24 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold">Recent messages</h6>
            <small class="text-secondary-light">Last {{ $messages->count() }}</small>
        </div>
        <div class="table-responsive">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Audience</th>
                        <th>Message</th>
                        <th>Sent</th>
                        <th>By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $row)
                        <tr class="sms-history-row">
                            <td>
                                <div class="fw-semibold">{{ $row->created_at->format('d M Y') }}</div>
                                <small class="text-secondary-light">{{ $row->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="ac-pill {{ $audiencePills[$row->audience] ?? 'ac-pill-teal' }}">
                                    {{ $row->audience_label ?: ucfirst($row->audience) }}
                                </span>
                            </td>
                            <td><div class="sms-msg">{{ \Illuminate\Support\Str::limit($row->message, 90) }}</div></td>
                            <td>
                                <strong>{{ $row->sent_count }}</strong>
                                @if($row->skipped_count)
                                    <div class="text-sm text-secondary-light">{{ $row->skipped_count }} skipped</div>
                                @endif
                            </td>
                            <td>{{ $row->creator?->name ?: '—' }}</td>
                            <td>
                                <span class="ac-pill {{ $row->status === 'sent' ? 'ac-pill-emerald' : 'ac-pill-rose' }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="sms-empty">
                                    <i class="ri-chat-off-line"></i>
                                    No SMS has been sent yet. Compose a message to get started.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="sms-modal" id="schoolConfirm" role="dialog" aria-modal="true" aria-labelledby="schoolConfirmTitle">
    <div class="sms-modal-card">
        <div class="d-flex align-items-start gap-12 mb-12">
            <span class="ac-avatar" style="background:rgba(139,92,246,.12);color:#7c3aed;">
                <i class="ri-building-4-line"></i>
            </span>
            <div>
                <h6 class="fw-semibold mb-4" id="schoolConfirmTitle">Send to the entire school?</h6>
                <p class="text-sm text-secondary-light mb-0">This will go to every parent / guardian with a valid phone number. You cannot undo it after it is sent.</p>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-8 mt-20">
            <button type="button" class="btn btn-outline-secondary" id="schoolCancel">Cancel</button>
            <button type="button" class="btn btn-primary-600" id="schoolOk">Send to school</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $staffPeople = $staffMembers->map(function ($member) {
        return [
            'id' => (int) $member->id,
            'name' => $member->full_name,
            'meta' => $member->employee_id ?: '',
            'search' => strtolower(trim($member->full_name.' '.($member->employee_id ?? ''))),
        ];
    })->values();
    $studentPeople = $students->map(function ($student) {
        return [
            'id' => (int) $student->id,
            'name' => $student->full_name,
            'meta' => $student->class_name ?: ($student->student_id ?: ''),
            'search' => strtolower(trim($student->full_name.' '.($student->student_id ?? '').' '.($student->class_name ?? ''))),
        ];
    })->values();
    $oldSmsTargetType = old('target_type', 'staff');
    $oldSmsTargetIds = array_values(array_map('intval', (array) old('target_ids', old('target_id') ? [old('target_id')] : [])));
@endphp
<script>
    (function () {
        const form = document.getElementById('smsForm');
        const message = document.getElementById('smsMessage');
        const charCount = document.getElementById('charCount');
        const smsParts = document.getElementById('smsParts');
        const smsPartsPlural = document.getElementById('smsPartsPlural');
        const smsMeter = document.getElementById('smsMeter');
        const smsBubble = document.getElementById('smsBubble');
        const targetType = document.getElementById('targetType');
        const personSearch = document.getElementById('personSearch');
        const personSuggest = document.getElementById('personSuggest');
        const personPicker = document.getElementById('personPicker');
        const selectedPeople = document.getElementById('selectedPeople');
        const selectedCount = document.getElementById('selectedCount');
        const targetIds = document.getElementById('targetIds');
        const previewCount = document.getElementById('previewCount');
        const previewLabel = document.getElementById('previewLabel');
        const previewNote = document.getElementById('previewNote');
        const previewSample = document.getElementById('previewSample');
        const schoolConfirm = document.getElementById('schoolConfirm');
        const sendBtn = document.getElementById('sendBtn');
        const recipientsUrl = @json(route('send-sms-recipients'));
        const people = {
            staff: @json($staffPeople),
            student: @json($studentPeople),
        };
        const selected = { staff: [], student: [] };
        const oldTargetType = @json($oldSmsTargetType);
        const oldTargetIds = @json($oldSmsTargetIds);
        let schoolConfirmed = false;
        let previewTimer = null;
        let activeIndex = 0;
        let matches = [];

        (oldTargetIds || []).forEach(function (id) {
            const person = (people[oldTargetType] || []).find(function (row) { return Number(row.id) === Number(id); });
            if (person && !selected[oldTargetType].some(function (row) { return Number(row.id) === Number(person.id); })) {
                selected[oldTargetType].push(person);
            }
        });

        function audience() {
            const checked = form.querySelector('input[name="audience"]:checked');
            return checked ? checked.value : 'teachers';
        }

        function currentType() {
            return targetType.value === 'student' ? 'student' : 'staff';
        }

        function selectedForType() {
            return selected[currentType()];
        }

        function syncExtras() {
            const value = audience();
            document.querySelectorAll('.extra').forEach(function (el) { el.classList.add('d-none'); });
            document.querySelectorAll('.extra-' + value).forEach(function (el) { el.classList.remove('d-none'); });
            closeSuggest();
            if (personSearch) personSearch.value = '';
            renderSelected();
            loadPreview();
        }

        function closeSuggest() {
            personSuggest.classList.remove('is-open');
            personSuggest.innerHTML = '';
            matches = [];
            activeIndex = 0;
        }

        function renderSelected() {
            const picks = selectedForType();
            selectedCount.textContent = picks.length + ' selected';
            selectedPeople.innerHTML = '';
            targetIds.innerHTML = '';
            picks.forEach(function (person) {
                const chip = document.createElement('span');
                chip.className = 'sms-chip-pick';
                chip.innerHTML = '<span></span><button type="button" aria-label="Remove">&times;</button>';
                chip.querySelector('span').textContent = person.name;
                chip.querySelector('button').addEventListener('click', function () {
                    removePerson(person.id);
                });
                selectedPeople.appendChild(chip);

                if (audience() === 'individual') {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'target_ids[]';
                    input.value = person.id;
                    targetIds.appendChild(input);
                }
            });
        }

        function addPerson(person) {
            if (!person) return;
            if (selectedForType().some(function (row) { return Number(row.id) === Number(person.id); })) return;
            selectedForType().push(person);
            personSearch.value = '';
            closeSuggest();
            renderSelected();
            loadPreview();
            personSearch.focus();
        }

        function removePerson(id) {
            selected[currentType()] = selectedForType().filter(function (row) { return Number(row.id) !== Number(id); });
            renderSelected();
            loadPreview();
            personSearch.focus();
        }

        function filterPeople() {
            const q = (personSearch.value || '').trim().toLowerCase();
            if (!q) {
                closeSuggest();
                return;
            }
            const picked = new Set(selectedForType().map(function (row) { return Number(row.id); }));
            matches = (people[currentType()] || []).filter(function (row) {
                return !picked.has(Number(row.id)) && (row.search || '').includes(q);
            }).slice(0, 12);
            activeIndex = 0;
            renderSuggest();
        }

        function renderSuggest() {
            personSuggest.innerHTML = '';
            if (matches.length === 0) {
                personSuggest.innerHTML = '<div class="sms-suggest-empty">No matching people</div>';
                personSuggest.classList.add('is-open');
                return;
            }
            matches.forEach(function (person, index) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sms-suggest-item' + (index === activeIndex ? ' is-active' : '');
                btn.setAttribute('role', 'option');
                btn.innerHTML = '<strong></strong>' + (person.meta ? ' <span class="sms-suggest-meta">· </span>' : '');
                btn.querySelector('strong').textContent = person.name;
                if (person.meta) btn.querySelector('.sms-suggest-meta').textContent = '· ' + person.meta;
                btn.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    addPerson(person);
                });
                personSuggest.appendChild(btn);
            });
            personSuggest.classList.add('is-open');
        }

        function moveActive(delta) {
            if (!matches.length) return;
            activeIndex = (activeIndex + delta + matches.length) % matches.length;
            const items = personSuggest.querySelectorAll('.sms-suggest-item');
            items.forEach(function (item, index) {
                item.classList.toggle('is-active', index === activeIndex);
            });
            if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function setPreview(count, label, note, sample) {
            previewCount.textContent = count === null || count === undefined ? '—' : count;
            previewLabel.textContent = label || 'Recipients';
            previewNote.textContent = note || '';
            previewSample.innerHTML = '';
            (sample || []).forEach(function (name) {
                const chip = document.createElement('span');
                chip.className = 'sms-chip';
                chip.textContent = name;
                previewSample.appendChild(chip);
            });
        }

        function loadPreview() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(fetchPreview, 120);
        }

        function fetchPreview() {
            const params = new URLSearchParams();
            params.set('audience', audience());
            params.set('school_class_id', form.school_class_id.value || '');
            params.set('target_type', targetType.value || '');
            selectedForType().forEach(function (person) {
                params.append('target_ids[]', person.id);
            });

            setPreview('…', 'Counting', 'Counting recipients…', []);

            fetch(recipientsUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        setPreview('—', 'Choose audience', data.message || 'Choose who should receive this message.', []);
                        return;
                    }
                    let note = data.count + ' recipient' + (data.count === 1 ? '' : 's') + ' will be reached.';
                    if (data.skipped) note += ' ' + data.skipped + ' skipped because there is no phone number.';
                    setPreview(data.count, data.label, note, data.sample || []);
                })
                .catch(function () {
                    setPreview('—', 'Unavailable', 'Could not count recipients.', []);
                });
        }

        function updateCount() {
            const text = message.value || '';
            const len = text.length;
            const parts = Math.max(1, Math.ceil(len / 160));
            charCount.textContent = len;
            smsParts.textContent = parts;
            smsPartsPlural.textContent = parts === 1 ? '' : 's';
            smsMeter.classList.toggle('is-warn', len >= 160 && len < 900);
            smsMeter.classList.toggle('is-full', len >= 900);
            smsMeter.querySelector('span').style.width = Math.min(100, (len / 1000) * 100) + '%';
            smsBubble.textContent = text.trim() ? text : 'Your message will appear here.';
            smsBubble.classList.toggle('is-empty', !text.trim());
        }

        form.querySelectorAll('input[name="audience"]').forEach(function (input) {
            input.addEventListener('change', syncExtras);
        });
        form.school_class_id.addEventListener('change', loadPreview);
        targetType.addEventListener('change', function () {
            if (personSearch) personSearch.value = '';
            closeSuggest();
            renderSelected();
            loadPreview();
        });
        message.addEventListener('input', updateCount);

        personSearch.addEventListener('input', filterPeople);
        personSearch.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                moveActive(1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                moveActive(-1);
            } else if (event.key === 'Enter') {
                if (personSuggest.classList.contains('is-open') && matches[activeIndex]) {
                    event.preventDefault();
                    addPerson(matches[activeIndex]);
                }
            } else if (event.key === 'Escape') {
                closeSuggest();
            } else if (event.key === 'Backspace' && !personSearch.value && selectedForType().length) {
                removePerson(selectedForType()[selectedForType().length - 1].id);
            }
        });
        document.addEventListener('click', function (event) {
            if (personPicker && !personPicker.contains(event.target)) closeSuggest();
        });

        form.addEventListener('submit', function (event) {
            if (audience() === 'school' && !schoolConfirmed) {
                event.preventDefault();
                schoolConfirm.classList.add('is-open');
                return;
            }
            if (sendBtn && !sendBtn.disabled) {
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="ri-loader-4-line"></i> Sending…';
            }
        });

        document.getElementById('schoolCancel').addEventListener('click', function () {
            schoolConfirmed = false;
            schoolConfirm.classList.remove('is-open');
        });
        document.getElementById('schoolOk').addEventListener('click', function () {
            schoolConfirmed = true;
            schoolConfirm.classList.remove('is-open');
            form.requestSubmit();
        });
        schoolConfirm.addEventListener('click', function (event) {
            if (event.target === schoolConfirm) {
                schoolConfirmed = false;
                schoolConfirm.classList.remove('is-open');
            }
        });

        syncExtras();
        updateCount();
    })();
</script>
@endsection
