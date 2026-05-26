<?php
 
namespace App\Http\Controllers\Lawyer;
 
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
 
class PaymentTransactionController extends Controller
{
    public function confirm(PaymentTransaction $transaction): JsonResponse
    {
        if ($transaction->lawyer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
 
        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Transaction is not pending.'], 422);
        }
 
        try {
            DB::transaction(function () use ($transaction) {
                $invoice = $transaction->invoice;
 
                // Mark transaction as completed
                $transaction->update(['status' => 'completed']);
 
                // Update invoice to paid
                $invoice->update([
                    'amount_paid' => $invoice->total,
                    'balance'     => 0,
                    'status'      => 'paid',
                    'paid_date'   => now(),
                ]);
            });
 
            Log::info('Payment confirmed by lawyer', [
                'transaction_id' => $transaction->id,
                'invoice_id'     => $transaction->invoice_id,
                'lawyer_id'      => auth()->id(),
            ]);
 
            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed. Invoice marked as paid.',
            ]);
 
        } catch (\Throwable $e) {
            Log::error('Payment confirmation failed', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
 
            return response()->json(['error' => 'Failed to confirm payment.'], 500);
        }
    }
 
    public function reject(PaymentTransaction $transaction): JsonResponse
    {
        if ($transaction->lawyer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
 
        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Transaction is not pending.'], 422);
        }
 
        try {
            DB::transaction(function () use ($transaction) {
                // Mark transaction as failed
                $transaction->update(['status' => 'failed']);
 
                // Revert invoice back to sent
                $transaction->invoice->update(['status' => 'sent']);
            });
 
            Log::info('Payment rejected by lawyer', [
                'transaction_id' => $transaction->id,
                'invoice_id'     => $transaction->invoice_id,
                'lawyer_id'      => auth()->id(),
            ]);
 
            return response()->json([
                'success' => true,
                'message' => 'Payment rejected. Invoice returned to sent status.',
            ]);
 
        } catch (\Throwable $e) {
            Log::error('Payment rejection failed', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
 
            return response()->json(['error' => 'Failed to reject payment.'], 500);
        }
    }
}
 