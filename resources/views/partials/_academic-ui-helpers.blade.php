@php
    $gradePillClass = function (?string $grade): string {
        $g = strtoupper(trim((string) $grade));
        return match ($g[0] ?? '') {
            'A' => 'ac-pill-grade-a',
            'B' => 'ac-pill-grade-b',
            'C' => 'ac-pill-grade-c',
            'D' => 'ac-pill-grade-d',
            'F' => 'ac-pill-grade-f',
            default => 'ac-pill-slate',
        };
    };

    $typePillClass = function (?string $type): string {
        return 'ac-pill-' . ($type ?: 'slate');
    };

    $attendancePillClass = function (?string $status): string {
        return 'ac-pill-' . ($status ?: 'slate');
    };
@endphp
