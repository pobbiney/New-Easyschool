@php $pageName = "hr"; $subpageName = "hr-payslips"; @endphp
@extends('layouts.app')
@section('css')
@include('hr.partials._styles')
@endsection
@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'title' => 'Payslips',
        'subtitle' => 'Print employee payslips from a payroll run.',
    ])

    <form method="GET" class="card hr-list-wrapper mb-24">
        <div class="card-body p-20 d-flex gap-3 align-items-end">
            <div>
                <label class="text-sm fw-semibold mb-8">Payroll run</label>
                <select name="run_id" class="form-control form-select" onchange="this.form.submit()">
                    @foreach($runs as $run)
                        <option value="{{ $run->id }}" {{ $selected && $selected->id === $run->id ? 'selected' : '' }}>{{ $run->periodLabel() }} ({{ ucfirst($run->status) }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="card hr-list-wrapper">
        <div class="table-responsive">
            <table class="table bordered-table mb-0">
                <thead><tr><th>Staff</th><th>Period</th><th>Gross</th><th>Net</th><th></th></tr></thead>
                <tbody>
                    @forelse($payslips as $slip)
                        <tr>
                            <td>{{ $slip->staff?->full_name }}</td>
                            <td>{{ $selected?->periodLabel() }}</td>
                            <td>{{ \App\Support\Money::ghs($slip->gross) }}</td>
                            <td>{{ \App\Support\Money::ghs($slip->net) }}</td>
                            <td><a class="btn btn-sm btn-primary-600" target="_blank" href="{{ route('hr-payslip-print', $slip->id) }}">Print</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-20 text-secondary-light">No payslips for this run.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
