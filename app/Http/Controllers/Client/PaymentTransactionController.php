<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTransactionRequest;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentTransactionController extends Controller
{
    public function initiatePayment(StorePaymentTransactionRequest $request): JsonResponse
    {
        $invoice = Invoice::findOrFail($request->invoice_id);

        if ($invoice->client_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized access to this invoice.'], 403);
        }

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return response()->json([
                'error' => 'Invoice cannot be paid in its current state.',
            ], 422);
        }

        $amount = $request->amount;
        if ($amount > $invoice->balance) {
            return response()->json([
                'error' => 'Payment amount exceeds outstanding balance.',
                'max_amount' => $invoice->balance,
            ], 422);
        }

        $gateway = $request->gateway;
        $user = auth()->user();

        try {
            $payload = PaymentGatewayService::buildPayload($invoice, $gateway, $amount);

            $transaction = DB::transaction(function () use ($invoice, $gateway, $amount, $user, $payload) {
                $transaction = PaymentTransaction::create([
                    'invoice_id'      => $invoice->id,
                    'appointment_id'  => $invoice->appointment_id,
                    'client_id'       => $user->id,
                    'lawyer_id'       => $invoice->lawyer_id,
                    'gateway'         => $gateway,
                    'amount'          => $amount,
                    'currency'        => config('payment.default_currency'),
                    'status'          => $payload['status'],
                    'reference_number'=> $payload['reference_number'] ?? null,
                    'reference_hash'  => $payload['reference_hash'] ?? null,
                    'qr_payload'      => $payload['qr_payload'] ?? null,
                    'payment_details' => $payload['payment_details'],
                    'metadata'        => $payload['metadata'],
                ]);

                $invoice->update([
                    'payment_gateway'  => $gateway,
                    'payment_reference'=> $transaction->reference_number,
                    'payment_details'  => $transaction->payment_details,
                ]);

                return $transaction;
            });

            $responseData = [
                'message' => 'Payment initiated successfully.',
                'transaction' => [
                    'id' => $transaction->id,
                    'reference_number' => $transaction->reference_number,
                    'reference_hash' => $transaction->reference_hash,
                    'qr_id' => $transaction->payment_details['qr_id'] ?? null,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'gateway' => $transaction->gateway,
                    'status' => $transaction->status,
                    'payment_details' => $transaction->payment_details,
                ],
            ];

            if (in_array($gateway, ['gcash', 'paymaya', 'card', 'dob', 'billease'], true)) {
                $paymentLink = PaymentGatewayService::createPaymentLink($invoice, $gateway);
                $responseData['checkout_url'] = $paymentLink['checkout_url'];
                $responseData['transaction']['payment_details']['paymongo'] = $paymentLink['response'] ?? [];
            }

            if ($transaction->qr_payload) {
                $responseData['transaction']['qr_code'] = PaymentGatewayService::generateQRCode($transaction->qr_payload);
            }

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to initiate payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPaymentMethods(): JsonResponse
    {
        return response()->json([
            'gateways' => collect(config('payment.gateways'))
                ->filter(fn($g) => $g['enabled'])
                ->map(fn($g, $k) => [
                    'id' => $k,
                    'label' => $g['label'],
                    'description' => $g['description'],
                    'qr_enabled' => $g['qr_enabled'] ?? false,
                ])
                ->values(),
            'default_currency' => config('payment.default_currency'),
            'currency_symbol' => config('legal.currency_symbol'),
        ]);
    }

    public function getTransactionStatus(PaymentTransaction $transaction): JsonResponse
    {
        if ($transaction->client_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'id' => $transaction->id,
            'status' => $transaction->status,
            'reference_number' => $transaction->reference_number,
            'amount' => PaymentGatewayService::formatCurrency($transaction->amount),
            'gateway' => $transaction->gateway,
            'created_at' => $transaction->created_at->toIso8601String(),
            'confirmed_at' => $transaction->confirmed_at?->toIso8601String(),
        ]);
    }
}
