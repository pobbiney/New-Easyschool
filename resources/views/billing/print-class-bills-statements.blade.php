@extends('layouts.print')

@php $title = 'Class School Bills'; @endphp

@section('content')
<div class="print-sheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print All
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
            <i class="ri-close-line"></i> Close
        </button>
    </div>

    <div class="doc-head" style="margin-bottom: 24px;padding:18px 34px;">
        <h1>Class School Bills</h1>
        <p>
            {{ $className }}
            @if(!empty($filterLabels))
                · {{ implode(' · ', $filterLabels) }}
            @endif
            · {{ $statements->count() }} student{{ $statements->count() === 1 ? '' : 's' }}
            · Printed {{ $printedAt->format('d M Y g:i A') }}
        </p>
    </div>

    @foreach($statements as $statement)
        @include('billing.partials._print-bill-statement-page', [
            'student' => $statement['student'],
            'bills' => $statement['bills'],
            'compulsoryBills' => $statement['compulsoryBills'],
            'optionalBills' => $statement['optionalBills'],
            'arrears' => $statement['arrears'],
            'summary' => $statement['summary'],
            'filterLabels' => $filterLabels,
            'school' => $school,
            'printedAt' => $printedAt,
            'withPageBreak' => ! $loop->last,
        ])
    @endforeach
</div>
@endsection
