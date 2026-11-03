@extends('layouts.print')

@section('content')
@php
    $logoUrl = $school->logoUrl();
    $statusClass = strtolower($student->status);
    $autoPrint = $autoPrint ?? false;
    $isPublicView = $isPublicView ?? false;
@endphp

<div class="print-sheet" id="printSheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print
        </button>
        @if(!$isPublicView)
            <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
                <i class="ri-close-line"></i> Close
            </button>
        @endif
    </div>

    <header class="letterhead">
        <div class="letterhead-logo">
            <img src="{{ $logoUrl }}" alt="{{ $school->name ?: 'School' }}" id="schoolLogo">
        </div>
        <div>
            <h2 class="letterhead-school">{{ $school->name ?: 'EasySchool' }}</h2>
            @if(!empty($school->motto))
                <p class="letterhead-motto">"{{ $school->motto }}"</p>
            @endif
            <div class="letterhead-meta">
                @if(!empty($school->address))
                    <span><i class="ri-map-pin-line"></i> {{ $school->address }}</span>
                @endif
                @if(!empty($school->phone))
                    <span><i class="ri-phone-line"></i> {{ $school->phone }}</span>
                @endif
                @if(!empty($school->email))
                    <span><i class="ri-mail-line"></i> {{ $school->email }}</span>
                @endif
                @if(!empty($school->website))
                    <span><i class="ri-global-line"></i> {{ $school->website }}</span>
                @endif
            </div>
        </div>
        <div class="qr-block">
            {!! QrCode::size(120)->margin(1)->errorCorrection('H')->generate($profileUrl) !!}
            <div class="qr-block-label">Scan to verify profile</div>
        </div>
    </header>

    <div class="doc-head">
        <h1>Student Profile Record</h1>
        <p>Academic Year {{ $student->academic_year }} &nbsp;&bull;&nbsp; Issued {{ now()->format('d M Y') }}</p>
    </div>

    <div class="student-banner">
        <div class="student-photo">
            @if(!empty($student->picture))
                <img src="{{ asset($student->picture) }}" alt="{{ $student->full_name }}">
            @elseif(strtolower($student->gender) == 'male')
                <img src="{{ asset('assets/images/thumbs/guardian-img1.png') }}" alt="Student">
            @else
                <img src="{{ asset('assets/images/thumbs/guardian-img2.png') }}" alt="Student">
            @endif
        </div>
        <div>
            <h2 class="student-name">{{ $student->full_name }}</h2>
            <div class="student-meta-row">
                <span>ID: <strong>{{ $student->student_id }}</strong></span>
                <span>Class: <strong>{{ $student->class_name }}</strong></span>
                <span>Gender: <strong>{{ $student->gender }}</strong></span>
            </div>
            <span class="status-pill {{ $statusClass }}">{{ $student->status }}</span>
        </div>
    </div>

    <div class="print-content">
        <div class="info-section">
            <h3 class="info-section-title">Personal Information</h3>
            <table class="info-table">
                <tr><td>Date of Birth</td><td>{{ $student->dob }}</td></tr>
                <tr><td>Category</td><td>{{ $student->category ?: '—' }}</td></tr>
                <tr><td>Phone</td><td>{{ $student->phone }}</td></tr>
                <tr><td>Email</td><td>{{ $student->email ?: '—' }}</td></tr>
                <tr><td>Academic Year</td><td>{{ $student->academic_year }}</td></tr>
            </table>
        </div>

        <div class="info-section">
            <h3 class="info-section-title">Parent &amp; Guardian</h3>
            <div class="info-columns">
                <div class="info-box">
                    <h4 class="info-box-title">Father</h4>
                    <table class="info-table">
                        <tr><td>Name</td><td>{{ $student->father_name ?: '—' }}</td></tr>
                        <tr><td>Phone</td><td>{{ $student->father_phone ?: '—' }}</td></tr>
                    </table>
                </div>
                <div class="info-box">
                    <h4 class="info-box-title">Mother</h4>
                    <table class="info-table">
                        <tr><td>Name</td><td>{{ $student->mother_name ?: '—' }}</td></tr>
                        <tr><td>Phone</td><td>{{ $student->mother_phone ?: '—' }}</td></tr>
                    </table>
                </div>
                <div class="info-box">
                    <h4 class="info-box-title">Guardian ({{ $student->guardian_type ?: 'N/A' }})</h4>
                    <table class="info-table">
                        <tr><td>Name</td><td>{{ $student->guardian_name ?: '—' }}</td></tr>
                        <tr><td>Phone</td><td>{{ $student->guardian_phone ?: '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3 class="info-section-title">Medical &amp; Other Details</h3>
            <table class="info-table">
                <tr><td>National Health Insurance</td><td>{{ $student->has_nhis ?: '—' }}</td></tr>
                @if($student->has_nhis === 'Yes')
                    <tr><td>Card Name</td><td>{{ $student->nhis_card_name ?: '—' }}</td></tr>
                    <tr><td>NHIS Number</td><td>{{ $student->nhis_number ?: '—' }}</td></tr>
                @endif
                <tr><td>Blood Group</td><td>{{ $student->blood_group ?: '—' }}</td></tr>
                <tr><td>Height</td><td>{{ $student->height ?: '—' }}</td></tr>
                <tr><td>Weight</td><td>{{ $student->weight ?: '—' }}</td></tr>
                <tr><td>Current Address</td><td>{{ $student->current_address ?: '—' }}</td></tr>
                <tr><td>Previous School</td><td>{{ $student->previous_school_name ?: '—' }}</td></tr>
                @if($student->notes)
                    <tr><td>Notes</td><td>{{ $student->notes }}</td></tr>
                @endif
            </table>
        </div>

        @if($docs->count() > 0)
            <div class="info-section">
                <h3 class="info-section-title">Documents on File</h3>
                <table class="info-table">
                    @foreach($docs as $doc)
                        <tr><td>{{ $doc->doc_name }}</td><td>On record</td></tr>
                    @endforeach
                </table>
            </div>
        @endif

        <div class="sig-row">
            <div class="sig-block"><div class="sig-line">Class Teacher</div></div>
            <div class="sig-block"><div class="sig-line">Head Teacher</div></div>
            <div class="sig-block"><div class="sig-line">Official Stamp</div></div>
        </div>

        <p class="print-footnote">
            Official document of {{ $school->name ?: 'EasySchool' }} &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y, h:i A') }}
        </p>
    </div>
</div>
@endsection

@section('scripts')
@include('student.partials._print-student-scripts')
@endsection
