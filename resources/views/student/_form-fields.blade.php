@php $student = $student ?? null; @endphp
<div class="row gy-3">
    <div class="col-lg-12">
        <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Personal Info</h6>
            </div>
            <div class="card-body p-20">
                @include('student.partials._section-personal')
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Parent & Guardian Info</h6>
            </div>
            <div class="card-body p-20">
                @include('student.partials._section-parent')
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Medical & Other Information</h6>
            </div>
            <div class="card-body p-20">
                @include('student.partials._section-other')
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Upload Documents</h6>
            </div>
            <div class="card-body p-20">
                @include('student.partials._section-documents')
            </div>
        </div>
    </div>
</div>
