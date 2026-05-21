@extends('layouts.client')
@section('title', 'My Invoices')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="section-header">
    <div>
        <h1 class="section-title">My Invoices</h1>
        <p class="section-subtitle">Manage and pay your outstanding legal fees.</p>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
    <div class="kpi-card">
        <div class="kpi-card-top"><span class="kpi-label">Total Billed</span></div>
        <div class="kpi-value">&#8369;{{ number_format($totalBilled??0,2) }}</div>
        <div class="kpi-meta">All time</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card-top"><span class="kpi-label">Amount Paid</span></div>
        <div class="kpi-value" style="color:var(--success);">&#8369;{{ number_format($totalPaid??0,2) }}</div>
        <div class="kpi-meta">Settled</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card-top"><span class="kpi-label">Outstanding</span></div>
        <div class="kpi-value" style="color:var(--danger);">&#8369;{{ number_format($totalOutstanding??0,2) }}</div>
        <div class="kpi-meta">Due now</div>
    </div>
</div>

<div class="tabs" style="margin-bottom:20px;">
    @foreach(['all'=>'All','unpaid'=>'Unpaid','paid'=>'Paid','overdue'=>'Overdue','partial'=>'Partial'] as $key=>$label)
    <a href="{{ route('client.invoices.index',['status'=>$key==='all'?null:$key]) }}" class="tab {{ ($currentFilter??'all')===$key?'active':'' }}">
        {{ $label }}<span class="tab-badge">{{ $tabCounts[$key]??0 }}</span>
    </a>
    @endforeach
</div>

<div class="card">
    <div class="card-body-flush">
        @forelse($invoices as $invoice)
        <div class="list-item">
            <div class="list-item-left">
                <div style="font-size:0.88rem;font-weight:600;color:var(--text-primary);margin-bottom:4px;">
                    {{ $invoice->invoice_number ?? 'INV-'.str_pad($invoice->id,4,'0',STR_PAD_LEFT) }}
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);">
                    Issued: {{ \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') }}
                    &nbsp;·&nbsp; Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                </div>
            </div>
            <div class="list-item-right">
                <span style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;font-weight:600;color:var(--text-primary);">&#8369;{{ number_format($invoice->total,2) }}</span>
                <span class="status-{{ strtolower($invoice->status) }}">{{ ucfirst($invoice->status) }}</span>
                @if(in_array(strtolower($invoice->status),['unpaid','partial','overdue']))
                <button type="button" class="btn-primary" style="padding:7px 14px;font-size:0.78rem;"
                    onclick="openPayModal({{ $invoice->id }},'{{ addslashes($invoice->invoice_number??'INV-'.str_pad($invoice->id,4,'0',STR_PAD_LEFT)) }}',{{ $invoice->total }})">Pay Now</button>
                @else
                <a href="{{ route('client.invoices.show',$invoice->id) }}" class="btn-icon" title="View">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-state-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
            <div class="empty-state-title">No invoices found</div>
            <div class="empty-state-text">Invoices from your lawyer will appear here.</div>
        </div>
        @endforelse
    </div>
</div>

{{ $invoices->links() }}

<div class="modal-overlay" id="payModal">
    <div class="modal-box">
        <div class="modal-header">
            <div><div class="modal-title">Pay Invoice</div><div class="modal-sub" id="payModalSub">Select your payment method below.</div></div>
            <button type="button" class="modal-close" onclick="closePayModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form method="POST" id="payForm" action="">
                @csrf
                <input type="hidden" name="payment_method" id="paymentMethodInput" value="">
                <input type="hidden" name="invoice_id" id="payInvoiceId" value="">
                <div style="margin-bottom:18px;padding:14px 16px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.78rem;color:var(--text-muted);">Amount Due</span>
                    <span style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:600;color:var(--text-primary);" id="payModalAmount">&#8369;0.00</span>
                </div>
                <div class="form-label" style="margin-bottom:10px;">Payment Method</div>
                <div class="payment-methods-grid">
                    <button id="method-gcash" type="button" class="payment-method-btn" onclick="selectPaymentMethod('gcash', this)"><div class="payment-method-icon" style="background:rgba(0,150,57,0.12);">
                            <svg viewBox="0 0 40 40" fill="none" style="width:24px;height:24px;"><circle cx="20" cy="20" r="20" fill="#009639"/><text x="20" y="26" text-anchor="middle" font-size="14" font-weight="700" fill="white" font-family="DM Sans,sans-serif">G</text></svg>
                        </div><span>GCash</span></button>
                    <button id="method-maya" type="button" class="payment-method-btn" onclick="selectPaymentMethod('maya', this)"><div class="payment-method-icon" style="background:rgba(100,55,200,0.12);">
                            <svg viewBox="0 0 40 40" fill="none" style="width:24px;height:24px;"><circle cx="20" cy="20" r="20" fill="#6437C8"/><text x="20" y="26" text-anchor="middle" font-size="14" font-weight="700" fill="white" font-family="DM Sans,sans-serif">M</text></svg>
                        </div><span>Maya</span></button>
                    <button id="method-card" type="button" class="payment-method-btn" onclick="selectPaymentMethod('card', this)"><div class="payment-method-icon" style="background:rgba(124,58,237,0.12);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" style="width:22px;height:22px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div><span>Credit / Debit Card</span></button>
                    <button id="method-bank_transfer" type="button" class="payment-method-btn" onclick="selectPaymentMethod('bank', this)"><div class="payment-method-icon" style="background:rgba(96,165,250,0.12);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" style="width:22px;height:22px;"><path d="M3 22V12M6 22V12M10 22V12M14 22V12M18 22V12M21 22V12M2 12L12 2l10 10H2z"/></svg>
                        </div><span>Bank Transfer</span></button>
                </div>
                <div class="form-group" id="referenceGroup" style="display:none;">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="Enter transaction reference">
                    <div class="form-hint">Enter the reference number from your payment provider.</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePayModal()">Cancel</button>
            <button type="button" class="btn-primary" id="paySubmitBtn" onclick="submitPayment()" disabled style="opacity:0.5;">Confirm Payment</button>
        </div>
    </div>
</div>
<script>
let selectedPaymentMethod=null;
function openPayModal(id,num,amt){
    document.getElementById('payInvoiceId').value=id;
    document.getElementById('payForm').action='/client/invoices/'+id+'/pay';
    document.getElementById('payModalSub').textContent=num;
    document.getElementById('payModalAmount').textContent='₱'+parseFloat(amt).toLocaleString('en-PH',{minimumFractionDigits:2});
    document.getElementById('payModal').classList.add('active');
    selectedPaymentMethod=null;
    document.querySelectorAll('.payment-method-btn').forEach(b=>b.classList.remove('selected'));
    document.getElementById('paySubmitBtn').disabled=true;
    document.getElementById('paySubmitBtn').style.opacity='0.5';
    document.getElementById('referenceGroup').style.display='none';
}
function closePayModal(){document.getElementById('payModal').classList.remove('active');}
function selectPaymentMethod(method,btn){
    selectedPaymentMethod=method;
    document.getElementById('paymentMethodInput').value=method;
    document.querySelectorAll('.payment-method-btn').forEach(b=>b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('paySubmitBtn').disabled=false;
    document.getElementById('paySubmitBtn').style.opacity='1';
    document.getElementById('referenceGroup').style.display='block';
}
function submitPayment(){if(selectedPaymentMethod)document.getElementById('payForm').submit();}
document.getElementById('payModal').addEventListener('click',function(e){if(e.target===this)closePayModal();});
</script>
@endsection
