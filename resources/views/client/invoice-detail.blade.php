<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice Details — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @include('client.partials.styles')
</head>
<body>
<div class="bg-scene"></div>
<div class="app">
    @include('client.partials.sidebar')
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Invoice #{{ $invoice->invoice_number }}</h1>
                <p>Review your invoice details and transaction summary.</p>
                @if($invoice->status === 'paid' && !empty($invoice->or_number))
                    <div class="mt-4 p-4 bg-gray-900 text-yellow-400 rounded-xl inline-block font-bold">
                        Official Receipt No: {{ $invoice->or_number }}
                    </div>
                @endif
            </div>
            <div class="topbar-right">
                <a href="{{ route('client.invoices.index') }}" class="btn-secondary">Back to Invoices</a>
                <a href="{{ route('client.invoices.pdf', $invoice) }}" class="btn btn-secondary">Download PDF</a>
                @if($invoice->status !== 'paid' && $invoice->balance > 0)
                <button class="btn-primary" onclick="payInvoice({{ $invoice->id }}, {{ $invoice->balance }})">Pay Now</button>
                @endif
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body">
                    <div class="flex flex-wrap justify-between gap-6 mb-6">
                        <div class="grid gap-3">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Status</label>
                                <span class="text-white">{{ ucfirst($invoice->status) }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Issued</label>
                                <span class="text-white">{{ $invoice->issued_date?->format('M d, Y') ?? 'N/A' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Due</label>
                                <span class="text-white">{{ $invoice->due_date?->format('M d, Y') ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="grid gap-3">
                            @if($invoice->appointment)
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Invoice Type</label>
                                <span class="text-white">Appointment Invoice</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Lawyer</label>
                                <span class="text-white">{{ $invoice->lawyer->name ?? 'N/A' }}</span>
                            </div>
                        @else
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Lawyer</label>
                                <span class="text-white">{{ $invoice->lawyer->name ?? 'N/A' }}</span>
                            </div>
                        @endif
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Balance</label>
                                <span class="text-white">{{ config('legal.currency_symbol') }}{{ number_format($invoice->balance, 2) }}</span>
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
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 mt-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-gray-500 uppercase tracking-wider">Subtotal</label>
                                    <span class="text-white">{{ config('legal.currency_symbol') }}{{ number_format($invoice->subtotal, 2) }}</span>
                                </div>
                                <div class="flex flex-col gap-1 mt-3">
                                    <label class="text-xs text-gray-500 uppercase tracking-wider">Tax</label>
                                    <span class="text-white">{{ config('legal.currency_symbol') }}{{ number_format($invoice->tax ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-gray-500 uppercase tracking-wider">Total</label>
                                    <span class="text-white">{{ config('legal.currency_symbol') }}{{ number_format($invoice->total, 2) }}</span>
                                </div>
                                <div class="flex flex-col gap-1 mt-3">
                                    <label class="text-xs text-gray-500 uppercase tracking-wider">Paid</label>
                                    <span class="text-white">{{ config('legal.currency_symbol') }}{{ number_format($invoice->amount_paid, 2) }}</span>
                                </div>
                                <div class="flex flex-col gap-1 mt-3">
                                    <label class="text-xs text-gray-500 uppercase tracking-wider">Remaining Balance</label>
                                    <span class="text-white">{{ config('legal.currency_symbol') }}{{ number_format($invoice->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($invoice->status === 'paid' && !empty($qrCode))
                    <div class="card mt-6">
                        <div class="card-body">
                            <h3 class="text-lg font-medium text-white mb-3">Payment Verification QR Code</h3>
                            <p class="text-gray-400 text-sm mb-4">Scan this QR code to verify payment</p>
                            <div class="max-w-40">
                                {!! base64_decode($qrCode) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

@include('client.partials.payment-popup')

<script>
    let currentInvoiceId = null;
    let currentAmount = 0;

    function payInvoice(invoiceId, amount) {
        currentInvoiceId = invoiceId;
        currentAmount = amount;
        document.getElementById('invoice-id-field').value = invoiceId;
        document.getElementById('amount-display').textContent = amount.toFixed(2);
        document.getElementById('payment-total').textContent = amount.toFixed(2);
        document.getElementById('payment-status').textContent = '';
        document.getElementById('paymentModal').classList.add('active');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.remove('active');
    }

    document.getElementById('payment-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const gateway = formData.get('payment_method');
        const invoiceId = formData.get('invoice_id');

        if (!gateway) {
            alert('Please select a payment method.');
            return;
        }

        const payButton = e.target.querySelector('button[type="submit"]');
        payButton.disabled = true;
        payButton.textContent = 'Initializing...';

        try {
            const response = await fetch(`/client/invoices/${invoiceId}/pay`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ payment_method: gateway }),
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Unable to initialize payment');
            }

            if (result.checkout_url) {
                window.location.href = result.checkout_url;
                return;
            }

            alert(result.message || 'Payment initialized successfully.');
            closePaymentModal();
            window.location.reload();
        } catch (error) {
            alert('Payment initialization failed: ' + error.message);
            payButton.disabled = false;
            payButton.textContent = 'Pay {{ config('legal.currency_symbol') }}' + currentAmount.toFixed(2);
        }
    });
</script>
</body>
</html>
