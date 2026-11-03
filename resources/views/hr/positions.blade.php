@php
    $pageName = "hr";
    $subpageName = "hr-positions";
    $inactiveCount = $positions->where('status', '!=', 'Active')->count();
    $unusedCount = $positions->where('staff_count', 0)->count();
    $unassignedCount = $positions->whereNull('department_id')->count();
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@include('hr.partials._styles')
<style>
    .pos-hero {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        padding: 24px 28px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.12), rgba(99, 102, 241, 0.08));
        margin-bottom: 24px;
    }
    .pos-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: #f8fafc;
    }
    .pos-search { min-width: 240px; max-width: 320px; }
    .pos-empty {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }
    .pos-empty i {
        font-size: 36px;
        color: #25A194;
        display: block;
        margin-bottom: 10px;
    }
    .pos-name {
        font-weight: 700;
    }
    .pos-meta {
        display: block;
        font-size: 12px;
        color: #64748b;
        font-weight: 400;
    }
    .pos-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.1);
        color: #25A194;
        font-size: 18px;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HR', 'url' => route('hr-dashboard')],
            ['label' => 'Positions', 'url' => null, 'active' => true],
        ],
        'title' => 'Positions',
        'subtitle' => 'Job titles used on employee records and teaching screens.',
        'actions' => '<a href="'.route('hr-departments').'" class="btn btn-outline-primary-600"><i class="ri-building-line"></i> Departments</a>
            <button type="button" class="btn btn-primary-600" data-bs-toggle="modal" data-bs-target="#addPositionModal"><i class="ri-add-line"></i> Add position</button>',
    ])

    <div class="pos-hero">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(37,161,148,.12);color:#25A194;">
                <i class="ri-briefcase-4-line"></i>
            </span>
            <div>
                <h5 class="fw-semibold mb-6">Job titles</h5>
                <p class="text-sm text-secondary-light mb-0">
                    Positions appear on employee forms and the teacher directory.
                    Assign a department so staff sit in the right unit.
                </p>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Positions</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-briefcase-4-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
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
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Staff assigned</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['staff'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-team-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Unused</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $unusedCount }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-user-unfollow-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="pos-toolbar">
            <div>
                <h6 class="mb-0 fw-semibold">All positions</h6>
                <p class="text-sm text-secondary-light mb-0 mt-4">
                    {{ $inactiveCount }} inactive
                    @if($unassignedCount)
                        · {{ $unassignedCount }} without a department
                    @endif
                </p>
            </div>
            <div class="pos-search">
                <input type="search" id="positionSearch" class="form-control" placeholder="Search position or department…">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table bordered-table mb-0" id="positionTable">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Staff</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $position)
                        <tr data-search="{{ strtolower(trim($position->name.' '.($position->department?->name ?? '').' '.$position->status)) }}">
                            <td>
                                <div class="ac-name-cell">
                                    <span class="pos-icon"><i class="ri-user-star-line"></i></span>
                                    <div>
                                        <span class="pos-name">{{ $position->name }}</span>
                                        <span class="pos-meta">{{ $position->staff_count }} staff member{{ $position->staff_count === 1 ? '' : 's' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($position->department)
                                    <span class="ac-pill ac-pill-indigo">{{ $position->department->name }}</span>
                                @else
                                    <span class="ac-pill ac-pill-slate">No department</span>
                                @endif
                            </td>
                            <td>
                                <span class="ac-pill {{ $position->staff_count ? 'ac-pill-teal' : 'ac-pill-slate' }}">
                                    {{ $position->staff_count }}
                                </span>
                            </td>
                            <td>
                                <span class="ac-pill {{ $position->status === 'Active' ? 'ac-pill-emerald' : 'ac-pill-slate' }}">
                                    {{ $position->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end gap-8">
                                    <button type="button" class="btn btn-sm btn-outline-primary-600 edit-pos" data-id="{{ $position->id }}">
                                        <i class="ri-edit-line"></i> Edit
                                    </button>
                                    @if($position->isInUse())
                                        <button type="button" class="btn btn-sm btn-outline-neutral-400 text-secondary-light"
                                            title="Cannot delete — assigned to {{ $position->staff_count }} staff member{{ $position->staff_count === 1 ? '' : 's' }}"
                                            disabled style="opacity:0.65;cursor:not-allowed;">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </button>
                                    @else
                                        <form action="{{ route('hr-positions-delete') }}" method="POST" class="d-inline"
                                            data-confirm-title="{{ 'Delete '.$position->name.'?' }}"
                                            data-confirm="This cannot be undone.">
                                            @csrf
                                            <input type="hidden" name="position_id" value="{{ $position->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger-600">
                                                <i class="ri-delete-bin-line"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="pos-empty">
                                    <i class="ri-briefcase-4-line"></i>
                                    No positions yet. Add a job title to start assigning staff.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('hr-positions-process') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">Add position</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="text-sm fw-semibold mb-8">Name</label>
                <input type="text" name="name" class="form-control mb-16" placeholder="e.g. Class teacher" required>
                <label class="text-sm fw-semibold mb-8">Department</label>
                <select name="department_id" class="form-control form-select mb-16">
                    <option value="">None</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <label class="text-sm fw-semibold mb-8">Status</label>
                <select name="status" class="form-control form-select">
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary-600">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('hr-positions-update') }}" class="modal-content">
            @csrf
            <input type="hidden" name="position_id" id="editPosId">
            <div class="modal-header">
                <h6 class="modal-title">Edit position</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="text-sm fw-semibold mb-8">Name</label>
                <input type="text" name="name" id="editPosName" class="form-control mb-16" required>
                <label class="text-sm fw-semibold mb-8">Department</label>
                <select name="department_id" id="editPosDept" class="form-control form-select mb-16">
                    <option value="">None</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <label class="text-sm fw-semibold mb-8">Status</label>
                <select name="status" id="editPosStatus" class="form-control form-select">
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-neutral-400" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary-600">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.edit-pos').forEach(function (btn) {
        btn.addEventListener('click', function () {
            fetch('{{ url('hr-positions') }}/' + this.dataset.id).then(r => r.json()).then(function (data) {
                document.getElementById('editPosId').value = data.id;
                document.getElementById('editPosName').value = data.name;
                document.getElementById('editPosDept').value = data.department_id || '';
                document.getElementById('editPosStatus').value = data.status;
                new bootstrap.Modal(document.getElementById('editPositionModal')).show();
            });
        });
    });

    (function () {
        const input = document.getElementById('positionSearch');
        const rows = document.querySelectorAll('#positionTable tbody tr[data-search]');
        if (!input) return;
        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.style.display = !q || row.getAttribute('data-search').includes(q) ? '' : 'none';
            });
        });
    })();
</script>
@endsection
