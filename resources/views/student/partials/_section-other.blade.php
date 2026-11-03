@php
    $student = $student ?? null;
    $hasNhis = old('has_nhis', $student?->has_nhis ?? '') === 'Yes';
@endphp
<div class="row gy-3">
    <div class="col-12"><h6 class="text-md fw-semibold text-primary-light mb-12">Medical Details</h6></div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Blood Group</label>
        <select name="blood_group" class="form-control form-select">
            <option value="" disabled selected>Select blood group</option>
            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                <option value="{{ $group }}" {{ old('blood_group', $student?->blood_group ?? '') == $group ? 'selected' : '' }}>{{ $group }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Height</label>
        <input type="text" name="height" class="form-control" value="{{ old('height', $student?->height ?? '') }}" placeholder="e.g. 150cm">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Weight</label>
        <input type="text" name="weight" class="form-control" value="{{ old('weight', $student?->weight ?? '') }}" placeholder="e.g. 45kg">
    </div>

    <div class="col-12 mt-16"><h6 class="text-md fw-semibold text-primary-light mb-12">Other Information</h6></div>
    <div class="col-xxl-6 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Current Address</label>
        <input type="text" name="current_address" class="form-control" value="{{ old('current_address', $student?->current_address ?? '') }}" placeholder="Enter current address">
    </div>
    <div class="col-xxl-6 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Previous School</label>
        <input type="text" name="previous_school_name" class="form-control" value="{{ old('previous_school_name', $student?->previous_school_name ?? '') }}" placeholder="Enter school name">
    </div>

    <div class="col-12">
        <div class="nhis-option-card {{ $hasNhis ? 'is-active' : '' }}" id="nhisOptionCard">
            <div class="nhis-option-header">
                <div class="form-check style-check nhis-option-check mb-0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="hasNhisCheckbox"
                        {{ $hasNhis ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="hasNhisCheckbox">
                        <span class="nhis-option-title">National Health Insurance (NHIS)</span>
                        <span class="nhis-option-desc">Check this box if the student is registered with NHIS</span>
                    </label>
                </div>
                <span class="nhis-option-badge">Optional</span>
            </div>
            <input type="hidden" name="has_nhis" id="hasNhisValue" value="{{ $hasNhis ? 'Yes' : '' }}">
            <div class="nhis-details-panel" id="nhisDetailsWrap" style="{{ $hasNhis ? '' : 'display: none;' }}">
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8" for="nhisCardNameInput">Card Name</label>
                        <input
                            type="text"
                            name="nhis_card_name"
                            id="nhisCardNameInput"
                            class="form-control"
                            value="{{ old('nhis_card_name', $student?->nhis_card_name ?? '') }}"
                            placeholder="Enter name on the card"
                        >
                    </div>
                    <div class="col-md-6">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8" for="nhisNumberInput">NHIS Number</label>
                        <input
                            type="text"
                            name="nhis_number"
                            id="nhisNumberInput"
                            class="form-control"
                            value="{{ old('nhis_number', $student?->nhis_number ?? '') }}"
                            placeholder="Enter NHIS membership number"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Additional Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Enter any important notes about the student">{{ old('notes', $student?->notes ?? '') }}</textarea>
    </div>
</div>

<style>
    .nhis-option-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 16px 18px;
        background: var(--white, #fff);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .nhis-option-card.is-active {
        border-color: rgba(37, 161, 148, 0.35);
        background: rgba(37, 161, 148, 0.04);
        box-shadow: 0 8px 24px rgba(37, 161, 148, 0.08);
    }

    .nhis-option-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .nhis-option-check {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .nhis-option-check .form-check-input {
        margin-top: 4px;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .nhis-option-check .form-check-label {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .nhis-option-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--neutral-900, #111827);
    }

    .nhis-option-desc {
        font-size: 13px;
        color: var(--neutral-500, #6b7280);
        line-height: 1.4;
    }

    .nhis-option-badge {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: var(--neutral-500, #6b7280);
        background: var(--neutral-100, #f3f4f6);
        border-radius: 999px;
        padding: 4px 10px;
    }

    .nhis-details-panel {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed var(--neutral-200, #e5e7eb);
    }
</style>

<script>
    (function () {
        let hasNhisCheckbox = document.getElementById('hasNhisCheckbox');
        let hasNhisValue = document.getElementById('hasNhisValue');
        let nhisOptionCard = document.getElementById('nhisOptionCard');
        let nhisDetailsWrap = document.getElementById('nhisDetailsWrap');
        let nhisNumberInput = document.getElementById('nhisNumberInput');
        let nhisCardNameInput = document.getElementById('nhisCardNameInput');

        if (!hasNhisCheckbox || !nhisDetailsWrap) {
            return;
        }

        function toggleNhisFields() {
            let hasNhis = hasNhisCheckbox.checked;

            if (hasNhisValue) {
                hasNhisValue.value = hasNhis ? 'Yes' : '';
            }

            if (nhisOptionCard) {
                nhisOptionCard.classList.toggle('is-active', hasNhis);
            }

            nhisDetailsWrap.style.display = hasNhis ? '' : 'none';

            if (!hasNhis) {
                if (nhisNumberInput) {
                    nhisNumberInput.value = '';
                }
                if (nhisCardNameInput) {
                    nhisCardNameInput.value = '';
                }
            }
        }

        hasNhisCheckbox.addEventListener('change', toggleNhisFields);
        toggleNhisFields();
    })();
</script>
