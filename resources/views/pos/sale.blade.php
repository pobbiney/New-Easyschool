@php
    $pageName = 'pos';
    $subpageName = 'pos-sale';
    $categoryIcons = ['ri-apps-2-line', 'ri-shirt-line', 'ri-book-open-line', 'ri-pencil-ruler-2-line', 'ri-gift-line', 'ri-shopping-basket-line'];
@endphp
@extends('layouts.app')

@section('css')
<style>
    .pos-terminal {
        --pos-orange: #fe9f43;
        --pos-orange-dark: #ea580c;
        --pos-teal: #25A194;
        --pos-teal-dark: #0f766e;
        --pos-indigo: #4f46e5;
        --pos-pink: #db2777;
        --pos-purple: #9333ea;
        --pos-blue: #2563eb;
        --pos-ink: #0f172a;
        --pos-muted: #64748b;
        --pos-bg: #eef2ff;
        min-height: calc(100vh - 120px);
    }

    .pos-terminal-shell {
        background:
            radial-gradient(circle at 0% 0%, rgba(37, 161, 148, 0.12), transparent 28%),
            radial-gradient(circle at 100% 0%, rgba(254, 159, 67, 0.14), transparent 24%),
            radial-gradient(circle at 100% 100%, rgba(79, 70, 229, 0.1), transparent 30%),
            linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        border-radius: 28px;
        padding: 24px;
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .pos-panel {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        border-radius: 22px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .pos-panel-accent {
        height: 5px;
        background: linear-gradient(90deg, var(--pos-teal), #6366f1, var(--pos-orange));
    }

    .pos-section-title {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--pos-ink);
        margin-bottom: 16px;
    }

    .pos-section-title::before {
        content: "";
        width: 4px;
        height: 18px;
        border-radius: 999px;
        background: linear-gradient(180deg, var(--pos-teal), var(--pos-indigo));
    }

    .pos-category-row {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 26px;
    }

    .pos-category-card {
        min-width: 188px;
        max-width: 188px;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #fff;
        padding: 20px 14px;
        text-align: center;
        cursor: pointer;
        transition: all .18s ease;
        flex-shrink: 0;
    }

    .pos-category-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.1);
    }

    .pos-category-card.active {
        border-color: var(--pos-orange);
        background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
        box-shadow: 0 12px 28px rgba(254, 159, 67, 0.18);
    }

    .pos-category-card.active .pos-category-name {
        color: var(--pos-orange-dark);
    }

    .pos-category-card.active .pos-category-count {
        color: #c2410c;
    }

    .pos-category-thumb {
        width: 78px;
        height: 78px;
        margin: 0 auto 12px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #fff;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
    }

    .pos-category-card:nth-child(1) .pos-category-thumb { background: linear-gradient(135deg, #25A194, #0f766e); }
    .pos-category-card:nth-child(2) .pos-category-thumb { background: linear-gradient(135deg, #6366f1, #4338ca); }
    .pos-category-card:nth-child(3) .pos-category-thumb { background: linear-gradient(135deg, #f59e0b, #ea580c); }
    .pos-category-card:nth-child(4) .pos-category-thumb { background: linear-gradient(135deg, #ec4899, #db2777); }
    .pos-category-card:nth-child(5) .pos-category-thumb { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .pos-category-card:nth-child(6) .pos-category-thumb { background: linear-gradient(135deg, #a855f7, #7e22ce); }
    .pos-category-card:nth-child(7) .pos-category-thumb { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .pos-category-card:nth-child(8) .pos-category-thumb { background: linear-gradient(135deg, #f97316, #c2410c); }

    .pos-category-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pos-category-thumb i {
        font-size: 30px;
        color: #fff;
    }

    .pos-category-name {
        font-size: 15px;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .pos-category-count {
        font-size: 13px;
        color: var(--pos-muted);
        font-weight: 600;
    }

    .pos-products-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding-top: 4px;
    }

    .pos-product-search {
        position: relative;
        min-width: 280px;
    }

    .pos-product-search input {
        border-radius: 999px;
        padding-left: 42px;
        border: 1px solid #dbeafe;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        height: 46px;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.08);
    }

    .pos-product-search input:focus {
        border-color: var(--pos-teal);
        box-shadow: 0 0 0 3px rgba(37, 161, 148, 0.12);
    }

    .pos-product-search i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--pos-teal);
    }

    .pos-product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 16px;
    }

    .pos-product-card {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #fff;
        padding: 14px;
        cursor: pointer;
        transition: all .18s ease;
        position: relative;
    }

    .pos-product-card:hover {
        transform: translateY(-3px);
        border-color: rgba(37, 161, 148, 0.35);
        box-shadow: 0 16px 34px rgba(37, 161, 148, 0.12);
    }

    .pos-product-card.in-cart {
        border-color: #22c55e;
        background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
        box-shadow: 0 12px 28px rgba(34, 197, 94, 0.16);
    }

    .pos-product-card.in-cart::after {
        content: "\eb7b";
        font-family: remixicon !important;
        position: absolute;
        top: 10px;
        right: 10px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        box-shadow: 0 6px 14px rgba(34, 197, 94, 0.35);
    }

    .pos-product-card.disabled {
        opacity: .55;
        cursor: not-allowed;
        pointer-events: none;
        filter: grayscale(0.2);
    }

    .pos-product-image-wrap {
        height: 132px;
        border-radius: 16px;
        background: linear-gradient(180deg, #f8fafc, #eef2ff);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 12px;
        border: 1px solid #eef2ff;
    }

    .pos-product-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pos-product-category {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        color: var(--pos-indigo);
        background: rgba(99, 102, 241, 0.1);
        padding: 3px 8px;
        border-radius: 999px;
        margin-bottom: 6px;
    }

    .pos-product-name {
        font-size: 14px;
        font-weight: 800;
        color: var(--pos-ink);
        line-height: 1.35;
        min-height: 38px;
        margin-bottom: 12px;
    }

    .pos-product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .pos-product-stock {
        font-size: 12px;
        font-weight: 800;
        color: var(--pos-pink);
        background: rgba(219, 39, 119, 0.1);
        padding: 4px 8px;
        border-radius: 999px;
    }

    .pos-product-stock.low {
        color: #c2410c;
        background: rgba(245, 158, 11, 0.14);
    }

    .pos-product-stock.out {
        color: #b91c1c;
        background: rgba(239, 68, 68, 0.12);
    }

    .pos-product-price {
        font-size: 15px;
        font-weight: 800;
        color: var(--pos-teal-dark);
        background: rgba(37, 161, 148, 0.1);
        padding: 4px 10px;
        border-radius: 999px;
    }

    .pos-order-panel {
        position: sticky;
        top: 90px;
        padding: 0;
        height: fit-content;
    }

    .pos-order-panel-inner {
        padding: 22px;
    }

    .pos-order-top {
        padding: 18px 22px;
        background: linear-gradient(135deg, #1e3a8a 0%, #25A194 55%, #6366f1 100%);
        color: #fff;
    }

    .pos-order-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .pos-order-head .pos-section-title {
        color: #fff;
        margin-bottom: 0;
    }

    .pos-order-head .pos-section-title::before {
        background: linear-gradient(180deg, #fff, rgba(255,255,255,0.5));
    }

    .pos-order-id {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
    }

    .pos-order-section {
        margin-bottom: 22px;
    }

    .pos-order-section-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--pos-ink);
        margin-bottom: 12px;
    }

    .pos-customer-row {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .pos-customer-select {
        flex: 1;
        height: 44px;
        border-radius: 12px;
        border-color: #dbeafe;
    }

    .pos-icon-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
        transition: transform .15s ease;
    }

    .pos-icon-btn:hover { transform: translateY(-1px); }
    .pos-icon-btn.teal { background: linear-gradient(135deg, #25A194, #0f766e); }
    .pos-icon-btn.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }

    .pos-customer-card {
        margin-top: 12px;
        padding: 14px 16px;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff7ed, #fffbeb);
        border: 1px solid #fed7aa;
        display: none;
    }

    .pos-customer-card.active { display: block; }

    #posSelectedStudent.active {
        background: linear-gradient(135deg, #ecfdf5, #f0fdfa);
        border-color: #99f6e4;
    }

    .pos-customer-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .pos-customer-card-name {
        font-weight: 800;
        color: var(--pos-ink);
        margin-bottom: 4px;
    }

    .pos-customer-card-meta {
        font-size: 12px;
        color: var(--pos-muted);
    }

    .pos-student-search-wrap {
        display: none;
        margin-top: 12px;
    }

    .pos-student-search-wrap.active { display: block; }

    .pos-student-results {
        max-height: 160px;
        overflow-y: auto;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        margin-top: 8px;
        display: none;
        background: #fff;
        box-shadow: 0 10px 24px rgba(59, 130, 246, 0.08);
    }

    .pos-student-results.active { display: block; }

    .pos-student-option {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
    }

    .pos-student-option:hover { background: #eff6ff; }

    .pos-order-details-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .pos-items-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 11px;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(37, 161, 148, 0.16), rgba(99, 102, 241, 0.12));
        color: var(--pos-teal-dark);
        font-size: 12px;
        font-weight: 800;
    }

    .pos-clear-all {
        border: none;
        background: rgba(239, 68, 68, 0.08);
        color: #dc2626;
        font-size: 12px;
        font-weight: 800;
        padding: 6px 10px;
        border-radius: 999px;
    }

    .pos-cart-table-head,
    .pos-cart-row {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) 120px 72px;
        gap: 10px;
        align-items: center;
    }

    .pos-cart-table-head {
        font-size: 12px;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .pos-cart-row {
        padding: 12px 10px;
        border-bottom: 1px solid #f1f5f9;
        border-radius: 12px;
        background: #fafbff;
        margin-bottom: 8px;
    }

    .pos-cart-row:last-child { border-bottom: none; margin-bottom: 0; }

    .pos-cart-item-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--pos-ink);
        line-height: 1.35;
    }

    .pos-cart-item-thumb {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        object-fit: cover;
        background: #eef2ff;
        margin-right: 8px;
        flex-shrink: 0;
        border: 1px solid #e0e7ff;
    }

    .pos-cart-item-info {
        display: flex;
        align-items: center;
        min-width: 0;
    }

    .pos-qty-cell {
        display: flex;
        justify-content: center;
        min-width: 0;
    }

    .pos-qty-controls {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 0;
        min-width: 104px;
        padding: 4px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #dbeafe;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .pos-qty-btn {
        width: 30px;
        height: 30px;
        min-width: 30px;
        min-height: 30px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #25A194, #0f766e);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0;
        flex-shrink: 0;
        font-size: 0;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(37, 161, 148, 0.25);
        transition: transform .12s ease, box-shadow .12s ease;
    }

    .pos-qty-btn i {
        font-size: 15px;
        line-height: 1;
        pointer-events: none;
    }

    .pos-qty-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 14px rgba(37, 161, 148, 0.32);
    }

    .pos-qty-btn:active {
        transform: scale(0.96);
    }

    .pos-qty-value {
        min-width: 28px;
        padding: 0 6px;
        text-align: center;
        font-size: 15px;
        font-weight: 800;
        color: var(--pos-ink);
        line-height: 1;
        user-select: none;
    }

    .pos-cart-cost {
        text-align: right;
        font-weight: 800;
        color: var(--pos-teal-dark);
        font-size: 13px;
    }

    .pos-remove-item {
        border: none;
        background: rgba(239, 68, 68, 0.08);
        color: #ef4444;
        padding: 4px;
        font-size: 15px;
        border-radius: 8px;
    }

    .pos-empty-cart {
        text-align: center;
        padding: 32px 12px;
        color: var(--pos-muted);
        font-size: 13px;
        background: linear-gradient(180deg, #fafbff, #ffffff);
        border: 1px dashed #dbeafe;
        border-radius: 16px;
    }

    .pos-summary-box {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
    }

    .pos-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 14px;
    }

    .pos-summary-row:last-child { border-bottom: none; }

    .pos-summary-row.total {
        font-size: 20px;
        font-weight: 800;
        color: var(--pos-teal-dark);
        padding-top: 14px;
        margin-top: 4px;
        border-top: 2px solid rgba(37, 161, 148, 0.15);
        border-bottom: none;
    }

    .pos-summary-row.discount label {
        margin: 0;
        font-weight: 700;
        color: var(--pos-orange-dark);
    }

    .pos-summary-row.discount input {
        width: 110px;
        text-align: right;
        border-radius: 10px;
        border-color: #fed7aa;
        background: #fff7ed;
    }

    .pos-pay-btn {
        width: 100%;
        height: 50px;
        border-radius: 14px;
        font-weight: 800;
        margin-top: 16px;
        border: none !important;
        color: #fff !important;
        background: linear-gradient(135deg, #25A194 0%, #0f766e 45%, #6366f1 100%) !important;
        box-shadow: 0 14px 28px rgba(37, 161, 148, 0.28);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .pos-pay-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(37, 161, 148, 0.34);
    }

    .pos-pay-btn:disabled {
        background: #cbd5e1;
        box-shadow: none;
    }

    .pos-walkin-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 12px;
    }

    .pos-paystack-hint {
        margin: -4px 0 12px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(37, 161, 148, 0.08);
        border: 1px solid rgba(37, 161, 148, 0.16);
        color: var(--pos-teal-dark);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.45;
    }

    @media (max-width: 991px) {
        .pos-order-panel { position: static; margin-top: 20px; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body pos-terminal">
    <div class="pos-terminal-shell">
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="pos-panel mb-0">
                    <div class="pos-panel-accent"></div>
                    <div class="p-24">
                    <div class="pos-section-title">Categories</div>
                    <div class="pos-category-row" id="posCategoryTabs">
                        <div class="pos-category-card active" data-category="all">
                            <div class="pos-category-thumb"><i class="ri-apps-2-line"></i></div>
                            <div class="pos-category-name">All Categories</div>
                            <div class="pos-category-count">{{ $totalProducts }} {{ $totalProducts === 1 ? 'Item' : 'Items' }}</div>
                        </div>
                        @foreach($categories as $index => $category)
                        <div class="pos-category-card" data-category="{{ $category->id }}">
                            <div class="pos-category-thumb">
                                <i class="{{ $categoryIcons[$index % count($categoryIcons)] }}"></i>
                            </div>
                            <div class="pos-category-name">{{ $category->name }}</div>
                            <div class="pos-category-count">{{ $category->products_count }} {{ $category->products_count === 1 ? 'Item' : 'Items' }}</div>
                        </div>
                        @endforeach
                    </div>

                    <div class="pos-products-head">
                        <div class="pos-section-title mb-0">Products</div>
                        <div class="pos-product-search">
                            <i class="ri-search-line"></i>
                            <input type="text" id="posProductSearch" class="form-control" placeholder="Search Product">
                        </div>
                    </div>

                    <div class="pos-product-grid" id="posProductGrid">
                        @foreach($products as $product)
                        <div class="pos-product-card {{ $product->isOutOfStock() ? 'disabled' : '' }}"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->name }}"
                             data-price="{{ $product->price }}"
                             data-stock="{{ $product->stock_qty }}"
                             data-image="{{ $product->imageUrl() }}"
                             data-category="{{ $product->pos_category_id }}"
                             data-category-name="{{ $product->category?->name }}"
                             data-search="{{ strtolower($product->name.' '.$product->sku.' '.($product->category?->name ?? '')) }}">
                            <div class="pos-product-image-wrap">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                            </div>
                            <div class="pos-product-category">{{ $product->category?->name ?: 'General' }}</div>
                            <div class="pos-product-name">{{ $product->name }}</div>
                            <div class="pos-product-footer">
                                @if($product->isOutOfStock())
                                    <span class="pos-product-stock out">0 Pcs</span>
                                @else
                                    <span class="pos-product-stock {{ $product->isLowStock() ? 'low' : '' }}">{{ $product->stock_qty }} Pcs</span>
                                @endif
                                <span class="pos-product-price">{{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if($products->isEmpty())
                        <div class="text-center py-56 text-secondary-light">
                            No active products. <a href="{{ route('pos-products') }}">Add products</a> first.
                        </div>
                    @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="pos-panel pos-order-panel">
                    <div class="pos-order-top">
                        <div class="pos-order-head">
                            <div class="pos-section-title mb-0">Order List</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="pos-order-id" id="posOrderId">#ORD{{ now()->format('His') }}</span>
                                <button type="button" class="pos-remove-item" id="posResetOrder" title="Reset order" style="background:rgba(255,255,255,0.15);color:#fff;"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="pos-order-panel-inner">
                    <div class="pos-order-section">
                        <div class="pos-order-section-title">Customer Information</div>
                        <div class="pos-customer-row">
                            <select class="form-control form-select pos-customer-select" id="posCustomerType">
                                <option value="walkin">Walk in Customer</option>
                                <option value="student">Link Student</option>
                            </select>
                            <button type="button" class="pos-icon-btn teal" id="posToggleStudentSearch" title="Search student"><i class="ri-user-add-line"></i></button>
                            <a href="{{ route('pos-sales') }}" class="pos-icon-btn blue" title="Sales history"><i class="ri-history-line"></i></a>
                        </div>

                        <div class="pos-student-search-wrap" id="posStudentSearchWrap">
                            <input type="text" class="form-control" id="posStudentSearch" placeholder="Search student name or ID...">
                            <div class="pos-student-results" id="posStudentResults"></div>
                        </div>

                        <div class="pos-customer-card active" id="posWalkinCard">
                            <div class="pos-customer-card-head">
                                <div>
                                    <div class="pos-customer-card-name">Walk in Customer</div>
                                    <div class="pos-customer-card-meta">Optional name and phone below</div>
                                </div>
                            </div>
                            <div class="pos-walkin-fields">
                                <input type="text" class="form-control" id="posCustomerName" placeholder="Customer name">
                                <input type="text" class="form-control" id="posCustomerPhone" placeholder="Phone">
                            </div>
                        </div>

                        <div class="pos-customer-card" id="posSelectedStudent">
                            <div class="pos-customer-card-head">
                                <div>
                                    <div class="pos-customer-card-name" id="posSelectedStudentName"></div>
                                    <div class="pos-customer-card-meta" id="posSelectedStudentMeta"></div>
                                </div>
                                <button type="button" class="pos-remove-item" id="posClearStudent"><i class="ri-close-line"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="pos-order-section">
                        <div class="pos-order-details-head">
                            <div class="d-flex align-items-center gap-2">
                                <div class="pos-order-section-title mb-0">Order Details</div>
                                <span class="pos-items-badge" id="posItemsBadge">Items : 0</span>
                            </div>
                            <button type="button" class="pos-clear-all" id="posClearCart">Clear all</button>
                        </div>

                        <div class="pos-cart-table-head">
                            <span></span>
                            <span>Item</span>
                            <span class="text-center">QTY</span>
                            <span class="text-end">Cost</span>
                        </div>
                        <div id="posCartItems">
                            <div class="pos-empty-cart">Select products to start the order.</div>
                        </div>
                    </div>

                    <div class="pos-order-section mb-0">
                        <div class="pos-order-section-title">Payment Summary</div>
                        <div class="pos-summary-box">
                        <div class="pos-summary-row">
                            <span>Subtotal</span>
                            <strong id="posSubtotal">0.00</strong>
                        </div>
                        <div class="pos-summary-row discount">
                            <label for="posDiscount">Discount</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="posDiscount" value="0">
                        </div>
                        <div class="pos-summary-row">
                            <span>Payment Method</span>
                            <select class="form-control form-select form-select-sm" id="posPaymentMethod" style="width:auto;min-width:150px;">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pos-paystack-hint" id="posPaystackHint" style="display:none;">
                            <i class="ri-bank-card-line"></i> Payments are processed securely via Paystack (mobile money, card, etc.).
                            @if(!($paystackConfigured ?? false))
                                <span class="text-danger-600"> Add your Paystack keys to the <code>.env</code> file (same as school fees).</span>
                            @endif
                        </div>
                        <div class="pos-summary-row total">
                            <span>Total Payable</span>
                            <strong id="posTotal">0.00</strong>
                        </div>
                        <div class="mb-12 mt-8">
                            <textarea class="form-control" id="posNotes" rows="2" placeholder="Order notes (optional)"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary-600 pos-pay-btn" id="posCompleteSale" disabled>
                            Complete Sale
                        </button>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
(function () {
    const cart = {};
    let selectedStudent = null;
    const searchUrl = @json(route('pos-student-search'));
    const checkoutUrl = @json(route('pos-sale-process'));
    const paystackInitializeUrl = @json(route('paystack-pos-sale-initialize'));
    const paystackVerifyUrl = @json(route('paystack-pos-sale-verify'));
    const paystackPublicKey = @json($paystackPublicKey ?? '');
    const paystackConfigured = @json($paystackConfigured ?? false);
    const csrf = @json(csrf_token());

    function formatMoney(value) {
        return Number(value || 0).toFixed(2);
    }

    function cartItems() {
        return Object.values(cart);
    }

    function cartCount() {
        return cartItems().reduce((sum, item) => sum + item.quantity, 0);
    }

    function syncProductSelection() {
        $('.pos-product-card').each(function () {
            const id = String($(this).data('id'));
            $(this).toggleClass('in-cart', !!cart[id]);
        });
    }

    function updateCompleteButton() {
        const hasItems = cartItems().length > 0;
        const method = $('#posPaymentMethod').val();
        const isPaystack = method === 'Paystack';

        $('#posPaystackHint').toggle(isPaystack);

        $('#posCompleteSale').prop('disabled', !hasItems).html(
            isPaystack
                ? '<i class="ri-secure-payment-line"></i> Pay with Paystack'
                : 'Complete Sale'
        );
    }

    function recalc() {
        let subtotal = 0;
        cartItems().forEach(item => { subtotal += item.price * item.quantity; });
        const discount = Math.max(parseFloat($('#posDiscount').val() || 0), 0);
        const total = Math.max(subtotal - discount, 0);
        $('#posSubtotal').text(formatMoney(subtotal));
        $('#posTotal').text(formatMoney(total));
        $('#posItemsBadge').text('Items : ' + cartCount());
        updateCompleteButton();
        syncProductSelection();
    }

    function buildCheckoutPayload() {
        return {
            _token: csrf,
            student_id: selectedStudent ? selectedStudent.id : null,
            customer_name: selectedStudent ? null : ($('#posCustomerName').val().trim() || null),
            customer_phone: selectedStudent ? null : ($('#posCustomerPhone').val().trim() || null),
            payment_method: $('#posPaymentMethod').val(),
            discount: $('#posDiscount').val() || 0,
            notes: $('#posNotes').val().trim() || null,
            items: cartItems().map(item => ({ product_id: item.id, quantity: item.quantity })),
        };
    }

    function resetCompleteButton() {
        updateCompleteButton();
    }

    function verifyPaystackPayment(reference) {
        const $btn = $('#posCompleteSale');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verifying payment...');

        $.ajax({
            url: paystackVerifyUrl,
            method: 'POST',
            data: {
                _token: csrf,
                reference: reference,
            },
            headers: { 'Accept': 'application/json' },
        }).done(function (response) {
            if (response.success && response.receipt_url) {
                window.location.href = response.receipt_url;
                return;
            }
            alert(response.message || 'Payment verified.');
        }).fail(function (xhr) {
            alert(xhr.responseJSON?.message || 'Unable to verify Paystack payment.');
            resetCompleteButton();
        });
    }

    function startPaystackPayment(payload) {
        const $btn = $('#posCompleteSale');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Initializing Paystack...');

        $.ajax({
            url: paystackInitializeUrl,
            method: 'POST',
            data: payload,
            headers: { 'Accept': 'application/json' },
        }).done(function (res) {
            if (typeof PaystackPop === 'undefined') {
                alert('Paystack checkout could not be loaded.');
                resetCompleteButton();
                return;
            }

            const handler = PaystackPop.setup({
                key: res.public_key || paystackPublicKey,
                email: res.email,
                label: res.label || undefined,
                amount: Number(res.amount),
                currency: res.currency || 'GHS',
                ref: res.reference,
                callback: function (response) {
                    verifyPaystackPayment(response.reference);
                },
                onClose: function () {
                    alert('Paystack payment was cancelled.');
                    resetCompleteButton();
                },
            });

            $btn.html('<i class="ri-secure-payment-line"></i> Opening Paystack...');
            handler.openIframe();
        }).fail(function (xhr) {
            alert(xhr.responseJSON?.message || 'Unable to initialize Paystack payment.');
            resetCompleteButton();
        });
    }

    function renderCart() {
        const container = $('#posCartItems');
        const items = cartItems();

        if (!items.length) {
            container.html('<div class="pos-empty-cart">Select products to start the order.</div>');
            recalc();
            return;
        }

        let html = '';
        items.forEach(item => {
            html += `
                <div class="pos-cart-row" data-id="${item.id}">
                    <button type="button" class="pos-remove-item pos-remove-line" data-id="${item.id}"><i class="ri-delete-bin-line"></i></button>
                    <div class="pos-cart-item-info">
                        <img src="${item.image}" alt="" class="pos-cart-item-thumb">
                        <div class="pos-cart-item-name">${item.name}</div>
                    </div>
                    <div class="pos-qty-cell">
                        <div class="pos-qty-controls">
                            <button type="button" class="pos-qty-btn pos-qty-minus" data-id="${item.id}" aria-label="Decrease quantity"><i class="ri-subtract-line"></i></button>
                            <span class="pos-qty-value">${item.quantity}</span>
                            <button type="button" class="pos-qty-btn pos-qty-plus" data-id="${item.id}" aria-label="Increase quantity"><i class="ri-add-line"></i></button>
                        </div>
                    </div>
                    <div class="pos-cart-cost">${formatMoney(item.price * item.quantity)}</div>
                </div>
            `;
        });

        container.html(html);
        recalc();
    }

    function addToCart(product) {
        const id = product.id;
        if (!cart[id]) {
            cart[id] = {
                id,
                name: product.name,
                price: product.price,
                stock: product.stock,
                image: product.image,
                quantity: 0,
            };
        }
        if (cart[id].quantity >= cart[id].stock) return;
        cart[id].quantity += 1;
        renderCart();
    }

    function resetOrder() {
        Object.keys(cart).forEach(key => delete cart[key]);
        selectedStudent = null;
        $('#posDiscount').val(0);
        $('#posNotes').val('');
        $('#posCustomerName').val('');
        $('#posCustomerPhone').val('');
        $('#posCustomerType').val('walkin');
        $('#posStudentSearchWrap').removeClass('active');
        $('#posSelectedStudent').removeClass('active');
        $('#posWalkinCard').addClass('active');
        $('#posStudentSearch').val('');
        $('#posStudentResults').removeClass('active').empty();
        renderCart();
    }

    $('.pos-product-card:not(.disabled)').on('click', function () {
        addToCart({
            id: $(this).data('id'),
            name: $(this).data('name'),
            price: parseFloat($(this).data('price')),
            stock: parseInt($(this).data('stock'), 10),
            image: $(this).data('image'),
        });
    });

    $('body').on('click', '.pos-qty-minus', function () {
        const id = $(this).data('id');
        if (!cart[id]) return;
        cart[id].quantity -= 1;
        if (cart[id].quantity <= 0) delete cart[id];
        renderCart();
    });

    $('body').on('click', '.pos-qty-plus', function () {
        const id = $(this).data('id');
        if (!cart[id]) return;
        if (cart[id].quantity >= cart[id].stock) return;
        cart[id].quantity += 1;
        renderCart();
    });

    $('body').on('click', '.pos-remove-line', function () {
        delete cart[$(this).data('id')];
        renderCart();
    });

    $('#posDiscount').on('input', recalc);
    $('#posClearCart, #posResetOrder').on('click', resetOrder);

    $('#posCategoryTabs').on('click', '.pos-category-card', function () {
        $('#posCategoryTabs .pos-category-card').removeClass('active');
        $(this).addClass('active');
        const category = String($(this).data('category'));
        $('.pos-product-card').each(function () {
            const match = category === 'all' || String($(this).data('category')) === category;
            $(this).toggle(match);
        });
    });

    $('#posProductSearch').on('input', function () {
        const term = $(this).val().toLowerCase().trim();
        $('.pos-product-card').each(function () {
            const search = String($(this).data('search'));
            $(this).toggle(!term || search.includes(term));
        });
    });

    $('#posCustomerType').on('change', function () {
        if ($(this).val() === 'student') {
            $('#posStudentSearchWrap').addClass('active');
            $('#posWalkinCard').removeClass('active');
        } else {
            $('#posStudentSearchWrap').removeClass('active');
            $('#posWalkinCard').addClass('active');
            selectedStudent = null;
            $('#posSelectedStudent').removeClass('active');
        }
    });

    $('#posToggleStudentSearch').on('click', function () {
        $('#posCustomerType').val('student').trigger('change');
        $('#posStudentSearch').focus();
    });

    let studentTimer = null;
    $('#posStudentSearch').on('input', function () {
        clearTimeout(studentTimer);
        const q = $(this).val().trim();
        if (q.length < 2) {
            $('#posStudentResults').removeClass('active').empty();
            return;
        }
        studentTimer = setTimeout(function () {
            $.get(searchUrl, { q }, function (response) {
                const results = response.students || [];
                if (!results.length) {
                    $('#posStudentResults').html('<div class="pos-student-option text-secondary-light">No students found</div>').addClass('active');
                    return;
                }
                let html = '';
                results.forEach(student => {
                    html += `<div class="pos-student-option" data-id="${student.id}" data-name="${student.full_name}" data-meta="${student.student_id} · ${student.class_name}">${student.full_name}<div class="text-sm text-secondary-light">${student.student_id} · ${student.class_name}</div></div>`;
                });
                $('#posStudentResults').html(html).addClass('active');
            });
        }, 250);
    });

    $('body').on('click', '.pos-student-option[data-id]', function () {
        selectedStudent = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            meta: $(this).data('meta'),
        };
        $('#posSelectedStudentName').text(selectedStudent.name);
        $('#posSelectedStudentMeta').text(selectedStudent.meta);
        $('#posSelectedStudent').addClass('active');
        $('#posWalkinCard').removeClass('active');
        $('#posStudentResults').removeClass('active').empty();
        $('#posStudentSearch').val('');
        $('#posCustomerName').val('');
        $('#posCustomerPhone').val('');
    });

    $('#posClearStudent').on('click', function () {
        selectedStudent = null;
        $('#posSelectedStudent').removeClass('active');
        $('#posCustomerType').val('walkin').trigger('change');
    });

    $('#posPaymentMethod').on('change', updateCompleteButton);

    $('#posCompleteSale').on('click', function () {
        const payload = buildCheckoutPayload();
        if (!payload.items.length) return;

        const method = payload.payment_method;

        if (method === 'Paystack') {
            if (!paystackConfigured) {
                alert('Paystack is not configured. Add PAYSTACK_PUBLIC_KEY and PAYSTACK_SECRET_KEY to your .env file (same settings used for school fees).');
                return;
            }
            startPaystackPayment(payload);
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: checkoutUrl,
            method: 'POST',
            data: payload,
            headers: { 'Accept': 'application/json' },
        }).done(function (response) {
            if (response.success && response.receipt_url) {
                window.location.href = response.receipt_url;
                return;
            }
            alert(response.message || 'Sale completed.');
        }).fail(function (xhr) {
            alert(xhr.responseJSON?.message || 'Unable to complete sale.');
        }).always(function () {
            resetCompleteButton();
        });
    });

    updateCompleteButton();
})();
</script>
@endsection
