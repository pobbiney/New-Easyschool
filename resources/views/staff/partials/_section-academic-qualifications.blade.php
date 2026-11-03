@php
    $educationLevels = \App\Models\StaffDoc::educationLevels();
    $oldRows = old('qualifications');
    $qualificationRows = [];

    if (is_array($oldRows) && count($oldRows)) {
        foreach ($oldRows as $row) {
            $qualificationRows[] = [
                'id' => $row['id'] ?? '',
                'level' => $row['level'] ?? '',
                'qualification' => $row['qualification'] ?? '',
                'institution' => $row['institution'] ?? '',
                'year' => $row['year'] ?? '',
                'document_path' => $row['document_path'] ?? '',
            ];
        }
    } elseif (!empty($existingQualifications) && count($existingQualifications)) {
        foreach ($existingQualifications as $doc) {
            $qualificationRows[] = [
                'id' => $doc->id,
                'level' => $doc->level,
                'qualification' => $doc->qualification,
                'institution' => $doc->institution,
                'year' => $doc->year,
                'document_path' => $doc->document_path,
            ];
        }
    }

    if (!count($qualificationRows)) {
        $qualificationRows = [[
            'id' => '',
            'level' => '',
            'qualification' => '',
            'institution' => '',
            'year' => '',
            'document_path' => '',
        ]];
    }

    $maxYear = (int) date('Y') + 1;
@endphp

<div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden mt-24" id="academic-qualifications-section">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-12">
            <span class="section-title-icon"><i class="ri-graduation-cap-line"></i></span>
            <div>
                <h6 class="text-lg fw-semibold mb-0 section-card-title">Academic Qualifications</h6>
                <p class="text-sm text-secondary-light mb-0">Add every academic qualification. Certificate upload is optional.</p>
            </div>
        </div>
        <button type="button" class="btn btn-outline-primary-600 d-flex align-items-center gap-6" id="addQualificationRow">
            <i class="ri-add-line"></i>
            Add qualification
        </button>
    </div>
    <div class="card-body p-24">
        <div id="qualificationRows">
            @foreach($qualificationRows as $index => $row)
                @include('staff.partials._qualification-row', [
                    'index' => $index,
                    'row' => $row,
                    'educationLevels' => $educationLevels,
                    'maxYear' => $maxYear,
                ])
            @endforeach
        </div>
        @error('qualifications')
            <small class="text-danger-600 d-block mt-8">{{ $message }}</small>
        @enderror
        <div id="removedQualificationIds"></div>
    </div>
</div>

<template id="qualificationRowTemplate">
    @include('staff.partials._qualification-row', [
        'index' => '__INDEX__',
        'row' => [
            'id' => '',
            'level' => '',
            'qualification' => '',
            'institution' => '',
            'year' => '',
            'document_path' => '',
        ],
        'educationLevels' => $educationLevels,
        'maxYear' => $maxYear,
    ])
</template>
