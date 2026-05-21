<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmPaymentTransactionRequest;
use App\Models\PaymentTransaction;
use App\Models\AuditLog;
use App\Services\RevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentTransactionController extends Controller
{
    public function confirmPayment(ConfirmPaymentTransactionRequest $request, PaymentTransaction $transaction): JsonResponse
    {
        if ($transaction->lawyer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // For cash payments, any pending status can be confirmed
        // For other payments, only strictly 'pending' status can be confirmed
        $isCashPayment = $transaction->gateway === 'cash' || 
                        ($transaction->metadata && $transaction->metadata['payment_method_type'] === 'cash');
        
        $validStatus = $isCashPayment ? in_array($transaction->status, ['pending', 'processing']) : $transaction->status === 'pending';
        
        if (!$validStatus) {
            return response()->json([
                'error' => 'Payment cannot be confirmed in its current status.',
                'current_status' => $transaction->status,
            ], 422);
        }

        $confirmed = $request->boolean('confirmed');

        $transaction = DB::transaction(function () use ($transaction, $confirmed, $request) {
            $newStatus = $confirmed ? 'completed' : 'failed';

            $metadata = $transaction->metadata ?? [];
            $metadata['confirmation_status'] = $confirmed ? 'approved' : 'rejected';
            $metadata['confirmation_notes'] = $request->input('notes');
            $metadata['confirmed_at_timestamp'] = now()->toIso8601String();
            $metadata['confirmed_by_user_id'] = auth()->id();

            $transaction->update([
                'status' => $newStatus,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
                'metadata' => $metadata,
            ]);

            if ($confirmed) {
                $invoice = $transaction->invoice;

                RevenueService::createRevenueFromInvoice($invoice, $transaction->id);

                $newBalance = $invoice->balance - $transaction->amount;
                $newInvoiceStatus = $newBalance <= 0 ? 'paid' : 'partial';

                $invoice->update([
                    'amount_paid' => $invoice->amount_paid + $transaction->amount,
                    'balance' => max(0, $newBalance),
                    'status' => $newInvoiceStatus,
                    'paid_date' => $newInvoiceStatus === 'paid' ? now()->toDateString() : $invoice->paid_date,
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'payment_confirmed',
                    'model_type' => PaymentTransaction::class,
                    'model_id' => $transaction->id,
                    'description' => 'Payment confirmed for invoice #' . $invoice->invoice_number,
                    'new_values' => [
                        'status' => $newStatus,
                        'amount' => $transaction->amount,
                        'invoice_status' => $invoice->status,
                        'gateway' => $transaction->gateway,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } else {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'payment_rejected',
                    'model_type' => PaymentTransaction::class,
                    'model_id' => $transaction->id,
                    'description' => 'Payment rejected for invoice #' . $transaction->invoice->invoice_number,
                    'new_values' => [
                        'status' => 'failed',
                        'reason' => $request->input('notes'),
                        'gateway' => $transaction->gateway,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $transaction->fresh();
        });

        return response()->json([
            'success' => true,
            'message' => $confirmed ? 'Payment confirmed successfully and revenue recorded.' : 'Payment rejected.',
            'transaction' => [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'confirmed_at' => $transaction->confirmed_at,
                'amount' => $transaction->amount,
                'gateway' => $transaction->gateway,
            ]
        ]);
    }

    public function getPendingPayments()
    {
        $lawyer = auth()->user();

        $pending = PaymentTransaction::where('lawyer_id', $lawyer->id)
            ->where('status', 'pending')
            ->with(['invoice', 'client:id,name,email'])
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'reference_number' => $t->reference_number,
                'amount' => $t->amount,
                'currency' => $t->currency,
                'gateway' => $t->gateway,
                'client' => $t->client->name,
                'invoice_number' => $t->invoice->invoice_number,
                'created_at' => $t->created_at->toIso8601String(),
            ]);

        return response()->json([
            'count' => $pending->count(),
            'transactions' => $pending,
        ]);
    }
}
