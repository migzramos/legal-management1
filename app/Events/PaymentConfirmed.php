<?php

namespace App\Events;

use App\Models\PaymentTransaction;
use App\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Confirmed Event
 * 
 * Triggered when a lawyer confirms a payment transaction
 * Used for real-time updates, notifications, and workflow automation
 */
class PaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PaymentTransaction $transaction,
        public Invoice $invoice
    ) {
    }
}
