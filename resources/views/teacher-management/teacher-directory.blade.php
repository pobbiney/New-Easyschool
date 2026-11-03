@php $pageName = "teacher-management"; $subpageName = "teacher-directory"; @endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
@include('partials._academic-ui-helpers')

<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Teacher Directory</span>
            </div>
        </div>
        <a href="{{ route('class-teacher-assignment') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-user-follow-line"></i> Class Teachers
        </a>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-team-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Teacher Directory</h5>
            <p class="text-sm text-secondary-light mb-0">Active staff with teacher login accounts, homeroom assignments, and subject teaching slots.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Teachers</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-team-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">With Login</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['with_login'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-shield-user-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Homeroom Assigned</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['homeroom'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-home-smile-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Subject Assigned</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $stats['subject'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper h-100">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-4">All Teachers</h6>
            <p class="text-sm text-secondary-light mb-0">Teachers must have system access (user category: Teacher) to use the portal.</p>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search teachers...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            @if($teachers->isNotEmpty())
            <div class="ac-list-scroll p-0">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Teacher</th>
                            <th>Employee ID</th>
                            <th>Login</th>
                            <th>Homeroom</th>
                            <th>Subjects</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                        @php
                            $initials = strtoupper(substr($teacher->firstname ?? '', 0, 1) . substr($teacher->surname ?? '', 0, 1));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="ac-name-cell">
                                    <span class="ac-avatar">
                                        @if($teacher->picture)
                                            <img src="{{ asset($teacher->picture) }}" alt="{{ $teacher->full_name }}">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </span>
                                    <div>
                                        <span class="d-block fw-semibold text-primary-600">{{ $teacher->full_name }}</span>
                                        <span class="ac-pill ac-pill-slate">{{ $teacher->position ?? 'Teacher' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $teacher->employee_id ?? '—' }}</td>
                            <td>
                                @if($teacher->user)
                                    <span class="ac-pill ac-pill-active"><i class="ri-checkbox-circle-fill"></i> Active login</span>
                                @else
                                    <span class="ac-pill ac-pill-amber"><i class="ri-error-warning-line"></i> No login</span>
                                @endif
                            </td>
                            <td>
                                @if($teacher->assignedClass)
                                    <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> {{ $teacher->assignedClass->name }}</span>
                                @else
                                    <span class="ac-pill ac-pill-slate">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="ac-pill ac-pill-indigo">{{ $teacher->courseTeachingAssignments->count() }} slot{{ $teacher->courseTeachingAssignments->count() === 1 ? '' : 's' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('view-staff-details', Crypt::encrypt($teacher->id)) }}" class="btn btn-sm btn-outline-primary-600"><i class="ri-eye-line"></i> View</a>
                                <a href="{{ route('edit-staff', Crypt::encrypt($teacher->id)) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-edit-2-line"></i> Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-56 px-24 text-secondary-light">No teachers found. Create staff with teacher category and system access.</div>
            @endif
        </div>
    </div>
</div>
@endsection
