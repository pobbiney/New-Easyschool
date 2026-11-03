@php $pageName = "hr"; $subpageName = "hr-appraisals"; @endphp
@extends('layouts.app')
@section('css')
@include('hr.partials._styles')
@endsection
@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'HR',
        'title' => $appraisal->staff?->full_name.' — '.$appraisal->periodLabel(),
        'actions' => '<a href="'.route('hr-appraisals').'" class="btn btn-outline-primary-600">Back</a>',
    ])
    <div class="card hr-list-wrapper">
        <div class="card-body p-24">
            <p>Overall: <strong>{{ $appraisal->overall }} / 5</strong> · {{ ucfirst($appraisal->status) }}</p>
            <div class="row gy-3">
                @foreach($criteria as $key => $label)
                    <div class="col-md-4">
                        <div class="hr-stat-card">
                            <div class="text-sm text-secondary-light">{{ $label }}</div>
                            <h4 class="mb-0">{{ $appraisal->scores[$key] ?? '—' }}</h4>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($appraisal->comments)
                <p class="mt-20 mb-0">{{ $appraisal->comments }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
