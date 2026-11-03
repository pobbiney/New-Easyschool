@php $pageName = "user-management"; $subpageName = "user-categories"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .category-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 18px 20px;
        background: var(--white, #fff);
        height: 100%;
    }

    .category-stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .category-list-wrapper,
    .category-list-wrapper .dt-container,
    .category-list-wrapper .dt-layout-cell {
        overflow: visible !important;
    }

    .category-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .category-list-wrapper table.dataTable {
        min-width: 860px;
    }

    .screen-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        margin: 0 6px 6px 0;
    }

    .category-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .category-avatar {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
        font-weight: 700;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">USER MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / User Categories</span>
            </div>
        </div>
        <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
            Add Category
        </button>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="category-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Categories</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-shield-user-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="category-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Active</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['active'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="category-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Inactive</p>
                        <h4 class="fw-semibold mb-0 text-danger-600">{{ $stats['inactive'] }}</h4>
                    </div>
                    <span class="stat-icon bg-danger-100 text-danger-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="category-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Assigned Users</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['users'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-group-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-20">
            <h6 class="text-lg fw-semibold mb-0">User Categories</h6>
            <p class="text-sm text-secondary-light mb-0 mt-4">Manage access groups and control which screens each category can use.</p>
        </div>
        <div class="card-body p-0 dataTable-wrapper category-list-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <div class="d-flex flex-wrap align-items-center gap-16">
                    <form class="navbar-search dt-search m-0">
                        <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search categories...">
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>
                    <div class="dropdown" id="categoryFilterDropdown">
                        <button type="button"
                            class="px-12 py-5-px border border-neutral-300 radius-8 d-flex align-items-center gap-20"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="d-flex align-items-center gap-1 text-secondary-light text-sm">
                                <i class="ri-filter-3-line text-md line-height-1"></i>
                                Filter
                            </span>
                            <span><i class="ri-arrow-down-s-line"></i></span>
                        </button>
                        <div class="dropdown-menu border bg-base shadow dropdown-menu-lg p-0">
                            <div class="d-flex align-items-center justify-content-between border-bottom py-8 px-16">
                                <span class="fw-semibold text-lg text-primary-light">Filter</span>
                                <button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-secondary-light" id="closeCategoryFilterDropdown">
                                    <i class="ri-close-large-line"></i>
                                </button>
                            </div>
                            <form action="#" class="p-16" id="categoryFilterForm">
                                <div class="mb-16">
                                    <label for="filter_category_status" class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                                    <select id="filter_category_status" class="form-control form-select">
                                        <option value="">All Statuses</option>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="d-grid grid-cols-2 gap-16">
                                    <button type="reset" class="btn btn-danger-200 text-danger-600 w-100" id="resetCategoryFilters">Reset</button>
                                    <button type="submit" class="btn btn-primary-600 w-100">Apply</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-8 text-secondary-light">
                    <span>Rows per page:</span>
                    <div class="dt-length">
                        <select name="dataTable_length" aria-controls="dataTable" class="dt-input form-control form-select">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-0 category-list-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Users</th>
                            <th>Allowed Screens</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            @php
                                $initial = strtoupper(substr($category->cat_name, 0, 1));
                                $screenCount = count($category->assigned_links);
                                $visibleScreens = array_slice($category->assigned_links, 0, 3);
                                $hiddenScreens = max(0, $screenCount - count($visibleScreens));
                            @endphp
                            <tr data-status="{{ $category->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="category-name-cell">
                                        <span class="category-avatar">{{ $initial }}</span>
                                        <div>
                                            <h6 class="text-md fw-semibold mb-0 text-primary-600">{{ $category->cat_name }}</h6>
                                            <span class="text-xs text-secondary-light">Access role group</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($category->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $category->status ?: 'Inactive' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $category->users_count }}</span>
                                    <span class="text-secondary-light text-sm">{{ Str::plural('user', $category->users_count) }}</span>
                                </td>
                                <td>
                                    @if($screenCount > 0)
                                        @foreach($visibleScreens as $screen)
                                            <span class="screen-badge">{{ $screen }}</span>
                                        @endforeach
                                        @if($hiddenScreens > 0)
                                            <span class="screen-badge">+{{ $hiddenScreens }} more</span>
                                        @endif
                                    @else
                                        <span class="text-secondary-light text-sm">No screens assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button"
                                            data-url="{{ route('get-user-category-id', $category->cat_id) }}"
                                            class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-category-edit">
                                            <i class="ri-edit-2-line"></i> Edit
                                        </button>
                                        <button type="button"
                                            data-url="{{ route('get-user-category-id', $category->cat_id) }}"
                                            class="btn btn-sm btn-outline-danger-600 show-category-delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                            <i class="ri-delete-bin-6-line"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-32 text-secondary-light">
                                    No user categories yet. Click <strong>Add Category</strong> to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('user-management.add-category-modal')
@include('user-management.edit-category-modal')
@include('user-management.delete-category-modal')

@endsection

@section('scripts')
<script>
    let categoryTableFilters = { status: '' };
    let categoryFilterRegistered = false;

    function getCategoryDataTable() {
        if (!$.fn.DataTable || !$.fn.DataTable.isDataTable('#dataTable')) {
            return null;
        }

        return $('#dataTable').DataTable();
    }

    function registerCategoryTableFilter() {
        if (categoryFilterRegistered) {
            return;
        }

        const searchExt = $.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search;

        if (!searchExt) {
            return;
        }

        categoryFilterRegistered = true;

        searchExt.push(function (settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'dataTable') {
                return true;
            }

            const row = settings.aoData[dataIndex]?.nTr;

            if (!row || !categoryTableFilters.status) {
                return true;
            }

            return (row.getAttribute('data-status') || '') === categoryTableFilters.status;
        });
    }

    function applyCategoryTableFilters() {
        categoryTableFilters.status = $('#filter_category_status').val() || '';
        registerCategoryTableFilter();

        const table = getCategoryDataTable();
        if (table) {
            table.draw();
        }

        bootstrap.Dropdown.getOrCreateInstance(document.querySelector('#categoryFilterDropdown > button')).hide();
    }

    function resetCategoryTableFilters() {
        $('#filter_category_status').val('');
        applyCategoryTableFilters();
    }

    $('#categoryFilterForm').on('submit', function (event) {
        event.preventDefault();
        applyCategoryTableFilters();
    });

    $('#resetCategoryFilters').on('click', function () {
        setTimeout(resetCategoryTableFilters, 0);
    });

    $('#closeCategoryFilterDropdown').on('click', function () {
        bootstrap.Dropdown.getOrCreateInstance(document.querySelector('#categoryFilterDropdown > button')).hide();
    });

    registerCategoryTableFilter();

    $('.my-sidebar-btn').on('click', function () {
        $('.my-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-my-sidebar, .overlay').on('click', function () {
        $('.my-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('.close-edit-sidebar, .overlay').on('click', function () {
        $('.edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('body').on('click', '.show-category-edit', function () {
        $('.edit-sidebar').addClass('active');
        $('.overlay').addClass('active');

        $.get($(this).attr('data-url'), function (data) {
            $('#edit_cat_id').val(data.cat_id);
            $('#edit_cat_name').val(data.cat_name);
            $('#edit_status').val(data.status);
            $('.edit-link-checkbox').prop('checked', false);

            if (data.link_ids) {
                data.link_ids.forEach(function (linkId) {
                    $('#edit_link_' + linkId).prop('checked', true);
                });
            }
        }).fail(function () {
            if (typeof showAppToast === 'function') {
                showAppToast('error', 'Could not load category details.');
            }
        });
    });

    $('body').on('click', '.show-category-delete', function () {
        $.get($(this).attr('data-url'), function (data) {
            $('#delete_cat_id').val(data.cat_id);
            $('#delete_cat_name').text(data.cat_name);
        });
    });
</script>
@endsection
