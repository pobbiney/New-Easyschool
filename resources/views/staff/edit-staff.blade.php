@php $pageName = "hr"; $subpageName = "list-staff"; @endphp

@extends('layouts.app')

@section('css')
@include('staff.partials._staff-form-styles')
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">HR</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('list-staff') }}" class="text-secondary-light hover-text-primary hover-underline"> / Staff List</a>
                <span class="text-secondary-light"> / Update Staff</span>
            </div>
        </div>
        <a href="{{ route('list-staff') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md"><i class="ri-list-check"></i></span>
            View Staff List
        </a>
    </div>

    <form action="{{ route('update-staff-process', $id) }}" enctype="multipart/form-data" method="POST">
        @csrf
        <input type="hidden" name="staff_number" value="{{ $datas->employee_id }}">

        <div class="row gy-4">
            <div class="col-xl-4">
                <div class="staff-sidebar-card shadow-1 radius-12 bg-base p-24">
                    @include('staff.partials._photo-capture', ['existingPhoto' => $datas->picture ?? null])

                    <div class="text-center mb-20 mt-20">
                        <span class="staff-id-badge">
                            <i class="ri-id-card-line"></i>
                            {{ $datas->employee_id }}
                        </span>
                    </div>

                    <div class="border-top pt-20">
                        <h6 class="text-sm fw-semibold mb-12">Quick tips</h6>
                        <ul class="staff-tip-list">
                            <li><i class="ri-checkbox-circle-line"></i><span>Update required fields marked with *.</span></li>
                            <li><i class="ri-checkbox-circle-line"></i><span>Update academic qualifications and optional certificates.</span></li>
                            <li><i class="ri-checkbox-circle-line"></i><span>Logins are managed under User Management → System Access.</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden mb-24">
                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
                        <span class="section-title-icon"><i class="ri-user-3-line"></i></span>
                        <div>
                            <h6 class="text-lg fw-semibold mb-0 section-card-title">Personal Information</h6>
                            <p class="text-sm text-secondary-light mb-0">Basic identity and contact details.</p>
                        </div>
                    </div>
                    <div class="card-body p-24">
                        <div class="row gy-3">
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Title <span class="text-danger-600">*</span></label>
                                <select name="title" class="form-control form-select">
                                    @foreach(['Mr', 'Miss', 'Mrs', 'Dr', 'Sir'] as $title)
                                        <option value="{{ $title }}" {{ old('title', $datas->title) == $title ? 'selected' : '' }}>{{ $title }}</option>
                                    @endforeach
                                </select>
                                @error('title') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">First Name <span class="text-danger-600">*</span></label>
                                <input type="text" name="firstname" class="form-control" value="{{ old('firstname', $datas->firstname) }}" placeholder="First name">
                                @error('firstname') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Other Name</label>
                                <input type="text" name="othername" class="form-control" value="{{ old('othername', $datas->othername) }}" placeholder="Other name">
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Surname <span class="text-danger-600">*</span></label>
                                <input type="text" name="surname" class="form-control" value="{{ old('surname', $datas->surname) }}" placeholder="Surname">
                                @error('surname') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="gender">
                                    <option value="Male" {{ old('gender', $datas->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $datas->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Date of Birth <span class="text-danger-600">*</span></label>
                                <input type="text" name="dob" class="form-control datepicker" placeholder="Select date" value="{{ old('dob', $datas->dob) }}">
                                @error('dob') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Nationality <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="nationality">
                                    <option value="" disabled>Select nationality</option>
                                    @foreach($listcountry as $country)
                                        <option value="{{ $country->id }}" {{ (string) old('nationality', $datas->nationality) === (string) $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('nationality') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Marital Status <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="marital_status">
                                    @foreach(['Married', 'Single', 'Divorced'] as $status)
                                        <option value="{{ $status }}" {{ old('marital_status', $datas->marital_status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('marital_status') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone <span class="text-danger-600">*</span></label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone', $datas->mobile) }}" placeholder="Phone number">
                                @error('phone') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-8 col-sm-12">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Email <span class="text-danger-600">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $datas->email) }}" placeholder="Email address">
                                @error('email') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Residential Address <span class="text-danger-600">*</span></label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $datas->residential_address) }}" placeholder="Residential address">
                                @error('address') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden">
                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
                        <span class="section-title-icon"><i class="ri-briefcase-4-line"></i></span>
                        <div>
                            <h6 class="text-lg fw-semibold mb-0 section-card-title">Employment Details</h6>
                            <p class="text-sm text-secondary-light mb-0">Role and employment status.</p>
                        </div>
                    </div>
                    <div class="card-body p-24">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Staff ID</label>
                                <input type="text" class="form-control" value="{{ $datas->employee_id }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="status">
                                    <option value="Active" {{ old('status', $datas->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status', $datas->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @include('staff.partials._section-hr-employment')

                @include('staff.partials._section-academic-qualifications', [
                    'existingQualifications' => $existingQualifications ?? [],
                ])
            </div>

            <div class="col-12">
                <div class="staff-form-actions d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <a href="{{ route('list-staff') }}" class="text-secondary-light hover-text-danger-600">
                        Cancel and go back
                    </a>
                    <div class="d-flex flex-wrap gap-3">
                        <button type="reset" class="btn btn-outline-neutral-400 text-secondary-light">
                            Reset form
                        </button>
                        <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6">
                            <i class="ri-save-line"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@include('staff.partials._photo-capture-modal')

@endsection

@section('scripts')
@include('staff.partials._photo-capture-scripts')
@include('staff.partials._qualification-scripts')
@endsection
