@php $pageName = "hr"; $subpageName = "hr-salary-structures"; @endphp
@extends('layouts.app')
@section('css')
@include('hr.partials._styles')
@endsection
@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'title' => 'Salary Structures',
        'subtitle' => 'Pay grades, allowances, and deductions used in payroll.',
    ])

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card hr-list-wrapper">
                <div class="card-header py-16 px-24"><h6 class="mb-0">Pay grades</h6></div>
                <div class="card-body p-20">
                    <form method="POST" action="{{ route('hr-pay-grades-process') }}" class="mb-16">
                        @csrf
                        <input name="name" class="form-control mb-8" placeholder="Grade name" required>
                        <input type="number" step="0.01" name="basic_salary" class="form-control mb-8" placeholder="Basic salary" required>
                        <select name="status" class="form-control form-select mb-8"><option>Active</option><option>Inactive</option></select>
                        <button class="btn btn-primary-600 btn-sm">Add grade</button>
                    </form>
                    @foreach($grades as $grade)
                        <div class="d-flex justify-content-between border-bottom py-8">
                            <div>
                                <div class="fw-semibold">{{ $grade->name }}</div>
                                <small>{{ \App\Support\Money::ghs($grade->basic_salary) }} · {{ $grade->staff_count }} staff</small>
                            </div>
                            <span class="hr-pill hr-pill-info">{{ $grade->status }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card hr-list-wrapper">
                <div class="card-header py-16 px-24"><h6 class="mb-0">Earning types</h6></div>
                <div class="card-body p-20">
                    <form method="POST" action="{{ route('hr-earning-types-process') }}" class="mb-16">
                        @csrf
                        <input name="name" class="form-control mb-8" placeholder="Name" required>
                        <input name="code" class="form-control mb-8" placeholder="Code">
                        <select name="method" class="form-control form-select mb-8">
                            <option value="fixed">Fixed amount</option>
                            <option value="percent_basic">% of basic</option>
                        </select>
                        <input type="number" step="0.01" name="default_amount" class="form-control mb-8" placeholder="Default" required>
                        <label class="text-sm d-block mb-8"><input type="checkbox" name="is_taxable" value="1" checked> Taxable</label>
                        <select name="status" class="form-control form-select mb-8"><option>Active</option><option>Inactive</option></select>
                        <button class="btn btn-primary-600 btn-sm">Add earning</button>
                    </form>
                    @foreach($earnings as $type)
                        <div class="border-bottom py-8">
                            <div class="fw-semibold">{{ $type->name }}</div>
                            <small>{{ $type->method === 'percent_basic' ? $type->default_amount.'% of basic' : \App\Support\Money::ghs($type->default_amount) }} · {{ $type->is_taxable ? 'Taxable' : 'Non-taxable' }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card hr-list-wrapper">
                <div class="card-header py-16 px-24"><h6 class="mb-0">Deduction types</h6></div>
                <div class="card-body p-20">
                    <form method="POST" action="{{ route('hr-deduction-types-process') }}" class="mb-16">
                        @csrf
                        <input name="name" class="form-control mb-8" placeholder="Name" required>
                        <input name="code" class="form-control mb-8" placeholder="Code">
                        <select name="method" class="form-control form-select mb-8">
                            <option value="fixed">Fixed amount</option>
                            <option value="percent_basic">% of basic</option>
                        </select>
                        <input type="number" step="0.01" name="default_amount" class="form-control mb-8" placeholder="Default" required>
                        <label class="text-sm d-block mb-8"><input type="checkbox" name="is_statutory" value="1"> Statutory</label>
                        <select name="status" class="form-control form-select mb-8"><option>Active</option><option>Inactive</option></select>
                        <button class="btn btn-primary-600 btn-sm">Add deduction</button>
                    </form>
                    @foreach($deductions as $type)
                        <div class="border-bottom py-8">
                            <div class="fw-semibold">{{ $type->name }}</div>
                            <small>{{ $type->is_statutory ? 'Statutory' : 'Other' }} · {{ $type->code }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
