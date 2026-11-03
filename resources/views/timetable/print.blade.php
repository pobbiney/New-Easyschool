@extends('layouts.print')

@section('css')
<style>
    @page { size: A4 landscape; margin: 6mm; }
    .print-sheet { max-width: 1100px; }
    .tt-fill { min-height: 0; }
    .tt-print { width: 100%; border-collapse: collapse; margin: 8px 40px 28px; width: calc(100% - 80px); }
    .tt-print th, .tt-print td {
        border: 1px solid #cbd5e1;
        padding: 8px 8px;
        font-size: 12px;
        vertical-align: middle;
    }
    .tt-print th {
        background: #f0fdfa;
        color: #0f766e;
        font-size: 11px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .tt-print .time {
        width: 140px;
        font-weight: 700;
        background: #f8fafc;
        white-space: nowrap;
    }
    .tt-print .time small { display: block; font-weight: 500; color: #64748b; }
    .tt-print .kind {
        text-align: center;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        background: #f8fafc;
    }
    .tt-print .kind.break { background: #fffbeb; color: #b45309; }
    .tt-print .kind.assembly { background: #eef2ff; color: #4338ca; }
    .tt-print .subject { font-weight: 700; }
    .tt-print .teacher { display: block; color: #64748b; font-size: 11px; margin-top: 2px; }
    .tt-note { padding: 0 40px 32px; font-size: 12px; color: #64748b; }

    @media print {
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .print-sheet {
            max-width: none;
            width: 100%;
            min-height: 198mm;
            display: flex;
            flex-direction: column;
            box-shadow: none;
            border-radius: 0;
        }

        #printSheet {
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: avoid;
            break-after: avoid;
        }

        .letterhead {
            padding: 5mm 6mm 3mm;
            gap: 14px;
            flex-shrink: 0;
        }

        .letterhead-logo {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            padding: 6px;
        }

        .letterhead-school {
            font-size: 22px;
            margin-bottom: 3px;
        }

        .letterhead-motto {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .letterhead-meta {
            font-size: 10px;
            gap: 4px 14px;
        }

        .qr-block {
            min-width: 118px !important;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .qr-block > div:first-child {
            font-size: 10px !important;
        }

        .qr-block > div:nth-child(2) {
            font-size: 17px !important;
            margin: 5px 0 2px !important;
        }

        .doc-head {
            padding: 3mm 6mm;
            flex-shrink: 0;
        }

        .doc-head h1 {
            font-size: 14px;
            letter-spacing: 2px;
        }

        .doc-head p {
            font-size: 10px;
            margin-top: 5px;
        }

        .tt-fill {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            padding: 0 6mm;
            min-height: 0;
        }

        .tt-print {
            flex: 1;
            width: 100%;
            height: 100%;
            margin: 0;
            table-layout: fixed;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .tt-print th,
        .tt-print td {
            padding: 6px 8px;
            font-size: 11px;
            line-height: 1.25;
        }

        .tt-print th {
            font-size: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .tt-print .time {
            width: 11%;
        }

        .tt-print .time small {
            font-size: 9px;
        }

        .tt-print .teacher {
            font-size: 9px;
            margin-top: 2px;
        }

        .tt-note {
            padding: 2mm 6mm 0;
            font-size: 8px;
            margin: 0;
            flex-shrink: 0;
        }

        .tt-print .kind,
        .tt-print .time,
        .tt-print th {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endsection

@section('content')
<div class="print-sheet" id="printSheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
            <i class="ri-close-line"></i> Close
        </button>
    </div>

    <header class="letterhead">
        <div class="letterhead-logo">
            <img src="{{ $logoUrl }}" alt="{{ $school->name ?: 'School' }}">
        </div>
        <div>
            <h2 class="letterhead-school">{{ $school->name ?: 'EasySchool' }}</h2>
            @if(!empty($school->motto))
                <p class="letterhead-motto">"{{ $school->motto }}"</p>
            @endif
            <div class="letterhead-meta">
                @if(!empty($school->address))
                    <span><i class="ri-map-pin-line"></i> {{ $school->address }}</span>
                @endif
                @if(!empty($school->phone))
                    <span><i class="ri-phone-line"></i> {{ $school->phone }}</span>
                @endif
            </div>
        </div>
        <div class="qr-block" style="min-width:132px;">
            <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--brand-dark);">Timetable</div>
            <div style="font-size:16px;font-weight:800;margin:6px 0 2px;">{{ $class->name }}</div>
            <div class="qr-block-label">{{ $period['year_name'] ?? '' }} {{ $period['term_name'] ?? '' }}</div>
        </div>
    </header>

    <div class="doc-head">
        <h1>{{ $class->name }} — class timetable</h1>
        <p>
            {{ $class->category?->name ?: 'Class' }}
            @if($class->classTeacher) &nbsp;&bull;&nbsp; Class teacher: {{ $class->classTeacher->full_name }} @endif
            @if(!empty($period['year_name'])) &nbsp;&bull;&nbsp; {{ $period['year_name'] }} · {{ $period['term_name'] }} @endif
        </p>
    </div>

    @if(! $timetable)
        <p class="tt-note">No timetable has been generated for this class in the selected period.</p>
    @else
        <div class="tt-fill">
            <table class="tt-print">
                <thead>
                    <tr>
                        <th>Time</th>
                        @foreach($days as $dayName)
                            <th>{{ $dayName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($periods as $slot)
                        <tr>
                            <td class="time">
                                {{ $slot->label }}
                                <small>{{ $slot->timeLabel() }}</small>
                            </td>
                            @foreach($days as $day => $dayName)
                                @if($slot->kind !== 'lesson')
                                    <td class="kind {{ $slot->kind }}">{{ $slot->label }}</td>
                                @else
                                    @php $entry = $grid[$day][$slot->id] ?? null; @endphp
                                    <td>
                                        @if($entry?->course)
                                            <span class="subject">{{ $entry->course->name }}</span>
                                            @if($entry->teacher)
                                                <span class="teacher">{{ $entry->teacher->full_name }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="tt-note">Set per class, Monday to Friday. Saved {{ $timetable->generated_at?->format('d M Y H:i') }}.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
(function () {
    var pageMarginMm = 6;
    var pageHeightMm = 210 - (pageMarginMm * 2);

    function fillTimetablePage() {
        var sheet = document.getElementById('printSheet');
        var table = sheet && sheet.querySelector('.tt-print');
        if (!sheet || !table) return;

        var pageHeightPx = pageHeightMm * (96 / 25.4);
        sheet.style.minHeight = pageHeightPx + 'px';

        var fixedParts = [
            sheet.querySelector('.letterhead'),
            sheet.querySelector('.doc-head'),
            sheet.querySelector('.tt-note'),
        ].filter(Boolean);

        var fixedHeight = fixedParts.reduce(function (sum, el) {
            return sum + el.offsetHeight;
        }, 6);

        var fill = sheet.querySelector('.tt-fill');
        var available = Math.max(pageHeightPx - fixedHeight, 0);

        if (fill) {
            fill.style.height = available + 'px';
        }

        table.style.height = available + 'px';

        var thead = table.querySelector('thead');
        var rows = table.querySelectorAll('tbody tr');
        if (!thead || !rows.length) return;

        var rowHeight = (available - thead.offsetHeight) / rows.length;
        rows.forEach(function (row) {
            row.style.height = rowHeight + 'px';
        });
    }

    function resetTimetablePage() {
        var sheet = document.getElementById('printSheet');
        if (!sheet) return;

        sheet.style.minHeight = '';

        var fill = sheet.querySelector('.tt-fill');
        if (fill) fill.style.height = '';

        var table = sheet.querySelector('.tt-print');
        if (table) {
            table.style.height = '';
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.height = '';
            });
        }
    }

    window.addEventListener('beforeprint', fillTimetablePage);
    window.addEventListener('afterprint', resetTimetablePage);
})();
</script>
@endsection
