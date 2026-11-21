@php
    $pageName = 'teacher-portal';
    $subpageName = 'teacher-assessments';
    $savedRatings = old('ratings', $assessment?->ratings ?? []);
    $periodQuery = array_filter([
        'academic_year_id' => $period['year_id'] ?? null,
        'academic_term_id' => $period['term_id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $studentPhoto = trim((string) ($student->picture ?? ''));
    $hasStudentPhoto = $studentPhoto !== '' && is_file(public_path($studentPhoto));
    $studentGender = strtolower(trim((string) $student->gender));
    $fallbackPhoto = match (true) {
        in_array($studentGender, ['male', 'm', 'boy'], true) => 'assets/images/avatars/student-male.svg',
        in_array($studentGender, ['female', 'f', 'girl'], true) => 'assets/images/avatars/student-female.svg',
        default => 'assets/images/student-placeholder.svg',
    };
@endphp

@extends('layouts.app')

@section('css')
<style>
    .psa-shell {
        --psa-purple: #6f42c1;
        --psa-purple-dark: #59359a;
        --psa-border: #e5e7eb;
        --psa-green: #4f9d69;
        --psa-green-soft: #edf8f0;
        --psa-blue: #5574b9;
        --psa-blue-soft: #eef3fc;
        --psa-amber: #c68a2b;
        --psa-amber-soft: #fff7e8;
        --psa-rose: #c95878;
        --psa-rose-soft: #fff0f4;
    }

    .psa-student-card,
    .psa-matrix-card {
        border: 1px solid var(--psa-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.05);
    }

    .psa-student-card { padding: 20px 22px; margin-bottom: 22px; }
    .psa-student-photo { width: 64px; height: 64px; object-fit: cover; border-radius: 16px; border: 3px solid #f0eafe; }

    .psa-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f5f3ff;
        color: var(--psa-purple-dark);
        font-size: 12px;
        font-weight: 700;
    }

    .psa-progress-box { min-width: 180px; padding: 12px 14px; border-radius: 12px; background: #f8fafc; border: 1px solid #edf0f4; }
    .psa-progress-track { height: 7px; overflow: hidden; border-radius: 999px; background: #e8eaf0; }
    .psa-progress-bar { width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--psa-purple), #9b6ce5); transition: width .2s ease; }
    .psa-matrix-card { overflow: hidden; }
    .psa-matrix-title { padding: 18px 22px; background: linear-gradient(135deg, var(--psa-purple-dark), var(--psa-purple)); color: #fff; }
    .psa-table-wrap { overflow-x: auto; }
    .psa-table { width: 100%; min-width: 900px; border-collapse: separate; border-spacing: 0; }

    .psa-table th,
    .psa-table td { border-right: 1px solid var(--psa-border); border-bottom: 1px solid var(--psa-border); vertical-align: middle; }
    .psa-table th:last-child,
    .psa-table td:last-child { border-right: 0; }
    .psa-table tbody tr:last-child td { border-bottom: 0; }

    .psa-table th {
        width: 16%;
        padding: 14px 10px;
        text-align: center;
        font-size: 11px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .psa-table th:first-child { width: 36%; padding-left: 22px; text-align: left; color: #fff; background: var(--psa-purple); }
    .psa-heading-green { color: #337b4b; background: var(--psa-green-soft); }
    .psa-heading-blue { color: #405f9f; background: var(--psa-blue-soft); }
    .psa-heading-amber { color: #9a661c; background: var(--psa-amber-soft); }
    .psa-heading-rose { color: #a23c5a; background: var(--psa-rose-soft); }
    .psa-skill-cell { position: sticky; left: 0; z-index: 1; padding: 11px 18px; background: #fff; }
    .psa-category-row td {
        padding: 11px 18px;
        color: var(--psa-purple-dark);
        background: #f4effd;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }
    .psa-skill { display: flex; align-items: center; gap: 12px; color: #374151; font-size: 14px; font-weight: 700; }

    .psa-skill-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        font-size: 16px;
    }

    .psa-tone-green { background: #58a96d; }
    .psa-tone-blue { background: #3e79ba; }
    .psa-tone-purple { background: #7752b5; }
    .psa-tone-orange { background: #e59435; }
    .psa-tone-pink { background: #db4f7c; }
    .psa-tone-cyan { background: #1ba3a3; }
    .psa-tone-yellow { background: #e9ae26; }
    .psa-rating-cell { padding: 8px; text-align: center; background: #fff; }

    .psa-choice {
        width: 42px;
        height: 42px;
        margin: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        cursor: pointer;
        transition: transform .15s ease, background .15s ease;
    }

    .psa-choice:hover { transform: scale(1.08); }
    .psa-choice input { position: absolute; opacity: 0; pointer-events: none; }

    .psa-choice-mark {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid currentColor;
        border-radius: 50%;
    }

    .psa-choice-mark::after { content: ''; width: 10px; height: 10px; border-radius: 50%; background: currentColor; transform: scale(0); transition: transform .15s ease; }
    .psa-choice input:checked + .psa-choice-mark::after { transform: scale(1); }
    .psa-choice:has(input:focus-visible) { outline: 3px solid rgba(111, 66, 193, .22); outline-offset: 2px; }
    .psa-choice-green { color: var(--psa-green); background: var(--psa-green-soft); }
    .psa-choice-blue { color: var(--psa-blue); background: var(--psa-blue-soft); }
    .psa-choice-amber { color: var(--psa-amber); background: var(--psa-amber-soft); }
    .psa-choice-rose { color: var(--psa-rose); background: var(--psa-rose-soft); }
    .psa-footer { padding: 18px 22px; border-top: 1px solid var(--psa-border); background: #fbfbfd; }

    @media (max-width: 767px) {
        .psa-student-card { padding: 16px; }
        .psa-progress-box { width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body psa-shell">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">PRESCHOOL ASSESSMENT</h1>
            <div>
                <a href="{{ route('teacher-assessments', $periodQuery) }}" class="text-secondary-light hover-text-primary hover-underline">Students</a>
                <span class="text-secondary-light"> / {{ $student->full_name }}</span>
            </div>
        </div>
        <a href="{{ route('teacher-assessments', $periodQuery) }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-arrow-left-line"></i> Back to Students
        </a>
    </div>

    <div class="psa-student-card d-flex align-items-center justify-content-between flex-wrap gap-16">
        <div class="d-flex align-items-center gap-16">
            <img src="{{ asset($hasStudentPhoto ? $studentPhoto : $fallbackPhoto) }}" alt="{{ $student->full_name }}" class="psa-student-photo">
            <div>
                <h5 class="fw-semibold mb-6">{{ $student->full_name }}</h5>
                <div class="d-flex flex-wrap gap-8">
                    <span class="psa-meta-pill"><i class="ri-id-card-line"></i> {{ $student->student_id }}</span>
                    <span class="psa-meta-pill"><i class="ri-school-line"></i> {{ $schoolClass->name }}</span>
                    <span class="psa-meta-pill"><i class="ri-calendar-line"></i> {{ $period['term_name'] ?? 'Term not set' }} · {{ $period['year_name'] ?? 'Year not set' }}</span>
                </div>
            </div>
        </div>
        <div class="psa-progress-box">
            <div class="d-flex justify-content-between align-items-center mb-8">
                <span class="text-sm fw-semibold">Completion</span>
                <span class="text-sm fw-bold text-primary-600" id="psaProgressText">0 / {{ count($skills) }}</span>
            </div>
            <div class="psa-progress-track"><div class="psa-progress-bar" id="psaProgressBar"></div></div>
        </div>
    </div>

    @if(!$period['year_id'] || !$period['term_id'])
        <div class="alert alert-warning mb-24">Set a default academic year and term in School Settings before saving this assessment.</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-24">
            <div class="fw-semibold mb-4">Please complete the assessment.</div>
            <ul class="mb-0 ps-18">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher-preschool-student-assessment-save', $student) }}" id="preschoolAssessmentForm">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $period['year_id'] }}">
        <input type="hidden" name="academic_term_id" value="{{ $period['term_id'] }}">

        <div class="psa-matrix-card">
            <div class="psa-matrix-title d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div>
                    <h5 class="text-white fw-bold mb-4">Academic &amp; Developmental Progress</h5>
                    <p class="mb-0 text-white opacity-75 text-sm">Select one developmental rating for every registered course.</p>
                </div>
                @if($assessment)
                    <span class="badge bg-white text-primary-600 px-12 py-8 radius-8">
                        <i class="ri-checkbox-circle-line me-1"></i> Last saved {{ $assessment->updated_at->format('d M Y, H:i') }}
                    </span>
                @endif
            </div>

            <div class="psa-table-wrap">
                <table class="psa-table">
                    <thead>
                        <tr>
                            <th>Academic &amp; Developmental Progress</th>
                            @foreach($ratingOptions as $ratingKey => $rating)
                                <th class="psa-heading-{{ $rating['tone'] }}">{{ $rating['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentCourseCategory = null; @endphp
                        @forelse($skills as $skillKey => $skill)
                            @if($currentCourseCategory !== $skill['category'])
                                @php $currentCourseCategory = $skill['category']; @endphp
                                <tr class="psa-category-row">
                                    <td colspan="{{ count($ratingOptions) + 1 }}">
                                        <i class="{{ $skill['icon'] }} me-2"></i>{{ $skill['category'] }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="psa-skill-cell">
                                    <div class="psa-skill">
                                        <span class="psa-skill-icon psa-tone-{{ $skill['tone'] }}"><i class="{{ $skill['icon'] }}"></i></span>
                                        <span>{{ $skill['label'] }}</span>
                                    </div>
                                </td>
                                @foreach($ratingOptions as $ratingKey => $rating)
                                    <td class="psa-rating-cell">
                                        <label class="psa-choice psa-choice-{{ $rating['tone'] }}" title="{{ $rating['label'] }}">
                                            <input
                                                type="radio"
                                                name="ratings[{{ $skillKey }}]"
                                                value="{{ $ratingKey }}"
                                                aria-label="{{ $skill['label'] }}: {{ $rating['label'] }}"
                                                {{ ($savedRatings[$skillKey] ?? null) === $ratingKey ? 'checked' : '' }}
                                                required
                                            >
                                            <span class="psa-choice-mark"></span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($ratingOptions) + 1 }}" class="text-center py-40 text-secondary-light">
                                    <i class="ri-book-open-line d-block text-3xl mb-8"></i>
                                    No active courses are registered for {{ $schoolClass->name }} in this academic period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="psa-footer d-flex align-items-center justify-content-between flex-wrap gap-12">
                <p class="text-sm text-secondary-light mb-0">
                    <i class="ri-information-line me-1"></i>
                    @if(count($skills))
                        All {{ count($skills) }} registered courses must be rated before saving.
                    @else
                        Register courses for this class and academic period before assessing the student.
                    @endif
                </p>
                <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6" {{ (!$period['year_id'] || !$period['term_id'] || !count($skills)) ? 'disabled' : '' }}>
                    <i class="ri-save-line"></i> {{ $assessment ? 'Update Assessment' : 'Save Assessment' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const form = document.getElementById('preschoolAssessmentForm');
    const total = {{ count($skills) }};
    const progressText = document.getElementById('psaProgressText');
    const progressBar = document.getElementById('psaProgressBar');

    function updateProgress() {
        const completed = form.querySelectorAll('input[type="radio"]:checked').length;
        progressText.textContent = completed + ' / ' + total;
        progressBar.style.width = (total ? ((completed / total) * 100) : 0) + '%';
    }

    form.addEventListener('change', updateProgress);
    updateProgress();
})();
</script>
@endsection
