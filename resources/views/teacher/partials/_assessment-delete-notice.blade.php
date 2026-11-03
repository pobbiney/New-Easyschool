@php
    $lockedDeleteCount = $assessments->filter(fn ($assessment) => $assessment->hasRecordedScores())->count();
@endphp
<div class="ah-delete-notice" @if($lockedDeleteCount === 0) style="border-color:#e5e7eb;background:#f8fafc;color:#64748b;" @endif>
    <i class="ri-information-line" @if($lockedDeleteCount === 0) style="color:#64748b;" @endif></i>
    <div>
        <p class="ah-delete-notice-title" @if($lockedDeleteCount === 0) style="color:#475569;" @endif>
            @if($lockedDeleteCount > 0)
                Delete is disabled on {{ $lockedDeleteCount }} assessment{{ $lockedDeleteCount === 1 ? '' : 's' }}
            @else
                About assessment deletion
            @endif
        </p>
        <p class="ah-delete-notice-text" @if($lockedDeleteCount === 0) style="color:#64748b;" @endif>
            An assessment can only be deleted when <strong>no marks have been entered</strong>.
            If the <strong>Scored</strong> column shows 1 or more, at least one student already has a recorded score,
            so delete is locked to protect gradebook records. Assessments with <strong>0 scored</strong> can still be deleted.
        </p>
    </div>
</div>
