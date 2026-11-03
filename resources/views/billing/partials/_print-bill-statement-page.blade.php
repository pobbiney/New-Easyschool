@php
    $logoUrl = $school->logoUrl();
    $schoolName = $school->name ?: 'EasySchool';
    $invoiceNo = 'BILL-' . $student->student_id . '-' . $printedAt->format('Ymd');
    $invoiceToName = $student->guardian_name ?: $student->full_name;
    $invoiceToAddress = $student->guardian_address ?: $student->current_address;
    $invoiceToPhone = $student->guardian_phone ?: ($student->phone ?: $school->phone);
    $invoiceToEmail = $student->guardian_email ?: ($student->email ?: $school->email);
    $compulsoryBills = $compulsoryBills ?? $bills->filter(fn ($bill) => (bool) $bill->billingItem?->is_compulsory)->values();
    $optionalBills = $optionalBills ?? $bills->reject(fn ($bill) => (bool) $bill->billingItem?->is_compulsory)->values();
    $arrears = $arrears ?? collect();
    $compulsoryDue = (float) ($summary['compulsory_due'] ?? $compulsoryBills->sum('amount_due'));
    $optionalDue = (float) ($summary['optional_due'] ?? $optionalBills->sum('amount_due'));
    $amountPaid = (float) ($summary['total_paid'] ?? 0);
    $arrearsTotal = (float) ($summary['arrears'] ?? $arrears->sum('balance'));
    $credit = (float) ($summary['credit_balance'] ?? 0);
    $grandTotal = (float) ($summary['net_payable'] ?? max(($summary['balance'] ?? 0) + $arrearsTotal - $credit, 0));
@endphp

<style>
    .fee-receipt-page {
        --fr-teal: #1a7a70;
        --fr-teal-dark: #145a52;
        --fr-teal-soft: #e8f5f3;
        --fr-ink: #111827;
        --fr-muted: #6b7280;
        --fr-line: #d1d5db;
        position: relative;
        background: #fff;
        color: var(--fr-ink);
        font-family: "Inter", Arial, sans-serif;
        overflow: hidden;
    }

    .fee-receipt-page.has-page-break {
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 2px dashed #e5e7eb;
    }

    .fee-receipt-accent-top {
        position: absolute;
        top: 0;
        right: 0;
        width: 220px;
        height: 72px;
        background: var(--fr-teal);
        clip-path: polygon(18% 0, 100% 0, 100% 100%, 0 100%);
    }

    .fee-receipt-accent-bottom {
        position: absolute;
        left: 0;
        bottom: 0;
        width: 180px;
        height: 56px;
        background: var(--fr-teal);
        clip-path: polygon(0 0, 100% 0, 72% 100%, 0 100%);
    }

    .fee-receipt-bottom-line {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: var(--fr-ink);
    }

    .fee-receipt-inner {
        position: relative;
        z-index: 1;
        padding: 28px 34px 36px;
    }

    .fee-receipt-header {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 16px;
        align-items: center;
        margin-bottom: 28px;
        padding-right: 120px;
    }

    .fee-receipt-logo {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 2px solid var(--fr-teal);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        padding: 6px;
    }

    .fee-receipt-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .fee-receipt-school-name {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        color: var(--fr-teal-dark);
        letter-spacing: 0.4px;
        text-transform: uppercase;
        line-height: 1.15;
    }

    .fee-receipt-school-motto {
        margin: 4px 0 8px;
        font-size: 12px;
        color: var(--fr-muted);
        font-style: italic;
    }

    .fee-receipt-school-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 16px;
        font-size: 11px;
        color: var(--fr-muted);
    }

    .fee-receipt-info-bar {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 18px;
        align-items: start;
        margin-bottom: 22px;
    }

    .fee-receipt-info-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--fr-teal-dark);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .fee-receipt-info-text {
        font-size: 12px;
        line-height: 1.55;
        color: var(--fr-ink);
    }

    .fee-receipt-info-text strong {
        display: block;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .fee-receipt-title-wrap {
        text-align: center;
        padding-top: 8px;
    }

    .fee-receipt-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--fr-ink);
    }

    .fee-receipt-title-line {
        width: 56px;
        height: 3px;
        background: var(--fr-teal);
        margin: 8px auto 0;
    }

    .fee-receipt-meta-right {
        text-align: right;
        font-size: 12px;
        line-height: 1.7;
        color: var(--fr-ink);
    }

    .fee-receipt-meta-right strong {
        color: var(--fr-teal-dark);
    }

    .fee-receipt-student-meta {
        margin: 0 0 14px;
        font-size: 12px;
        color: var(--fr-muted);
    }

    .fee-receipt-student-meta strong {
        color: var(--fr-ink);
    }

    .fee-receipt-section {
        margin: 0 0 8px;
        font-size: 13px;
        font-weight: 800;
        color: var(--fr-teal-dark);
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .fee-receipt-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
    }

    .fee-receipt-table thead th {
        background: var(--fr-teal);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        padding: 10px 12px;
        border: 1px solid var(--fr-teal);
    }

    .fee-receipt-table thead th:nth-child(1) { width: 56px; text-align: center; }
    .fee-receipt-table thead th:nth-child(3) { width: 120px; text-align: right; }

    .fee-receipt-table tbody td {
        padding: 10px 12px;
        border: 1px solid var(--fr-line);
        font-size: 13px;
        vertical-align: top;
    }

    .fee-receipt-table tbody td:first-child {
        text-align: center;
        font-weight: 600;
        color: var(--fr-muted);
    }

    .fee-receipt-table tbody td:last-child {
        text-align: right;
        font-weight: 600;
        white-space: nowrap;
    }

    .fee-receipt-table .fee-desc-sub {
        display: block;
        font-size: 11px;
        color: var(--fr-muted);
        margin-top: 2px;
    }

    .fee-receipt-table tfoot td {
        padding: 10px 12px;
        border: 1px solid var(--fr-line);
        background: #f8fafc;
        font-size: 13px;
        font-weight: 800;
    }

    .fee-receipt-table tfoot td:last-child {
        text-align: right;
        white-space: nowrap;
    }

    .fee-receipt-footer {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 24px;
        align-items: start;
    }

    .fee-receipt-payment-box,
    .fee-receipt-terms {
        font-size: 11px;
        line-height: 1.6;
        color: var(--fr-muted);
    }

    .fee-receipt-payment-box strong,
    .fee-receipt-terms strong {
        display: block;
        font-size: 11px;
        color: var(--fr-teal-dark);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .fee-receipt-summary {
        margin-left: auto;
        width: 100%;
        max-width: 280px;
    }

    .fee-receipt-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 7px 0;
        font-size: 13px;
        border-bottom: 1px solid #eef2f7;
    }

    .fee-receipt-summary-row span:last-child {
        font-weight: 600;
        white-space: nowrap;
    }

    .fee-receipt-total {
        margin-top: 10px;
        background: var(--fr-teal);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.3px;
    }

    .fee-receipt-credit-note {
        margin: 0 0 14px;
        padding: 10px 12px;
        border-radius: 8px;
        background: var(--fr-teal-soft);
        border: 1px solid rgba(26, 122, 112, 0.18);
        font-size: 12px;
        color: var(--fr-teal-dark);
    }

    @media print {
        .fee-receipt-page.has-page-break {
            page-break-after: always;
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .fee-receipt-page,
        .fee-receipt-table thead th,
        .fee-receipt-total,
        .fee-receipt-accent-top,
        .fee-receipt-accent-bottom {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="fee-receipt-page {{ !empty($withPageBreak) ? 'has-page-break' : '' }}">
    <div class="fee-receipt-accent-top"></div>
    <div class="fee-receipt-accent-bottom"></div>
    <div class="fee-receipt-bottom-line"></div>

    <div class="fee-receipt-inner">
        <header class="fee-receipt-header">
            <div class="fee-receipt-logo">
                <img src="{{ $logoUrl }}" alt="{{ $schoolName }}">
            </div>
            <div>
                <h2 class="fee-receipt-school-name">{{ $schoolName }}</h2>
                @if(!empty($school->motto))
                    <p class="fee-receipt-school-motto">{{ $school->motto }}</p>
                @endif
                <div class="fee-receipt-school-meta">
                    @if(!empty($school->address))<span>{{ $school->address }}</span>@endif
                    @if(!empty($school->phone))<span>Tel: {{ $school->phone }}</span>@endif
                    @if(!empty($school->email))<span>{{ $school->email }}</span>@endif
                    @if(!empty($school->website))<span>{{ $school->website }}</span>@endif
                </div>
            </div>
        </header>

        <div class="fee-receipt-info-bar">
            <div>
                <div class="fee-receipt-info-label">Invoice To</div>
                <div class="fee-receipt-info-text">
                    <strong>{{ $invoiceToName }}</strong>
                    @if(!empty($invoiceToAddress))<span>{{ $invoiceToAddress }}</span><br>@endif
                    @if(!empty($invoiceToPhone))<span>Phone: {{ $invoiceToPhone }}</span><br>@endif
                    @if(!empty($invoiceToEmail))<span>Email: {{ $invoiceToEmail }}</span>@endif
                </div>
            </div>

            <div class="fee-receipt-title-wrap">
                <h1 class="fee-receipt-title">School Bill</h1>
                <div class="fee-receipt-title-line"></div>
            </div>

            <div class="fee-receipt-meta-right">
                <div><strong>Invoice No:</strong> {{ $invoiceNo }}</div>
                <div><strong>Invoice Date:</strong> {{ $printedAt->format('d-m-Y') }}</div>
                <div><strong>Student ID:</strong> {{ $student->student_id }}</div>
            </div>
        </div>

        <p class="fee-receipt-student-meta">
            Student: <strong>{{ $student->full_name }}</strong>
            · Class: <strong>{{ $student->class_name }}</strong>
            · Category: <strong>{{ $student->schoolClass?->category?->name ?: '—' }}</strong>
            @if(!empty($filterLabels))
                · {{ implode(' · ', $filterLabels) }}
            @endif
        </p>

        @if($credit > 0)
            <p class="fee-receipt-credit-note">
                This student has <strong>₵{{ number_format($credit, 2) }}</strong> credit from overpayment. It is deducted from the amount payable below.
            </p>
        @endif

        @foreach([
            ['title' => 'Compulsory bills', 'rows' => $compulsoryBills, 'total' => $compulsoryDue, 'empty' => 'No compulsory bills for this period.'],
            ['title' => 'Non-compulsory bills', 'rows' => $optionalBills, 'total' => $optionalDue, 'empty' => 'No non-compulsory bills for this period.'],
        ] as $group)
            <h3 class="fee-receipt-section">{{ $group['title'] }}</h3>
            <table class="fee-receipt-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Fee Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($group['rows'] as $index => $bill)
                        @php
                            $period = collect([$bill->setup?->academicTerm?->name, $bill->setup?->academicYear?->name])->filter()->join(' · ');
                        @endphp
                        <tr>
                            <td>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                {{ $bill->billingItem?->name ?: 'Bill Item' }}
                                <span class="fee-desc-sub">@if($period){{ $period }} · @endif Balance: ₵{{ number_format($bill->balance, 2) }} · {{ $bill->status }}</span>
                            </td>
                            <td>₵{{ number_format($bill->amount_due, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;color:var(--fr-muted);padding:18px;">
                                {{ $group['empty'] }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($group['rows']->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="2">Total</td>
                            <td>₵{{ number_format($group['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @endforeach

        @if($arrears->isNotEmpty())
            <h3 class="fee-receipt-section">Arrears from previous terms</h3>
            <table class="fee-receipt-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Fee Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($arrears as $index => $bill)
                        @php
                            $period = collect([
                                $bill->academicTerm?->name ?? $bill->setup?->academicTerm?->name,
                                $bill->academicYear?->name ?? $bill->setup?->academicYear?->name,
                            ])->filter()->join(' · ');
                        @endphp
                        <tr>
                            <td>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                {{ $bill->billingItem?->name ?: 'Bill Item' }}
                                <span class="fee-desc-sub">{{ $period ?: 'Previous term' }} · Outstanding</span>
                            </td>
                            <td>₵{{ number_format($bill->balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Arrears total</td>
                        <td>₵{{ number_format($arrearsTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="fee-receipt-footer">
            <div>
                <div class="fee-receipt-payment-box">
                    <strong>Payment Information</strong>
                    @if(!empty($school->name))
                        Pay fees to <strong>{{ $school->name }}</strong><br>
                    @endif
                    @if(!empty($school->phone))
                        Phone: {{ $school->phone }}<br>
                    @endif
                    @if(!empty($school->email))
                        Email: {{ $school->email }}<br>
                    @endif
                    @if(!empty($school->website))
                        Website: {{ $school->website }}<br>
                    @endif
                    @if(!empty($school->address))
                        Address: {{ $school->address }}
                    @endif
                </div>

                <div class="fee-receipt-terms" style="margin-top:16px;">
                    <strong>Terms &amp; Conditions</strong>
                    Fees are payable by the due date shown on your bill statement.
                    Keep this receipt for your records. Amount payable:
                    <strong>₵{{ number_format($grandTotal, 2) }}</strong>.
                    Amount paid this term:
                    <strong>₵{{ number_format($amountPaid, 2) }}</strong>.
                </div>
            </div>

            <div class="fee-receipt-summary">
                <div class="fee-receipt-summary-row">
                    <span>Compulsory bills</span>
                    <span>₵{{ number_format($compulsoryDue, 2) }}</span>
                </div>
                <div class="fee-receipt-summary-row">
                    <span>Non-compulsory bills</span>
                    <span>₵{{ number_format($optionalDue, 2) }}</span>
                </div>
                @if($amountPaid > 0)
                    <div class="fee-receipt-summary-row">
                        <span>Amount paid</span>
                        <span>−₵{{ number_format($amountPaid, 2) }}</span>
                    </div>
                @endif
                @if($arrearsTotal > 0)
                    <div class="fee-receipt-summary-row">
                        <span>Arrears</span>
                        <span>₵{{ number_format($arrearsTotal, 2) }}</span>
                    </div>
                @endif
                @if($credit > 0)
                    <div class="fee-receipt-summary-row">
                        <span>Credit (overpayment)</span>
                        <span>−₵{{ number_format($credit, 2) }}</span>
                    </div>
                @endif
                <div class="fee-receipt-total">
                    <span>Amount payable</span>
                    <span>₵{{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
