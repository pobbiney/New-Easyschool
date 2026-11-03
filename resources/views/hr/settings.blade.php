@php $pageName = "hr"; $subpageName = "hr-settings"; @endphp
@extends('layouts.app')
@section('css')
@include('hr.partials._styles')
@endsection
@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'title' => 'Statutory Settings',
        'subtitle' => 'SSNIT and PAYE rates used by payroll. Confirm against current GRA/SSNIT notices.',
    ])

    <form method="POST" action="{{ route('hr-settings-process') }}" class="card hr-list-wrapper">
        @csrf
        <div class="card-body p-24">
            <div class="row gy-3 mb-24">
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">SSNIT employee %</label>
                    <input type="number" step="0.01" name="ssnit_employee_rate" class="form-control" value="{{ $settings->ssnit_employee_rate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">SSNIT employer %</label>
                    <input type="number" step="0.01" name="ssnit_employer_rate" class="form-control" value="{{ $settings->ssnit_employer_rate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">SSNIT ceiling (optional)</label>
                    <input type="number" step="0.01" name="ssnit_ceiling" class="form-control" value="{{ $settings->ssnit_ceiling }}">
                </div>
                <div class="col-md-3">
                    <label class="text-sm fw-semibold mb-8">Personal relief</label>
                    <input type="number" step="0.01" name="personal_relief" class="form-control" value="{{ $settings->personal_relief }}" required>
                </div>
            </div>

            <h6 class="fw-semibold mb-12">PAYE bands (monthly)</h6>
            <p class="text-sm text-secondary-light mb-16">Leave “Up to” blank on the last row for income above the previous band.</p>
            <div class="table-responsive">
                <table class="table bordered-table">
                    <thead><tr><th>Up to (GHS)</th><th>Rate %</th></tr></thead>
                    <tbody>
                        @foreach(($settings->paye_bands ?? []) as $index => $band)
                            <tr>
                                <td><input type="number" step="0.01" name="bands[{{ $index }}][up_to]" class="form-control" value="{{ $band['up_to'] }}"></td>
                                <td><input type="number" step="0.01" name="bands[{{ $index }}][rate]" class="form-control" value="{{ $band['rate'] }}" required></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary-600">Save settings</button>
        </div>
    </form>
</div>
@endsection
