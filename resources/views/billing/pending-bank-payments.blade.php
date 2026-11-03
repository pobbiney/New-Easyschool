@php $pageName = "bill-management"; $subpageName = "pending-bank-payments"; @endphp
@extends('layouts.app')
@section('css')
<style>
    .pbp-card {
        border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden;
        box-shadow: 0 4px 18px rgba(15,23,42,.04); background: #fff; margin-bottom: 24px;
    }
    .pbp-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        padding: 18px 24px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(180deg, #fafafa, #fff);
    }
    .pbp-head h6 { margin: 0; font-size: 16px; font-weight: 700; }
    .pbp-head p { margin: 4px 0 0; font-size: 12px; color: #9ca3af; }
    .pbp-table { width: 100%; margin: 0; }
    .pbp-table th {
        background: #f9fafb; font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #6b7280; padding: 14px 16px; border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .pbp-table td { padding: 14px 16px; vertical-align: middle; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
    .pbp-student { font-weight: 700; color: #111827; }
    .pbp-meta { font-size: 12px; color: #9ca3af; }
    .pbp-badge {
        display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px;
        font-size: 11px; font-weight: 700;
    }
    .pbp-badge.is-pending { background: rgba(245,158,11,.12); color: #b45309; }
    .pbp-badge.is-approved { background: rgba(34,197,94,.12); color: #15803d; }
    .pbp-badge.is-rejected { background: rgba(220,38,38,.12); color: #b91c1c; }
    .pbp-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .pbp-empty { padding: 40px 20px; text-align: center; color: #9ca3af; }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Pending Bank Payments</span>
            </div>
        </div>
        <a href="{{ route('student-bills') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-bill-line"></i> Student Bills
        </a>
    </div>

    <div class="pbp-card">
        <div class="pbp-head">
            <div>
                <h6>Awaiting approval</h6>
                <p>Cheque and bankers draft payments. Bills are not updated until you approve.</p>
            </div>
            <span class="pbp-badge is-pending">{{ $pending->count() }} pending</span>
        </div>
        @if ($pending->isEmpty())
            <div class="pbp-empty">No bank payments waiting for approval.</div>
        @else
            <div class="table-responsive">
                <table class="pbp-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Instrument</th>
                            <th>Number</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Receipt</th>
                            <th>Recorded by</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pending as $row)
                            <tr>
                                <td>
                                    <div class="pbp-student">{{ $row->student->full_name ?? 'Student' }}</div>
                                    <div class="pbp-meta">{{ $row->student->student_id ?? '' }} · {{ $row->student->class_name ?? ($row->student->schoolClass->name ?? '') }}</div>
                                </td>
                                <td>{{ $row->bank_instrument }}</td>
                                <td>{{ $row->reference }}</td>
                                <td>
                                    <strong>₵{{ number_format((float) $row->amount, 2) }}</strong>
                                    @if ((float) $row->credit_applied > 0)
                                        <div class="pbp-meta">+ ₵{{ number_format((float) $row->credit_applied, 2) }} credit</div>
                                    @endif
                                </td>
                                <td>{{ $row->paid_at?->format('d M Y g:i A') }}</td>
                                <td>
                                    @if ($row->receiptUrl())
                                        <a href="{{ $row->receiptUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary-600">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $row->creator->name ?? '—' }}</td>
                                <td>
                                    <div class="pbp-actions">
                                        <form method="POST" action="{{ route('pending-bank-payments-approve', $row) }}" class="pbp-approve-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('pending-bank-payments-reject', $row) }}" class="pbp-reject-form">
                                            @csrf
                                            <input type="hidden" name="rejected_reason" value="">
                                            <button type="submit" class="btn btn-sm btn-outline-danger-600">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="pbp-card">
        <div class="pbp-head">
            <div>
                <h6>Recent decisions</h6>
                <p>Approved and rejected bank payments.</p>
            </div>
        </div>
        @if ($history->isEmpty())
            <div class="pbp-empty">No approved or rejected bank payments yet.</div>
        @else
            <div class="table-responsive">
                <table class="pbp-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Instrument</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Decided</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr>
                                <td>
                                    <div class="pbp-student">{{ $row->student->full_name ?? 'Student' }}</div>
                                    <div class="pbp-meta">{{ $row->reference }}</div>
                                </td>
                                <td>{{ $row->bank_instrument }}</td>
                                <td>₵{{ number_format((float) $row->amount, 2) }}</td>
                                <td>
                                    <span class="pbp-badge {{ $row->status === 'approved' ? 'is-approved' : 'is-rejected' }}">{{ ucfirst($row->status) }}</span>
                                    @if ($row->rejected_reason)
                                        <div class="pbp-meta">{{ $row->rejected_reason }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $row->approved_at?->format('d M Y g:i A') }}</div>
                                    <div class="pbp-meta">{{ $row->approver->name ?? '' }}</div>
                                </td>
                                <td>
                                    @if ($row->bill_payment_id)
                                        <a href="{{ route('bill-payment-receipt', $row->bill_payment_id) }}" target="_blank">Receipt</a>
                                    @elseif ($row->receiptUrl())
                                        <a href="{{ $row->receiptUrl() }}" target="_blank">File</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    function swalConfirm(options) {
        if (typeof Swal === 'undefined') {
            const ok = window.confirm([options.title, options.text].filter(Boolean).join('\n'));
            return Promise.resolve({ isConfirmed: ok, value: '' });
        }

        return Swal.fire({
            title: options.title,
            text: options.text || '',
            icon: options.icon || 'warning',
            input: options.input || undefined,
            inputPlaceholder: options.inputPlaceholder || '',
            inputAttributes: options.input ? { maxlength: 500 } : undefined,
            showCancelButton: true,
            focusCancel: true,
            reverseButtons: true,
            confirmButtonColor: options.confirmButtonColor || '#E5484D',
            cancelButtonColor: '#64748b',
            confirmButtonText: options.confirmButtonText || 'Yes',
            cancelButtonText: 'Cancel',
            customClass: { popup: 'app-confirm-swal' },
        });
    }

    document.querySelectorAll('.pbp-approve-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1') {
                return;
            }
            event.preventDefault();
            swalConfirm({
                title: 'Approve this payment?',
                text: 'This will post the bank payment to the student bills.',
                icon: 'question',
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Yes, approve',
            }).then(function (result) {
                if (!result || result.isConfirmed !== true) {
                    return;
                }
                form.dataset.confirmed = '1';
                form.submit();
            });
        });
    });

    document.querySelectorAll('.pbp-reject-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1') {
                return;
            }
            event.preventDefault();
            swalConfirm({
                title: 'Reject this payment?',
                text: 'Student bills will not be updated. You can add a reason below.',
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'Reason (optional)',
                confirmButtonText: 'Yes, reject',
            }).then(function (result) {
                if (!result || result.isConfirmed !== true) {
                    return;
                }
                const reasonInput = form.querySelector('input[name="rejected_reason"]');
                if (reasonInput) {
                    reasonInput.value = result.value || '';
                }
                form.dataset.confirmed = '1';
                form.submit();
            });
        });
    });
})();
</script>
@endsection
