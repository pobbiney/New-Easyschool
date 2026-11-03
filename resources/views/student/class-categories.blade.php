@php $pageName = "class-setup"; $subpageName = "class-categories"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .class-setup-hero {
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12) 0%, rgba(99, 102, 241, 0.08) 100%);
        border: 1px solid rgba(37, 161, 148, 0.15);
        margin-bottom: 24px;
    }

    .class-setup-hero-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-600, #25A194);
        color: #fff;
        font-size: 24px;
        flex-shrink: 0;
    }

    .class-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 20px 22px;
        background: var(--white, #fff);
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .class-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .class-stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .class-list-wrapper {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--neutral-200, #e5e7eb);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .class-list-wrapper .card-header {
        background: linear-gradient(180deg, #fff 0%, var(--neutral-50, #f9fafb) 100%);
    }

    .class-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .class-list-wrapper table.dataTable {
        min-width: 920px;
    }

    .category-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .category-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .category-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .category-pill-0 { background: rgba(37, 161, 148, 0.14); color: #0f766e; }
    .category-pill-1 { background: rgba(99, 102, 241, 0.14); color: #4338ca; }
    .category-pill-2 { background: rgba(236, 72, 153, 0.14); color: #be185d; }
    .category-pill-3 { background: rgba(245, 158, 11, 0.16); color: #b45309; }
    .category-pill-4 { background: rgba(59, 130, 246, 0.14); color: #1d4ed8; }
    .category-pill-5 { background: rgba(168, 85, 247, 0.14); color: #7e22ce; }

    .description-text {
        max-width: 320px;
        color: var(--neutral-500, #6b7280);
        font-size: 13px;
        line-height: 1.5;
    }

    .count-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
    }

    .status-badge-active,
    .status-badge-inactive {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge-active {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .status-badge-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">CLASS SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('school-classes') }}" class="text-secondary-light hover-text-primary hover-underline"> / Classes</a>
                <span class="text-secondary-light"> / Class Categories</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('school-classes') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-layout-grid-line"></i>
                Manage Classes
            </a>
            <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <i class="ri-add-large-line"></i>
                Add Category
            </button>
        </div>
    </div>

    <div class="class-setup-hero d-flex align-items-start gap-16">
        <span class="class-setup-hero-icon"><i class="ri-folder-3-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Class Categories</h5>
            <p class="text-sm text-secondary-light mb-0">Organize your classes into categories such as Primary, JHS, or SHS. Each class must belong to a category.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="class-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Categories</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-folder-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="class-stat-card">
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
            <div class="class-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Inactive</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['inactive'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="class-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Linked Classes</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['classes'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-group-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 class-list-wrapper">
        <div class="card-header border-bottom py-16 px-24">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">All Categories</h6>
                    <p class="text-sm text-secondary-light mb-0">Manage category names, descriptions, and status.</p>
                </div>
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search categories...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($categories->isNotEmpty())
            <div class="class-list-scroll p-0">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Classes</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>
                                <div class="category-name-cell">
                                    <span class="category-avatar">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                                    <div>
                                        <span class="category-pill category-pill-{{ $category->id % 6 }}">{{ $category->name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="description-text">{{ $category->description ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="count-badge">
                                    <i class="ri-group-line"></i>
                                    {{ $category->school_classes_count }}
                                </span>
                            </td>
                            <td>
                                @if($category->status == 'Active')
                                    <span class="status-badge-active">Active</span>
                                @else
                                    <span class="status-badge-inactive">{{ $category->status }}</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" data-url="{{ route('get-class-category-id', $category->id) }}"
                                    class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-category-edit">
                                    <i class="ri-edit-2-line"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-56 px-24">
                <div class="category-avatar mx-auto mb-16" style="width: 56px; height: 56px; font-size: 18px;">
                    <i class="ri-folder-3-line"></i>
                </div>
                <h6 class="fw-semibold mb-6">No class categories yet</h6>
                <p class="text-sm text-secondary-light mb-16">Create categories like Primary, JHS, or SHS before adding classes.</p>
                <button type="button" class="my-sidebar-btn btn btn-primary-600 btn-sm">Add your first category</button>
            </div>
            @endif
        </div>
    </div>
</div>

@include('student.modals.add-class-category-modal')
@include('student.modals.edit-class-category-modal')

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

    $('body').on('click', '.show-category-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_category_id').val(data.id);
            $('#edit_category_name').val(data.name);
            $('#edit_category_description').val(data.description || '');
            $('#edit_category_status').val(data.status);
        });
    });
</script>
@endsection
