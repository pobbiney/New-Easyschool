@php $pageName = "dormitory"; $subpageName = "dormitory-setup"; @endphp

@extends('layouts.app')

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">DORMITORY MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Dormitory Setup</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="add-house-btn btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md"><i class="ri-home-4-line"></i></span>
                Add House
            </button>
            <button type="button" class="add-dormitory-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
                Add Dormitory
            </button>
        </div>
    </div>

    <div class="row gy-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header border-bottom bg-base py-16 px-20">
                    <h6 class="text-lg fw-semibold mb-0">Houses</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">Create boarding houses and manage dormitories under each house.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>House Name</th>
                                    <th>Description</th>
                                    <th>Dormitories</th>
                                    <th>Total Beds</th>
                                    <th>Occupied</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($houses as $house)
                                    @php
                                        $totalBeds = $house->dormitories->sum('bed_count');
                                        $occupiedBeds = $house->dormitories->flatMap->beds->whereNotNull('student_id')->count();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><span class="text-primary-600 fw-semibold">{{ $house->name }}</span></td>
                                        <td>{{ $house->description ?: '—' }}</td>
                                        <td>{{ $house->dormitories->count() }}</td>
                                        <td>{{ $totalBeds }}</td>
                                        <td>{{ $occupiedBeds }}</td>
                                        <td>
                                            @if($house->status == 'Active')
                                                <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                            @else
                                                <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $house->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" data-url="{{ route('get-house-id', $house->id) }}"
                                                class="edit-house-btn btn btn-sm btn-outline-primary-600">
                                                <i class="ri-edit-2-line"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-24 text-secondary-light">No houses created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card h-100">
                <div class="card-header border-bottom bg-base py-16 px-20">
                    <h6 class="text-lg fw-semibold mb-0">Dormitories & Beds</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">Each dormitory has a fixed number of beds for student assignment.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table bordered-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>House</th>
                                    <th>Dormitory</th>
                                    <th>Beds</th>
                                    <th>Available</th>
                                    <th>Assigned Students</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $row = 0; @endphp
                                @forelse($houses as $house)
                                    @foreach($house->dormitories as $dormitory)
                                        @php
                                            $row++;
                                            $occupied = $dormitory->beds->whereNotNull('student_id')->count();
                                            $available = max(0, $dormitory->bed_count - $occupied);
                                        @endphp
                                        <tr>
                                            <td>{{ $row }}</td>
                                            <td>{{ $house->name }}</td>
                                            <td><span class="text-primary-600 fw-semibold">{{ $dormitory->name }}</span></td>
                                            <td>{{ $dormitory->bed_count }}</td>
                                            <td>{{ $available }}</td>
                                            <td>
                                                @if($occupied > 0)
                                                    @foreach($dormitory->beds->whereNotNull('student_id') as $bed)
                                                        <span class="badge bg-primary-50 text-primary-600 me-1 mb-1">{{ $bed->bed_label }}: {{ $bed->student?->full_name }}</span>
                                                    @endforeach
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($dormitory->status == 'Active')
                                                    <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                                @else
                                                    <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $dormitory->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" data-url="{{ route('get-dormitory-id', $dormitory->id) }}"
                                                    class="edit-dormitory-btn btn btn-sm btn-outline-primary-600">
                                                    <i class="ri-edit-2-line"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-24 text-secondary-light">No dormitories created yet.</td>
                                    </tr>
                                @endforelse
                                @if($houses->isNotEmpty() && $houses->flatMap->dormitories->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center py-24 text-secondary-light">Add a dormitory to start assigning beds.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('dormitory.modals.add-house-modal')
@include('dormitory.modals.edit-house-modal')
@include('dormitory.modals.add-dormitory-modal')
@include('dormitory.modals.edit-dormitory-modal')

@endsection

@section('scripts')
<script>
    function openSidebar(selector) {
        $(selector).addClass('active');
        $('.overlay').addClass('active');
    }

    function closeSidebars() {
        $('.my-sidebar, .edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    }

    $('.add-house-btn').on('click', function () {
        openSidebar('.house-sidebar');
    });

    $('.add-dormitory-btn').on('click', function () {
        openSidebar('.dormitory-sidebar');
    });

    $('.close-my-sidebar, .close-edit-sidebar, .overlay').on('click', function () {
        closeSidebars();
    });

    $('.edit-house-btn').on('click', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_house_id').val(data.id);
            $('#edit_house_name').val(data.name);
            $('#edit_house_description').val(data.description || '');
            $('#edit_house_status').val(data.status);
            openSidebar('.edit-house-sidebar');
        });
    });

    $('.edit-dormitory-btn').on('click', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_dormitory_id').val(data.id);
            $('#edit_dormitory_house_id').val(data.house_id);
            $('#edit_dormitory_name').val(data.name);
            $('#edit_dormitory_bed_count').val(data.bed_count);
            $('#edit_dormitory_status').val(data.status);
            openSidebar('.edit-dormitory-sidebar');
        });
    });
</script>
@endsection
