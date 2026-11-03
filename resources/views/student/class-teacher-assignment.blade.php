@php $pageName = "teacher-management"; $subpageName = "class-teacher-assignment"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .assignment-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 18px 20px;
        background: var(--white, #fff);
        height: 100%;
    }

    .assignment-stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .assignment-list-wrapper,
    .assignment-list-wrapper .dt-container,
    .assignment-list-wrapper .dt-layout-cell {
        overflow: visible !important;
    }

    .assignment-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .assignment-list-wrapper table.dataTable {
        min-width: 920px;
    }

    .class-name-cell,
    .teacher-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .class-avatar,
    .teacher-avatar {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .teacher-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .teacher-empty-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-500, #6b7280);
        font-size: 12px;
        font-weight: 600;
    }

    .position-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
    }

    .assign-class-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        background: var(--neutral-50, #f9fafb);
        border: 1px solid var(--neutral-200, #e5e7eb);
    }

    .assign-class-preview-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.12);
        color: var(--primary-600, #25A194);
        font-size: 20px;
    }

    .current-teacher-box {
        padding: 14px 16px;
        border-radius: 12px;
        background: rgba(37, 161, 148, 0.05);
        border: 1px dashed rgba(37, 161, 148, 0.25);
    }

    .teacher-mini-avatar {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-600, #25A194);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
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
                <span class="text-secondary-light"> / Class Teachers</span>
            </div>
        </div>
        <a href="{{ route('school-classes') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-layout-grid-line"></i>
            Manage Classes
        </a>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Classes</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Assigned</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['assigned'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-user-follow-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Unassigned</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['unassigned'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-user-unfollow-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Teacher Category</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['teachers'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-team-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 assignment-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">Class Teacher Assignments</h6>
                    <p class="text-sm text-secondary-light mb-0">Assign exactly one teacher to each class.</p>
                </div>
            </div>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search classes or teachers...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            <div class="assignment-list-scroll p-0">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Class</th>
                            <th>Assigned Teacher</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schoolClasses as $class)
                            @php
                                $teacher = $class->classTeacher;
                                $initials = strtoupper(substr($class->name, 0, 2));
                                $teacherInitials = $teacher
                                    ? strtoupper(substr($teacher->firstname, 0, 1) . substr($teacher->surname, 0, 1))
                                    : '';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="class-name-cell">
                                        <span class="class-avatar">{{ $initials }}</span>
                                        <div>
                                            <span class="d-block fw-semibold text-primary-600">{{ $class->name }}</span>
                                            <span class="text-xs text-secondary-light">Class ID #{{ $class->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($teacher)
                                        <div class="teacher-name-cell">
                                            <span class="teacher-avatar">
                                                @if($teacher->picture)
                                                    <img src="{{ asset($teacher->picture) }}" alt="{{ $teacher->full_name }}">
                                                @else
                                                    {{ $teacherInitials }}
                                                @endif
                                            </span>
                                            <div>
                                                <span class="d-block fw-semibold">{{ $teacher->full_name }}</span>
                                                <span class="text-xs text-secondary-light">{{ $teacher->employee_id }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="teacher-empty-pill">
                                            <i class="ri-user-unfollow-line"></i>
                                            No teacher assigned
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($teacher)
                                        <span class="position-badge">{{ $teacher->position }}</span>
                                    @else
                                        <span class="text-secondary-light">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($class->status === 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $class->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary-600 assign-class-teacher-btn"
                                        data-url="{{ route('get-class-teacher-assignment', $class->id) }}">
                                        <i class="ri-user-shared-line"></i>
                                        {{ $teacher ? 'Change Teacher' : 'Assign Teacher' }}
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

@include('student.modals.assign-class-teacher-modal')

@endsection

@section('scripts')
<script>
    (function () {
        const modalEl = document.getElementById('assignClassTeacherModal');
        const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

        function setTeacherAvatar(initials) {
            document.getElementById('currentTeacherAvatar').textContent = initials || 'T';
        }

        $('body').on('click', '.assign-class-teacher-btn', function () {
            const url = $(this).data('url');

            $.get(url, function (data) {
                $('#assign_class_id').val(data.id);
                $('#unassign_class_id').val(data.id);
                $('#assign_class_name').text(data.name);
                $('#assign_class_name_inline').text(data.name);
                $('#assign_staff_id').val(data.class_teacher_id || '');

                if (data.teacher) {
                    $('#currentTeacherBox').show();
                    $('#currentTeacherName').text(data.teacher.name);
                    $('#currentTeacherMeta').text(data.teacher.position + ' • ' + data.teacher.employee_id);
                    $('#unassignClassTeacherBtn').show();
                    setTeacherAvatar((data.teacher.name || 'T').split(' ').map(function (part) {
                        return part.charAt(0);
                    }).join('').slice(0, 2).toUpperCase());
                } else {
                    $('#currentTeacherBox').hide();
                    $('#currentTeacherName').text('');
                    $('#currentTeacherMeta').text('');
                    $('#unassignClassTeacherBtn').hide();
                }

                if (modal) {
                    modal.show();
                }
            });
        });

        $('#unassignClassTeacherBtn').on('click', function () {
            confirmAppAction({
                title: 'Remove this class teacher?',
                text: 'The teacher will be unassigned from this class.',
                confirmButtonText: 'Yes, remove',
            }).then(function (ok) {
                if (ok) {
                    $('#unassignClassTeacherForm').trigger('submit');
                }
            });
        });
    })();
</script>
@endsection
