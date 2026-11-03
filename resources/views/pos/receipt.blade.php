<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt - {{ $sale->receipt_no }}</title>
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
        .actions { margin-top: 24px; display: flex; gap: 12px; }
        .btn { border: none; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: #25A194; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        @media print { body { background: #fff; padding: 0; } .actions { display: none; } .receipt { border: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div style="display:flex;align-items:center;gap:14px;">
                <img src="{{ $school->logoUrl() }}" alt="{{ $school->name ?? 'School' }}" style="width:64px;height:64px;object-fit:contain;border-radius:12px;background:#f8fafc;border:1px solid #e5e7eb;">
                <div>
                    <div class="school-name">{{ $school->name ?? 'School' }}</div>
                    <div class="meta">{{ $school->address ?? '' }}</div>
                    @if(!empty($school->phone))
                        <div class="meta">{{ $school->phone }}</div>
                    @endif
                </div>
            </div>
            <div class="meta" style="text-align:right;">
                <div><strong>Receipt No:</strong> {{ $sale->receipt_no }}</div>
                <div><strong>Date:</strong> {{ $sale->sold_at->format('M j, Y g:i A') }}</div>
            </div>
        </div>

        <div class="title">Sales Receipt</div>
        <div class="meta">
            <div><strong>Customer:</strong> {{ $sale->buyerLabel() }}</div>
            @if($sale->customer_phone)
                <div><strong>Phone:</strong> {{ $sale->customer_phone }}</div>
            @endif
            @if($sale->student)
                <div><strong>Class:</strong> {{ $sale->student->class_name ?: 'Unassigned' }}</div>
            @endif
            <div><strong>Payment Method:</strong> {{ $sale->payment_method }}</div>
            @if($sale->payment_reference)
                <div><strong>Payment Ref:</strong> {{ $sale->payment_reference }}</div>
            @endif
            <div><strong>Cashier:</strong> {{ $sale->cashier?->name ?? '—' }}</div>
            @if($sale->notes)
                <div><strong>Notes:</strong> {{ $sale->notes }}</div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->sku ?: '—' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="meta" style="margin-top:16px;">
            <div><strong>Subtotal:</strong> {{ number_format($sale->subtotal, 2) }}</div>
            @if($sale->discount > 0)
                <div><strong>Discount:</strong> -{{ number_format($sale->discount, 2) }}</div>
            @endif
        </div>

        <div class="total-box">
            Total Paid: {{ number_format($sale->total, 2) }}
        </div>

        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print Receipt</button>
            <a href="{{ route('pos-sale') }}" class="btn btn-secondary">New Sale</a>
            <a href="{{ route('pos-sales') }}" class="btn btn-secondary">Sales History</a>
        </div>
    </div>
</body>
</html>
