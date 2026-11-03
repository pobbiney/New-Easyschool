@php $pageName = 'pos'; $subpageName = 'pos-categories'; @endphp
@extends('layouts.app')

@section('css')
<style>
    .pos-cat-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        border: 1px solid rgba(37, 161, 148, 0.18);
        background:
            radial-gradient(circle at top right, rgba(99, 102, 241, 0.16), transparent 42%),
            linear-gradient(135deg, rgba(37, 161, 148, 0.14) 0%, rgba(255, 255, 255, 0.96) 55%, rgba(245, 158, 11, 0.08) 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .pos-cat-hero::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(37, 161, 148, 0.08);
        pointer-events: none;
    }

    .pos-cat-hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #25A194, #17897e);
        color: #fff;
        font-size: 26px;
        flex-shrink: 0;
        box-shadow: 0 12px 24px rgba(37, 161, 148, 0.28);
    }

    .pos-cat-hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
        color: #0f172a;
    }

    .pos-cat-hero-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .pos-cat-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(37, 161, 148, 0.14);
        color: #334155;
    }

    .pos-cat-stat {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 20px 22px;
        background: #fff;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .pos-cat-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        border-color: rgba(37, 161, 148, 0.22);
    }

    .pos-cat-stat .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    .pos-cat-list {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .pos-cat-list .card-header {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .pos-cat-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .pos-cat-list table.dataTable {
        min-width: 880px;
    }

    .pos-cat-name-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .pos-cat-avatar {
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

    .pos-cat-tone-0 { background: linear-gradient(135deg, #25A194, #0f766e); }
    .pos-cat-tone-1 { background: linear-gradient(135deg, #6366f1, #4338ca); }
    .pos-cat-tone-2 { background: linear-gradient(135deg, #ec4899, #be185d); }
    .pos-cat-tone-3 { background: linear-gradient(135deg, #f59e0b, #b45309); }
    .pos-cat-tone-4 { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .pos-cat-tone-5 { background: linear-gradient(135deg, #a855f7, #7e22ce); }

    .pos-cat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .pos-cat-pill-0 { background: rgba(37, 161, 148, 0.12); color: #0f766e; }
    .pos-cat-pill-1 { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
    .pos-cat-pill-2 { background: rgba(236, 72, 153, 0.12); color: #be185d; }
    .pos-cat-pill-3 { background: rgba(245, 158, 11, 0.14); color: #b45309; }
    .pos-cat-pill-4 { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
    .pos-cat-pill-5 { background: rgba(168, 85, 247, 0.12); color: #7e22ce; }

    .pos-cat-desc {
        max-width: 340px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
    }

    .pos-cat-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
    }

    .pos-cat-count.empty {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .pos-cat-status-active,
    .pos-cat-status-inactive {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .pos-cat-status-active {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .pos-cat-status-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
    }

    .pos-cat-empty {
        padding: 72px 24px;
        text-align: center;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }

    .pos-cat-empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 18px;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: #25A194;
        background: rgba(37, 161, 148, 0.1);
        border: 1px dashed rgba(37, 161, 148, 0.28);
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

    .pos-cat-edit-btn {
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
            ['label' => 'Categories', 'active' => true],
        ],
        'title' => 'Product Categories',
        'subtitle' => 'Organize shop items into groups like uniforms, books, stationery, and souvenirs.',
        'actions' => '
            <div class="d-flex flex-wrap gap-2">
                <a href="'.route('pos-products').'" class="pos-quick-link"><i class="ri-shopping-bag-3-line"></i> Products</a>
                <a href="'.route('pos-sale').'" class="pos-quick-link"><i class="ri-store-2-line"></i> New Sale</a>
                <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                    <i class="ri-add-large-line"></i> Add Category
                </button>
            </div>
        ',
    ])

    <div class="pos-cat-hero d-flex align-items-start gap-16">
        <span class="pos-cat-hero-icon"><i class="ri-price-tag-3-line"></i></span>
        <div class="position-relative" style="z-index:1;">
            <div class="pos-cat-hero-title">Shop category setup</div>
            <p class="text-sm text-secondary-light mb-0" style="max-width:640px;">
                Categories power your POS counter. Group related products so cashiers can find uniforms, books, pens, and souvenirs faster at checkout.
            </p>
            <div class="pos-cat-hero-tags">
                <span class="pos-cat-hero-tag"><i class="ri-shirt-line"></i> Uniforms</span>
                <span class="pos-cat-hero-tag"><i class="ri-book-open-line"></i> Books</span>
                <span class="pos-cat-hero-tag"><i class="ri-pencil-ruler-2-line"></i> Stationery</span>
                <span class="pos-cat-hero-tag"><i class="ri-gift-line"></i> Souvenirs</span>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="pos-cat-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Categories</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-folder-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-cat-stat">
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
            <div class="pos-cat-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Inactive</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['inactive'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="pos-cat-stat">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Products Listed</p>
                        <h4 class="fw-semibold mb-0 text-primary-600">{{ $stats['products'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-shopping-basket-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card pos-cat-list">
        <div class="card-header border-bottom px-24">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="text-lg fw-semibold mb-4">All Categories</h6>
                    <p class="text-sm text-secondary-light mb-0">Edit names, descriptions, and availability for each shop group.</p>
                </div>
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search categories...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($categories->isNotEmpty())
            <div class="pos-cat-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        @php $tone = $category->id % 6; @endphp
                        <tr>
                            <td>
                                <div class="pos-cat-name-cell">
                                    <span class="pos-cat-avatar pos-cat-tone-{{ $tone }}">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                                    <div>
                                        <div class="fw-semibold mb-4">{{ $category->name }}</div>
                                        <span class="pos-cat-pill pos-cat-pill-{{ $tone }}">
                                            <i class="ri-store-2-line"></i> POS group
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pos-cat-desc">{{ $category->description ?: 'No description added yet.' }}</span>
                            </td>
                            <td>
                                <span class="pos-cat-count {{ $category->products_count ? '' : 'empty' }}">
                                    <i class="ri-shopping-bag-3-line"></i>
                                    {{ $category->products_count }} {{ $category->products_count === 1 ? 'product' : 'products' }}
                                </span>
                            </td>
                            <td>
                                @if($category->status == 'Active')
                                    <span class="pos-cat-status-active"><i class="ri-checkbox-circle-fill"></i> Active</span>
                                @else
                                    <span class="pos-cat-status-inactive"><i class="ri-indeterminate-circle-fill"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button"
                                        data-url="{{ route('get-pos-category-id', $category->id) }}"
                                        class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 pos-cat-edit-btn show-pos-category-edit">
                                        <i class="ri-edit-2-line"></i> Edit
                                    </button>
                                    @if($category->isInUse())
                                        <button type="button" class="btn btn-sm btn-outline-neutral-400 text-secondary-light pos-cat-edit-btn"
                                            title="Cannot delete — used by {{ $category->products_count }} product{{ $category->products_count === 1 ? '' : 's' }}"
                                            disabled style="opacity:0.65;cursor:not-allowed;">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </button>
                                    @else
                                        <form action="{{ route('delete-pos-category-process') }}" method="POST" class="d-inline"
                                            data-confirm-title="{{ 'Delete '.$category->name.'?' }}"
                                            data-confirm="This cannot be undone.">
                                            @csrf
                                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger-600 pos-cat-edit-btn">
                                                <i class="ri-delete-bin-line"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="pos-cat-empty">
                <div class="pos-cat-empty-icon"><i class="ri-price-tag-3-line"></i></div>
                <h6 class="fw-semibold mb-6">No categories yet</h6>
                <p class="text-sm text-secondary-light mb-16 mx-auto" style="max-width:420px;">
                    Start with common shop groups like Uniforms, Books, Stationery, or Souvenirs before adding products.
                </p>
                <button type="button" class="my-sidebar-btn btn btn-primary-600">
                    <i class="ri-add-line"></i> Add your first category
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

@include('pos.modals.add-category-modal')
@include('pos.modals.edit-category-modal')
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

    $('body').on('click', '.show-pos-category-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_pos_category_id').val(data.id);
            $('#edit_pos_category_name').val(data.name);
            $('#edit_pos_category_description').val(data.description || '');
            $('#edit_pos_category_status').val(data.status);
        });
    });
</script>
@endsection
