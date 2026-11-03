@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $educationLevels = $educationLevels ?? \App\Models\StaffDoc::educationLevels();
    $maxYear = $maxYear ?? ((int) date('Y') + 1);
    $rowId = $row['id'] ?? '';
    $rowLevel = $row['level'] ?? '';
    $rowQualification = $row['qualification'] ?? '';
    $rowInstitution = $row['institution'] ?? '';
    $rowYear = $row['year'] ?? '';
    $rowDocument = $row['document_path'] ?? '';
@endphp

<div class="qualification-row border radius-12 p-20 mb-16" data-qualification-row>
    @if($rowId !== '')
        <input type="hidden" name="qualifications[{{ $index }}][id]" value="{{ $rowId }}">
    @endif
    @if($rowDocument !== '')
        <input type="hidden" name="qualifications[{{ $index }}][document_path]" value="{{ $rowDocument }}">
    @endif
    <div class="d-flex align-items-start justify-content-between gap-12 mb-16">
        <span class="qualification-row-label text-sm fw-semibold">Qualification <span data-qualification-number></span></span>
        <button type="button" class="btn btn-sm btn-outline-danger-600 d-flex align-items-center gap-4 remove-qualification-row" data-existing-id="{{ $rowId }}">
            <i class="ri-delete-bin-line"></i>
            Remove
        </button>
    </div>
    <div class="row gy-3">
        <div class="col-md-6">
            <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Educational Level</label>
            <select name="qualifications[{{ $index }}][level]" class="form-control form-select">
                <option value="">Select level</option>
                @if($rowLevel !== '' && !in_array($rowLevel, $educationLevels, true))
                    <option value="{{ $rowLevel }}" selected>{{ $rowLevel }}</option>
                @endif
                @foreach($educationLevels as $level)
                    <option value="{{ $level }}" {{ $rowLevel === $level ? 'selected' : '' }}>{{ $level }}</option>
                @endforeach
            </select>
            @error("qualifications.$index.level") <small class="text-danger-600">{{ $message }}</small> @enderror
        </div>
        <div class="col-md-6">
            <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Year of Completion</label>
            <input type="number" name="qualifications[{{ $index }}][year]" class="form-control"
                min="1950" max="{{ $maxYear }}" value="{{ $rowYear }}" placeholder="e.g. {{ date('Y') }}">
            @error("qualifications.$index.year") <small class="text-danger-600">{{ $message }}</small> @enderror
        </div>
        <div class="col-md-6">
            <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Qualification / Award</label>
            <input type="text" name="qualifications[{{ $index }}][qualification]" class="form-control"
                value="{{ $rowQualification }}" placeholder="e.g. B.Ed Mathematics, WASSCE">
            @error("qualifications.$index.qualification") <small class="text-danger-600">{{ $message }}</small> @enderror
        </div>
        <div class="col-md-6">
            <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Institution</label>
            <input type="text" name="qualifications[{{ $index }}][institution]" class="form-control"
                value="{{ $rowInstitution }}" placeholder="e.g. University of Ghana">
            @error("qualifications.$index.institution") <small class="text-danger-600">{{ $message }}</small> @enderror
        </div>
        <div class="col-12">
            <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Certificate (optional)</label>
            <input type="file" name="qualifications[{{ $index }}][document]" class="form-control"
                accept=".pdf,.jpg,.jpeg,.png,.webp">
            @if(!empty($rowDocument))
                <small class="text-secondary-light d-block mt-6">
                    Current file:
                    <a href="{{ asset('uploads/staffdocs/' . $rowDocument) }}" target="_blank" rel="noopener">View certificate</a>
                </small>
            @endif
            @error("qualifications.$index.document") <small class="text-danger-600">{{ $message }}</small> @enderror
        </div>
    </div>
</div>
