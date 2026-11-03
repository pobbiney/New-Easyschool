@php
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<form method="GET" action="{{ $periodFilterAction ?? url()->current() }}" class="ah-period-filter d-flex flex-wrap align-items-end gap-3" id="ahPeriodFilter">
    @foreach(request()->except(['academic_year_id', 'academic_term_id', 'page']) as $key => $value)
        @if(is_array($value))
            @foreach($value as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div>
        <label for="ah_academic_year_id" class="form-label text-xs fw-semibold text-secondary-light mb-4">Academic Year</label>
        <select name="academic_year_id" id="ah_academic_year_id" class="form-select radius-4" style="min-width:160px;">
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected((string) ($period['year_id'] ?? '') === (string) $year->id)>{{ $year->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="ah_academic_term_id" class="form-label text-xs fw-semibold text-secondary-light mb-4">Term</label>
        <select name="academic_term_id" id="ah_academic_term_id" class="form-select radius-4" style="min-width:140px;">
            @foreach($academicTerms as $term)
                <option value="{{ $term->id }}" @selected((string) ($period['term_id'] ?? '') === (string) $term->id)>{{ $term->name }}</option>
            @endforeach
        </select>
    </div>

    @if(!empty($period['year_name']) && !empty($period['term_name']))
        <div class="pb-1">
            <span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> {{ $period['year_name'] }} · {{ $period['term_name'] }}</span>
        </div>
    @endif
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ahPeriodFilter');
    if (!form) {
        return;
    }

    form.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', function () {
            form.submit();
        });
    });
});
</script>
