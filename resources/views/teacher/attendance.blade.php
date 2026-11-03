@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-attendance";
    $attendancePillClass = fn (?string $status) => 'ac-pill-' . ($status ?: 'slate');
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .attendance-select-present { border-color: rgba(34,197,94,.35) !important; }
    .attendance-select-absent  { border-color: rgba(239,68,68,.35) !important; }
    .attendance-select-late    { border-color: rgba(245,158,11,.35) !important; }
    .attendance-select-excused { border-color: rgba(99,102,241,.35) !important; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">ATTENDANCE</h1>
            <div>
                <a href="{{ route('teacher-attendance') }}" class="text-secondary-light hover-text-primary hover-underline">Attendance</a>
                <span class="text-secondary-light"> / {{ $schoolClass->name }}</span>
            </div>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(245,158,11,.12);color:#b45309;"><i class="ri-calendar-check-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">{{ $schoolClass->name }} — Daily Mark Sheet</h5>
            <span class="ac-pill ac-pill-amber"><i class="ri-calendar-line"></i> {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</span>
        </div>
    </div>

    <form method="GET" class="card ac-list-wrapper mb-24">
        <div class="ac-filter-bar d-flex flex-wrap gap-3 align-items-end">
            <div>
                <label class="form-label text-sm fw-semibold">Select Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-outline-primary-600">Load Date</button>
        </div>
    </form>

    <form action="{{ route('teacher-class-attendance-process', $schoolClass) }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <div class="card ac-list-wrapper">
            <div class="card-header border-bottom bg-base py-16 px-24 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">Mark Attendance</h6>
                    <div class="ac-summary-pills">
                        <span class="ac-summary-pill ac-pill-present">P Present</span>
                        <span class="ac-summary-pill ac-pill-absent">A Absent</span>
                        <span class="ac-summary-pill ac-pill-late">L Late</span>
                        <span class="ac-summary-pill ac-pill-excused">E Excused</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary-600"><i class="ri-save-line"></i> Save Attendance</button>
            </div>
            <div class="card-body p-0 dataTable-wrapper">
                <div class="ac-list-scroll">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr><th>#</th><th>Student</th><th>Status</th><th>Notes</th><th>Month Summary</th></tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            @php
                                $record = $records->get($student->id);
                                $summary = $monthSummary->get($student->id, collect());
                                $currentStatus = $record?->status ?? 'present';
                                $initials = strtoupper(substr($student->firstname ?? '', 0, 1) . substr($student->surname ?? '', 0, 1));
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <input type="hidden" name="attendance[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                                    <div class="ac-name-cell">
                                        <span class="ac-avatar">@if($student->picture)<img src="{{ asset($student->picture) }}" alt="">@else{{ $initials }}@endif</span>
                                        <span class="fw-semibold">{{ $student->full_name }}</span>
                                    </div>
                                </td>
                                <td style="min-width:140px;">
                                    <select name="attendance[{{ $loop->index }}][status]" class="form-select attendance-select-{{ $currentStatus }}">
                                        @foreach(\App\Models\ClassAttendance::STATUSES as $status)
                                            <option value="{{ $status }}" @selected($currentStatus === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width:160px;"><input type="text" name="attendance[{{ $loop->index }}][notes]" class="form-control" value="{{ $record?->notes }}"></td>
                                <td>
                                    <div class="ac-summary-pills">
                                        <span class="ac-summary-pill ac-pill-present">P {{ $summary->where('status', 'present')->count() }}</span>
                                        <span class="ac-summary-pill ac-pill-absent">A {{ $summary->where('status', 'absent')->count() }}</span>
                                        <span class="ac-summary-pill ac-pill-late">L {{ $summary->where('status', 'late')->count() }}</span>
                                        <span class="ac-summary-pill ac-pill-excused">E {{ $summary->where('status', 'excused')->count() }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('select[name*="[status]"]').forEach(function (select) {
    select.addEventListener('change', function () {
        this.className = 'form-select attendance-select-' + this.value;
    });
});
</script>
@endsection
