@php
    $defaultYearId = old('academic_year_id', $academicYears->firstWhere('status', 'Active')?->id ?? $academicYears->first()?->id);
    $defaultTermId = old('academic_term_id', $academicTerms->firstWhere('status', 'Active')?->id ?? $academicTerms->first()?->id);
@endphp

<div class="card border-0 shadow-1 radius-12 mb-24">
    <div class="card-body p-24">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-16 mb-20">
            <div>
                <h6 class="fw-semibold mb-4">Bulk upload students</h6>
                <p class="text-sm text-secondary-light mb-0">
                    Download the Excel template, fill one student per row, then upload. Leave <code>student_id</code> blank to auto-generate IDs.
                    Class names must match existing Active classes. Parent portal accounts and bills are created for Active students.
                </p>
            </div>
            <a href="{{ route('download-student-bulk-template') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-download-2-line"></i>
                Download Template
            </a>
        </div>

        @if(session('bulk_import_errors'))
            <div class="alert alert-warning radius-8 mb-20">
                <div class="fw-semibold mb-8">Some rows need attention:</div>
                <ul class="mb-0 ps-18">
                    @foreach(session('bulk_import_errors') as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('bulk-import-students-process') }}" enctype="multipart/form-data" class="row gy-3 align-items-end">
            @csrf
            <div class="col-xxl-3 col-md-4 col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Academic Year <span class="text-danger-600">*</span></label>
                <select name="academic_year_id" class="form-control form-select" required>
                    <option value="" disabled {{ $defaultYearId ? '' : 'selected' }}>Select Academic Year</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ (string) $defaultYearId === (string) $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-xxl-3 col-md-4 col-sm-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Academic Term <span class="text-danger-600">*</span></label>
                <select name="academic_term_id" class="form-control form-select" required>
                    <option value="" disabled {{ $defaultTermId ? '' : 'selected' }}>Select Academic Term</option>
                    @foreach($academicTerms as $term)
                        <option value="{{ $term->id }}" {{ (string) $defaultTermId === (string) $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                    @endforeach
                </select>
                @error('academic_term_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-xxl-4 col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Excel / CSV file <span class="text-danger-600">*</span></label>
                <input type="file" name="students_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                @error('students_file') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-xxl-2 col-md-4">
                <button type="submit" class="btn btn-primary-600 w-100 d-flex align-items-center justify-content-center gap-6">
                    <i class="ri-upload-2-line"></i>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>
