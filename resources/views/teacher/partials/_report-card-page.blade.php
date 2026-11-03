@once
<style>
    .terminal-report {
        --tr-navy: #000080;
        --tr-ink: #000;
        border: 2px solid #000;
        font-family: "Times New Roman", Times, serif;
        color: var(--tr-ink);
        font-size: 13px;
        line-height: 1.35;
        background: #fff;
    }

    .terminal-report__header {
        display: grid;
        grid-template-columns: 95px 1fr 95px;
        gap: 12px;
        align-items: start;
        padding: 14px 16px 10px;
    }

    .terminal-report__photo,
    .terminal-report__logo {
        width: 95px;
        height: 95px;
        border: 1px solid #000;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        flex-shrink: 0;
    }

    .terminal-report__photo img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
    }

    .terminal-report__logo img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        padding: 4px;
    }

    .terminal-report__school {
        text-align: center;
    }

    .terminal-report__school-name {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 700;
        letter-spacing: 0.4px;
        color: var(--tr-navy);
        text-transform: uppercase;
        line-height: 1.2;
    }

    .terminal-report__school-meta {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }

    .terminal-report__banner {
        background: var(--tr-navy);
        color: #fff;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        padding: 7px 12px;
    }

    .terminal-report__info {
        padding: 10px 16px 8px;
    }

    .terminal-report__info-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 24px;
        margin-bottom: 8px;
    }

    .terminal-report__field {
        display: flex;
        align-items: baseline;
        gap: 6px;
        min-width: 0;
    }

    .terminal-report__label {
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        font-size: 12px;
    }

    .terminal-report__value {
        flex: 1;
        border-bottom: 1px solid #000;
        min-height: 18px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0 4px 1px;
    }

    .terminal-report__table-wrap {
        padding: 0 16px 10px;
    }

    .terminal-report__table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .terminal-report__table th,
    .terminal-report__table td {
        border: 1px solid #000;
        text-align: center;
        vertical-align: middle;
        padding: 5px 4px;
    }

    .terminal-report__table th {
        background: var(--tr-navy);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }

    .terminal-report__table td {
        font-weight: 700;
        font-size: 12px;
        height: 26px;
    }

    .terminal-report__table td.subject-name {
        text-align: left;
        padding-left: 8px;
        text-transform: uppercase;
        font-size: 11px;
    }

    .terminal-report__footer {
        padding: 6px 16px 16px;
    }

    .terminal-report__footer-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .terminal-report__footer-row--split {
        justify-content: space-between;
    }

    .terminal-report__footer-row .terminal-report__value {
        min-width: 120px;
    }

    .terminal-report__footer-row .terminal-report__value--wide {
        flex: 1;
        min-width: 200px;
    }

    @media print {
        .terminal-report {
            border-width: 2px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .terminal-report__banner,
        .terminal-report__table th {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endonce

@php
    use App\Support\MediaUrl;

    $subjectRows = collect($report['subject_grades']);
    $minSubjectRows = 9;
    $emptyRows = max(0, $minSubjectRows - $subjectRows->count());
    $attendancePresent = $report['attendance']['present'] ?? 0;
    $attendanceTotal = $report['attendance']['total_days'] ?? 0;
    $studentPhotoUrl = MediaUrl::resolve($report['student']->picture ?? null);
    $schoolLogoUrl = $school?->logoUrl() ?: asset(\App\Models\SchoolSetting::dummyLogoPath());
@endphp

<div class="terminal-report report-card-page" @if(!empty($withPageBreak)) style="page-break-after: always;" @endif>
    <div class="terminal-report__header">
        <div class="terminal-report__photo">
            @if($studentPhotoUrl)
                <img src="{{ $studentPhotoUrl }}" alt="{{ $report['student']->full_name }}">
            @endif
        </div>

        <div class="terminal-report__school">
            <h1 class="terminal-report__school-name">{{ $school->name ?? 'EasySchool' }}</h1>
            @if(!empty($school->address))
                <p class="terminal-report__school-meta">{{ strtoupper($school->address) }}</p>
            @endif
            @if(!empty($school->phone))
                <p class="terminal-report__school-meta">Contact: {{ $school->phone }}</p>
            @endif
            @if(!empty($school->motto))
                <p class="terminal-report__school-meta">Motto: {{ strtoupper($school->motto) }}</p>
            @endif
        </div>

        <div class="terminal-report__logo">
            <img src="{{ $schoolLogoUrl }}" alt="{{ $school->name ?? 'School logo' }}">
        </div>
    </div>

    <div class="terminal-report__banner">Learner's Terminal Report</div>

    <div class="terminal-report__info">
        <div class="terminal-report__info-row">
            <div class="terminal-report__field">
                <span class="terminal-report__label">Name:</span>
                <span class="terminal-report__value">{{ strtoupper($report['student']->full_name) }}</span>
            </div>
            <div class="terminal-report__field">
                <span class="terminal-report__label">Class:</span>
                <span class="terminal-report__value">{{ strtoupper($report['student']->schoolClass?->name ?? '') }}</span>
            </div>
        </div>

        <div class="terminal-report__info-row">
            <div class="terminal-report__field">
                <span class="terminal-report__label">Number on Roll:</span>
                <span class="terminal-report__value">{{ $report['roll_number'] ?? '' }}</span>
            </div>
            <div class="terminal-report__field">
                <span class="terminal-report__label">Position in Class:</span>
                <span class="terminal-report__value">{{ $report['class_position'] ?? '' }}</span>
            </div>
        </div>

        <div class="terminal-report__info-row">
            <div class="terminal-report__field">
                <span class="terminal-report__label">Academic Year:</span>
                <span class="terminal-report__value">{{ strtoupper($period['year_name'] ?? '') }}</span>
            </div>
            <div class="terminal-report__field">
                <span class="terminal-report__label">Term:</span>
                <span class="terminal-report__value">{{ strtoupper($period['term_name'] ?? '') }}</span>
            </div>
        </div>

        <div class="terminal-report__info-row">
            <div class="terminal-report__field">
                <span class="terminal-report__label">Next Term Begins:</span>
                <span class="terminal-report__value"></span>
            </div>
            <div class="terminal-report__field">
                <span class="terminal-report__label">Vacation Date:</span>
                <span class="terminal-report__value"></span>
            </div>
        </div>
    </div>

    <div class="terminal-report__table-wrap">
        <table class="terminal-report__table">
            <thead>
                <tr>
                    <th style="width:28%;">Subject</th>
                    <th style="width:14%;">Class Score (50%)</th>
                    <th style="width:14%;">Exam Score (50%)</th>
                    <th style="width:14%;">Total Score (100%)</th>
                    <th style="width:12%;">Position</th>
                    <th style="width:18%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjectRows as $subject)
                <tr>
                    <td class="subject-name">{{ $subject['course_name'] }}</td>
                    <td>{{ $subject['class_score'] !== null ? $subject['class_score'] : '' }}</td>
                    <td>{{ $subject['exam_score'] !== null ? $subject['exam_score'] : '' }}</td>
                    <td>{{ $subject['total_score'] !== null ? $subject['total_score'] : '' }}</td>
                    <td>{{ $subject['position'] ?? '' }}</td>
                    <td>{{ $subject['remark'] ?? '' }}</td>
                </tr>
                @endforeach

                @for($i = 0; $i < $emptyRows; $i++)
                <tr>
                    <td class="subject-name">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="terminal-report__footer">
        <div class="terminal-report__footer-row terminal-report__footer-row--split">
            <div class="terminal-report__field">
                <span class="terminal-report__label">Attendance:</span>
                <span class="terminal-report__value">
                    @if($attendanceTotal > 0)
                        {{ $attendancePresent }} out of {{ $attendanceTotal }}
                    @endif
                </span>
            </div>
            <div class="terminal-report__field">
                <span class="terminal-report__label">Promoted To:</span>
                <span class="terminal-report__value">{{ strtoupper($report['promotion_label'] ?? '') }}</span>
            </div>
        </div>

        <div class="terminal-report__footer-row">
            <span class="terminal-report__label">Attitude:</span>
            <span class="terminal-report__value terminal-report__value--wide">{{ $report['attitude'] ?? '' }}</span>
        </div>

        <div class="terminal-report__footer-row">
            <span class="terminal-report__label">Conduct:</span>
            <span class="terminal-report__value terminal-report__value--wide">{{ $report['conduct'] ?? '' }}</span>
        </div>

        <div class="terminal-report__footer-row">
            <span class="terminal-report__label">Interest:</span>
            <span class="terminal-report__value terminal-report__value--wide">{{ $report['interest'] ?? '' }}</span>
        </div>

        <div class="terminal-report__footer-row">
            <span class="terminal-report__label">Class Teacher's Remarks:</span>
            <span class="terminal-report__value terminal-report__value--wide">{{ $report['class_teacher_remark'] ?? '' }}</span>
        </div>

        <div class="terminal-report__footer-row">
            <span class="terminal-report__label">Headteacher's Signature:</span>
            <span class="terminal-report__value terminal-report__value--wide">{{ $report['headteacher_signature'] ?? '' }}</span>
        </div>
    </div>
</div>
