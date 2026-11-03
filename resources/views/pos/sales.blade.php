@php $pageName = 'pos'; $subpageName = 'pos-sales'; @endphp
@extends('layouts.app')
@section('css')
<style>
    .pos-card{border:1px solid var(--neutral-200,#e5e7eb);border-radius:16px;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.04)}
</style>
@endsection
@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">POS</h1>
            <div><a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a><span class="text-secondary-light"> / Sales History</span></div>
        </div>
        <a href="{{ route('pos-sale') }}" class="btn btn-primary-600"><i class="ri-add-line"></i> New Sale</a>
    </div>

    <div class="card pos-card mb-24">
        <div class="card-body">
            <form method="GET" action="{{ route('pos-sales') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="text-sm fw-semibold d-block mb-8">Search</label>
                    <input type="text" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Customer or student">
                </div>
                <div class="col-md-2">
                    <label class="text-sm fw-semibold d-block mb-8">Receipt No</label>
                    <input type="text" class="form-control" name="receipt_no" value="{{ $filters['receipt_no'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="text-sm fw-semibold d-block mb-8">Payment</label>
                    <select class="form-control form-select" name="payment_method">
                        <option value="">All</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method }}" @selected(($filters['payment_method'] ?? '') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="text-sm fw-semibold d-block mb-8">From</label>
                    <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="text-sm fw-semibold d-block mb-8">To</label>
                    <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-outline-primary-600 w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card pos-card">
        <div class="card-header border-bottom py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Sales</h6>
        </div>
        <div class="card-body p-0">
            @if($sales->isEmpty())
                <div class="text-center py-56 px-24 text-secondary-light">No sales found.</div>
            @else
            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead><tr><th>Receipt</th><th>Date</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Cashier</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td class="fw-semibold">{{ $sale->receipt_no }}</td>
                            <td>{{ $sale->sold_at->format('d M Y H:i') }}</td>
                            <td>{{ $sale->buyerLabel() }}</td>
                            <td>{{ $sale->items_count }}</td>
                            <td>{{ number_format($sale->total, 2) }}</td>
                            <td>{{ $sale->payment_method }}</td>
                            <td>{{ $sale->cashier?->name ?? '—' }}</td>
                            <td><a href="{{ route('pos-receipt', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-primary-600"><i class="ri-printer-line"></i> Receipt</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-24">{{ $sales->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
