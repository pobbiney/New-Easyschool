@php
    $pageName = "teacher-management";
    $subpageName = "grading-scheme";
    $gradePillClass = fn (?string $grade) => match (strtoupper(trim((string) $grade)[0] ?? '')) {
        'A' => 'ac-pill-grade-a', 'B' => 'ac-pill-grade-b', 'C' => 'ac-pill-grade-c',
        'D' => 'ac-pill-grade-d', 'F' => 'ac-pill-grade-f', default => 'ac-pill-slate',
    };
    $passCount = $schemes->filter(fn ($s) => strtoupper($s->letter_grade) !== 'F')->count();
    $topBand = $schemes->sortByDesc('max_percentage')->first();
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Grading Scheme</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal" data-bs-target="#addSchemeModal">
            <i class="ri-add-large-line"></i> Add Grade Row
        </button>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-bar-chart-grouped-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Grading Scheme</h5>
            <p class="text-sm text-secondary-light mb-0">Letter-grade thresholds applied when teachers save scores and generate report cards.</p>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Grade Bands</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-stack-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Highest Grade</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $topBand?->letter_grade ?? '—' }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-trophy-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Passing Levels</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $passCount }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-shield-check-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom py-16 px-24 d-flex justify-content-between flex-wrap gap-3">
            <h6 class="text-lg fw-semibold mb-0">All Grade Bands</h6>
            <form class="navbar-search dt-search m-0">
                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" placeholder="Search grades...">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
            </form>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($schemes->isNotEmpty())
            <table class="table bordered-table mb-0 data-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Letter Grade</th>
                        <th>Min %</th>
                        <th>Max %</th>
                        <th>Range</th>
                        <th>Remark</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schemes as $scheme)
                    <tr>
                        <td><span class="ac-pill {{ $gradePillClass($scheme->letter_grade) }}">{{ $scheme->letter_grade }}</span></td>
                        <td>{{ number_format($scheme->min_percentage, 2) }}</td>
                        <td>{{ number_format($scheme->max_percentage, 2) }}</td>
                        <td><span class="ac-pill ac-pill-slate">{{ number_format($scheme->min_percentage, 0) }}% – {{ number_format($scheme->max_percentage, 0) }}%</span></td>
                        <td class="text-secondary-light">{{ $scheme->remark ?: '—' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary-600"
                                data-bs-toggle="modal" data-bs-target="#editSchemeModal"
                                data-id="{{ $scheme->id }}"
                                data-min="{{ $scheme->min_percentage }}"
                                data-max="{{ $scheme->max_percentage }}"
                                data-grade="{{ $scheme->letter_grade }}"
                                data-remark="{{ $scheme->remark }}">
                                <i class="ri-edit-2-line"></i> Edit
                            </button>
                            <form action="{{ route('delete-grading-scheme-process') }}" method="POST" class="d-inline"
                                data-confirm-title="Remove this grading row?"
                                data-confirm="This cannot be undone.">
                                @csrf
                                <input type="hidden" name="grading_scheme_id" value="{{ $scheme->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-56 px-24">
                <p class="text-secondary-light mb-12">No grading rows yet.</p>
                <button type="button" class="btn btn-primary-600 btn-sm" data-bs-toggle="modal" data-bs-target="#addSchemeModal">Add your first grade band</button>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="addSchemeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('add-grading-scheme-process') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Grading Row</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Letter Grade</label><input type="text" name="letter_grade" class="form-control" required maxlength="5"></div>
                <div class="row g-3">
                    <div class="col-6"><label class="form-label">Min %</label><input type="number" step="0.01" name="min_percentage" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Max %</label><input type="number" step="0.01" name="max_percentage" class="form-control" required></div>
                </div>
                <div class="mt-3"><label class="form-label">Remark</label><input type="text" name="remark" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary-600">Save</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editSchemeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('update-grading-scheme-process') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="grading_scheme_id" id="edit_scheme_id">
            <div class="modal-header"><h5 class="modal-title">Edit Grading Row</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Letter Grade</label><input type="text" name="letter_grade" id="edit_letter_grade" class="form-control" required maxlength="5"></div>
                <div class="row g-3">
                    <div class="col-6"><label class="form-label">Min %</label><input type="number" step="0.01" name="min_percentage" id="edit_min_percentage" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Max %</label><input type="number" step="0.01" name="max_percentage" id="edit_max_percentage" class="form-control" required></div>
                </div>
                <div class="mt-3"><label class="form-label">Remark</label><input type="text" name="remark" id="edit_remark" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary-600">Update</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('editSchemeModal')?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('edit_scheme_id').value = btn.dataset.id;
    document.getElementById('edit_min_percentage').value = btn.dataset.min;
    document.getElementById('edit_max_percentage').value = btn.dataset.max;
    document.getElementById('edit_letter_grade').value = btn.dataset.grade;
    document.getElementById('edit_remark').value = btn.dataset.remark || '';
});
</script>
@endsection
