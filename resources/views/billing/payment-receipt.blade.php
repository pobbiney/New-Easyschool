<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->receipt_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 0; padding: 24px; background: #f3f4f6; }
        .receipt { max-width: 760px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px; }
        .header { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid #25A194; padding-bottom: 16px; margin-bottom: 24px; }
        .school-name { font-size: 22px; font-weight: 700; color: #25A194; }
        .meta { font-size: 13px; color: #6b7280; line-height: 1.6; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px 12px; text-align: left; font-size: 14px; }
        th { background: #f9fafb; }
        .total-box { margin-top: 20px; padding: 16px; background: rgba(37,161,148,.08); border-radius: 8px; font-size: 16px; font-weight: 700; }
        .credit-box {
            margin-top: 16px; padding: 16px; border-radius: 8px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
        }
        .credit-box strong { color: #15803d; }
        .actions { margin-top: 24px; display: flex; gap: 12px; }
        .btn { border: none; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #25A194; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        @media print { body { background: #fff; padding: 0; } .actions { display: none; } .receipt { border: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div style="display:flex;align-items:center;gap:14px;">
                <img src="{{ $school->logoUrl() }}" alt="{{ $school->name ?? $school->school_name ?? 'School' }}" style="width:64px;height:64px;object-fit:contain;border-radius:12px;background:#f8fafc;border:1px solid #e5e7eb;">
                <div>
                    <div class="school-name">{{ $school->name ?? $school->school_name ?? 'School' }}</div>
                    <div class="meta">{{ $school->address ?? '' }}</div>
                </div>
            </div>
            <div class="meta" style="text-align:right;">
                <div><strong>Receipt No:</strong> {{ $payment->receipt_no }}</div>
                <div><strong>Date:</strong> {{ $payment->paid_at->format('M j, Y g:i A') }}</div>
            </div>
        </div>

        <div class="title">Payment Receipt</div>
        <div class="meta">
            <div><strong>Student:</strong> {{ $payment->student->full_name }} ({{ $payment->student->student_id }})</div>
            <div><strong>Class:</strong> {{ $payment->student->class_name }}</div>
            <div><strong>Category:</strong> {{ $payment->student->schoolClass?->category?->name ?: '—' }}</div>
            <div><strong>Method:</strong> {{ $payment->payment_method }} @if($payment->reference) | <strong>Ref:</strong> {{ $payment->reference }} @endif</div>
            @if($payment->payment_channel)
                <div><strong>Channel:</strong> {{ ucwords(str_replace('_', ' ', $payment->payment_channel)) }}</div>
            @endif
            @if($payment->gateway_transaction_id)
                <div><strong>Transaction ID:</strong> {{ $payment->gateway_transaction_id }}</div>
            @endif
        </div>

        <table>
            <thead><tr><th>Billing Item</th><th>Amount Applied</th></tr></thead>
            <tbody>
                @foreach($payment->allocations as $allocation)
                <tr>
                    <td>{{ $allocation->studentBill?->billingItem?->name ?: 'Bill Item' }}</td>
                    <td>{{ number_format($allocation->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            Total Applied to Bills: {{ number_format($payment->amount + $payment->credit_applied, 2) }}
        </div>
        @if($payment->credit_applied > 0)
            <p class="meta" style="margin-top:12px;"><strong>Credit applied:</strong> {{ number_format($payment->credit_applied, 2) }}</p>
        @endif
        @if($payment->amount > 0)
            <p class="meta"><strong>Cash / Paystack collected:</strong> {{ number_format($payment->amount, 2) }}</p>
        @endif
        @if($payment->credit_generated > 0)
            <p class="meta"><strong>Overpayment saved as credit:</strong> {{ number_format($payment->credit_generated, 2) }}</p>
        @endif
        <div class="credit-box">
            <p class="meta" style="margin:0;"><strong>Remaining credit balance:</strong> {{ number_format($payment->student->credit_balance, 2) }}</p>
            <p class="meta" style="margin:8px 0 0;">This amount can be used to reduce the student's next bill payment. Toggle "Use credit" on the record payment page.</p>
        </div>
        @if($payment->notes)<p class="meta" style="margin-top:16px;"><strong>Notes:</strong> {{ $payment->notes }}</p>@endif

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
            <a href="{{ $statementUrl }}" target="_blank" class="btn btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Print Bill Statement</a>
            <a href="{{ $backUrl ?? route('student-bills') }}" class="btn btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">{{ $backLabel ?? 'Back to Student Bills' }}</a>
        </div>
    </div>
</body>
</html>
