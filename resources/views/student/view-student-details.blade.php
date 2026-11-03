@php $pageName = "student"; $subpageName = "list-students"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .student-hero {
        background: linear-gradient(135deg, #25A194 0%, #1d8a7f 100%);
        border-radius: 12px;
        padding: 28px 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .student-hero::after {
        content: "";
        position: absolute;
        right: -40px;
        top: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .student-hero-photo {
        width: 120px;
        height: 120px;
        border-radius: 16px;
        overflow: hidden;
        border: 4px solid rgba(255, 255, 255, 0.35);
        flex-shrink: 0;
    }

    .student-hero-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detail-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        height: 100%;
    }

    .detail-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: var(--neutral-50, #f9fafb);
        border-radius: 12px 12px 0 0;
    }

    .detail-card-header h6 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-row {
        display: flex;
        gap: 8px;
        padding: 10px 0;
        border-bottom: 1px dashed var(--neutral-200, #e5e7eb);
    }

    .detail-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .detail-label {
        min-width: 130px;
        font-size: 13px;
        font-weight: 600;
        color: var(--neutral-600, #4b5563);
    }

    .detail-value {
        font-size: 14px;
        color: var(--neutral-800, #1f2937);
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STUDENT MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('list-students') }}" class="text-secondary-light hover-text-primary hover-underline"> / Student List</a>
                <span class="text-secondary-light"> / Student Details</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('print-student-details', $id) }}" target="_blank" class="btn btn-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md"><i class="ri-printer-line"></i></span>
                Print Profile
            </a>
            <a href="{{ route('edit-student', $id) }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md"><i class="ri-edit-line"></i></span>
                Edit Student
            </a>
        </div>
    </div>

    <div class="mt-24">
        <div class="student-hero mb-24">
            <div class="d-flex gap-24 align-items-center flex-wrap position-relative" style="z-index:1;">
                <div class="student-hero-photo">
                    @if(!empty($student->picture))
                        <img src="{{ asset($student->picture) }}" alt="{{ $student->full_name }}">
                    @elseif(strtolower($student->gender) == 'male')
                        <img src="{{ asset('assets/images/thumbs/guardian-img1.png') }}" alt="Student">
                    @else
                        <img src="{{ asset('assets/images/thumbs/guardian-img2.png') }}" alt="Student">
                    @endif
                </div>
                <div>
                    <h2 class="h4 fw-bold mb-8">{{ $student->full_name }}</h2>
                    <p class="mb-8 opacity-90">Student ID: <strong>{{ $student->student_id }}</strong></p>
                    <p class="mb-12 opacity-90">{{ $student->class_name }} &bull; {{ $student->academic_year }}</p>
                    @if($student->status == 'Active')
                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                    @elseif($student->status == 'Draft')
                        <span class="bg-warning-100 text-warning-600 px-24 py-4 radius-4 fw-medium text-sm">Draft</span>
                    @else
                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $student->status }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row gy-3">
            <div class="col-lg-6">
                <div class="detail-card bg-base">
                    <div class="detail-card-header">
                        <h6 class="text-lg fw-semibold"><i class="ri-user-3-line text-primary-600"></i> Personal Information</h6>
                    </div>
                    <div class="p-20">
                        <div class="detail-row"><span class="detail-label">Gender</span><span class="detail-value">: {{ $student->gender }}</span></div>
                        <div class="detail-row"><span class="detail-label">Date of Birth</span><span class="detail-value">: {{ $student->dob }}</span></div>
                        <div class="detail-row"><span class="detail-label">Category</span><span class="detail-value">: {{ $student->category ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value text-primary-600">: {{ $student->phone }}</span></div>
                        <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value text-primary-600">: {{ $student->email ?: '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="detail-card bg-base">
                    <div class="detail-card-header">
                        <h6 class="text-lg fw-semibold"><i class="ri-parent-line text-primary-600"></i> Father</h6>
                    </div>
                    <div class="p-20">
                        <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">: {{ $student->father_name ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">: {{ $student->father_phone ?: '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="detail-card bg-base">
                    <div class="detail-card-header">
                        <h6 class="text-lg fw-semibold"><i class="ri-parent-line text-primary-600"></i> Mother</h6>
                    </div>
                    <div class="p-20">
                        <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">: {{ $student->mother_name ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">: {{ $student->mother_phone ?: '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="detail-card bg-base">
                    <div class="detail-card-header">
                        <h6 class="text-lg fw-semibold"><i class="ri-shield-user-line text-primary-600"></i> Guardian</h6>
                    </div>
                    <div class="p-20">
                        <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value">: {{ $student->guardian_type ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">: {{ $student->guardian_name ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">: {{ $student->guardian_phone ?: '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="detail-card bg-base">
                    <div class="detail-card-header">
                        <h6 class="text-lg fw-semibold"><i class="ri-heart-pulse-line text-primary-600"></i> Medical & Address</h6>
                    </div>
                    <div class="p-20">
                        <div class="detail-row"><span class="detail-label">National Health Insurance</span><span class="detail-value">: {{ $student->has_nhis ?: '-' }}</span></div>
                        @if($student->has_nhis === 'Yes')
                            <div class="detail-row"><span class="detail-label">Card Name</span><span class="detail-value">: {{ $student->nhis_card_name ?: '-' }}</span></div>
                            <div class="detail-row"><span class="detail-label">NHIS Number</span><span class="detail-value">: {{ $student->nhis_number ?: '-' }}</span></div>
                        @endif
                        <div class="detail-row"><span class="detail-label">Blood Group</span><span class="detail-value">: {{ $student->blood_group ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Height</span><span class="detail-value">: {{ $student->height ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Weight</span><span class="detail-value">: {{ $student->weight ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Current Address</span><span class="detail-value">: {{ $student->current_address ?: '-' }}</span></div>
                        <div class="detail-row"><span class="detail-label">Previous School</span><span class="detail-value">: {{ $student->previous_school_name ?: '-' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="detail-card bg-base">
                    <div class="detail-card-header">
                        <h6 class="text-lg fw-semibold"><i class="ri-file-upload-line text-primary-600"></i> Documents</h6>
                    </div>
                    <div class="p-20">
                        @if($docs->count() > 0)
                            <div class="mt-12">
                                <h6 class="text-md fw-semibold mb-8">Documents</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($docs as $doc)
                                        <li class="mb-8">
                                            <a href="{{ asset($doc->document_path) }}" target="_blank" class="text-primary-600 hover-underline">
                                                <i class="ri-file-line"></i> {{ $doc->doc_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="mb-0 text-secondary-light mt-12">No documents uploaded.</p>
                        @endif
                        @if($student->notes)
                            <div class="mt-16">
                                <h6 class="text-md fw-semibold mb-8">Notes</h6>
                                <p class="mb-0">{{ $student->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
