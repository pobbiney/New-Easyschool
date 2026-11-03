@php $pageName = "course"; $subpageName = "add-course"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .course-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 18px 20px;
        background: var(--white, #fff);
        height: 100%;
    }

    .course-stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .course-table-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 14px 20px;
        background: linear-gradient(90deg, rgba(37, 161, 148, 0.08), rgba(59, 130, 246, 0.06));
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--neutral-600, #4b5563);
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .legend-dot.is-main { background: #25A194; }
    .legend-dot.is-sub { background: #6366f1; }

    .course-table-wrapper {
        overflow-x: auto;
    }

    .course-table {
        min-width: 900px;
    }

    .course-table thead th {
        background: var(--primary-600, #25A194);
        color: #fff;
        font-weight: 600;
        border: none;
        white-space: nowrap;
    }

    .course-table tbody tr.main-course-row {
        background: linear-gradient(90deg, rgba(37, 161, 148, 0.12) 0%, rgba(37, 161, 148, 0.04) 100%);
        border-top: 3px solid var(--primary-600, #25A194);
    }

    .course-table tbody tr.main-course-row td {
        border-bottom: 1px solid rgba(37, 161, 148, 0.15);
        vertical-align: middle;
    }

    .course-table tbody tr.subcourse-row {
        background: #fafbff;
    }

    .course-table tbody tr.subcourse-row td {
        border-bottom: 1px solid var(--neutral-100, #f3f4f6);
        vertical-align: middle;
    }

    .course-table tbody tr.subcourse-row td:first-child {
        border-left: 4px solid #6366f1;
    }

    .course-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .course-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        font-size: 14px;
    }

    .course-avatar.is-main {
        background: rgba(37, 161, 148, 0.18);
        color: #0f766e;
    }

    .course-avatar.is-sub {
        background: rgba(99, 102, 241, 0.15);
        color: #4338ca;
        font-size: 16px;
    }

    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .type-badge.is-main {
        background: #ccfbf1;
        color: #0f766e;
    }

    .type-badge.is-sub {
        background: #e0e7ff;
        color: #4338ca;
    }

    .category-badge {
        display: inline-flex;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .category-badge.is-subject {
        background: #fef3c7;
        color: #b45309;
    }

    .category-badge.is-4rs {
        background: #fce7f3;
        color: #be185d;
    }

    .category-badge.is-default {
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-600, #4b5563);
    }

    .parent-hint {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #6366f1;
        font-weight: 500;
    }

    .subcourse-count-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(37, 161, 148, 0.12);
        color: #0f766e;
    }

    .subcourse-empty-hint {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        font-size: 12px;
        font-style: italic;
        color: var(--neutral-500, #6b7280);
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">COURSE SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Courses</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('course-teacher-assignment') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
                <i class="ri-user-shared-line"></i>
                Course Teachers
            </a>
            <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <i class="ri-add-large-line"></i>
                Add Course
            </button>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-4">
            <div class="course-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Courses</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="course-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Sub-Courses</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['subcourses'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-node-tree"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="course-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Active Courses</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['active'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-4">Courses & Sub-Courses</h6>
            <p class="text-sm text-secondary-light mb-0">Green rows are main courses. Purple-indented rows are sub-courses under the main course above them.</p>
        </div>

        <div class="course-table-legend">
            <span class="legend-item"><span class="legend-dot is-main"></span> Main course (counts as 1 course)</span>
            <span class="legend-item"><span class="legend-dot is-sub"></span> Sub-course (part of main course)</span>
        </div>

        <div class="card-body p-0 dataTable-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="courseDataTable" name="search" placeholder="Search courses...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            <div class="course-table-wrapper p-0">
                <table class="table bordered-table mb-0 course-table" id="courseDataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNumber = 0; @endphp
                        @forelse($topLevelCourses as $course)
                            @php
                                $rowNumber++;
                                $initials = strtoupper(substr($course->name, 0, 2));
                                $categoryClass = $course->category === 'Subject' ? 'is-subject' : ($course->category === '4RS' ? 'is-4rs' : 'is-default');
                            @endphp
                            <tr class="main-course-row">
                                <td><span class="fw-bold text-primary-600">{{ $rowNumber }}</span></td>
                                <td>
                                    <div class="course-name-cell">
                                        <span class="course-avatar is-main">{{ $initials }}</span>
                                        <div>
                                            <span class="d-block fw-bold text-primary-600">{{ $course->name }}</span>
                                            @if($course->subCourses->count())
                                                <span class="subcourse-count-chip">
                                                    <i class="ri-node-tree"></i>
                                                    {{ $course->subCourses->count() }} sub-course{{ $course->subCourses->count() > 1 ? 's' : '' }}
                                                </span>
                                            @else
                                                <span class="subcourse-empty-hint">
                                                    <i class="ri-information-line"></i>
                                                    No sub-courses yet
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="type-badge is-main"><i class="ri-book-2-line"></i> Main Course</span></td>
                                <td><span class="category-badge {{ $categoryClass }}">{{ $course->category }}</span></td>
                                <td>
                                    @if($course->status === 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $course->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" data-url="{{ route('get-course-id', $course->id) }}"
                                            class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-course-edit">
                                            <i class="ri-edit-2-line"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary-600 add-subcourse-btn"
                                            data-parent-id="{{ $course->id }}" data-parent-name="{{ $course->name }}">
                                            <i class="ri-add-line"></i> Add Sub-Course
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            @foreach($course->subCourses as $subCourse)
                                @php
                                    $subCategoryClass = $subCourse->category === 'Subject' ? 'is-subject' : ($subCourse->category === '4RS' ? 'is-4rs' : 'is-default');
                                @endphp
                                <tr class="subcourse-row">
                                    <td><span class="text-secondary-light">↳</span></td>
                                    <td>
                                        <div class="course-name-cell">
                                            <span class="course-avatar is-sub"><i class="ri-corner-down-right-line"></i></span>
                                            <div>
                                                <span class="d-block fw-semibold">{{ $subCourse->name }}</span>
                                                <span class="parent-hint">Under: {{ $course->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="type-badge is-sub"><i class="ri-git-branch-line"></i> Sub-Course</span></td>
                                    <td><span class="category-badge {{ $subCategoryClass }}">{{ $subCourse->category }}</span></td>
                                    <td>
                                        @if($subCourse->status === 'Active')
                                            <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                        @else
                                            <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $subCourse->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" data-url="{{ route('get-course-id', $subCourse->id) }}"
                                            class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-course-edit">
                                            <i class="ri-edit-2-line"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td></td>
                                <td class="text-center py-32 text-secondary-light">
                                    <i class="ri-book-open-line d-block mb-8" style="font-size:32px;color:#25A194;"></i>
                                    No courses added yet. Click <strong>Add Course</strong> to get started.
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('course-setup.add-course-modal')
@include('course-setup.edit-course-modal')
@include('course-setup.modals.add-sub-course-modal')

@endsection

@section('scripts')
<script>
    (function () {
        const tableEl = document.getElementById('courseDataTable');
        if (!tableEl || typeof DataTable === 'undefined') {
            return;
        }

        const courseTable = new DataTable(tableEl, {
            ordering: false,
            paging: false,
            info: false,
            searching: true,
        });

        $('.dataTable-wrapper .dt-search .dt-input').on('keyup', function () {
            courseTable.search(this.value).draw();
        });
    })();

    function closeAllCourseSidebars() {
        $('.add-course-sidebar, .subcourse-sidebar, .edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    }

    $('.my-sidebar-btn').on('click', function () {
        $('.subcourse-sidebar, .edit-sidebar').removeClass('active');
        $('.add-course-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-my-sidebar').on('click', closeAllCourseSidebars);
    $('.edit-sidebar-btn').on('click', function () {
        $('.add-course-sidebar, .subcourse-sidebar').removeClass('active');
        $('.edit-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-edit-sidebar').on('click', closeAllCourseSidebars);
    $('.add-subcourse-btn').on('click', function () {
        $('#subcourse_parent_id').val($(this).data('parent-id'));
        $('#subcourse_parent_name').val($(this).data('parent-name'));
        $('.add-course-sidebar, .edit-sidebar').removeClass('active');
        $('.subcourse-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-subcourse-sidebar').on('click', closeAllCourseSidebars);
    $('.overlay').on('click', closeAllCourseSidebars);

    $('#add_course_parent_id').on('change', function () {
        const isSubCourse = !!this.value;
        $('#add_course_category').prop('disabled', isSubCourse);
        if (isSubCourse) {
            $('#add_course_category').val('');
        }
    }).trigger('change');

    $('body').on('click', '.show-course-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#CourseID').val(data.id);
            $('#coursename').val(data.name);
            $('#coursecat').val(data.category);
            $('#coursedesc').val(data.description);
            $('#coursestats').val(data.status);
            $('#edit_course_parent_id').val(data.parent_id || '');

            const isSubCourse = !!data.parent_id;
            $('#coursecat').prop('disabled', isSubCourse);
            $('#edit_course_parent_id').prop('disabled', data.sub_courses_count > 0);
        });
    });
</script>
@endsection
