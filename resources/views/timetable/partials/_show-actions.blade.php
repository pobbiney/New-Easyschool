@php
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ]);
@endphp
<div class="d-flex flex-wrap gap-8">
    <a class="btn btn-primary-600" href="{{ route('timetable-periods', ['school_class_id' => $class->id, 'day' => 1] + $periodQuery) }}">
        <i class="ri-time-line"></i> Edit week
    </a>
    <a class="btn btn-outline-primary-600" target="_blank" href="{{ route('timetable-print', ['class' => $class->id] + $printQuery) }}">
        <i class="ri-printer-line"></i> Print
    </a>
</div>
