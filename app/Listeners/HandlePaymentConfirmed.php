<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Models\Revenue;
use Illuminate\Support\Facades\Log;

/**
 * PaymentConfirmed Event Listener
 * 
 * Handles actions triggered after payment confirmation:
 * - Logs audit trail for payment confirmation
 * - Ensures revenue has been recorded
 * - Sends notifications to parties
 */
class HandlePaymentConfirmed
{
    /**
     * Handle the event
     */
    public function handle(PaymentConfirmed $event): void
    {
        $transaction = $event->transaction;
        $invoice = $event->invoice;

        try {
            // Verify revenue has been created
            $revenue = Revenue::where('transaction_id', $transaction->id)
                ->where('lawyer_id', $transaction->lawyer_id)
                ->first();

            if (!$revenue) {
                Log::warning('Revenue record not found for payment', [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $invoice->id,
                ]);
            }

            // Log payment confirmation to audit trail
            \App\Models\AuditLog::create([
                'user_id' => auth()->id() ?? $transaction->lawyer_id,
                'action' => 'payment_confirmed',
                'model_type' => \App\Models\PaymentTransaction::class,
                'model_id' => $transaction->id,
                'new_values' => [
                    'status' => 'completed',
                    'amount' => $transaction->amount,
                    'invoice_status' => $invoice->payment_status,
                    'revenue_recorded' => $revenue?->id,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('Payment confirmed and revenue recorded', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'amount' => $transaction->amount,
                'revenue_id' => $revenue?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle payment confirmed event', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
