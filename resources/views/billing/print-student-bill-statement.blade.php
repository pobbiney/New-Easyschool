@extends('layouts.print')

@php $title = 'School Bill'; @endphp

@section('content')
<div class="print-sheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
            <i class="ri-close-line"></i> Close
        </button>
    </div>

    @include('billing.partials._print-bill-statement-page', [
        'student' => $student,
        'bills' => $bills,
        'compulsoryBills' => $compulsoryBills,
        'optionalBills' => $optionalBills,
        'arrears' => $arrears,
        'summary' => $summary,
        'filterLabels' => $filterLabels,
        'school' => $school,
        'printedAt' => $printedAt,
    ])
</div>
@endsection
