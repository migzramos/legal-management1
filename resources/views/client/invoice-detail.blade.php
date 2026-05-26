@extends('layouts.client')
 
@section('title', 'Invoice #' . $invoice->invoice_number)
 
@section('content')
 
<div class="topbar">
    <div class="topbar-left">
        <h1>Invoice #{{ $invoice->invoice_number }}</h1>
        <p>Review your invoice details and transaction summary.</p>
        @if($invoice->status === 'paid' && !empty($invoice->or_number))
            <div style="margin-top:12px;padding:10px 16px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);border-radius:10px;color:#fbbf24;font-weight:700;display:inline-block;">
                Official Receipt No: {{ $invoice->or_number }}
            </div>
        @endif
    </div>
    <div class="topbar-right">
        <a href="{{ route('client.invoices.index') }}" class="btn-secondary">Back to Invoices</a>
        <a href="{{ route('client.invoices.pdf', $invoice) }}" class="btn-secondary">Download PDF</a>
        @if(!in_array($invoice->status, ['paid', 'cancelled', 'under_review']) && $invoice->balance > 0)
            <button class="btn-primary" onclick="payInvoice({{ $invoice->id }}, {{ $invoice->balance }})">Pay Now</button>
        @endif
        @if($invoice->status === 'under_review')
            <span style="padding:8px 16px;border-radius:10px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);color:#fbbf24;font-size:0.82rem;font-weight:600;">
                ⏳ Payment Under Review
            </span>
        @endif
    </div>
</div>
 
<div class="content">
    <div class="card">
        <div class="card-body">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:24px;margin-bottom:24px;">
                <div style="display:grid;gap:12px;">
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Status</div>
                        <div style="color:var(--text-primary);font-weight:500;">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Issued</div>
                        <div style="color:var(--text-primary);">{{ $invoice->issued_date?->format('M d, Y') ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Due</div>
                        <div style="color:var(--text-primary);">{{ $invoice->due_date?->format('M d, Y') ?? 'N/A' }}</div>
                    </div>
                </div>
                <div style="display:grid;gap:12px;">
                    @if($invoice->appointment)
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Invoice Type</div>
                        <div style="color:var(--text-primary);">Appointment Invoice</div>
                    </div>
                    @endif
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Lawyer</div>
                        <div style="color:var(--text-primary);">{{ $invoice->lawyer->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Balance</div>
                        <div style="color:var(--text-primary);">{{ config('legal.currency_symbol') }}{{ number_format($invoice->balance, 2) }}</div>
                    </div>
                </div>
            </div>
 
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ config('legal.currency_symbol') }}{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ config('legal.currency_symbol') }}{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
 
            <div style="display:flex;justify-content:flex-end;margin-top:24px;">
                <div class="card" style="min-width:260px;">
                    <div class="card-body" style="display:grid;gap:12px;">
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Subtotal</div>
                            <div style="color:var(--text-primary);">{{ config('legal.currency_symbol') }}{{ number_format($invoice->subtotal, 2) }}</div>
                        </div>
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Tax</div>
                            <div style="color:var(--text-primary);">{{ config('legal.currency_symbol') }}{{ number_format($invoice->tax ?? 0, 2) }}</div>
                        </div>
                        <div style="border-top:1px solid var(--border);padding-top:12px;">
                            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Total</div>
                            <div style="color:var(--text-primary);font-weight:700;font-size:1.1rem;">{{ config('legal.currency_symbol') }}{{ number_format($invoice->total, 2) }}</div>
                        </div>
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Paid</div>
                            <div style="color:var(--text-primary);">{{ config('legal.currency_symbol') }}{{ number_format($invoice->amount_paid, 2) }}</div>
                        </div>
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Remaining Balance</div>
                            <div style="color:var(--purple-light);font-weight:700;">{{ config('legal.currency_symbol') }}{{ number_format($invoice->balance, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 
@include('client.partials.payment-popup')
 
<script>
    function payInvoice(invoiceId, amount) {
        document.getElementById('invoice-id-field').value = invoiceId;
        document.getElementById('amount-display').textContent = parseFloat(amount).toFixed(2);
        document.getElementById('payment-status').textContent = '';
        document.getElementById('paymentModal').classList.add('active');
    }
 
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.remove('active');
    }
</script>
 
@endsection
 