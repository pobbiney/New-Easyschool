@php
    $pageName = 'class-setup';
    $subpageName = 'student-promotion';
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .sp-class-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        padding: 20px 22px;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .sp-class-card:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 161, 148, 0.28);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .sp-class-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .sp-class-name {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        color: var(--primary-600, #25A194);
    }

    .sp-mini-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .sp-mini-stat {
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        background: var(--neutral-50, #f9fafb);
        border: 1px solid var(--neutral-200, #e5e7eb);
    }

    .sp-mini-stat strong {
        display: block;
        font-size: 1.125rem;
        line-height: 1.2;
    }

    .sp-mini-stat span {
        font-size: 0.6875rem;
        color: var(--neutral-500, #6b7280);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
    }

    .sp-mini-stat.is-success strong { color: #15803d; }
    .sp-mini-stat.is-danger strong { color: #b91c1c; }
    .sp-mini-stat.is-muted strong { color: #475569; }

    .sp-meta {
        font-size: 0.8125rem;
        color: var(--neutral-500, #6b7280);
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">CLASS SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Student Promotion</span>
            </div>
        </div>
        <a href="{{ route('promotion-settings') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-settings-3-line"></i> Promotion Settings
        </a>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;"><i class="ri-user-shared-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Student Promotion</h5>
            <p class="text-sm text-secondary-light mb-0">
                Review each class using total subject scores for the selected term. Students who meet the pass mark can be promoted;
                those below the pass mark remain in the class.
            </p>
        </div>
    </div>

    <div class="card ac-list-wrapper mb-24">
        <div class="card-body p-20">
            @include('teacher.partials._academic-period-filter', [
                'periodFilterAction' => route('student-promotion'),
            ])
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Classes</p>
                <h4 class="fw-semibold mb-0">{{ $totals['classes'] }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Students</p>
                <h4 class="fw-semibold mb-0">{{ $totals['students'] }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Ready to Promote</p>
                <h4 class="fw-semibold mb-0 text-success-600">{{ $totals['eligible'] }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ac-stat-card">
                <p class="text-secondary-light text-sm mb-4">Below Pass Mark</p>
                <h4 class="fw-semibold mb-0 text-danger-600">{{ $totals['below'] }}</h4>
            </div>
        </div>
    </div>

    @if($summaries->isEmpty())
    <div class="card ac-list-wrapper">
        <div class="text-center py-56 px-24">
            <p class="text-secondary-light mb-0">No active classes found.</p>
        </div>
    </div>
    @else
    <div class="row gy-4">
        @foreach($summaries as $item)
        @php
            $class = $item['class'];
            $counts = $item['counts'];
        @endphp
        <div class="col-xl-4 col-md-6">
            <div class="sp-class-card">
                <div class="sp-class-head">
                    <div>
                        <h6 class="sp-class-name">{{ $class->name }}</h6>
                        @if($class->category)
                            <span class="ac-pill ac-pill-teal">{{ $class->category->name }}</span>
                        @endif
                    </div>
                    <a href="{{ route('student-promotion-class', array_merge(['class' => $class], $periodQuery)) }}" class="btn btn-sm btn-primary-600">
                        Open
                    </a>
                </div>

                <p class="sp-meta mb-0">
                    Pass mark:
                    <strong>{{ $item['minimum'] !== null ? number_format($item['minimum']) : 'Not set' }}</strong>
                    @if($item['next_class'])
                        · Next: <strong>{{ $item['next_class']->name }}</strong>
                    @else
                        · <em>Final class</em>
                    @endif
                </p>

                <div class="sp-mini-stats">
                    <div class="sp-mini-stat is-muted">
                        <strong>{{ $counts['total'] }}</strong>
                        <span>Total</span>
                    </div>
                    <div class="sp-mini-stat is-success">
                        <strong>{{ $counts['eligible'] }}</strong>
                        <span>Promote</span>
                    </div>
                    <div class="sp-mini-stat is-danger">
                        <strong>{{ $counts['below'] }}</strong>
                        <span>Below</span>
                    </div>
                    <div class="sp-mini-stat is-muted">
                        <strong>{{ $counts['promoted'] ?? 0 }}</strong>
                        <span>Done</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
