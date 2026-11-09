@php
    $student = $student ?? null;
    $selectedYearId = old('academic_year_id');
    if ($selectedYearId === null) {
        $selectedYearId = $student?->academic_year_id ?? ($defaultAcademicYearId ?? '');
    }
    $selectedTermId = old('academic_term_id');
    if ($selectedTermId === null) {
        $selectedTermId = $student?->academic_term_id ?? ($defaultAcademicTermId ?? '');
    }
@endphp
<div class="row gy-3">
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Student ID <span class="text-danger-600">*</span></label>
        <input type="text" name="student_id" class="form-control" value="{{ old('student_id', $student?->student_id ?? $studentCode ?? '') }}" readonly>
        @error('student_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Academic Year <span class="text-danger-600">*</span></label>
        <select name="academic_year_id" class="form-control form-select wizard-required">
            <option value="" disabled {{ $selectedYearId ? '' : 'selected' }}>Select Academic Year</option>
            @forelse($academicYears ?? [] as $year)
                <option value="{{ $year->id }}" {{ (string) $selectedYearId === (string) $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
            @empty
                <option value="" disabled>No academic years found — add them first</option>
            @endforelse
        </select>
        @error('academic_year_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Academic Term <span class="text-danger-600">*</span></label>
        <select name="academic_term_id" class="form-control form-select wizard-required">
            <option value="" disabled {{ $selectedTermId ? '' : 'selected' }}>Select Academic Term</option>
            @forelse($academicTerms ?? [] as $term)
                <option value="{{ $term->id }}" {{ (string) $selectedTermId === (string) $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
            @empty
                <option value="" disabled>No academic terms found</option>
            @endforelse
        </select>
        @error('academic_term_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Class <span class="text-danger-600">*</span></label>
        <select name="school_class_id" class="form-control form-select wizard-required">
            <option value="" disabled {{ old('school_class_id', $student?->school_class_id ?? '') ? '' : 'selected' }}>Select Class</option>
            @forelse($schoolClasses ?? [] as $class)
                <option value="{{ $class->id }}" {{ old('school_class_id', $student?->school_class_id ?? '') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @empty
                <option value="" disabled>No classes found — add them first</option>
            @endforelse
        </select>
        @error('school_class_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">First Name <span class="text-danger-600">*</span></label>
        <input type="text" name="firstname" class="form-control wizard-required" value="{{ old('firstname', $student?->firstname ?? '') }}" placeholder="Enter first name">
        @error('firstname') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Other Name</label>
        <input type="text" name="othername" class="form-control" value="{{ old('othername', $student?->othername ?? '') }}" placeholder="Enter other name">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Surname <span class="text-danger-600">*</span></label>
        <input type="text" name="surname" class="form-control wizard-required" value="{{ old('surname', $student?->surname ?? '') }}" placeholder="Enter surname">
        @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Category</label>
        <select name="category" class="form-control form-select">
            <option value="" disabled selected>Select Category</option>
            @foreach(['Day Student', 'Boarding', 'Scholarship', 'International'] as $cat)
                <option value="{{ $cat }}" {{ old('category', $student?->category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender <span class="text-danger-600">*</span></label>
        <select name="gender" class="form-control form-select wizard-required">
            <option value="" disabled selected>Select Gender</option>
            <option value="Male" {{ old('gender', $student?->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ old('gender', $student?->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
        </select>
        @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Date of Birth</label>
        <input type="text" name="dob" class="form-control datepicker" value="{{ old('dob', $student?->dob ?? '') }}" placeholder="Enter date of birth">
        @error('dob') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone</label>
        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $student?->phone ?? '') }}" placeholder="Enter phone number">
        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student?->email ?? '') }}" placeholder="Enter email">
    </div>
    <div class="col-xxl-3 col-xl-4 col-sm-6">
        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status <span class="text-danger-600">*</span></label>
        @php
            $selectedStatus = old('status', $student?->status ?? 'Active');
            if ($selectedStatus === 'Draft') {
                $selectedStatus = 'Active';
            }
        @endphp
        <select name="status" class="form-control form-select wizard-required">
            <option value="Active" {{ $selectedStatus == 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ $selectedStatus == 'Inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
</div>
