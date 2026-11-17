@extends('layouts.print')

@php $title = 'Class Report Cards — '.$className; @endphp

@section('content')
<div class="print-sheet print-sheet--terminal">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print All
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
            <i class="ri-close-line"></i> Close
        </button>
    </div>

    @foreach($reports as $report)
        @include('teacher.partials._report-card-page', [
            'report' => $report,
            'school' => $school,
            'printedAt' => $printedAt,
            'period' => $period,
            'withPageBreak' => ! $loop->last,
        ])
    @endforeach
</div>
@endsection

@section('css')
<style>
    .print-sheet--terminal::before { display: none; }
    .print-sheet--terminal { box-shadow: none; border-radius: 0; }
</style>
@endsection
