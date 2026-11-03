@php
    $employee = $datas ?? null;
    $departments = $departments ?? collect();
    $positions = $positions ?? collect();
    $payGrades = $payGrades ?? collect();
    $earningTypes = $earningTypes ?? collect();
    $deductionTypes = $deductionTypes ?? collect();
    $assignedEarnings = $assignedEarnings ?? [];
    $assignedDeductions = $assignedDeductions ?? [];
    $employmentTypes = ['Permanent', 'Contract', 'National Service', 'Intern', 'Casual'];
@endphp

<div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden mt-24">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
        <span class="section-title-icon"><i class="ri-building-4-line"></i></span>
        <div>
            <h6 class="text-lg fw-semibold mb-0 section-card-title">Organisation &amp; Contract</h6>
            <p class="text-sm text-secondary-light mb-0">Department, role, and employment dates.</p>
        </div>
    </div>
    <div class="card-body p-24">
        <div class="row gy-3">
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Department</label>
                <select name="department_id" class="form-control form-select">
                    <option value="">Select department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) old('department_id', $employee->department_id ?? '') === (string) $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Position <span class="text-danger-600">*</span></label>
                @if($positions->count())
                    <select name="position_id" id="staff_position_id" class="form-control form-select"
                        data-teacher-position-id="{{ $teacherPositionId ?? '' }}"
                        data-teacher-category-id="{{ $teacherCategoryId ?? '' }}">
                        <option value="">Select position</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" {{ (string) old('position_id', $employee->position_id ?? '') === (string) $position->id ? 'selected' : '' }}>
                                {{ $position->name }}@if($position->department) — {{ $position->department->name }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('position_id') <small class="text-danger-600">{{ $message }}</small> @enderror
                @else
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position ?? '') }}" placeholder="e.g. Mathematics Teacher">
                    @error('position') <small class="text-danger-600">{{ $message }}</small> @enderror
                @endif
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Employment Type</label>
                <select name="employment_type" class="form-control form-select">
                    <option value="">Select type</option>
                    @foreach($employmentTypes as $type)
                        <option value="{{ $type }}" {{ old('employment_type', $employee->employment_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Appointment Date</label>
                <input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', optional($employee)->appointment_date?->format('Y-m-d') ?? $employee->appointment_date ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Confirmation Date</label>
                <input type="date" name="confirmation_date" class="form-control" value="{{ old('confirmation_date', optional($employee)->confirmation_date?->format('Y-m-d') ?? $employee->confirmation_date ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Contract End Date</label>
                <input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date', optional($employee)->contract_end_date?->format('Y-m-d') ?? $employee->contract_end_date ?? '') }}">
            </div>
        </div>
    </div>
</div>

<div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden mt-24">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
        <span class="section-title-icon"><i class="ri-bank-card-line"></i></span>
        <div>
            <h6 class="text-lg fw-semibold mb-0 section-card-title">Statutory, Bank &amp; Next of Kin</h6>
            <p class="text-sm text-secondary-light mb-0">SSNIT, TIN, salary account, and emergency contact.</p>
        </div>
    </div>
    <div class="card-body p-24">
        <div class="row gy-3">
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">SSNIT Number</label>
                <input type="text" name="ssnit_number" class="form-control" value="{{ old('ssnit_number', $employee->ssnit_number ?? '') }}" placeholder="SSNIT number">
            </div>
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">TIN</label>
                <input type="text" name="tin" class="form-control" value="{{ old('tin', $employee->tin ?? '') }}" placeholder="GRA TIN">
            </div>
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Branch</label>
                <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $employee->bank_branch ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Account Name</label>
                <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $employee->account_name ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Account Number</label>
                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $employee->account_number ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Next of Kin</label>
                <input type="text" name="next_of_kin_name" class="form-control" value="{{ old('next_of_kin_name', $employee->next_of_kin_name ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Next of Kin Phone</label>
                <input type="text" name="next_of_kin_phone" class="form-control" value="{{ old('next_of_kin_phone', $employee->next_of_kin_phone ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Relationship</label>
                <input type="text" name="next_of_kin_relationship" class="form-control" value="{{ old('next_of_kin_relationship', $employee->next_of_kin_relationship ?? '') }}" placeholder="e.g. Spouse">
            </div>
        </div>
    </div>
</div>

<div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden mt-24">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
        <span class="section-title-icon"><i class="ri-money-cny-circle-line"></i></span>
        <div>
            <h6 class="text-lg fw-semibold mb-0 section-card-title">Compensation</h6>
            <p class="text-sm text-secondary-light mb-0">Pay grade, basic salary, and recurring items. Leave amount blank to skip.</p>
        </div>
    </div>
    <div class="card-body p-24">
        <div class="row gy-3">
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Pay Grade</label>
                <select name="pay_grade_id" id="payGradeSelect" class="form-control form-select">
                    <option value="">Select pay grade</option>
                    @foreach($payGrades as $grade)
                        <option value="{{ $grade->id }}" data-basic="{{ $grade->basic_salary }}"
                            {{ (string) old('pay_grade_id', $employee->pay_grade_id ?? '') === (string) $grade->id ? 'selected' : '' }}>
                            {{ $grade->name }} ({{ \App\Support\Money::ghs($grade->basic_salary) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Basic Salary Override</label>
                <input type="number" step="0.01" min="0" name="basic_salary" id="basicSalaryInput" class="form-control"
                    value="{{ old('basic_salary', $employee->basic_salary ?? '') }}" placeholder="Leave blank to use pay grade">
            </div>
        </div>

        @if($earningTypes->count())
            <h6 class="text-sm fw-semibold mt-20 mb-12">Recurring allowances</h6>
            <div class="row gy-3">
                @foreach($earningTypes as $type)
                    @php $oldAmount = old('earnings.'.$type->id, $assignedEarnings[$type->id] ?? ''); @endphp
                    <div class="col-md-4">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">{{ $type->name }}</label>
                        <input type="number" step="0.01" min="0" name="earnings[{{ $type->id }}]" class="form-control"
                            value="{{ $oldAmount }}" placeholder="{{ $type->method === 'percent_basic' ? '% of basic' : 'Amount' }}">
                        <small class="text-secondary-light">Default: {{ $type->method === 'percent_basic' ? $type->default_amount.'%' : \App\Support\Money::ghs($type->default_amount) }}</small>
                    </div>
                @endforeach
            </div>
        @endif

        @if($deductionTypes->where('is_statutory', false)->count())
            <h6 class="text-sm fw-semibold mt-20 mb-12">Recurring deductions</h6>
            <div class="row gy-3">
                @foreach($deductionTypes->where('is_statutory', false) as $type)
                    @php $oldAmount = old('deductions.'.$type->id, $assignedDeductions[$type->id] ?? ''); @endphp
                    <div class="col-md-4">
                        <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">{{ $type->name }}</label>
                        <input type="number" step="0.01" min="0" name="deductions[{{ $type->id }}]" class="form-control"
                            value="{{ $oldAmount }}" placeholder="Amount">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('staff_user_cat');
        const positionSelect = document.getElementById('staff_position_id');
        if (!categorySelect || !positionSelect) return;

        const teacherPositionId = positionSelect.getAttribute('data-teacher-position-id');
        const teacherCategoryId = positionSelect.getAttribute('data-teacher-category-id');
        if (!teacherPositionId || !teacherCategoryId) return;

        const syncTeacherPosition = function () {
            if (String(categorySelect.value) === String(teacherCategoryId)) {
                positionSelect.value = teacherPositionId;
            }
        };

        categorySelect.addEventListener('change', syncTeacherPosition);
        syncTeacherPosition();
    });
</script>
