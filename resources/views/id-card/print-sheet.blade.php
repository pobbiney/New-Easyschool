<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print ID Cards</title>
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <style>
        :root {
            --card-dark: {{ $palette['dark'] ?? '#3B0A5C' }};
            --card-accent: {{ $palette['accent'] ?? '#F5C518' }};
            --card-ink: {{ $palette['ink'] ?? '#1A0B2E' }};
        }
        * { box-sizing: border-box; }
        html, body, .idcard-face, .idcard-face * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body {
            margin: 0;
            background: #e2e8f0;
            font-family: "Segoe UI", Arial, sans-serif;
        }
        .sheet-bar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            background: #0f172a;
            color: #fff;
        }
        .sheet-bar a, .sheet-bar button {
            color: #0f172a;
            background: var(--card-accent);
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .sheet-wrap { padding: 24px; }
        .sheet-pair {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 22px;
            margin-bottom: 36px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .sheet-pair:not(:last-child) {
            page-break-after: always;
            break-after: page;
        }
        @include('id-card.partials._card-styles')

        .idcard-face {
            border: 1px solid #d4d4d8;
        }
        .idcard-face-head {
            overflow: hidden;
        }
        .idcard-face-head::after { display: none !important; }
        .idcard-head-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            display: block;
        }
        .idcard-hex {
            position: relative;
            background: none !important;
            clip-path: none !important;
            padding: 0 !important;
        }
        .idcard-hex svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }
        .idcard-hex img {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 68%;
            height: 68%;
            transform: translate(-50%, -50%);
            object-fit: contain;
            z-index: 1;
        }
        .idcard-wave,
        .idcard-stripe,
        .idcard-foot,
        .idcard-foot-accent {
            background: none !important;
            clip-path: none !important;
            display: block;
        }
        .idcard-wave,
        .idcard-stripe {
            width: 100%;
            left: 0;
            right: 0;
        }
        .idcard-foot,
        .idcard-foot-accent {
            width: 100%;
            height: 42px;
        }
        .idcard-barcode {
            background: #fff !important;
            background-image: none !important;
            width: 78px;
            height: 78px;
            padding: 3px;
            overflow: hidden;
        }
        .idcard-barcode svg {
            display: block;
            width: 72px;
            height: 72px;
        }
        .idcard-back-block {
            background: #fbf6e0;
            border: 1px solid color-mix(in srgb, var(--card-accent) 45%, #fff);
        }

        @media print {
            .sheet-bar { display: none !important; }
            body { background: #fff !important; }
            .sheet-wrap { padding: 0; }
            .sheet-pair {
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: center;
                align-items: flex-start;
                gap: 12px;
                margin: 0;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .idcard-face {
                box-shadow: none;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="sheet-bar">
        <span>{{ $people->count() }} ID card{{ $people->count() === 1 ? '' : 's' }} — using Card Settings</span>
        <div>
            <a href="{{ route('print-id-cards', ['tab' => request('tab', 'student')]) }}">Back</a>
            <button type="button" onclick="window.print()">Print</button>
        </div>
    </div>
    <div class="sheet-wrap">
        @foreach ($people as $person)
            <div class="sheet-pair">
                @include('id-card.partials._card-faces', [
                    'person' => $person,
                    'school' => $school,
                    'settings' => $settings,
                    'logoUrl' => $logoUrl,
                    'cardTitle' => $cardTitle,
                    'signatureUrl' => $signatureUrl,
                    'palette' => $palette,
                ])
            </div>
        @endforeach
    </div>
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>
</body>
</html>
