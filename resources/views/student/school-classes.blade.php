@php $pageName = "class-setup"; $subpageName = "school-classes"; @endphp

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

    .class-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .class-avatar {
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

    .no-category-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-500, #6b7280);
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

    .class-row:hover {
        background: rgba(37, 161, 148, 0.03);
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
                <span class="text-secondary-light"> / Classes</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('class-categories') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-folder-3-line"></i>
                Class Categories
            </a>
            <a href="{{ route('class-teacher-assignment') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-user-follow-line"></i>
                Class Teachers
            </a>
            <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <i class="ri-add-large-line"></i>
                Add Class
            </button>
        </div>
    </div>

    <div class="class-setup-hero d-flex align-items-start gap-16">
        <span class="class-setup-hero-icon"><i class="ri-layout-grid-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">School Classes</h5>
            <p class="text-sm text-secondary-light mb-0">Create and manage classes grouped by category. Assign teachers and register courses from the related Class Setup pages.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="class-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Classes</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-layout-grid-line"></i></span>
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
                        <p class="text-secondary-light text-sm mb-4">Categories</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['categories'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-folder-3-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 class-list-wrapper">
        <div class="card-header border-bottom py-16 px-24">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">All Classes</h6>
                    <p class="text-sm text-secondary-light mb-0">Browse classes by name, category, and status.</p>
                </div>
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search classes...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($schoolClasses->isNotEmpty())
            <div class="class-list-scroll p-0">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schoolClasses as $class)
                        <tr class="class-row">
                            <td>
                                <div class="class-name-cell">
                                    <span class="class-avatar">{{ strtoupper(substr($class->name, 0, 2)) }}</span>
                                    <span class="fw-semibold text-primary-600">{{ $class->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($class->category)
                                    <span class="category-pill category-pill-{{ $class->category->id % 6 }}">
                                        <i class="ri-folder-3-line"></i>
                                        {{ $class->category->name }}
                                    </span>
                                @else
                                    <span class="no-category-badge">Uncategorized</span>
                                @endif
                            </td>
                            <td>
                                @if($class->status == 'Active')
                                    <span class="status-badge-active">Active</span>
                                @else
                                    <span class="status-badge-inactive">{{ $class->status }}</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" data-url="{{ route('get-school-class-id', $class->id) }}"
                                    class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-class-edit">
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
                <div class="class-avatar mx-auto mb-16" style="width: 56px; height: 56px; font-size: 18px;">
                    <i class="ri-layout-grid-line"></i>
                </div>
                <h6 class="fw-semibold mb-6">No classes yet</h6>
                <p class="text-sm text-secondary-light mb-16">Add a class category first, then create classes under it.</p>
                @if($activeClassCategories->isEmpty())
                    <a href="{{ route('class-categories') }}" class="btn btn-outline-primary-600 btn-sm">Add a category first</a>
                @else
                    <button type="button" class="my-sidebar-btn btn btn-primary-600 btn-sm">Add your first class</button>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@include('student.modals.add-school-class-modal')
@include('student.modals.edit-school-class-modal')

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

    $('body').on('click', '.show-class-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_class_id').val(data.id);
            $('#edit_class_name').val(data.name);
            $('#edit_class_category_id').val(data.class_category_id || '');
            $('#edit_class_status').val(data.status);
        });
    });
</script>
@endsection
