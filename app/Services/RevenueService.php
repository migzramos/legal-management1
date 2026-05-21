<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class RevenueService
{
    public static function createRevenueFromInvoice(Invoice $invoice, ?int $paymentTransactionId = null): Revenue
    {
        return DB::transaction(function () use ($invoice, $paymentTransactionId) {
            $amount = $invoice->total;
            $description = "Revenue from invoice {$invoice->invoice_number}";
            $category = $invoice->appointment_id ? 'appointment' : 'case';

            if ($paymentTransactionId) {
                $transaction = PaymentTransaction::find($paymentTransactionId);
                if ($transaction) {
                    $amount = $transaction->amount;
                    $description = "Revenue from payment {$transaction->reference_number} for invoice {$invoice->invoice_number}";
                }
            }

            return Revenue::create([
                'lawyer_id' => $invoice->lawyer_id,
                'invoice_id' => $invoice->id,
                'payment_transaction_id' => $paymentTransactionId,
                'amount' => $amount,
                'currency' => config('payment.default_currency'),
                'revenue_date' => now()->toDateString(),
                'category' => $category,
                'description' => $description,
                'is_reconciled' => false,
            ]);
        });
    }

    public static function getLawyerMonthlyRevenue(int $lawyerId, int $month = null, int $year = null): float
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return Revenue::where('lawyer_id', $lawyerId)
            ->whereMonth('revenue_date', $month)
            ->whereYear('revenue_date', $year)
            ->sum('amount');
    }

    public static function getLawyerYearlyRevenue(int $lawyerId, int $year = null): float
    {
        $year = $year ?? now()->year;

        return Revenue::where('lawyer_id', $lawyerId)
            ->whereYear('revenue_date', $year)
            ->sum('amount');
    }

    public static function getLawyerTotalRevenue(int $lawyerId): float
    {
        return Revenue::where('lawyer_id', $lawyerId)->sum('amount');
    }

    public static function reconcileRevenue(int $lawyerId, int $month, int $year): array
    {
        return DB::transaction(function () use ($lawyerId, $month, $year) {
            $revenues = Revenue::where('lawyer_id', $lawyerId)
                ->whereMonth('revenue_date', $month)
                ->whereYear('revenue_date', $year)
                ->where('is_reconciled', false)
                ->get();

            $total = 0;
            foreach ($revenues as $revenue) {
                $revenue->update(['is_reconciled' => true]);
                $total += $revenue->amount;
            }

            return [
                'count' => $revenues->count(),
                'total' => $total,
                'month' => $month,
                'year' => $year,
            ];
        });
    }
}
