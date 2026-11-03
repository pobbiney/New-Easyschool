@php $pageName = "hr"; $subpageName = "hr-departments"; @endphp
@extends('layouts.app')
@section('css')
@include('hr.partials._styles')
@endsection
@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'title' => 'Departments',
        'subtitle' => 'Organise staff into school units.',
        'actions' => '<button type="button" class="btn btn-primary-600" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"><i class="ri-add-line"></i> Add department</button>',
    ])

    <div class="row g-3 mb-24">
        <div class="col-md-4"><div class="hr-stat-card"><div class="text-sm text-secondary-light">Departments</div><h4 class="mb-0">{{ $stats['total'] }}</h4></div></div>
        <div class="col-md-4"><div class="hr-stat-card"><div class="text-sm text-secondary-light">Active</div><h4 class="mb-0">{{ $stats['active'] }}</h4></div></div>
        <div class="col-md-4"><div class="hr-stat-card"><div class="text-sm text-secondary-light">Staff assigned</div><h4 class="mb-0">{{ $stats['staff'] }}</h4></div></div>
    </div>

    <div class="card hr-list-wrapper">
        <div class="table-responsive">
            <table class="table bordered-table mb-0">
                <thead><tr><th>#</th><th>Name</th><th>Code</th><th>Positions</th><th>Staff</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->code ?: '—' }}</td>
                            <td>{{ $department->positions_count }}</td>
                            <td>{{ $department->staff_count }}</td>
                            <td><span class="hr-pill {{ $department->status === 'Active' ? 'hr-pill-success' : 'hr-pill-slate' }}">{{ $department->status }}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary-600 edit-dept" data-id="{{ $department->id }}">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-20 text-secondary-light">No departments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('hr-departments-process') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">Add department</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="text-sm fw-semibold mb-8">Name</label>
                <input type="text" name="name" class="form-control mb-16" required>
                <label class="text-sm fw-semibold mb-8">Code</label>
                <input type="text" name="code" class="form-control mb-16">
                <label class="text-sm fw-semibold mb-8">Status</label>
                <select name="status" class="form-control form-select"><option>Active</option><option>Inactive</option></select>
            </div>
            <div class="modal-footer"><button class="btn btn-primary-600">Save</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('hr-departments-update') }}" class="modal-content">
            @csrf
            <input type="hidden" name="department_id" id="editDeptId">
            <div class="modal-header"><h6 class="modal-title">Edit department</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="text-sm fw-semibold mb-8">Name</label>
                <input type="text" name="name" id="editDeptName" class="form-control mb-16" required>
                <label class="text-sm fw-semibold mb-8">Code</label>
                <input type="text" name="code" id="editDeptCode" class="form-control mb-16">
                <label class="text-sm fw-semibold mb-8">Status</label>
                <select name="status" id="editDeptStatus" class="form-control form-select"><option>Active</option><option>Inactive</option></select>
            </div>
            <div class="modal-footer"><button class="btn btn-primary-600">Update</button></div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
    document.querySelectorAll('.edit-dept').forEach(function (btn) {
        btn.addEventListener('click', function () {
            fetch('{{ url('hr-departments') }}/' + this.dataset.id).then(r => r.json()).then(function (data) {
                document.getElementById('editDeptId').value = data.id;
                document.getElementById('editDeptName').value = data.name;
                document.getElementById('editDeptCode').value = data.code || '';
                document.getElementById('editDeptStatus').value = data.status;
                new bootstrap.Modal(document.getElementById('editDepartmentModal')).show();
            });
        });
    });
</script>
@endsection
