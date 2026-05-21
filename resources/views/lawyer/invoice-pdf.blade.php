<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->status === 'paid' ? 'Official Receipt' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #fff;
        }

        .page {
            padding: 40px 48px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 36px;
            border-bottom: 3px solid #1a1a2e;
            padding-bottom: 24px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
        }
        .header-right { text-align: right; }

        .firm-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: 0.03em;
            margin-bottom: 6px;
        }
        .firm-tagline {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .firm-contact {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.8;
        }

        .doc-type {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .invoice-number {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }
        .invoice-number span { color: #6b7280; font-weight: 400; }

        .or-badge {
            display: inline-block;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .meta-row {
            font-size: 11px;
            color: #4b5563;
            margin-bottom: 3px;
        }
        .meta-row strong { color: #1a1a2e; }

        /* ── Status pill ── */
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .status-draft   { background: #f3f4f6; color: #6b7280; }
        .status-sent    { background: #dbeafe; color: #1d4ed8; }
        .status-partial { background: #fef3c7; color: #b45309; }
        .status-paid    { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #b91c1c; }

        /* ── Two-column party section ── */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            border-spacing: 12px 0;
        }
        .party-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .party-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .party-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 8px;
        }
        .party-name {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .party-detail {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.7;
        }

        /* ── Items table ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .items-table thead tr {
            background: #1a1a2e;
            color: #fff;
        }
        .items-table th {
            padding: 10px 12px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-align: left;
        }
        .items-table th.num { text-align: right; }
        .items-table tbody tr:nth-child(even) { background: #f9fafb; }
        .items-table tbody tr:nth-child(odd)  { background: #fff; }
        .items-table td {
            padding: 11px 12px;
            font-size: 12px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .items-table .empty-row td {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 20px;
        }

        /* ── Totals ── */
        .totals-wrap {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-box-cell { display: table-cell; width: 45%; vertical-align: top; }
        .totals-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        .totals-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-row:last-child { border-bottom: none; }
        .totals-row .t-label,
        .totals-row .t-value {
            display: table-cell;
            padding: 9px 14px;
            font-size: 12px;
        }
        .totals-row .t-label { color: #6b7280; }
        .totals-row .t-value { text-align: right; color: #111827; font-weight: 500; white-space: nowrap; }
        .totals-row.subtotal-row { background: #fff; }
        .totals-row.tax-row      { background: #fff; }
        .totals-row.grand-total  { background: #1a1a2e; }
        .totals-row.grand-total .t-label,
        .totals-row.grand-total .t-value {
            color: #fff; font-size: 13px; font-weight: 700;
        }
        .totals-row.paid-row     { background: #f0fdf4; }
        .totals-row.paid-row .t-label  { color: #065f46; }
        .totals-row.paid-row .t-value  { color: #065f46; font-weight: 600; }
        .totals-row.balance-row  { background: #fef2f2; }
        .totals-row.balance-row .t-label { color: #991b1b; font-weight: 700; }
        .totals-row.balance-row .t-value { color: #991b1b; font-weight: 700; font-size: 13px; }
        .totals-row.zero-balance { background: #f0fdf4; }
        .totals-row.zero-balance .t-label,
        .totals-row.zero-balance .t-value { color: #065f46; font-weight: 700; }

        /* ── QR + Notes footer ── */
        .footer-wrap {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .footer-notes { display: table-cell; vertical-align: top; padding-right: 24px; }
        .footer-qr    { display: table-cell; vertical-align: top; width: 140px; text-align: center; }

        .notes-label {
            font-size: 9px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #9ca3af; margin-bottom: 8px;
        }
        .notes-text {
            font-size: 11px; color: #4b5563; line-height: 1.7;
            background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 6px; padding: 12px 14px;
        }

        .qr-label {
            font-size: 9px; color: #9ca3af; margin-top: 8px;
            text-align: center; line-height: 1.4;
        }

        /* ── Legal footer ── */
        .legal-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
            font-size: 10px;
            color: #9ca3af;
            line-height: 1.6;
            text-align: center;
        }

        /* ── Paid watermark ── */
        @if($invoice->status === 'paid')
        .paid-watermark {
            position: fixed;
            top: 38%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 96px;
            font-weight: 900;
            color: rgba(5, 150, 105, 0.08);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }
        @endif
    </style>
</head>
<body>
<div class="page">

    @if($invoice->status === 'paid')
        <div class="paid-watermark">PAID</div>
    @endif

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-left">
            <div class="firm-name">Legal Management</div>
            <div class="firm-tagline">Professional Legal Services &amp; Billing</div>
            <div class="firm-contact">
                Phone: {{ optional($invoice->lawyer)->phone ?? 'N/A' }}<br>
                Email: {{ optional($invoice->lawyer)->email ?? 'N/A' }}
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">
                {{ $invoice->status === 'paid' ? 'Official Receipt' : 'Invoice' }}
            </div>
            @if($invoice->status === 'paid' && !empty($invoice->or_number))
                <div class="or-badge">OR No: {{ $invoice->or_number }}</div><br>
            @endif
            <div class="invoice-number">
                <span>No. </span>{{ $invoice->invoice_number }}
            </div>
            <div class="meta-row" style="margin-top: 10px;">
                <strong>Issued:</strong> {{ optional($invoice->issued_date)->format('F j, Y') ?? 'N/A' }}
            </div>
            <div class="meta-row">
                <strong>Due:</strong> {{ optional($invoice->due_date)->format('F j, Y') ?? 'N/A' }}
            </div>
            @if($invoice->paid_date)
            <div class="meta-row">
                <strong>Paid:</strong> {{ optional($invoice->paid_date)->format('F j, Y') }}
            </div>
            @endif
            <div class="meta-row" style="margin-top: 8px;">
                <span class="status-pill status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>
    </div>

    {{-- ── Parties ── --}}
    <div class="parties">
        <div class="party-cell" style="padding-right: 8px;">
            <div class="party-box">
                <div class="party-label">Bill To</div>
                <div class="party-name">{{ optional($invoice->client)->name ?? 'N/A' }}</div>
                <div class="party-detail">
                    {{ optional($invoice->client)->email ?? '' }}<br>
                    {{ optional($invoice->client)->phone ?? '' }}
                </div>
            </div>
        </div>
        <div class="party-cell" style="padding-left: 8px;">
            <div class="party-box">
                <div class="party-label">Reference</div>
                <div class="party-detail">
                    <strong>Case No:</strong> {{ optional($invoice->case)->case_number ?? 'N/A' }}<br>
                    <strong>Case:</strong> {{ optional($invoice->case)->title ?? 'N/A' }}<br>
                    @if($invoice->appointment)
                    <strong>Appointment:</strong>
                        {{ optional($invoice->appointment->appointment_at)->format('F j, Y h:i A') ?? 'N/A' }}<br>
                    @endif
                    <strong>Lawyer:</strong> {{ optional($invoice->lawyer)->name ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Items ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $i => $item)
            <tr>
                <td style="color: #9ca3af; width: 28px;">{{ $i + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="num">{{ number_format($item->quantity, 2) }}</td>
                <td class="num">{{ config('legal.currency_symbol') }}{{ number_format($item->unit_price, 2) }}</td>
                <td class="num">{{ config('legal.currency_symbol') }}{{ number_format($item->total, 2) }}</td>
            </tr>
            @empty
            <tr class="empty-row">
                <td colspan="5">No invoice items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <div class="totals-spacer"></div>
        <div class="totals-box-cell">
            <div class="totals-box">
                <div class="totals-row subtotal-row">
                    <div class="t-label">Subtotal</div>
                    <div class="t-value">{{ config('legal.currency_symbol') }}{{ number_format($invoice->subtotal, 2) }}</div>
                </div>
                <div class="totals-row tax-row">
                    <div class="t-label">Tax / VAT</div>
                    <div class="t-value">{{ config('legal.currency_symbol') }}{{ number_format($invoice->tax ?? 0, 2) }}</div>
                </div>
                <div class="totals-row grand-total">
                    <div class="t-label">Total</div>
                    <div class="t-value">{{ config('legal.currency_symbol') }}{{ number_format($invoice->total, 2) }}</div>
                </div>
                <div class="totals-row paid-row">
                    <div class="t-label">Amount Paid</div>
                    <div class="t-value">{{ config('legal.currency_symbol') }}{{ number_format($invoice->amount_paid, 2) }}</div>
                </div>
                @if($invoice->balance > 0)
                <div class="totals-row balance-row">
                    <div class="t-label">Balance Due</div>
                    <div class="t-value">{{ config('legal.currency_symbol') }}{{ number_format($invoice->balance, 2) }}</div>
                </div>
                @else
                <div class="totals-row zero-balance">
                    <div class="t-label">Balance Due</div>
                    <div class="t-value">{{ config('legal.currency_symbol') }}0.00</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Notes + QR ── --}}
    <div class="footer-wrap">
        <div class="footer-notes">
            <div class="notes-label">Notes</div>
            <div class="notes-text">
                {{ $invoice->notes ?? 'Thank you for your business. Please remit payment by the due date.' }}
                @if($invoice->status === 'paid' && !empty($invoice->or_number))
                    <br><br><strong>This document serves as your Official Receipt (OR No: {{ $invoice->or_number }}).</strong>
                @endif
            </div>
        </div>
        @if(!empty($qrCode))
        <div class="footer-qr">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="110" height="110" alt="QR Code">
            <div class="qr-label">Scan to verify<br>this invoice</div>
        </div>
        @endif
    </div>

    {{-- ── Legal footer ── --}}
    <div class="legal-footer">
        This invoice was generated for professional legal services rendered by your attorney.<br>
        It complies with standard invoice presentation requirements in the Philippines.<br>
        For disputes or inquiries, please contact your lawyer directly.
    </div>

</div>
</body>
</html>