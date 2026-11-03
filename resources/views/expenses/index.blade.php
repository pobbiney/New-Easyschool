@php
    $pageName = 'expenses';
    $subpageName = 'expenses';
    $hasFilters = ($filters['from_date'] ?? '') || ($filters['to_date'] ?? '') || ($filters['expense_category_id'] ?? '') || ($filters['payment_method'] ?? '');
    $maxCategoryTotal = max(1, (float) ($categoryTotals->max('total') ?: 0));
    $methodPills = [
        'Cash' => 'exp-method-cash',
        'Bank Transfer' => 'exp-method-bank',
        'Mobile Money' => 'exp-method-momo',
        'Cheque' => 'exp-method-cheque',
    ];
    $methodIcons = [
        'Cash' => 'ri-money-dollar-circle-line',
        'Bank Transfer' => 'ri-bank-line',
        'Mobile Money' => 'ri-smartphone-line',
        'Cheque' => 'ri-file-paper-2-line',
    ];
@endphp
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
        transition: all .15s ease;
    }
    .exp-quick-link:hover {
        border-color: #25A194;
        color: #25A194;
        background: rgba(37, 161, 148, .04);
        transform: translateY(-1px);
    }
    .exp-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 24px;
        border: 1px solid rgba(37, 161, 148, .18);
        background:
            radial-gradient(circle at top right, rgba(244, 63, 94, .12), transparent 42%),
            linear-gradient(135deg, rgba(37, 161, 148, .14) 0%, rgba(255, 255, 255, .96) 52%, rgba(99, 102, 241, .08) 100%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
    }
    .exp-hero::after {
        content: "";
        position: absolute;
        right: -28px;
        bottom: -48px;
        width: 190px;
        height: 190px;
        border-radius: 50%;
        background: rgba(37, 161, 148, .08);
        pointer-events: none;
    }
    .exp-hero-icon {
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
        box-shadow: 0 12px 24px rgba(37, 161, 148, .28);
    }
    .exp-hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -.02em;
        margin-bottom: 6px;
        color: #0f172a;
    }
    .exp-hero-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .exp-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255, 255, 255, .82);
        border: 1px solid rgba(37, 161, 148, .14);
        color: #334155;
    }
    .exp-stat {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 20px 22px;
        background: #fff;
        height: 100%;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        overflow: hidden;
        position: relative;
    }
    .exp-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .08);
        border-color: rgba(37, 161, 148, .22);
    }
    .exp-stat .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
        flex-shrink: 0;
    }
    .exp-stat-value {
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -.02em;
        color: #0f172a;
        margin: 0;
    }
    .exp-board {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
    }
    .exp-board .card-head {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f6;
    }
    .exp-filter {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f6;
    }
    .exp-spend {
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f6;
        background: linear-gradient(180deg, #fff 0%, #f8fffd 100%);
    }
    .exp-spend-total {
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: -.03em;
        color: #0f766e;
        line-height: 1.1;
    }
    .exp-cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 16px;
    }
    .exp-cat-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        background: #fff;
    }
    .exp-cat-card small {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .exp-cat-card strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 8px;
    }
    .exp-bar {
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .exp-bar span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #25A194, #6366f1);
    }
    .exp-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .exp-board table { min-width: 980px; }
    .exp-date {
        min-width: 88px;
    }
    .exp-date strong { display: block; color: #0f172a; }
    .exp-date small { color: #94a3b8; font-size: 11px; font-weight: 600; }
    .exp-payee {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .exp-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
        color: #fff;
        flex-shrink: 0;
    }
    .exp-tone-0 { background: linear-gradient(135deg, #25A194, #0f766e); }
    .exp-tone-1 { background: linear-gradient(135deg, #6366f1, #4338ca); }
    .exp-tone-2 { background: linear-gradient(135deg, #f59e0b, #b45309); }
    .exp-tone-3 { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .exp-tone-4 { background: linear-gradient(135deg, #ec4899, #be185d); }
    .exp-tone-5 { background: linear-gradient(135deg, #a855f7, #7e22ce); }
    .exp-method {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .exp-method-cash { background: rgba(37, 161, 148, .12); color: #0f766e; }
    .exp-method-bank { background: rgba(99, 102, 241, .12); color: #4338ca; }
    .exp-method-momo { background: rgba(245, 158, 11, .14); color: #b45309; }
    .exp-method-cheque { background: rgba(139, 92, 246, .12); color: #6d28d9; }
    .exp-amount {
        font-weight: 800;
        color: #0f766e;
        white-space: nowrap;
        font-size: 14px;
    }
    .exp-empty {
        padding: 72px 24px;
        text-align: center;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .exp-empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 18px;
        border-radius: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: #25A194;
        background: rgba(37, 161, 148, .1);
        border: 1px dashed rgba(37, 161, 148, .28);
    }
    .exp-act {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    @media (max-width: 767px) {
        .exp-hero { padding: 22px 18px; }
        .exp-stat-value { font-size: 1.05rem; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Expenses',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Record Expenses', 'active' => true],
        ],
        'title' => 'Record Expenses',
        'subtitle' => 'Log what the school spends and see totals by day, month, year, and category.',
        'actions' => '
            <div class="d-flex flex-wrap gap-2">
                <a href="'.route('expense-categories').'" class="exp-quick-link"><i class="ri-price-tag-3-line"></i> Categories</a>
                <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                    <i class="ri-add-large-line"></i> Record Expense
                </button>
            </div>
        ',
    ])

    <div class="exp-hero d-flex align-items-start gap-16">
        <span class="exp-hero-icon"><i class="ri-wallet-3-line"></i></span>
        <div class="position-relative" style="z-index:1;">
            <div class="exp-hero-title">School spending ledger</div>
            <p class="text-sm text-secondary-light mb-0" style="max-width:640px;">
                Capture every outgoing payment — utilities, feeding, repairs, transport — and watch where the money goes.
            </p>
            <div class="exp-hero-tags">
                <span class="exp-hero-tag"><i class="ri-flashlight-line"></i> Utilities</span>
                <span class="exp-hero-tag"><i class="ri-restaurant-line"></i> Feeding</span>
                <span class="exp-hero-tag"><i class="ri-tools-line"></i> Maintenance</span>
                <span class="exp-hero-tag"><i class="ri-bus-line"></i> Transport</span>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="exp-stat">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                    <p class="text-secondary-light text-sm mb-0">Today</p>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-calendar-check-line"></i></span>
                </div>
                <p class="exp-stat-value">{{ \App\Support\Money::ghs($stats['today']) }}</p>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="exp-stat">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                    <p class="text-secondary-light text-sm mb-0">This month</p>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-calendar-line"></i></span>
                </div>
                <p class="exp-stat-value">{{ \App\Support\Money::ghs($stats['month']) }}</p>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="exp-stat">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                    <p class="text-secondary-light text-sm mb-0">This year</p>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-calendar-2-line"></i></span>
                </div>
                <p class="exp-stat-value">{{ \App\Support\Money::ghs($stats['year']) }}</p>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="exp-stat">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-12">
                    <p class="text-secondary-light text-sm mb-0">{{ $hasFilters ? 'Matching records' : 'All records' }}</p>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-file-list-3-line"></i></span>
                </div>
                <p class="exp-stat-value">{{ number_format($stats['records']) }}</p>
            </div>
        </div>
    </div>

    <div class="exp-board mb-24">
        <div class="card-head d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Expense records</h6>
                <p class="text-sm text-secondary-light mb-0">Filter by date, category, or how the money was paid.</p>
            </div>
            @if($hasFilters)
                <a href="{{ route('expenses') }}" class="exp-quick-link"><i class="ri-refresh-line"></i> Clear filters</a>
            @endif
        </div>

        <form method="GET" action="{{ route('expenses') }}" class="exp-filter">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-xl-2">
                    <label class="text-sm fw-semibold d-block mb-8">From</label>
                    <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="text-sm fw-semibold d-block mb-8">To</label>
                    <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label class="text-sm fw-semibold d-block mb-8">Category</label>
                    <select class="form-control form-select" name="expense_category_id">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['expense_category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label class="text-sm fw-semibold d-block mb-8">Payment method</label>
                    <select class="form-control form-select" name="payment_method">
                        <option value="">All methods</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method }}" @selected(($filters['payment_method'] ?? '') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2">
                    <button type="submit" class="btn btn-primary-600 w-100"><i class="ri-search-line"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="exp-spend">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-12">
                <div>
                    <div class="text-sm text-secondary-light mb-6">Spend in this view</div>
                    <div class="exp-spend-total">{{ \App\Support\Money::ghs($filteredTotal) }}</div>
                </div>
                <span class="ac-pill ac-pill-teal">
                    <i class="ri-hashtag"></i>
                    {{ number_format($filteredCount) }} {{ $filteredCount === 1 ? 'record' : 'records' }}
                </span>
            </div>
            @if($categoryTotals->isNotEmpty())
                <div class="exp-cat-grid">
                    @foreach($categoryTotals as $row)
                        <div class="exp-cat-card">
                            <small>{{ $row['name'] }}</small>
                            <strong>{{ \App\Support\Money::ghs($row['total']) }}</strong>
                            <div class="exp-bar"><span style="width: {{ min(100, ($row['total'] / $maxCategoryTotal) * 100) }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($expenses->isEmpty())
            <div class="exp-empty">
                <div class="exp-empty-icon"><i class="ri-wallet-3-line"></i></div>
                <h6 class="fw-semibold mb-6">{{ $hasFilters ? 'No matching expenses' : 'No expenses yet' }}</h6>
                <p class="text-sm text-secondary-light mb-16 mx-auto" style="max-width:420px;">
                    {{ $hasFilters ? 'Try a wider date range or clear the filters.' : 'Record the first payment to start tracking school spending.' }}
                </p>
                <button type="button" class="my-sidebar-btn btn btn-primary-600">
                    <i class="ri-add-line"></i> Record expense
                </button>
            </div>
        @else
            <div class="exp-scroll">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Paid to</th>
                            <th>Category</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Recorded by</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            @php
                                $tone = ($expense->expense_category_id ?? 0) % 6;
                                $initials = strtoupper(substr(preg_replace('/\s+/', '', $expense->payee) ?: 'EX', 0, 2));
                            @endphp
                            <tr>
                                <td>
                                    <div class="exp-date">
                                        <strong>{{ $expense->expense_date->format('d M Y') }}</strong>
                                        <small>{{ $expense->expense_date->format('l') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="exp-payee">
                                        <span class="exp-avatar exp-tone-{{ $tone }}">{{ $initials }}</span>
                                        <div>
                                            <div class="fw-semibold">{{ $expense->payee }}</div>
                                            @if($expense->notes)
                                                <div class="text-sm text-secondary-light">{{ \Illuminate\Support\Str::limit($expense->notes, 42) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ac-pill ac-pill-indigo">{{ $expense->category?->name ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="exp-method {{ $methodPills[$expense->payment_method] ?? 'exp-method-cash' }}">
                                        <i class="{{ $methodIcons[$expense->payment_method] ?? 'ri-wallet-line' }}"></i>
                                        {{ $expense->payment_method }}
                                    </span>
                                </td>
                                <td class="text-secondary-light">{{ $expense->reference ?: '—' }}</td>
                                <td class="exp-amount">{{ \App\Support\Money::ghs($expense->amount) }}</td>
                                <td class="text-sm">{{ $expense->recorder?->name ?: '—' }}</td>
                                <td>
                                    <div class="d-flex gap-8 justify-content-end">
                                        <button type="button"
                                            data-url="{{ route('get-expense-id', $expense->id) }}"
                                            class="edit-sidebar-btn btn btn-sm btn-outline-primary-600 exp-act show-expense-edit"
                                            title="Edit">
                                            <i class="ri-edit-2-line"></i>
                                        </button>
                                        <form method="POST" action="{{ route('delete-expense-process') }}"
                                            data-confirm-title="Delete this expense?"
                                            data-confirm="This cannot be undone.">
                                            @csrf
                                            <input type="hidden" name="expense_id" value="{{ $expense->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger-600 exp-act" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-24">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>

@include('expenses.modals.add-expense-modal')
@include('expenses.modals.edit-expense-modal')
@endsection

@section('scripts')
<script>
    $('.my-sidebar-btn').on('click', function () {
        $('.my-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-my-sidebar, .overlay').on('click', function () {
        $('.my-sidebar').removeClass('active');
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
    $('body').on('click', '.show-expense-edit', function () {
        $.get($(this).data('url'), function (data) {
            $('#edit_expense_id').val(data.id);
            $('#edit_expense_date').val(data.expense_date);
            $('#edit_expense_amount').val(data.amount);
            $('#edit_expense_payee').val(data.payee);
            $('#edit_expense_payment_method').val(data.payment_method);
            $('#edit_expense_reference').val(data.reference || '');
            $('#edit_expense_year_id').val(data.academic_year_id || '');
            $('#edit_expense_notes').val(data.notes || '');
            var $opt = $('#edit_expense_category_id option[value="' + data.expense_category_id + '"]');
            $opt.prop('disabled', false);
            $('#edit_expense_category_id').val(data.expense_category_id);
        });
    });
</script>
@endsection
