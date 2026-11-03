@php $pageName = 'pos'; $subpageName = 'pos-stock'; @endphp
@extends('layouts.app')

@section('css')
<style>
    .pos-stock-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        border: 1px solid rgba(79, 70, 229, 0.18);
        background:
            radial-gradient(circle at top right, rgba(37, 161, 148, 0.14), transparent 40%),
            linear-gradient(135deg, rgba(79, 70, 229, 0.12) 0%, rgba(255, 255, 255, 0.96) 52%, rgba(37, 161, 148, 0.08) 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .pos-stock-hero::after {
        content: "";
        position: absolute;
        right: -24px;
        bottom: -36px;
        width: 170px;
        height: 170px;
        border-radius: 50%;
        background: rgba(79, 70, 229, 0.08);
        pointer-events: none;
    }

    .pos-stock-hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1, #4338ca);
        color: #fff;
        font-size: 26px;
        flex-shrink: 0;
        box-shadow: 0 12px 24px rgba(99, 102, 241, 0.28);
    }

    .pos-stock-hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
        color: #0f172a;
    }

    .pos-stock-hero-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .pos-stock-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(99, 102, 241, 0.16);
        color: #334155;
    }

    .pos-stock-stat {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 20px 22px;
        background: #fff;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .pos-stock-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        border-color: rgba(99, 102, 241, 0.22);
    }

    .pos-stock-stat .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    .pos-stock-panel {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        height: 100%;
    }

    .pos-stock-panel-head {
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }

    .pos-stock-panel-body {
        padding: 24px;
    }

    .pos-stock-form-label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .pos-stock-type-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 18px;
    }

    .pos-stock-type-option {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #fff;
    }

    .pos-stock-type-option input {
        display: none;
    }

    .pos-stock-type-option i {
        display: block;
        font-size: 22px;
        margin-bottom: 6px;
        color: #94a3b8;
    }

    .pos-stock-type-option span {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
    }

    .pos-stock-type-option small {
        display: block;
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .pos-stock-type-option:hover {
        border-color: rgba(99, 102, 241, 0.35);
        transform: translateY(-1px);
    }

    .pos-stock-type-option.active {
        border-color: #6366f1;
        background: linear-gradient(180deg, #eef2ff 0%, #fff 100%);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.12);
    }

    .pos-stock-type-option.active i { color: #6366f1; }
    .pos-stock-type-option.active span { color: #4338ca; }

    .pos-stock-type-option.type-restock.active { border-color: #25A194; background: linear-gradient(180deg, #ecfdf5 0%, #fff 100%); }
    .pos-stock-type-option.type-restock.active i, .pos-stock-type-option.type-restock.active span { color: #0f766e; }

    .pos-stock-type-option.type-return.active { border-color: #3b82f6; background: linear-gradient(180deg, #eff6ff 0%, #fff 100%); }
    .pos-stock-type-option.type-return.active i, .pos-stock-type-option.type-return.active span { color: #1d4ed8; }

    .pos-stock-type-option.type-adjustment.active { border-color: #f59e0b; background: linear-gradient(180deg, #fffbeb 0%, #fff 100%); }
    .pos-stock-type-option.type-adjustment.active i, .pos-stock-type-option.type-adjustment.active span { color: #c2410c; }

    .pos-stock-submit {
        width: 100%;
        height: 48px;
        border-radius: 14px;
        font-weight: 800;
        border: none;
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 55%, #25A194 100%);
        box-shadow: 0 12px 24px rgba(99, 102, 241, 0.22);
    }

    .pos-stock-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(99, 102, 241, 0.28);
    }

    .pos-stock-alert-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #fee2e2;
        background: linear-gradient(180deg, #fff5f5 0%, #fff 100%);
        margin-bottom: 10px;
    }

    .pos-stock-alert-item:last-child { margin-bottom: 0; }

    .pos-stock-alert-item.is-low {
        border-color: #fed7aa;
        background: linear-gradient(180deg, #fff7ed 0%, #fff 100%);
    }

    .pos-stock-alert-thumb {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .pos-stock-alert-thumb.out {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
    }

    .pos-stock-alert-thumb.low {
        background: rgba(245, 158, 11, 0.14);
        color: #ea580c;
    }

    .pos-stock-alert-name {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 2px;
    }

    .pos-stock-alert-meta {
        font-size: 12px;
        color: #64748b;
    }

    .pos-stock-badge {
        margin-left: auto;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .pos-stock-badge.out { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
    .pos-stock-badge.low { background: rgba(245, 158, 11, 0.14); color: #c2410c; }

    .pos-stock-empty {
        padding: 48px 24px;
        text-align: center;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border-radius: 16px;
        border: 1px dashed #dbeafe;
    }

    .pos-stock-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 14px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #6366f1;
        background: rgba(99, 102, 241, 0.1);
    }

    .pos-stock-list {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .pos-stock-list .card-header {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .pos-stock-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .pos-stock-list table {
        min-width: 920px;
    }

    .pos-stock-list thead th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        font-weight: 800;
        white-space: nowrap;
    }

    .pos-stock-list tbody td {
        vertical-align: middle;
        font-size: 14px;
    }

    .pos-movement-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .pos-movement-type.sale { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
    .pos-movement-type.restock { background: rgba(37, 161, 148, 0.12); color: #0f766e; }
    .pos-movement-type.return { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
    .pos-movement-type.adjustment { background: rgba(245, 158, 11, 0.14); color: #c2410c; }

    .pos-movement-change {
        font-weight: 800;
        font-size: 14px;
    }

    .pos-movement-change.in { color: #15803d; }
    .pos-movement-change.out { color: #dc2626; }

    .pos-stock-qty-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .pos-quick-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .pos-quick-link:hover {
        border-color: #6366f1;
        color: #4338ca;
        background: rgba(99, 102, 241, 0.04);
        transform: translateY(-1px);
    }

    @media (max-width: 767px) {
        .pos-stock-type-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">

    @include('partials._page-header', [
        'section' => 'POS',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'POS', 'url' => route('pos-sale')],
            ['label' => 'Stock', 'active' => true],
        ],
        'title' => 'Stock Management',
        'subtitle' => 'Restock items, record returns, adjust inventory, and review the full audit trail.',
        'actions' => '
            <div class="d-flex flex-wrap gap-2">
                <a href="'.route('pos-products').'" class="pos-quick-link"><i class="ri-shopping-bag-3-line"></i> Products</a>
                <a href="'.route('pos-sale').'" class="pos-quick-link"><i class="ri-store-2-line"></i> New Sale</a>
                <a href="'.route('pos-sales').'" class="pos-quick-link"><i class="ri-history-line"></i> Sales</a>
            </div>
        ',
    ])

    <div class="pos-stock-hero d-flex align-items-start gap-16">
        <span class="pos-stock-hero-icon"><i class="ri-stack-line"></i></span>
        <div class="flex-grow-1">
            <div class="pos-stock-hero-title">Inventory Control Center</div>
            <p class="text-sm text-secondary-light mb-0">Keep shop shelves stocked, catch low inventory early, and track every quantity change with a clear audit log.</p>
            <div class="pos-stock-hero-tags">
                <span class="pos-stock-hero-tag"><i class="ri-box-3-line"></i> {{ number_format($stats['active_products']) }} active products</span>
                <span class="pos-stock-hero-tag"><i class="ri-arrow-up-down-line"></i> {{ number_format($stats['movements_today']) }} movements today</span>
                @if($stats['low_stock'] > 0)
                    <span class="pos-stock-hero-tag" style="border-color:rgba(245,158,11,.25);color:#c2410c;"><i class="ri-alert-line"></i> {{ $stats['low_stock'] }} need attention</span>
                @else
                    <span class="pos-stock-hero-tag"><i class="ri-checkbox-circle-line"></i> Stock levels healthy</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="pos-stock-stat">
                <div class="d-flex align-items-center justify-content-between mb-14">
                    <span class="stat-icon" style="background:rgba(99,102,241,.12);color:#4338ca;"><i class="ri-shopping-bag-3-line"></i></span>
                </div>
                <div class="text-secondary-light text-sm mb-4">Active Products</div>
                <div class="h4 fw-bold mb-0">{{ number_format($stats['active_products']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-stock-stat">
                <div class="d-flex align-items-center justify-content-between mb-14">
                    <span class="stat-icon" style="background:rgba(37,161,148,.12);color:#0f766e;"><i class="ri-archive-line"></i></span>
                </div>
                <div class="text-secondary-light text-sm mb-4">Total Units In Stock</div>
                <div class="h4 fw-bold mb-0">{{ number_format($stats['total_units']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-stock-stat">
                <div class="d-flex align-items-center justify-content-between mb-14">
                    <span class="stat-icon" style="background:rgba(245,158,11,.14);color:#c2410c;"><i class="ri-alert-line"></i></span>
                </div>
                <div class="text-secondary-light text-sm mb-4">Low Stock Items</div>
                <div class="h4 fw-bold mb-0">{{ number_format($stats['low_stock']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-stock-stat">
                <div class="d-flex align-items-center justify-content-between mb-14">
                    <span class="stat-icon" style="background:rgba(239,68,68,.1);color:#b91c1c;"><i class="ri-close-circle-line"></i></span>
                </div>
                <div class="text-secondary-light text-sm mb-4">Out of Stock</div>
                <div class="h4 fw-bold mb-0">{{ number_format($stats['out_of_stock']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-24">
        <div class="col-lg-5">
            <div class="pos-stock-panel">
                <div class="pos-stock-panel-head">
                    <h6 class="text-lg fw-bold mb-4">Adjust Stock</h6>
                    <p class="text-sm text-secondary-light mb-0">Add or remove units and leave a note for the audit trail.</p>
                </div>
                <div class="pos-stock-panel-body">
                    <form method="POST" action="{{ route('pos-stock-process') }}" id="posStockForm">
                        @csrf
                        <div class="mb-18">
                            <label class="pos-stock-form-label d-block">Product</label>
                            <select class="form-control form-select" name="pos_product_id" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('pos_product_id') == $product->id)>
                                        {{ $product->name }} — {{ $product->stock_qty }} in stock
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="pos-stock-form-label d-block">Movement Type</label>
                        <div class="pos-stock-type-grid mb-18">
                            <label class="pos-stock-type-option type-restock active">
                                <input type="radio" name="movement_type" value="restock" @checked(old('movement_type', 'restock') === 'restock') required>
                                <i class="ri-add-box-line"></i>
                                <span>Restock</span>
                                <small>Add stock</small>
                            </label>
                            <label class="pos-stock-type-option type-return">
                                <input type="radio" name="movement_type" value="return" @checked(old('movement_type') === 'return')>
                                <i class="ri-arrow-go-back-line"></i>
                                <span>Return</span>
                                <small>Add back</small>
                            </label>
                            <label class="pos-stock-type-option type-adjustment">
                                <input type="radio" name="movement_type" value="adjustment" @checked(old('movement_type') === 'adjustment')>
                                <i class="ri-indeterminate-circle-line"></i>
                                <span>Adjust</span>
                                <small>Remove</small>
                            </label>
                        </div>

                        <div class="mb-18">
                            <label class="pos-stock-form-label d-block">Quantity</label>
                            <input type="number" min="1" class="form-control" name="quantity" value="{{ old('quantity') }}" placeholder="Enter quantity" required>
                        </div>
                        <div class="mb-20">
                            <label class="pos-stock-form-label d-block">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional reason for this adjustment">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary-600 pos-stock-submit">
                            <i class="ri-save-3-line"></i> Save Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="pos-stock-panel">
                <div class="pos-stock-panel-head d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <h6 class="text-lg fw-bold mb-4">Low Stock Alerts</h6>
                        <p class="text-sm text-secondary-light mb-0">Products at or below their reorder threshold.</p>
                    </div>
                    @if($stats['low_stock'] > 0)
                        <span class="pos-stock-badge low">{{ $stats['low_stock'] }} alerts</span>
                    @endif
                </div>
                <div class="pos-stock-panel-body">
                    @if($lowStockProducts->isEmpty())
                        <div class="pos-stock-empty">
                            <div class="pos-stock-empty-icon"><i class="ri-checkbox-circle-line"></i></div>
                            <div class="fw-bold mb-6">All stock levels look good</div>
                            <p class="text-sm text-secondary-light mb-0">No products are below their low-stock threshold right now.</p>
                        </div>
                    @else
                        @foreach($lowStockProducts as $product)
                            <div class="pos-stock-alert-item {{ $product->isOutOfStock() ? '' : 'is-low' }}">
                                <div class="pos-stock-alert-thumb {{ $product->isOutOfStock() ? 'out' : 'low' }}">
                                    <i class="{{ $product->isOutOfStock() ? 'ri-error-warning-line' : 'ri-alert-line' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="pos-stock-alert-name">{{ $product->name }}</div>
                                    <div class="pos-stock-alert-meta">
                                        {{ $product->category?->name ?: 'Uncategorized' }}
                                        · threshold {{ $product->low_stock_threshold }}
                                    </div>
                                </div>
                                <span class="pos-stock-badge {{ $product->isOutOfStock() ? 'out' : 'low' }}">
                                    {{ $product->isOutOfStock() ? 'Out of stock' : $product->stock_qty.' left' }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card pos-stock-list p-0 overflow-hidden">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-3">
            <div>
                <h6 class="text-lg fw-bold mb-4">Recent Stock Movements</h6>
                <p class="text-sm text-secondary-light mb-0">Latest 100 inventory changes across sales, restocks, returns, and adjustments.</p>
            </div>
            <span class="pos-stock-qty-pill">{{ $movements->count() }} records</span>
        </div>
        <div class="card-body p-0">
            @if($movements->isEmpty())
                <div class="pos-stock-empty m-24">
                    <div class="pos-stock-empty-icon"><i class="ri-file-list-3-line"></i></div>
                    <div class="fw-bold mb-6">No movements yet</div>
                    <p class="text-sm text-secondary-light mb-0">Stock changes from sales and adjustments will appear here.</p>
                </div>
            @else
                <div class="pos-stock-scroll">
                    <table class="table bordered-table mb-0 pos-stock-list">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Change</th>
                                <th>Before</th>
                                <th>After</th>
                                <th>By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $movement)
                            @php
                                $typeClass = match($movement->movement_type) {
                                    'sale' => 'sale',
                                    'restock' => 'restock',
                                    'return' => 'return',
                                    'adjustment' => 'adjustment',
                                    default => 'adjustment',
                                };
                                $typeIcon = match($movement->movement_type) {
                                    'sale' => 'ri-shopping-cart-2-line',
                                    'restock' => 'ri-add-box-line',
                                    'return' => 'ri-arrow-go-back-line',
                                    default => 'ri-indeterminate-circle-line',
                                };
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $movement->product?->name ?: '—' }}</div>
                                    @if($movement->product?->category)
                                        <div class="text-xs text-secondary-light">{{ $movement->product->category->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="pos-movement-type {{ $typeClass }}">
                                        <i class="{{ $typeIcon }}"></i>
                                        {{ ucfirst($movement->movement_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="pos-movement-change {{ $movement->quantity_change >= 0 ? 'in' : 'out' }}">
                                        {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                    </span>
                                </td>
                                <td><span class="pos-stock-qty-pill">{{ $movement->qty_before }}</span></td>
                                <td><span class="pos-stock-qty-pill">{{ $movement->qty_after }}</span></td>
                                <td>{{ $movement->creator?->name ?? '—' }}</td>
                                <td class="text-secondary-light">{{ $movement->notes ?: '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    $('.pos-stock-type-option').on('click', function () {
        $('.pos-stock-type-option').removeClass('active');
        $(this).addClass('active').find('input[type="radio"]').prop('checked', true);
    });
})();
</script>
@endsection
