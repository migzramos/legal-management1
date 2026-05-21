@extends('layouts.lawyer')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
    .page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 600; margin-bottom: 4px; color: var(--text-primary); }
    .page-header p { color: var(--text-muted); font-size: 0.9rem; }
    .page-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

    .detail-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
    .full-width { grid-column: 1 / -1; }

    .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 28px; }
    .card-title {
        font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; font-weight: 600;
        color: var(--text-primary); margin-bottom: 24px; padding-bottom: 16px;
        border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;
    }
    .card-title svg { opacity: 0.5; }

    .info-grid { display: flex; flex-direction: column; gap: 14px; }
    .info-item { display: flex; justify-content: space-between; align-items: center; }
    .info-item .i-label { font-size: 0.78rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: var(--text-muted); }
    .info-item .i-value { font-size: 0.9rem; color: var(--text-primary); font-weight: 500; text-align: right; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
        letter-spacing: 0.04em; text-transform: uppercase;
    }
    .status-draft    { background: rgba(148,163,184,0.12); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); }
    .status-sent     { background: rgba(96,165,250,0.12);  color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }
    .status-partial  { background: rgba(251,191,36,0.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
    .status-paid     { background: rgba(52,211,153,0.12);  color: #34d399; border: 1px solid rgba(52,211,153,0.2); }
    .status-overdue  { background: rgba(248,113,113,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
    .status-success  { background: rgba(52,211,153,0.12);  color: #34d399; border: 1px solid rgba(52,211,153,0.2); }
    .status-warning  { background: rgba(251,191,36,0.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }

    .or-badge {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 12px;
        background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.25);
        color: #fbbf24; font-weight: 700; font-size: 0.9rem; margin-top: 14px;
    }

    /* Summary */
    .summary-list { display: flex; flex-direction: column; gap: 12px; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; }
    .summary-row .s-label { font-size: 0.85rem; color: var(--text-muted); }
    .summary-row .s-value { font-size: 0.95rem; color: var(--text-primary); font-weight: 500; }
    .summary-row.grand-total { border-top: 1px solid var(--border); padding-top: 14px; margin-top: 4px; }
    .summary-row.grand-total .s-label { font-size: 1rem; font-weight: 600; color: var(--text-primary); }
    .summary-row.grand-total .s-value { font-size: 1.25rem; font-weight: 700; color: var(--purple-core); }
    .summary-row.balance-row .s-value { color: var(--danger); }
    .summary-divider { border: none; border-top: 1px solid var(--border); margin: 4px 0; }

    /* Items table */
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th {
        font-size: 0.72rem; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase;
        color: var(--text-muted); padding: 10px 14px; text-align: left;
        border-bottom: 1px solid var(--border);
    }
    .items-table td { padding: 14px; font-size: 0.875rem; color: var(--text-primary); border-bottom: 1px solid rgba(255,255,255,0.04); }
    .items-table tr:last-child td { border-bottom: none; }
    .items-table td.num { text-align: right; font-variant-numeric: tabular-nums; }
    .items-table th.num { text-align: right; }

    /* Buttons */
    .btn {
        padding: 9px 18px; border-radius: 10px;
        font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 500;
        cursor: pointer; transition: all 0.2s;
        text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
        border: none; white-space: nowrap;
    }
    .btn-primary    { background: var(--purple-core); color: #fff; box-shadow: 0 4px 14px rgba(139,92,246,0.3); }
    .btn-primary:hover { opacity: 0.88; }
    .btn-ghost      { background: transparent; border: 1px solid var(--border); color: var(--text-muted); }
    .btn-ghost:hover { border-color: var(--purple-core); color: var(--text-primary); }
    .btn-outline    { background: transparent; border: 1px solid var(--border); color: var(--text-primary); }
    .btn-outline:hover { border-color: var(--purple-core); background: rgba(139,92,246,0.06); }
    .btn-danger     { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: var(--danger); }
    .btn-danger:hover { background: rgba(248,113,113,0.18); }
    .btn-success    { background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25); color: #34d399; }
    .btn-success:hover { background: rgba(52,211,153,0.18); }

    /* Modal */
    .modal { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; }
    .modal-box { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 32px; width: 100%; max-width: 440px; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .modal-header h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 600; color: var(--text-primary); }
    .modal-close { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.4rem; line-height: 1; padding: 4px; }
    .modal-close:hover { color: var(--text-primary); }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); }
    .form-group { margin-bottom: 18px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; }
    .form-control {
        width: 100%; box-sizing: border-box;
        background: var(--bg-input, rgba(255,255,255,0.04));
        border: 1px solid var(--border); border-radius: 10px;
        padding: 10px 14px; color: var(--text-primary);
        font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .form-control:focus { border-color: var(--purple-core); box-shadow: 0 0 0 3px rgba(139,92,246,0.12); }

    .notes-box {
        background: rgba(255,255,255,0.025); border: 1px solid var(--border);
        border-radius: 12px; padding: 16px; font-size: 0.875rem;
        color: var(--text-primary); line-height: 1.6;
    }

    .qr-card { text-align: center; }
    .qr-card p { font-size: 0.8rem; color: var(--text-muted); margin: 8px 0 16px; }
    .qr-card svg { max-width: 160px; }
</style>

<div class="page-header">
    <div>
        <h1>{{ $invoice->invoice_number }}</h1>
        <p>
            {{ $invoice->client->name }}
            @if($invoice->case) · {{ $invoice->case->title }} @endif
            @if($invoice->appointment) · Appointment Invoice @endif
        </p>
        @if($invoice->status === 'paid' && !empty($invoice->or_number))
            <div class="or-badge">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                OR No: {{ $invoice->or_number }}
            </div>
        @endif
    </div>
    <div class="page-actions">
        <a href="{{ route('lawyer.billing.invoices.pdf', $invoice) }}" class="btn btn-outline">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            Download PDF
        </a>
        @if(!$invoice->is_validated && $invoice->status !== 'paid')
            <button class="btn btn-primary" onclick="openValidateModal()">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Validate & Confirm
            </button>
        @endif
        @if($invoice->status !== 'paid')
            <button class="btn btn-success" onclick="openPaymentModal()">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Record Payment
            </button>
        @endif
        @if(!$invoice->is_validated)
            <a href="{{ route('lawyer.billing.invoices.edit', $invoice) }}" class="btn btn-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <form action="{{ route('lawyer.billing.invoices.destroy', $invoice) }}" method="POST" style="display:inline;"
                onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        @endif
    </div>
</div>

<div class="detail-layout">

    {{-- Invoice Info --}}
    <div class="card">
        <div class="card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Invoice Details
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="i-label">Invoice Number</span>
                <span class="i-value" style="font-family: monospace; font-size: 0.85rem;">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-item">
                <span class="i-label">Status</span>
                <span class="i-value">
                    <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                </span>
            </div>
            <div class="info-item">
                <span class="i-label">Validation</span>
                <span class="i-value">
                    @if($invoice->is_validated)
                        <span class="status-badge status-success">✓ Confirmed</span>
                    @else
                        <span class="status-badge status-warning">Pending</span>
                    @endif
                </span>
            </div>
            <div class="info-item">
                <span class="i-label">Client</span>
                <span class="i-value">{{ $invoice->client->name }}</span>
            </div>
            @if($invoice->case)
            <div class="info-item">
                <span class="i-label">Case</span>
                <span class="i-value">{{ $invoice->case->case_number }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="i-label">Issued Date</span>
                <span class="i-value">{{ $invoice->issued_date?->format('M d, Y') }}</span>
            </div>
            <div class="info-item">
                <span class="i-label">Due Date</span>
                <span class="i-value">{{ $invoice->due_date?->format('M d, Y') }}</span>
            </div>
            @if($invoice->paid_date)
            <div class="info-item">
                <span class="i-label">Paid Date</span>
                <span class="i-value">{{ $invoice->paid_date?->format('M d, Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment Summary --}}
    <div class="card">
        <div class="card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Payment Summary
        </div>
        <div class="summary-list">
            <div class="summary-row">
                <span class="s-label">Subtotal</span>
                <span class="s-value">{{ money_display($invoice->subtotal) }}</span>
            </div>
            <div class="summary-row">
                <span class="s-label">Tax</span>
                <span class="s-value">{{ money_display($invoice->tax ?? 0) }}</span>
            </div>
            <div class="summary-row grand-total">
                <span class="s-label">Total</span>
                <span class="s-value">{{ money_display($invoice->total) }}</span>
            </div>
            <hr class="summary-divider">
            <div class="summary-row">
                <span class="s-label">Amount Paid</span>
                <span class="s-value" style="color: #34d399;">{{ money_display($invoice->amount_paid) }}</span>
            </div>
            <div class="summary-row">
                <span class="s-label">Balance Due</span>
                <span class="s-value {{ $invoice->balance > 0 ? 'balance-row' : '' }}" style="{{ $invoice->balance > 0 ? 'color: var(--danger);' : 'color: #34d399;' }}">
                    {{ money_display($invoice->balance) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="card">
        <div class="card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            Notes
        </div>
        <div class="notes-box">{{ $invoice->notes }}</div>
    </div>
    @endif

    {{-- QR Code --}}
    @if($invoice->status === 'paid' && !empty($qrCode))
    <div class="card qr-card">
        <div class="card-title" style="justify-content: center;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h.01M18 14h.01M14 18h.01M18 18h.01"/></svg>
            Payment Verification
        </div>
        <p>Scan to verify this payment</p>
        {!! base64_decode($qrCode) !!}
    </div>
    @endif

    {{-- Invoice Items --}}
    <div class="card full-width">
        <div class="card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Invoice Items
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Quantity</th>
                    <th class="num">Unit Price</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ number_format($item->quantity, 2) }}</td>
                    <td class="num">{{ money_display($item->unit_price) }}</td>
                    <td class="num">{{ money_display($item->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

{{-- Payment Modal --}}
<div id="payment-modal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Record Payment</h3>
            <button class="modal-close" onclick="closePaymentModal()">&times;</button>
        </div>
        <form action="{{ route('lawyer.billing.invoices.payment', $invoice) }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Payment Amount *</label>
                <input type="number" name="amount" class="form-control"
                    step="0.01" min="0.01" max="{{ $invoice->balance }}"
                    placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label>Payment Date *</label>
                <input type="date" name="paid_date" class="form-control"
                    value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Record Payment</button>
            </div>
        </form>
    </div>
</div>

{{-- Validate Modal --}}
<div id="validate-modal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Validate Invoice</h3>
            <button class="modal-close" onclick="closeValidateModal()">&times;</button>
        </div>
        <div style="margin-bottom: 20px; font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
            Once confirmed, this invoice cannot be edited and financial data will be locked.
        </div>
        <div class="form-group">
            <label>Notes (optional)</label>
            <textarea id="validate-notes" class="form-control" rows="3"
                placeholder="Any notes about this validation..."></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeValidateModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitValidation()">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Confirm & Lock
            </button>
        </div>
    </div>
</div>

<script>
function openPaymentModal()  { document.getElementById('payment-modal').style.display  = 'flex'; }
function closePaymentModal() { document.getElementById('payment-modal').style.display  = 'none'; }
function openValidateModal() { document.getElementById('validate-modal').style.display = 'flex'; }
function closeValidateModal(){ document.getElementById('validate-modal').style.display = 'none'; }

async function submitValidation() {
    const notes = document.getElementById('validate-notes').value;
    try {
        const response = await fetch('{{ route("lawyer.billing.invoices.validate", $invoice) }}', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notes }),
        });
        const result = await response.json();
        if (response.ok) {
            closeValidateModal();
            window.location.reload();
        } else {
            alert(result.error || 'Failed to validate invoice.');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Close modals on backdrop click
['payment-modal', 'validate-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

@endsection