@php $pageName = "settings"; $subpageName = "assessment-types"; @endphp

@extends('layouts.app')

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">SETTINGS</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Settings / Assessment Types</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('academic-terms') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-calendar-event-line"></i> Academic Terms
            </a>
            <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
                Add Assessment Type
            </button>
        </div>
    </div>

    <div class="mt-24">
        <div class="card h-100">
            <div class="card-body p-0 dataTable-wrapper">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                    <div class="d-flex flex-wrap align-items-center gap-8">
                        @forelse($classCategories as $category)
                            <a href="{{ route('assessment-types', ['class_category_id' => $category->id]) }}"
                                class="btn btn-sm {{ (int) $selectedCategoryId === (int) $category->id ? 'btn-primary-600' : 'btn-outline-primary-600' }}">
                                {{ $category->name }}
                            </a>
                        @empty
                            <span class="text-secondary-light text-sm">Add a class category first, then set assessment types for it.</span>
                        @endforelse
                    </div>
                    <form class="navbar-search dt-search m-0">
                        <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search...">
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>
                </div>
                <div class="px-20 py-12 border-bottom border-neutral-200">
                    <p class="text-sm text-secondary-light mb-0">
                        Types are set per class category. Teachers assign the marks for each type on a class and subject for the current term.
                    </p>
                </div>
                <div class="p-0">
                    @if($assessmentTypes->isNotEmpty())
                    <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Assessment Type</th>
                                <th>Category</th>
                                <th>Code</th>
                                <th>Max Number</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessmentTypes as $type)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-primary-600 fw-semibold">{{ $type->name }}</span></td>
                                <td><span class="bg-neutral-100 text-secondary-light px-16 py-4 radius-4 fw-medium text-sm">{{ $type->categoryLabel() }}</span></td>
                                <td><span class="bg-neutral-100 text-secondary-light px-16 py-4 radius-4 fw-medium text-sm">{{ $type->slug }}</span></td>
                                <td>{{ $type->max_number }}</td>
                                <td>{{ $type->sort_order }}</td>
                                <td>
                                    @if($type->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $type->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" data-url="{{ route('get-assessment-type-id', $type->id) }}"
                                            class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-type-edit">
                                            <i class="ri-edit-2-line"></i> Edit
                                        </button>
                                        @if($type->isInUse())
                                            <button type="button" class="btn btn-sm btn-outline-neutral-400 text-secondary-light"
                                                title="Cannot delete — used by {{ $type->assessments_count }} assessment{{ $type->assessments_count === 1 ? '' : 's' }}"
                                                disabled style="opacity:0.65;cursor:not-allowed;">
                                                <i class="ri-delete-bin-line"></i> Delete
                                            </button>
                                        @else
                                            <form action="{{ route('delete-assessment-type-process') }}" method="POST" class="d-inline"
                                                data-confirm-title="{{ 'Delete '.$type->name.'?' }}"
                                                data-confirm="This cannot be undone.">
                                                @csrf
                                                <input type="hidden" name="assessment_type_id" value="{{ $type->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger-600">
                                                    <i class="ri-delete-bin-line"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-56 px-24">
                        <h6 class="fw-semibold mb-6">No assessment types yet</h6>
                        <p class="text-sm text-secondary-light mb-16">
                            @if($classCategories->isEmpty())
                                Add a class category first, then set assessment types for it.
                            @else
                                No assessment types for this class category yet.
                            @endif
                        </p>
                        @if($classCategories->isNotEmpty())
                            <button type="button" class="my-sidebar-btn btn btn-primary-600 btn-sm">Add Assessment Type</button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('settings.modals.add-assessment-type-modal')
@include('settings.modals.edit-assessment-type-modal')

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

    $('body').on('click', '.show-type-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_assessment_type_id').val(data.id);
            $('#edit_assessment_type_class_category_id').val(data.class_category_id);
            $('#edit_assessment_type_name').val(data.name);
            $('#edit_assessment_type_slug').val(data.slug);
            $('#edit_assessment_type_category').val(data.category);
            $('#edit_assessment_type_max_number').val(data.max_number);
            $('#edit_assessment_type_sort_order').val(data.sort_order);
            $('#edit_assessment_type_status').val(data.status);
        });
    });
</script>
@endsection
