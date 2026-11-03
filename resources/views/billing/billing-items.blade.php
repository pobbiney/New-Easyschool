@php $pageName = "bill-management"; $subpageName = "billing-items"; @endphp
@extends('layouts.app')
@section('css')
<style>
    .bill-hero,.bill-stat-card,.bill-list-wrapper{border:1px solid var(--neutral-200,#e5e7eb);border-radius:16px;background:#fff}
    .bill-hero{padding:24px 28px;background:linear-gradient(135deg,rgba(37,161,148,.12),rgba(99,102,241,.08));margin-bottom:24px}
    .bill-stat-card{padding:20px 22px;height:100%}
    .bill-list-wrapper{overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04)}
    .item-avatar{width:42px;height:42px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:rgba(37,161,148,.1);color:var(--primary-600,#25A194);font-weight:700}
    .status-badge-active,.status-badge-inactive{padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600}
    .status-badge-active{background:rgba(34,197,94,.1);color:#15803d}
    .status-badge-inactive{background:rgba(239,68,68,.1);color:#b91c1c}
    .type-badge-compulsory,.type-badge-optional{padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600}
    .type-badge-compulsory{background:rgba(234,88,12,.1);color:#c2410c}
    .type-badge-optional{background:rgba(99,102,241,.1);color:#4338ca}
</style>
@endsection
@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div><a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a><span class="text-secondary-light"> / Billing Items</span></div>
        </div>
        <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6"><i class="ri-add-large-line"></i> Add Billing Item</button>
    </div>
    <div class="bill-hero d-flex align-items-start gap-16 mb-24">
        <span class="item-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-price-tag-3-line"></i></span>
        <div><h5 class="fw-semibold mb-6">Billing Items</h5><p class="text-sm text-secondary-light mb-0">Define reusable fee types such as tuition, PTA, transport, and levies.</p></div>
    </div>
    <div class="row gy-4 mb-24">
        <div class="col-sm-4"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Total Items</p><h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4></div></div>
        <div class="col-sm-4"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Compulsory</p><h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['compulsory'] }}</h4></div></div>
        <div class="col-sm-4"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Optional</p><h4 class="fw-semibold mb-0 text-primary-600">{{ $stats['optional'] }}</h4></div></div>
    </div>
    <div class="card bill-list-wrapper">
        <div class="card-header border-bottom py-16 px-24 d-flex justify-content-between flex-wrap gap-3">
            <h6 class="text-lg fw-semibold mb-0">All Billing Items</h6>
            <form class="navbar-search dt-search m-0"><input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" placeholder="Search items..."><iconify-icon icon="ion:search-outline" class="icon"></iconify-icon></form>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($items->isNotEmpty())
            <table class="table bordered-table mb-0 data-table" id="dataTable">
                <thead><tr><th>Item</th><th>Description</th><th>Payment Type</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td><div class="d-flex align-items-center gap-12"><span class="item-avatar">{{ strtoupper(substr($item->name,0,2)) }}</span><span class="fw-semibold">{{ $item->name }}</span></div></td>
                        <td class="text-secondary-light">{{ $item->description ?: '—' }}</td>
                        <td><span class="{{ $item->is_compulsory ? 'type-badge-compulsory' : 'type-badge-optional' }}">{{ $item->is_compulsory ? 'Compulsory' : 'Optional' }}</span></td>
                        <td><span class="{{ $item->status == 'Active' ? 'status-badge-active' : 'status-badge-inactive' }}">{{ $item->status }}</span></td>
                        <td><button type="button" data-url="{{ route('get-billing-item-id', $item->id) }}" class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-billing-item-edit"><i class="ri-edit-2-line"></i> Edit</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-56 px-24"><p class="text-secondary-light mb-12">No billing items yet.</p><button type="button" class="my-sidebar-btn btn btn-primary-600 btn-sm">Add your first item</button></div>
            @endif
        </div>
    </div>
</div>
@include('billing.modals.add-billing-item-modal')
@include('billing.modals.edit-billing-item-modal')
@endsection
@section('scripts')
<script>
    $('.my-sidebar-btn').on('click', function(){ $('.my-sidebar,.overlay').addClass('active'); });
    $('.close-my-sidebar,.overlay').on('click', function(){
        $('.my-sidebar').removeClass('active');
        $('.my-sidebar form').trigger('reset');
        if (!$('.edit-sidebar').hasClass('active')) {
            $('.overlay').removeClass('active');
        }
    });
    $('.edit-sidebar-btn').on('click', function(){ $('.edit-sidebar,.overlay').addClass('active'); });
    $('.close-edit-sidebar').on('click', function(){ $('.edit-sidebar,.overlay').removeClass('active'); });
    $('body').on('click', '.show-billing-item-edit', function(){
        $.get($(this).data('url'), function(data){
            $('#edit_billing_item_id').val(data.id);
            $('#edit_billing_item_name').val(data.name);
            $('#edit_billing_item_description').val(data.description || '');
            $('#edit_billing_item_is_compulsory').val(data.is_compulsory ? '1' : '0');
            $('#edit_billing_item_status').val(data.status);
        });
    });
</script>
@endsection
