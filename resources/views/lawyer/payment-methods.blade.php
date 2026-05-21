@extends('lawyer.layout')

@section('title', 'Payment Methods')

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <h1>Payment Methods</h1>
        <p>Manage and review your available payment options.</p>
    </div>
    <div class="topbar-right">
        <a href="{{ route('lawyer.billing.index') }}" class="btn-secondary">Back to Billing</a>
    </div>
</div>

<div class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment Methods</h3>
        </div>
        <div class="card-body">
            <p>This area is reserved for payment method management.</p>
            <p>At the moment, you can review invoices and add new invoices from the billing area.</p>
            <p>Future updates will include saved cards, bank transfer details, and billing preferences.</p>
        </div>
    </div>
</div>
@endsection
