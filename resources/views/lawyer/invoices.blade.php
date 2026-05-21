@extends('layouts.lawyer')

@section('title', 'Invoices')

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <h1>Invoices</h1>
        <p>Manage billing and track payments</p>
    </div>
    {{-- Auto-generated invoices are created on appointment confirmation; manual creation is still available --}}
    <div class="topbar-right">
        <a href="{{ route('lawyer.billing.invoices.create') }}" class="btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Create Manual Invoice
        </a>
    </div>
</div>
<div class="content">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($invoices->count() > 0)
    @foreach($invoices as $invoice)
    <div class="invoice-card">
        <div class="invoice-header">
            <div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                    {{ $invoice->client->name ?? 'N/A' }}
                    @if($invoice->appointment_id)
                        • Appointment Invoice
                    @endif
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                @if($invoice->is_validated)
                <span style="font-size: 0.7rem; padding: 3px 8px; background: rgba(52,211,153,0.12); color: #34d399; border-radius: 6px; font-weight: 600;">VALIDATED</span>
                @endif
                <span class="invoice-status {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>

        <div class="invoice-details">
            <div class="invoice-detail-item">
                <label>Issued Date</label>
                <span>{{ $invoice->issued_date->format('M d, Y') }}</span>
            </div>
            <div class="invoice-detail-item">
                <label>Due Date</label>
                <span>{{ $invoice->due_date->format('M d, Y') }}</span>
            </div>
            <div class="invoice-detail-item">
                <label>Amount Paid</label>
                <span>{{ money_display($invoice->amount_paid) }}</span>
            </div>
            <div class="invoice-detail-item">
                <label>Balance</label>
                <span style="{{ $invoice->balance > 0 ? 'color: var(--warning);' : 'color: var(--success);' }}">
                    {{ money_display($invoice->balance) }}
                </span>
            </div>
        </div>

        <div class="invoice-amount">
            {{ money_display($invoice->total) }}
        </div>

        <div class="invoice-actions">
            <a href="{{ route('lawyer.billing.invoices.show', $invoice) }}" class="btn-secondary">View</a>
            @if(!$invoice->is_validated && $invoice->status === 'draft')
            <a href="{{ route('lawyer.billing.invoices.edit', $invoice) }}" class="btn-secondary">Edit</a>
            @endif
            @if(!$invoice->is_validated && in_array($invoice->status, ['sent', 'partial']))
            <button class="btn-primary" style="font-size:0.82rem;padding:7px 14px;"
                onclick="validateInvoice({{ $invoice->id }})">Validate</button>
            @endif
        </div>
    </div>
    @endforeach

    {{ $invoices->links() }}
    @else
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        <h3>No invoices yet</h3>
        <p>Invoices are auto-created when you confirm appointments.</p>
    </div>
    @endif
</div>

<script>
function validateInvoice(invoiceId) {
    if (!confirm('Validate this invoice? Financial data will be locked after confirmation.')) return;

    fetch(`/lawyer/billing/invoices/${invoiceId}/validate`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                          ?? '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error ?? 'Validation failed.');
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}
</script>
@endsection