<?php
 
namespace App\Http\Controllers\Client;
 
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
 
class PaymentController extends Controller
{
    public function initiate(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', Rule::in(['gcash', 'paypal', 'rcbc'])],
            'proof_image'    => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);
 
        if ($invoice->client_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
 
        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            return response()->json(['error' => 'Invoice cannot be paid in its current state.'], 422);
        }
 
        try {
            // Store the uploaded proof image
            $path = $request->file('proof_image')->store('payment-proofs', 'public');
 
            // Create the transaction record
            PaymentTransaction::create([
                'invoice_id'     => $invoice->id,
                'appointment_id' => $invoice->appointment_id,
                'client_id'      => auth()->id(),
                'lawyer_id'      => $invoice->lawyer_id,
                'gateway'        => $request->payment_method,
                'amount'         => $invoice->balance,
                'currency'       => config('payment.default_currency', 'PHP'),
                'status'         => 'pending',
                'proof_image'    => $path,
                'metadata'       => [
                    'invoice_number' => $invoice->invoice_number,
                    'client_name'    => $invoice->client->name,
                ],
            ]);
 
            // Update invoice status to under_review
            $invoice->update(['status' => 'under_review']);
 
            Log::info('Payment proof submitted', [
                'invoice_id' => $invoice->id,
                'client_id'  => auth()->id(),
                'method'     => $request->payment_method,
            ]);
 
            return response()->json([
                'success' => true,
                'message' => 'Payment proof submitted. Your lawyer will confirm it shortly.',
            ], 201);
 
        } catch (\Throwable $e) {
            Log::error('Payment proof submission failed', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
 
            return response()->json(['error' => 'Failed to submit payment proof. Please try again.'], 500);
        }
    }
 
    public function payInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        return $this->initiate($request, $invoice);
    }
 
    public function success(Request $request)
    {
        $invoice = null;
        if ($request->query('invoice_id')) {
            $invoice = Invoice::where('id', $request->query('invoice_id'))
                ->where('client_id', auth()->id())
                ->first();
        }
        return view('client.payments.success', compact('invoice'));
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
}
 