<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Print' }} — {{ $school->name ?? 'EasySchool' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <style>
        :root {
            --brand-primary: #25A194;
            --brand-dark: #1a7a70;
            --brand-light: rgba(37, 161, 148, 0.08);
            --brand-border: rgba(37, 161, 148, 0.22);
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --paper: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #eef2f6;
            color: var(--ink);
            font-family: "Inter", sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        .print-sheet {
            max-width: 820px;
            margin: 0 auto;
            background: var(--paper);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.1);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .print-sheet .report-card-page + .report-card-page {
            margin-top: 28px;
        }

        .print-sheet::before {
            content: "";
            display: block;
            height: 6px;
            background: linear-gradient(90deg, var(--brand-dark), var(--brand-primary));
        }

        .print-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .print-btn-primary {
            background: var(--brand-primary);
            color: #fff;
        }

        .print-btn-secondary {
            background: #fff;
            color: #64748b;
            border: 1px solid var(--line);
        }

        /* Letterhead */
        .letterhead {
            padding: 32px 40px 24px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 24px;
            align-items: center;
            border-bottom: 2px solid var(--brand-primary);
        }

        .qr-block {
            text-align: center;
            padding: 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            min-width: 108px;
        }

        .qr-block-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--muted);
            margin-top: 8px;
            line-height: 1.3;
        }

        #studentQrCode img,
        #studentQrCode canvas,
        .qr-block svg {
            display: block;
            margin: 0 auto;
            width: 120px;
            height: 120px;
        }

        .letterhead-logo {
            width: 92px;
            height: 92px;
            border-radius: 16px;
            background: #fff;
            border: 2px solid var(--brand-border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 8px;
        }

        .letterhead-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .letterhead-school {
            font-size: 26px;
            font-weight: 800;
            color: var(--ink);
            margin: 0 0 4px;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .letterhead-motto {
            font-size: 13px;
            color: var(--brand-primary);
            font-style: italic;
            font-weight: 500;
            margin: 0 0 12px;
        }

        .letterhead-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            font-size: 12px;
            color: var(--muted);
        }

        .letterhead-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .letterhead-meta i {
            color: var(--brand-primary);
            font-size: 14px;
        }

        /* Document title */
        .doc-head {
            text-align: center;
            padding: 22px 40px 18px;
            background: var(--brand-light);
        }

        .doc-head h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--brand-dark);
        }

        .doc-head p {
            margin: 8px 0 0;
            font-size: 12px;
            color: var(--muted);
        }

        /* Student highlight */
        .student-banner {
            margin: 24px 40px;
            padding: 20px 24px;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand-light) 0%, #fff 60%);
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .student-photo {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid var(--brand-primary);
            flex-shrink: 0;
            background: #fff;
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-name {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 6px;
            color: var(--ink);
        }

        .student-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .student-meta-row strong {
            color: var(--brand-primary);
            font-weight: 700;
        }

        .status-pill {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pill.active { background: #dcfce7; color: #15803d; }
        .status-pill.draft { background: #fef3c7; color: #b45309; }
        .status-pill.inactive { background: #fee2e2; color: #b91c1c; }

        /* Sections */
        .print-content {
            padding: 0 40px 32px;
        }

        .info-section {
            margin-bottom: 22px;
        }

        .info-section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--brand-primary);
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-section-title::before {
            content: "";
            width: 4px;
            height: 14px;
            background: var(--brand-primary);
            border-radius: 2px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 9px 0;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 38%;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--muted);
        }

        .info-table td:last-child {
            font-size: 13px;
            font-weight: 500;
            color: var(--ink);
        }

        .info-columns {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .info-box {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 14px 16px;
            background: #fafbfc;
        }

        .info-box-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--brand-dark);
            margin: 0 0 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--line);
        }

        .info-box .info-table td {
            padding: 6px 0;
        }

        .info-box .info-table td:first-child {
            width: 42%;
            font-size: 10px;
        }

        .info-box .info-table td:last-child {
            font-size: 12px;
        }

        /* Signatures */
        .sig-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px dashed var(--line);
        }

        .sig-block {
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #94a3b8;
            margin-top: 44px;
            padding-top: 8px;
            font-size: 11px;
            color: var(--muted);
            font-weight: 500;
        }

        .print-footnote {
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .print-sheet {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            .no-print { display: none !important; }

            .letterhead,
            .doc-head,
            .student-banner,
            .print-sheet::before,
            .qr-block {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 640px) {
            .letterhead,
            .print-content,
            .student-banner,
            .doc-head {
                padding-left: 20px;
                padding-right: 20px;
            }

            .student-banner {
                margin-left: 20px;
                margin-right: 20px;
                flex-direction: column;
                text-align: center;
            }

            .info-columns,
            .sig-row {
                grid-template-columns: 1fr;
            }

            .letterhead {
                grid-template-columns: 1fr;
                text-align: center;
                justify-items: center;
            }
        }
    </style>
    @yield('css')
</head>
<body>
    @yield('content')
    @yield('scripts')
</body>
</html>
