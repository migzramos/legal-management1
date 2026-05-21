<?php
namespace App\Observers;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Revenue;
use App\Services\RevenueService;
use Illuminate\Support\Facades\DB;

class PaymentTransactionObserver
{
    public function created(PaymentTransaction $transaction): void
    {
        if ($transaction->status === 'paid') {
            $this->markInvoicePaid($transaction);
        }
    }

    public function updated(PaymentTransaction $transaction): void
    {
        if (! $transaction->wasChanged('status')) {
            return;
        }

        if ($transaction->status === 'paid') {
            $this->markInvoicePaid($transaction);
        }

        if ($transaction->status === 'completed') {
            $alreadyHasRevenue = Revenue::where('payment_transaction_id', $transaction->id)->exists();

            if ($alreadyHasRevenue) {
                return;
            }

            RevenueService::createRevenueFromInvoice($transaction->invoice, $transaction->id);
        }
    }

    private function markInvoicePaid(PaymentTransaction $transaction): void
    {
        $invoice = $transaction->invoice;

        if (! $invoice || $invoice->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($invoice) {
            if (empty($invoice->or_number)) {
                $invoice->or_number = Invoice::generateOrNumber();
            }

            $invoice->status = 'paid';
            $invoice->paid_date = $invoice->paid_date ?? now();
            $invoice->save();
        });
    }
}
