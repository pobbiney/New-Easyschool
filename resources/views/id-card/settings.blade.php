@php
    $pageName = 'id-card';
    $subpageName = 'id-card-settings';
    $settings = $settings ?? null;
    $school = $school ?? null;
    $palette = $palette ?? ['dark' => '#3B0A5C', 'accent' => '#F5C518', 'ink' => '#1A0B2E'];
    $audience = ($audience ?? request('tab', 'student')) === 'employee' ? 'employee' : 'student';
    $selectedFields = old('student_fields', $selectedFields ?? []);
    $selectedEmployeeFields = old('employee_fields', $selectedEmployeeFields ?? []);
    $fieldGroups = $fieldGroups ?? [];
    $employeeFieldGroups = $employeeFieldGroups ?? [];
    $signatureUrl = $signatureUrl ?? null;
    $logoUrl = $school && method_exists($school, 'logoUrl') ? $school->logoUrl() : asset('assets/images/logo-icon.png');
    $sampleValues = [
        'picture' => 'Photo',
        'student_id' => 'STU-0001',
        'full_name' => 'Ama Mensah',
        'firstname' => 'Ama',
        'othername' => 'Akosua',
        'surname' => 'Mensah',
        'gender' => 'Female',
        'dob' => '12 Mar 2012',
        'category' => 'Day Student',
        'status' => 'Active',
        'nhis_number' => 'NHIA-102938',
    ];
    $employeeSampleValues = [
        'picture' => 'Photo',
        'employee_id' => 'EMP-0042',
        'full_name' => 'Kwame Boateng',
        'title' => 'Mr',
        'firstname' => 'Kwame',
        'othername' => 'Kofi',
        'surname' => 'Boateng',
        'gender' => 'Male',
        'dob' => '04 Aug 1988',
        'position' => 'Staff',
        'status' => 'Active',
    ];
    $shortLabels = [
        'student_id' => 'ID',
        'dob' => 'D.O.B',
        'nhis_number' => 'NHIA',
        'gender' => 'Gender',
        'category' => 'Category',
        'status' => 'Status',
        'firstname' => 'First name',
        'othername' => 'Other name',
        'surname' => 'Surname',
        'full_name' => 'Name',
        'picture' => 'Photo',
        'employee_id' => 'ID',
        'position' => 'Position',
        'title' => 'Title',
    ];
    $allLabels = [];
    foreach ($fieldGroups as $fields) {
        foreach ($fields as $key => $label) {
            $allLabels[$key] = $label;
        }
    }
@endphp
@extends('layouts.app')

@section('css')
<style>
    .idcard-page {
        --teal: {{ $palette['accent'] }};
        --ink: {{ $palette['ink'] }};
        --muted: #64748b;
        --card-dark: {{ $palette['dark'] }};
        --card-accent: {{ $palette['accent'] }};
        --card-ink: {{ $palette['ink'] }};
    }
    .idcard-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 991px) {
        .idcard-grid { grid-template-columns: 1fr; }
    }
    .idcard-preview-wrap {
        position: sticky;
        top: 88px;
        max-height: calc(100vh - 110px);
        overflow-y: auto;
        padding-right: 4px;
    }
    .idcard-preview-item { margin-bottom: 22px; }
    .idcard-preview-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--card-dark);
        margin: 0 0 10px 10px;
    }

    @include('id-card.partials._card-styles')
    .idcard-panel {
        background: #fff;
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(15,23,42,.05);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .idcard-panel-head { padding: 16px 22px; border-bottom: 1px solid #f1f5f9; }
    .idcard-panel-head h6 { margin: 0; font-weight: 700; }
    .idcard-panel-head p { margin: 4px 0 0; font-size: 13px; color: var(--muted); }
    .idcard-panel-body { padding: 20px 22px; }
    .idcard-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        min-height: 44px;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .idcard-check:hover { border-color: color-mix(in srgb, var(--card-accent) 55%, #fff); }
    .idcard-check.is-on {
        border-color: var(--card-accent);
        background: color-mix(in srgb, var(--card-accent) 14%, #fff);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--card-accent) 16%, transparent);
    }
    .idcard-check .form-check-input { margin: 0; flex-shrink: 0; cursor: pointer; width: 18px; height: 18px; }
    .idcard-check span { font-size: 13px; font-weight: 600; color: var(--ink); line-height: 1.3; }
    .idcard-group-title {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--card-dark);
        margin: 0 0 10px;
    }
    .idcard-drop {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 22px 18px;
        text-align: center;
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        position: relative;
        cursor: pointer;
    }
    .idcard-drop:hover, .idcard-drop.is-over { border-color: var(--card-accent); }
    .idcard-drop input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .idcard-drop-preview {
        height: 72px;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .idcard-drop-preview img { max-height: 72px; max-width: 220px; object-fit: contain; }
    .idcard-drop-preview i { font-size: 36px; color: #94a3b8; }
    .idcard-swatches { display: flex; gap: 8px; align-items: center; }
    .idcard-swatch { width: 22px; height: 22px; border-radius: 6px; border: 1px solid rgba(15,23,42,.12); }
    .idcard-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        padding: 4px;
        background: #fff;
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(15,23,42,.05);
        width: fit-content;
        max-width: 100%;
    }
    .idcard-tab {
        border: 0;
        background: transparent;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
    }
    .idcard-tab.active {
        background: var(--card-dark);
        color: #fff;
    }
    .idcard-audience-pane { display: none; }
    .idcard-audience-pane.is-on { display: block; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body idcard-page">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">ID Card</h1>
            <div>
                <a href="{{ route('dashboard', [], false) }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Card Settings</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('id-card-settings-update') }}" id="idCardSettingsForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tab" id="idCardAudienceInput" value="{{ $audience }}">

        <div class="idcard-tabs" role="tablist">
            <button type="button" class="idcard-tab {{ $audience === 'student' ? 'active' : '' }}" data-audience="student">Student</button>
            <button type="button" class="idcard-tab {{ $audience === 'employee' ? 'active' : '' }}" data-audience="employee">Employee</button>
        </div>

        <div class="idcard-grid">
            <div class="idcard-preview-wrap">
                <div class="idcard-preview-item">
                    <p class="idcard-preview-label">Front</p>
                    <div class="idcard-face">
                        <div class="idcard-face-head">
                            <div class="idcard-head-row">
                                <div class="idcard-hex">
                                    <div class="idcard-hex-inner">
                                        <img src="{{ $logoUrl }}" alt="">
                                    </div>
                                </div>
                                <div>
                                    <p class="idcard-school" id="idCardPreviewSchool">{{ $settings->header_text ?? ($school->name ?? 'School name') }}</p>
                                    <p class="idcard-tagline" id="idCardPreviewTitle">{{ $audience === 'employee' ? ($settings->employee_card_title ?? 'Employee Identity Card') : ($settings->card_title ?? ($school->motto ?? 'Student Identity Card')) }}</p>
                                </div>
                            </div>
                            <div class="idcard-wave"></div>
                            <div class="idcard-stripe"></div>
                        </div>

                        <div class="idcard-photo-wrap" id="idCardPreviewPhotoWrap">
                            <div class="idcard-photo" id="idCardPreviewPhoto">
                                <img src="{{ $audience === 'employee' ? asset('assets/images/id-card-employee.png') : asset('assets/images/id-card-school-girl.png') }}" alt="" id="idCardPreviewPhotoImg">
                            </div>
                        </div>

                        <div class="idcard-body">
                            <p class="idcard-name" id="idCardPreviewName">{{ $audience === 'employee' ? 'Kwame Boateng' : 'Ama Mensah' }}</p>
                            <p class="idcard-role" id="idCardPreviewRole">{{ $audience === 'employee' ? 'Employee' : 'STUDENT' }}</p>
                            <div class="idcard-mid-mark">
                                <img src="{{ $logoUrl }}" alt="">
                            </div>
                            <div class="idcard-meta" id="idCardPreviewRows"></div>
                            <div class="idcard-barcode" id="idCardPreviewBarcode">
                                <span class="idcard-qr-pane {{ $audience === 'student' ? 'is-on' : '' }}" data-audience="student">
                                    {!! QrCode::size(72)->margin(0)->errorCorrection('M')->generate('STU-0001') !!}
                                </span>
                                <span class="idcard-qr-pane {{ $audience === 'employee' ? 'is-on' : '' }}" data-audience="employee">
                                    {!! QrCode::size(72)->margin(0)->errorCorrection('M')->generate('EMP-0042') !!}
                                </span>
                            </div>
                        </div>

                        <div class="idcard-foot"></div>
                        <div class="idcard-foot-accent"></div>
                    </div>
                </div>

                <div class="idcard-preview-item">
                    <p class="idcard-preview-label">Back</p>
                    <div class="idcard-face is-back">
                        <div class="idcard-face-head">
                            <div class="idcard-head-row">
                                
                            </div>
                            <div class="idcard-wave"></div>
                            <div class="idcard-stripe"></div>
                        </div>
                        <div class="idcard-back-logo">
                            <img src="{{ $logoUrl }}" alt="">
                        </div>
                        <div class="idcard-back-body">
                            <p class="idcard-back-school" id="idCardBackSchool">{{ $settings->header_text ?? ($school->name ?? 'School name') }}</p>
                            <p class="idcard-back-motto">{{ $school->motto ?? 'Student Identity Card' }}</p>
                            <div class="idcard-back-block">
                                <span class="idcard-back-kicker">If found, return to</span>
                                @if (! empty($school->address))
                                    <div class="idcard-back-line"><i class="ri-map-pin-line"></i><span>{{ $school->address }}</span></div>
                                @endif
                                @if (! empty($school->phone))
                                    <div class="idcard-back-line"><i class="ri-phone-line"></i><span>{{ $school->phone }}</span></div>
                                @endif
                                @if (! empty($school->email))
                                    <div class="idcard-back-line"><i class="ri-mail-line"></i><span>{{ $school->email }}</span></div>
                                @endif
                                @if (empty($school->address) && empty($school->phone) && empty($school->email))
                                    <div class="idcard-back-line"><i class="ri-building-line"></i><span>School office</span></div>
                                @endif
                            </div>
                            <p class="idcard-back-note" id="idCardBackFooter">{{ $settings->footer_text ?? 'This card remains the property of the school and must be produced on request.' }}</p>
                            <div class="idcard-back-sign">
                                <img src="{{ $signatureUrl }}" alt="" id="idCardPreviewSignImg" @if (! $signatureUrl) style="display:none;padding-top:-15px" @endif>
                                <div class="line" id="idCardPreviewSignLine" @if ($signatureUrl) style="display:none" @endif></div><br/>
                                <small>Authorised signature</small>
                                <span id="idCardPreviewSignName" class="d-none">{{ $settings->director_name ?? 'Director' }}</span>
                            </div>
                        </div>
                        <div class="idcard-foot"></div>
                        <div class="idcard-foot-accent"></div>
                    </div>
                </div>
            </div>

            <div>
                <div class="idcard-panel">
                    <div class="idcard-panel-head">
                        <h6>Card layout</h6>
                        <p>School name and title printed on the card. Colours follow the logo.</p>
                    </div>
                    <div class="idcard-panel-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="text-sm fw-semibold mb-8" for="headerTextInput">School name on card</label>
                                <input type="text" name="header_text" id="headerTextInput" class="form-control" value="{{ old('header_text', $settings->header_text ?? ($school->name ?? '')) }}" maxlength="180">
                            </div>
                            <div class="col-md-6 idcard-title-row" data-audience="student" @if ($audience !== 'student') style="display:none" @endif>
                                <label class="text-sm fw-semibold mb-8" for="cardTitleInput">Student card title</label>
                                <input type="text" name="card_title" id="cardTitleInput" class="form-control" value="{{ old('card_title', $settings->card_title ?? ($school->motto ?? 'Student Identity Card')) }}" maxlength="120">
                            </div>
                            <div class="col-md-6 idcard-title-row" data-audience="employee" @if ($audience !== 'employee') style="display:none" @endif>
                                <label class="text-sm fw-semibold mb-8" for="employeeCardTitleInput">Employee card title</label>
                                <input type="text" name="employee_card_title" id="employeeCardTitleInput" class="form-control" value="{{ old('employee_card_title', $settings->employee_card_title ?? 'Employee Identity Card') }}" maxlength="120">
                            </div>
                            <div class="col-12">
                                <label class="text-sm fw-semibold mb-8" for="footerTextInput">Footer note</label>
                                <input type="text" name="footer_text" id="footerTextInput" class="form-control" value="{{ old('footer_text', $settings->footer_text ?? '') }}" maxlength="180" placeholder="If found, return to the school office">
                            </div>
                            <div class="col-12">
                                <div class="idcard-swatches">
                                    <span class="text-sm text-secondary-light">Logo colours</span>
                                    <span class="idcard-swatch" style="background: {{ $palette['dark'] }}" title="{{ $palette['dark'] }}"></span>
                                    <span class="idcard-swatch" style="background: {{ $palette['accent'] }}" title="{{ $palette['accent'] }}"></span>
                                    <span class="idcard-swatch" style="background: {{ $palette['ink'] }}" title="{{ $palette['ink'] }}"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="idcard-panel idcard-audience-pane {{ $audience === 'student' ? 'is-on' : '' }}" data-audience="student">
                    <div class="idcard-panel-head d-flex flex-wrap align-items-center justify-content-between gap-12">
                        <div>
                            <h6>Student details</h6>
                            <p>Select the checkboxes for fields that should appear on the student ID card. <strong class="idcard-selected-count" data-audience="student">{{ count($selectedFields) }}</strong> selected.</p>
                        </div>
                        <div class="d-flex gap-8">
                            <button type="button" class="btn btn-sm btn-outline-primary-600 idcard-select-all" data-audience="student">Select all</button>
                            <button type="button" class="btn btn-sm btn-outline-neutral-400 idcard-select-none" data-audience="student">Clear</button>
                        </div>
                    </div>
                    <div class="idcard-panel-body">
                        @error('student_fields')
                            <p class="text-danger-600 text-sm mb-16">{{ $message }}</p>
                        @enderror
                        <div class="row gy-4">
                            @foreach ($fieldGroups as $group => $fields)
                                <div class="col-12">
                                    <p class="idcard-group-title">{{ $group }}</p>
                                    <div class="row gy-2 gx-2">
                                        @foreach ($fields as $key => $label)
                                            @php $checked = in_array($key, $selectedFields, true); @endphp
                                            <div class="col-xl-4 col-sm-6">
                                                <label class="idcard-check {{ $checked ? 'is-on' : '' }}" for="idcard_student_{{ $key }}">
                                                    <input
                                                        class="form-check-input idcard-field-check"
                                                        type="checkbox"
                                                        name="student_fields[]"
                                                        data-audience="student"
                                                        id="idcard_student_{{ $key }}"
                                                        value="{{ $key }}"
                                                        {{ $checked ? 'checked' : '' }}
                                                    >
                                                    <span>{{ $label }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="idcard-panel idcard-audience-pane {{ $audience === 'employee' ? 'is-on' : '' }}" data-audience="employee">
                    <div class="idcard-panel-head d-flex flex-wrap align-items-center justify-content-between gap-12">
                        <div>
                            <h6>Employee details</h6>
                            <p>Select the checkboxes for fields that should appear on the employee ID card. <strong class="idcard-selected-count" data-audience="employee">{{ count($selectedEmployeeFields) }}</strong> selected.</p>
                        </div>
                        <div class="d-flex gap-8">
                            <button type="button" class="btn btn-sm btn-outline-primary-600 idcard-select-all" data-audience="employee">Select all</button>
                            <button type="button" class="btn btn-sm btn-outline-neutral-400 idcard-select-none" data-audience="employee">Clear</button>
                        </div>
                    </div>
                    <div class="idcard-panel-body">
                        @error('employee_fields')
                            <p class="text-danger-600 text-sm mb-16">{{ $message }}</p>
                        @enderror
                        <div class="row gy-4">
                            @foreach ($employeeFieldGroups as $group => $fields)
                                <div class="col-12">
                                    <p class="idcard-group-title">{{ $group }}</p>
                                    <div class="row gy-2 gx-2">
                                        @foreach ($fields as $key => $label)
                                            @php $checked = in_array($key, $selectedEmployeeFields, true); @endphp
                                            <div class="col-xl-4 col-sm-6">
                                                <label class="idcard-check {{ $checked ? 'is-on' : '' }}" for="idcard_employee_{{ $key }}">
                                                    <input
                                                        class="form-check-input idcard-field-check"
                                                        type="checkbox"
                                                        name="employee_fields[]"
                                                        data-audience="employee"
                                                        id="idcard_employee_{{ $key }}"
                                                        value="{{ $key }}"
                                                        {{ $checked ? 'checked' : '' }}
                                                    >
                                                    <span>{{ $label }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="idcard-panel">
                    <div class="idcard-panel-head">
                        <h6>Director’s signature</h6>
                        <p>Upload a clear signature image. PNG with a transparent background works best.</p>
                    </div>
                    <div class="idcard-panel-body">
                        <div class="row gy-3 align-items-start">
                            <div class="col-md-5">
                                <label class="text-sm fw-semibold mb-8" for="directorNameInput">Director’s name</label>
                                <input type="text" name="director_name" id="directorNameInput" class="form-control" value="{{ old('director_name', $settings->director_name ?? '') }}" maxlength="120" placeholder="Name printed under the signature">
                            </div>
                            <div class="col-md-7">
                                <label class="text-sm fw-semibold mb-8">Signature image</label>
                                <div class="idcard-drop" id="idCardSignDrop">
                                    <input type="file" name="director_signature" id="directorSignatureInput" accept="image/png,image/jpeg,image/webp">
                                    <div class="idcard-drop-preview">
                                        <img src="{{ $signatureUrl }}" alt="" id="idCardSignPreview" @if (! $signatureUrl) style="display:none" @endif>
                                        <i class="ri-pen-nib-line" id="idCardSignPlaceholder" @if ($signatureUrl) style="display:none" @endif></i>
                                    </div>
                                    <div class="fw-semibold">Drop signature here or click to browse</div>
                                    <div class="text-sm text-secondary-light mt-4">PNG, JPG or WEBP · max 2MB</div>
                                </div>
                                @error('director_signature')
                                    <p class="text-danger-600 text-sm mt-8 mb-0">{{ $message }}</p>
                                @enderror
                                @if ($signatureUrl)
                                    <label class="d-flex align-items-center gap-8 mt-12 mb-0">
                                        <input type="checkbox" name="remove_director_signature" value="1" class="form-check-input">
                                        <span class="text-sm">Remove current signature</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-600 px-28 py-12 radius-8">Save card settings</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const labels = @json($shortLabels);
    const packs = {
        student: {
            samples: @json($sampleValues),
            photo: @json(asset('assets/images/id-card-school-girl.png')),
            fallbackRole: 'Student',
            titleInput: 'cardTitleInput',
            titleFallback: 'Student Identity Card',
            large: ['student_id', 'gender', 'nhis_number'],
            skip: ['picture', 'full_name', 'firstname', 'othername', 'surname', 'category'],
        },
        employee: {
            samples: @json($employeeSampleValues),
            photo: @json(asset('assets/images/id-card-employee.png')),
            fallbackRole: 'Employee',
            titleInput: 'employeeCardTitleInput',
            titleFallback: 'Employee Identity Card',
            large: ['employee_id', 'gender', 'position'],
            skip: ['picture', 'full_name', 'firstname', 'othername', 'surname', 'title', 'position'],
        },
    };
    let audience = @json($audience);
    const photoWrap = document.getElementById('idCardPreviewPhotoWrap');
    const photoImg = document.getElementById('idCardPreviewPhotoImg');
    const nameEl = document.getElementById('idCardPreviewName');
    const roleEl = document.getElementById('idCardPreviewRole');
    const rowsEl = document.getElementById('idCardPreviewRows');
    const titleEl = document.getElementById('idCardPreviewTitle');
    const tabInput = document.getElementById('idCardAudienceInput');

    function boxes(which) {
        return Array.from(document.querySelectorAll('.idcard-field-check[data-audience="' + (which || audience) + '"]'));
    }

    function selectedKeys() {
        return boxes().filter((el) => el.checked).map((el) => el.value);
    }

    function paintChecks() {
        document.querySelectorAll('.idcard-field-check').forEach((el) => {
            el.closest('.idcard-check')?.classList.toggle('is-on', el.checked);
        });
        document.querySelectorAll('.idcard-selected-count').forEach((el) => {
            const which = el.getAttribute('data-audience');
            el.textContent = String(boxes(which).filter((box) => box.checked).length);
        });
    }

    function paintPreview() {
        const pack = packs[audience];
        const samples = pack.samples;
        const keys = selectedKeys();
        if (photoWrap) photoWrap.style.visibility = keys.includes('picture') ? 'visible' : 'hidden';
        if (photoImg) photoImg.src = pack.photo;

        const nameParts = [];
        if (keys.includes('full_name')) nameParts.push(samples.full_name);
        else {
            if (keys.includes('title') && samples.title) nameParts.push(samples.title);
            if (keys.includes('firstname')) nameParts.push(samples.firstname);
            if (keys.includes('othername')) nameParts.push(samples.othername);
            if (keys.includes('surname')) nameParts.push(samples.surname);
        }
        if (nameEl) nameEl.textContent = (nameParts.join(' ') || samples.full_name).toUpperCase();
        if (roleEl) {
            if (keys.includes('category')) roleEl.textContent = samples.category;
            else if (keys.includes('position')) roleEl.textContent = samples.position;
            else roleEl.textContent = pack.fallbackRole;
        }

        const rows = keys.filter((key) => !pack.skip.includes(key)).slice(0, 5);
        if (rowsEl) {
            rowsEl.innerHTML = rows.map((key) => {
                const label = labels[key] || key;
                const value = samples[key] || '—';
                const large = pack.large.includes(key);
                return '<div' + (large ? ' class="idcard-meta-lg"' : '') + '><b>' + label + '</b> : ' + value + '</div>';
            }).join('');
        }

        const titleInput = document.getElementById(pack.titleInput);
        if (titleEl && titleInput) titleEl.textContent = titleInput.value || pack.titleFallback;
        const backTitle = document.getElementById('idCardBackHeaderTitle');
        if (backTitle && titleInput) backTitle.textContent = titleInput.value || pack.titleFallback;

        document.querySelectorAll('.idcard-qr-pane').forEach((el) => {
            el.classList.toggle('is-on', el.getAttribute('data-audience') === audience);
        });
    }

    function setAudience(next) {
        audience = next === 'employee' ? 'employee' : 'student';
        if (tabInput) tabInput.value = audience;
        document.querySelectorAll('.idcard-tab').forEach((btn) => {
            btn.classList.toggle('active', btn.getAttribute('data-audience') === audience);
        });
        document.querySelectorAll('.idcard-audience-pane').forEach((pane) => {
            pane.classList.toggle('is-on', pane.getAttribute('data-audience') === audience);
        });
        document.querySelectorAll('.idcard-title-row').forEach((row) => {
            row.style.display = row.getAttribute('data-audience') === audience ? '' : 'none';
        });
        refresh();
    }

    function refresh() {
        paintChecks();
        paintPreview();
    }

    document.querySelectorAll('.idcard-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            setAudience(btn.getAttribute('data-audience'));
        });
    });
    document.querySelectorAll('.idcard-select-all').forEach((btn) => {
        btn.addEventListener('click', function () {
            boxes(btn.getAttribute('data-audience')).forEach((el) => { el.checked = true; });
            refresh();
        });
    });
    document.querySelectorAll('.idcard-select-none').forEach((btn) => {
        btn.addEventListener('click', function () {
            boxes(btn.getAttribute('data-audience')).forEach((el) => { el.checked = false; });
            refresh();
        });
    });

    document.getElementById('idCardSettingsForm')?.addEventListener('change', refresh);

    document.getElementById('cardTitleInput')?.addEventListener('input', function () {
        if (audience !== 'student') return;
        const el = document.getElementById('idCardPreviewTitle');
        const backTitle = document.getElementById('idCardBackHeaderTitle');
        if (el) el.textContent = this.value || 'Student Identity Card';
        if (backTitle) backTitle.textContent = this.value || 'Student Identity Card';
    });
    document.getElementById('employeeCardTitleInput')?.addEventListener('input', function () {
        if (audience !== 'employee') return;
        const el = document.getElementById('idCardPreviewTitle');
        const backTitle = document.getElementById('idCardBackHeaderTitle');
        if (el) el.textContent = this.value || 'Employee Identity Card';
        if (backTitle) backTitle.textContent = this.value || 'Employee Identity Card';
    });
    document.getElementById('headerTextInput')?.addEventListener('input', function () {
        const name = this.value || 'School name';
        const front = document.getElementById('idCardPreviewSchool');
        const back = document.getElementById('idCardBackSchool');
        const backHead = document.getElementById('idCardBackHeaderSchool');
        if (front) front.textContent = name;
        if (back) back.textContent = name;
        if (backHead) backHead.textContent = name;
    });
    document.getElementById('footerTextInput')?.addEventListener('input', function () {
        const el = document.getElementById('idCardBackFooter');
        if (el) el.textContent = this.value || 'This card remains the property of the school and must be produced on request.';
    });
    document.getElementById('directorNameInput')?.addEventListener('input', function () {
        const el = document.getElementById('idCardPreviewSignName');
        if (el) el.textContent = this.value || 'Director';
    });

    const drop = document.getElementById('idCardSignDrop');
    const fileInput = document.getElementById('directorSignatureInput');
    const preview = document.getElementById('idCardSignPreview');
    const placeholder = document.getElementById('idCardSignPlaceholder');
    const cardSign = document.getElementById('idCardPreviewSignImg');
    const cardSignLine = document.getElementById('idCardPreviewSignLine');

    function showSignature(src) {
        if (preview) { preview.src = src; preview.style.display = ''; }
        if (placeholder) placeholder.style.display = 'none';
        if (cardSign) { cardSign.src = src; cardSign.style.display = ''; }
        if (cardSignLine) cardSignLine.style.display = 'none';
    }

    fileInput?.addEventListener('change', function () {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) { showSignature(e.target.result); };
        reader.readAsDataURL(file);
    });
    ['dragenter', 'dragover'].forEach((evt) => {
        drop?.addEventListener(evt, function (e) {
            e.preventDefault();
            drop.classList.add('is-over');
        });
    });
    ['dragleave', 'drop'].forEach((evt) => {
        drop?.addEventListener(evt, function (e) {
            e.preventDefault();
            drop.classList.remove('is-over');
        });
    });

    refresh();
})();
</script>
@endsection
