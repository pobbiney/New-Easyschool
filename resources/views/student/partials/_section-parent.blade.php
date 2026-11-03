@php $student = $student ?? null; @endphp
<div class="row gy-3">
    <div class="col-md-4">
        <div class="border radius-12 p-20 h-100">
            <h6 class="text-md fw-semibold text-primary-light mb-16">Father's Information</h6>
            <div class="mb-16">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Name <span class="text-danger-600">*</span></label>
                <input type="text" name="father_name" class="form-control wizard-step2-required" value="{{ old('father_name', $student?->father_name ?? '') }}" placeholder="Father's full name">
            </div>
            <div>
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone <span class="text-danger-600">*</span></label>
                <input type="tel" name="father_phone" class="form-control wizard-step2-required" value="{{ old('father_phone', $student?->father_phone ?? '') }}" placeholder="Father's phone number">
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="border radius-12 p-20 h-100">
            <h6 class="text-md fw-semibold text-primary-light mb-16">Mother's Information</h6>
            <div class="mb-16">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Name <span class="text-danger-600">*</span></label>
                <input type="text" name="mother_name" class="form-control wizard-step2-required" value="{{ old('mother_name', $student?->mother_name ?? '') }}" placeholder="Mother's full name">
            </div>
            <div>
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone <span class="text-danger-600">*</span></label>
                <input type="tel" name="mother_phone" class="form-control wizard-step2-required" value="{{ old('mother_phone', $student?->mother_phone ?? '') }}" placeholder="Mother's phone number">
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="border radius-12 p-20 h-100">
            <h6 class="text-md fw-semibold text-primary-light mb-16">Guardian</h6>
            <div class="mb-16">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Guardian Type <span class="text-danger-600">*</span></label>
                <select name="guardian_type" class="form-control form-select wizard-step2-required">
                    <option value="" disabled {{ old('guardian_type', $student?->guardian_type ?? '') ? '' : 'selected' }}>Select guardian</option>
                    <option value="Father" {{ old('guardian_type', $student?->guardian_type ?? '') == 'Father' ? 'selected' : '' }}>Father</option>
                    <option value="Mother" {{ old('guardian_type', $student?->guardian_type ?? '') == 'Mother' ? 'selected' : '' }}>Mother</option>
                    <option value="Others" {{ old('guardian_type', $student?->guardian_type ?? '') == 'Others' ? 'selected' : '' }}>Others</option>
                </select>
            </div>
            <div class="mb-16">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Name <span class="text-danger-600">*</span></label>
                <input type="text" name="guardian_name" class="form-control wizard-step2-required" value="{{ old('guardian_name', $student?->guardian_name ?? '') }}" placeholder="Guardian's full name">
            </div>
            <div>
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone <span class="text-danger-600">*</span></label>
                <input type="tel" name="guardian_phone" class="form-control wizard-step2-required" value="{{ old('guardian_phone', $student?->guardian_phone ?? '') }}" placeholder="Guardian's phone number">
            </div>
        </div>
    </div>
</div>
