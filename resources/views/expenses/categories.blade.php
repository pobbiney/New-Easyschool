@php $pageName = 'expenses'; $subpageName = 'expense-categories'; @endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .exp-quick-link {
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
    }
    .exp-quick-link:hover {
        border-color: #25A194;
        color: #25A194;
        background: rgba(37, 161, 148, 0.04);
    }
    .exp-cat-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        color: #fff;
        flex-shrink: 0;
    }
    .exp-tone-0 { background: linear-gradient(135deg, #25A194, #0f766e); }
    .exp-tone-1 { background: linear-gradient(135deg, #6366f1, #4338ca); }
    .exp-tone-2 { background: linear-gradient(135deg, #f59e0b, #b45309); }
    .exp-tone-3 { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .exp-tone-4 { background: linear-gradient(135deg, #ec4899, #be185d); }
    .exp-tone-5 { background: linear-gradient(135deg, #a855f7, #7e22ce); }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Expenses',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Expenses', 'url' => route('expenses')],
            ['label' => 'Categories', 'active' => true],
        ],
        'title' => 'Expense Categories',
        'subtitle' => 'Group school spending so you can see where money goes.',
        'actions' => '
            <div class="d-flex flex-wrap gap-2">
                <a href="'.route('expenses').'" class="exp-quick-link"><i class="ri-list-check-2"></i> Record Expenses</a>
                <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                    <i class="ri-add-large-line"></i> Add Category
                </button>
            </div>
        ',
    ])

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Categories</div>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-folder-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Active</div>
                        <h4 class="fw-semibold mb-0">{{ $stats['active'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Inactive</div>
                        <h4 class="fw-semibold mb-0">{{ $stats['inactive'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-close-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-sm text-secondary-light mb-6">Expense records</div>
                        <h4 class="fw-semibold mb-0">{{ $stats['records'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ac-list-wrapper">
        <div class="card-header border-bottom py-16 px-24 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="mb-0 fw-semibold">All categories</h6>
                <small class="text-secondary-light">Inactive categories stay off the record-expense form.</small>
            </div>
        </div>
        @if($categories->isNotEmpty())
        <div class="ac-list-scroll">
            <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Records</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        @php $tone = $category->id % 6; @endphp
                        <tr>
                            <td>
                                <div class="ac-name-cell">
                                    <span class="exp-cat-avatar exp-tone-{{ $tone }}">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                                    <div class="fw-semibold">{{ $category->name }}</div>
                                </div>
                            </td>
                            <td class="text-secondary-light text-sm">{{ $category->description ?: '—' }}</td>
                            <td>
                                <span class="ac-pill ac-pill-indigo">{{ $category->expenses_count }} {{ $category->expenses_count === 1 ? 'record' : 'records' }}</span>
                            </td>
                            <td>
                                <span class="ac-pill {{ $category->status === 'Active' ? 'ac-pill-active' : 'ac-pill-inactive' }}">
                                    {{ $category->status }}
                                </span>
                            </td>
                            <td>
                                <button type="button"
                                    data-url="{{ route('get-expense-category-id', $category->id) }}"
                                    class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 show-expense-category-edit">
                                    <i class="ri-edit-2-line"></i> Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-56 px-24 text-secondary-light">
            <i class="ri-folder-add-line" style="font-size:32px;color:#25A194;"></i>
            <p class="mt-12 mb-16">No categories yet. Add groups like Utilities or Transport.</p>
            <button type="button" class="my-sidebar-btn btn btn-primary-600">Add category</button>
        </div>
        @endif
    </div>
</div>

@include('expenses.modals.add-category-modal')
@include('expenses.modals.edit-category-modal')
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
    $('body').on('click', '.show-expense-category-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_expense_category_id').val(data.id);
            $('#edit_expense_category_name').val(data.name);
            $('#edit_expense_category_description').val(data.description || '');
            $('#edit_expense_category_status').val(data.status);
        });
    });
</script>
@endsection
