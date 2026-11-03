@php $pageName = "settings"; $subpageName = "academic-terms"; @endphp

@extends('layouts.app')

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">SETTINGS</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Settings / Academic Terms</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('academic-years') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-calendar-line"></i> Academic Years
            </a>
            <a href="{{ route('academic-session') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-calendar-2-line"></i> Set Current Session
            </a>
            <a href="{{ route('assessment-types') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-file-list-3-line"></i> Assessment Types
            </a>
            <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
                Add Academic Term
            </button>
        </div>
    </div>

    <div class="mt-24">
        <div class="card h-100">
            <div class="card-body p-0 dataTable-wrapper">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                    <form class="navbar-search dt-search m-0">
                        <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search...">
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>
                </div>
                <div class="p-0">
                    <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Academic Term</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($academicTerms as $term)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-primary-600 fw-semibold">{{ $term->name }}</span></td>
                                <td>{{ $term->sort_order }}</td>
                                <td>
                                    @if($term->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $term->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" data-url="{{ route('get-academic-term-id', $term->id) }}"
                                        class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-term-edit">
                                        <i class="ri-edit-2-line"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('student.modals.add-academic-term-modal')
@include('student.modals.edit-academic-term-modal')

@endsection

@section('scripts')
<script>
    $('.my-sidebar-btn').on('click', function () {
        $('.my-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-my-sidebar, .overlay').on('click', function () {
        $('.my-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });
    $('.edit-sidebar-btn').on('click', function () {
        $('.edit-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-edit-sidebar, .overlay').on('click', function () {
        $('.edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('body').on('click', '.show-term-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_term_id').val(data.id);
            $('#edit_term_name').val(data.name);
            $('#edit_term_sort_order').val(data.sort_order);
            $('#edit_term_status').val(data.status);
        });
    });
</script>
@endsection
