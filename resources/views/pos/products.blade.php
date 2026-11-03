@php $pageName = 'pos'; $subpageName = 'pos-products'; @endphp
@extends('layouts.app')

@section('css')
<style>
    .pos-prod-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        border: 1px solid rgba(37, 161, 148, 0.18);
        background:
            radial-gradient(circle at top right, rgba(245, 158, 11, 0.14), transparent 40%),
            linear-gradient(135deg, rgba(37, 161, 148, 0.14) 0%, rgba(255, 255, 255, 0.96) 52%, rgba(99, 102, 241, 0.08) 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .pos-prod-hero::after {
        content: "";
        position: absolute;
        right: -24px;
        bottom: -36px;
        width: 170px;
        height: 170px;
        border-radius: 50%;
        background: rgba(245, 158, 11, 0.08);
        pointer-events: none;
    }

    .pos-prod-hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        font-size: 26px;
        flex-shrink: 0;
        box-shadow: 0 12px 24px rgba(245, 158, 11, 0.28);
    }

    .pos-prod-hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
        color: #0f172a;
    }

    .pos-prod-hero-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .pos-prod-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(245, 158, 11, 0.18);
        color: #334155;
    }

    .pos-prod-stat {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 20px 22px;
        background: #fff;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .pos-prod-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        border-color: rgba(37, 161, 148, 0.22);
    }

    .pos-prod-stat .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    .pos-prod-list {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .pos-prod-list .card-header {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .pos-prod-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .pos-prod-list table.dataTable {
        min-width: 980px;
    }

    .pos-prod-name-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .pos-prod-avatar {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        flex-shrink: 0;
        color: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    }

    .pos-prod-tone-0 { background: linear-gradient(135deg, #25A194, #0f766e); }
    .pos-prod-tone-1 { background: linear-gradient(135deg, #6366f1, #4338ca); }
    .pos-prod-tone-2 { background: linear-gradient(135deg, #ec4899, #be185d); }
    .pos-prod-tone-3 { background: linear-gradient(135deg, #f59e0b, #b45309); }
    .pos-prod-tone-4 { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .pos-prod-tone-5 { background: linear-gradient(135deg, #a855f7, #7e22ce); }

    .pos-prod-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }

    .pos-prod-category {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(37, 161, 148, 0.1);
        color: #0f766e;
    }

    .pos-prod-sku {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
    }

    .pos-prod-price {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .pos-prod-price small {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        margin-top: 2px;
    }

    .pos-prod-stock,
    .pos-prod-stock-low,
    .pos-prod-stock-out {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .pos-prod-stock {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .pos-prod-stock-low {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .pos-prod-stock-out {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
    }

    .pos-prod-status-active,
    .pos-prod-status-inactive {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .pos-prod-status-active {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .pos-prod-status-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
    }

    .pos-prod-empty {
        padding: 72px 24px;
        text-align: center;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }

    .pos-prod-empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 18px;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: #d97706;
        background: rgba(245, 158, 11, 0.12);
        border: 1px dashed rgba(245, 158, 11, 0.28);
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
        border-color: #25A194;
        color: #25A194;
        background: rgba(37, 161, 148, 0.04);
        transform: translateY(-1px);
    }

    .pos-prod-edit-btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 8px 14px;
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
            ['label' => 'Products', 'active' => true],
        ],
        'title' => 'Shop Products',
        'subtitle' => 'Manage item names, prices, stock levels, and SKUs for the school shop.',
        'actions' => '
            <div class="d-flex flex-wrap gap-2">
                <a href="'.route('pos-categories').'" class="pos-quick-link"><i class="ri-price-tag-3-line"></i> Categories</a>
                <a href="'.route('pos-stock').'" class="pos-quick-link"><i class="ri-stack-line"></i> Stock</a>
                <a href="'.route('pos-sale').'" class="pos-quick-link"><i class="ri-store-2-line"></i> New Sale</a>
                <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                    <i class="ri-add-large-line"></i> Add Product
                </button>
            </div>
        ',
    ])

    <div class="pos-prod-hero d-flex align-items-start gap-16">
        <span class="pos-prod-hero-icon"><i class="ri-shopping-bag-3-line"></i></span>
        <div class="position-relative" style="z-index:1;">
            <div class="pos-prod-hero-title">Product catalog</div>
            <p class="text-sm text-secondary-light mb-0" style="max-width:640px;">
                Every item sold at the POS counter lives here. Set prices, track stock, and keep SKUs ready for fast checkout.
            </p>
            <div class="pos-prod-hero-tags">
                <span class="pos-prod-hero-tag"><i class="ri-money-dollar-circle-line"></i> Selling price</span>
                <span class="pos-prod-hero-tag"><i class="ri-barcode-box-line"></i> SKU codes</span>
                <span class="pos-prod-hero-tag"><i class="ri-stack-line"></i> Stock levels</span>
                <span class="pos-prod-hero-tag"><i class="ri-alarm-warning-line"></i> Low-stock alerts</span>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="pos-prod-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Products</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-shopping-bag-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-prod-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Active</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['active'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-prod-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Low Stock</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['low_stock'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-alarm-warning-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-prod-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Out of Stock</p>
                        <h4 class="fw-semibold mb-0 text-danger-600">{{ $stats['out_of_stock'] }}</h4>
                    </div>
                    <span class="stat-icon bg-danger-100 text-danger-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card pos-prod-list">
        <div class="card-header border-bottom px-24">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">All Products</h6>
                    <p class="text-sm text-secondary-light mb-0">Review pricing, inventory, and availability across every shop item.</p>
                </div>
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search products, SKU, category...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($products->isNotEmpty())
            <div class="pos-prod-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        @php $tone = $product->id % 6; @endphp
                        <tr>
                            <td>
                                <div class="pos-prod-name-cell">
                                    <span class="pos-prod-avatar pos-prod-tone-{{ $tone }}">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        @if($product->description)
                                            <div class="pos-prod-sub">{{ \Illuminate\Support\Str::limit($product->description, 48) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pos-prod-category">
                                    <i class="ri-price-tag-3-line"></i>
                                    {{ $product->category?->name ?: 'Uncategorized' }}
                                </span>
                            </td>
                            <td><span class="pos-prod-sku">{{ $product->sku }}</span></td>
                            <td>
                                <span class="pos-prod-price">
                                    {{ number_format($product->price, 2) }}
                                    @if($product->cost_price)
                                        <small>Cost {{ number_format($product->cost_price, 2) }}</small>
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="pos-prod-stock-out"><i class="ri-close-circle-fill"></i> Out of stock</span>
                                @elseif($product->isLowStock())
                                    <span class="pos-prod-stock-low"><i class="ri-alarm-warning-fill"></i> {{ $product->stock_qty }} left</span>
                                @else
                                    <span class="pos-prod-stock"><i class="ri-checkbox-circle-fill"></i> {{ $product->stock_qty }} in stock</span>
                                @endif
                            </td>
                            <td>
                                @if($product->status == 'Active')
                                    <span class="pos-prod-status-active"><i class="ri-checkbox-circle-fill"></i> Active</span>
                                @else
                                    <span class="pos-prod-status-inactive"><i class="ri-indeterminate-circle-fill"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                    data-url="{{ route('get-pos-product-id', $product->id) }}"
                                    class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 pos-prod-edit-btn show-pos-product-edit">
                                    <i class="ri-edit-2-line"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="pos-prod-empty">
                <div class="pos-prod-empty-icon"><i class="ri-shopping-bag-3-line"></i></div>
                <h6 class="fw-semibold mb-6">No products yet</h6>
                <p class="text-sm text-secondary-light mb-16 mx-auto" style="max-width:420px;">
                    Add your first shop item with a price, SKU, and opening stock quantity. Create categories first if you have not already.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('pos-categories') }}" class="pos-quick-link"><i class="ri-price-tag-3-line"></i> Manage Categories</a>
                    <button type="button" class="my-sidebar-btn btn btn-primary-600">
                        <i class="ri-add-line"></i> Add your first product
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@include('pos.modals.add-product-modal')
@include('pos.modals.edit-product-modal')
@endsection

@section('scripts')
<script>
    $('.my-sidebar-btn').on('click', function () {
        $('.my-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });

    $('.close-my-sidebar, .overlay').on('click', function () {
        $('.my-sidebar').removeClass('active');
        $('.my-sidebar form').trigger('reset');
        if (!$('.edit-sidebar').hasClass('active')) {
            $('.overlay').removeClass('active');
        }
    });

    $('.edit-sidebar-btn').on('click', function () {
        $('.edit-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });

    $('.close-edit-sidebar, .overlay').on('click', function () {
        $('.edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('body').on('click', '.show-pos-product-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_pos_product_id').val(data.id);
            $('#edit_pos_product_category_id').val(data.pos_category_id);
            $('#edit_pos_product_name').val(data.name);
            $('#edit_pos_product_sku').val(data.sku);
            $('#edit_pos_product_price').val(data.price);
            $('#edit_pos_product_cost_price').val(data.cost_price || '');
            $('#edit_pos_product_stock_qty').val(data.stock_qty);
            $('#edit_pos_product_low_stock_threshold').val(data.low_stock_threshold);
            $('#edit_pos_product_status').val(data.status);
            $('#edit_pos_product_description').val(data.description || '');
            const preview = $('#edit_pos_product_image_preview');
            if (data.image_url) {
                preview.attr('src', data.image_url).show();
            } else {
                preview.hide();
            }
        });
    });
</script>
@endsection
