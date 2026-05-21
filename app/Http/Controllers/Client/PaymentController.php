<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\InvoicePaid;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'gateways' => config('payment.gateways'),
            'default_currency' => config('payment.default_currency'),
            'pending_transactions' => PaymentTransaction::where('client_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->get(),
        ]);
    }

    public function createSetupIntent(): JsonResponse
    {
        $user = auth()->user();

        if (!config('services.stripe.secret')) {
            return response()->json([
                'error' => 'Stripe support is not enabled for this account.',
            ], 422);
        }

        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        $intent = $user->createSetupIntent();

        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        if (!config('services.stripe.secret')) {
            return response()->json(['error' => 'Stripe support is not enabled.'], 422);
        }

        $user = auth()->user();

        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        $paymentMethod = $user->addPaymentMethod($request->payment_method);

        if ($request->has('set_default') && $request->set_default) {
            $user->updateDefaultPaymentMethod($paymentMethod->id);
        }

        return response()->json([
            'message' => 'Payment method added successfully',
            'payment_method' => $paymentMethod,
        ]);
    }

    public function initiate(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'payment_method' => [
                'required',
                Rule::in(['gcash', 'paymaya', 'card', 'bank_transfer', 'dob', 'billease']),
            ],
        ]);

        if ($invoice->client_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            return response()->json(['error' => 'Invoice cannot be paid in its current state.'], 422);
        }

        $method = $request->payment_method;
        $paymongoMethod = $method === 'bank_transfer' ? 'dob' : $method;

        try {
            $paymentLink = PaymentGatewayService::createPaymentLink($invoice, $paymongoMethod);

            $transaction = PaymentTransaction::create([
                'invoice_id' => $invoice->id,
                'appointment_id' => $invoice->appointment_id,
                'client_id' => auth()->id(),
                'lawyer_id' => $invoice->lawyer_id,
                'gateway' => $method,
                'amount' => $invoice->balance,
                'currency' => config('payment.default_currency'),
                'status' => 'pending',
                'reference_number' => $paymentLink['source_id'] ?? null,
                'payment_details' => [
                    'paymongo' => $paymentLink['response'] ?? [],
                    'method' => $method,
                ],
                'metadata' => [
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client->name,
                ],
            ]);

            return response()->json([
                'message' => 'Redirecting to PayMongo checkout.',
                'checkout_url' => $paymentLink['checkout_url'],
                'transaction_id' => $transaction->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('PayMongo initiate payment failed', [
                'invoice_id' => $invoice->id,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Unable to initiate payment. Please try again.'], 500);
        }
    }

    public function payInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        return $this->initiate($request, $invoice);
    }

    public function success(Request $request)
    {
        $invoice = null;
        $amount = $request->query('amount');

        if ($request->query('invoice_id')) {
            $invoice = Invoice::where('id', $request->query('invoice_id'))
                ->where('client_id', auth()->id())
                ->first();
        }

        return view('client.payments.success', compact('invoice', 'amount'));
    }

    public function failed(Request $request)
    {
        $invoice = null;
        if ($request->query('invoice_id')) {
            $invoice = Invoice::where('id', $request->query('invoice_id'))
                ->where('client_id', auth()->id())
                ->first();
        }

        $reason = $request->query('reason', 'Your payment attempt was not completed.');

        return view('client.payments.failed', compact('invoice', 'reason'));
    }

    public function webhook(Request $request)
    {
        if (!$this->verifyPaymongoSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $invoice = PaymentGatewayService::handleWebhook($request->json()->all());

        if ($invoice) {
            $invoice->loadMissing('lawyer:id,name,email', 'client:id,name,email');
            if ($invoice->lawyer && $invoice->lawyer->email) {
                Mail::to($invoice->lawyer->email)->queue(new InvoicePaid($invoice));
            }
        }

        return response()->json(['received' => true], 200);
    }

    private function verifyPaymongoSignature(Request $request): bool
    {
        $signatureHeader = $request->header('Paymongo-Signature') ?? $request->header('X-Paymongo-Signature');
        if (!$signatureHeader) {
            return false;
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload, config('payment.paymongo.secret_key'));

        if (str_contains($signatureHeader, 'v1=')) {
            preg_match('/v1=(?<sig>[a-f0-9]+)/', $signatureHeader, $matches);
            $signatureHeader = $matches['sig'] ?? $signatureHeader;
        }

        return hash_equals($expected, $signatureHeader);
    }
}
