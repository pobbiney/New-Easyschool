<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .muted { color: #64748b; font-size: 10px; margin: 0 0 12px; }
        .school { font-size: 13px; font-weight: bold; color: #1a7a70; margin-bottom: 2px; }
        .logo { width: 42px; height: 42px; object-fit: contain; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #e6f4f2; color: #0f766e; font-size: 9px; text-transform: uppercase; }
        .money { font-weight: bold; white-space: nowrap; }
        .totals { margin: 0 0 10px; }
        .totals span { display: inline-block; margin: 0 12px 6px 0; padding: 4px 8px; background: #f0fdfa; border: 1px solid #99f6e4; }
    </style>
</head>
<body>
    <img class="logo" src="{{ $school->logoFilePath() }}" alt="{{ $school->name ?: 'School' }}">
    <div class="school">{{ $school->name ?: 'EasySchool' }}</div>
    <h1>{{ $report['title'] }}</h1>
    <p class="muted">{{ $report['subtitle'] }} · Generated {{ $report['printed_at'] }} · {{ number_format(count($report['rows'])) }} rows</p>

    @if($report['totals'])
        <div class="totals">
            @foreach($report['totals'] as $total)
                <span><strong>{{ $total['label'] }}:</strong> {{ $total['value'] }}</span>
            @endforeach
        </div>
    @endif

    @if(count($report['rows']) === 0)
        <p>No records match these filters.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach($report['columns'] as $column)
                        <th>{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($report['rows'] as $row)
                    <tr>
                        @foreach($report['columns'] as $column)
                            <td class="{{ !empty($column['money']) ? 'money' : '' }}">{{ $row[$column['key']] ?? '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
