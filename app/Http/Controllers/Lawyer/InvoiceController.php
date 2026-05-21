<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LegalCase;
use App\Models\TimeEntry;
use App\Services\InvoiceValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('client:id,name,email', 'case:id,title,case_number')
            ->where('lawyer_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('lawyer.invoices', compact('invoices'));
    }

    public function create()
    {
        $cases = LegalCase::where('lawyer_id', auth()->id())
            ->where('status', '!=', 'resolution')
            ->with('client:id,name,email')
            ->get();

        return view('lawyer.invoice-create', compact('cases'));
    }

    // FIX BUG 2: Removed wrong ': JsonResponse' return type — method returns RedirectResponse
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $case = LegalCase::findOrFail($request->case_id);
        $this->authorize('update', $case);

        $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $tax      = $request->tax ?? 0;
        $total    = $subtotal + $tax;

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
            'case_id'        => $request->case_id,   // FIX BUG 4: case_id now in Invoice::$fillable
            'client_id'      => $request->client_id,
            'lawyer_id'      => auth()->id(),
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'total'          => $total,
            'amount_paid'    => 0,
            'balance'        => $total,
            'status'         => 'draft',
            'issued_date'    => $request->issued_date,
            'due_date'       => $request->due_date,
            'notes'          => $request->notes,
        ]);

        foreach ($request->items as $item) {
            InvoiceItem::create([
                'invoice_id'    => $invoice->id,
                'time_entry_id' => $item['time_entry_id'] ?? null,
                'description'   => $item['description'],
                'quantity'      => $item['quantity'],
                'unit_price'    => $item['unit_price'],
                'total'         => $item['quantity'] * $item['unit_price'],
            ]);

            if (!empty($item['time_entry_id'])) {
                TimeEntry::where('id', $item['time_entry_id'])
                    ->update(['is_billed' => true]);
            }
        }

        // FIX BUG 1: Use inherited auditLog() from base Controller instead of private log()
        $this->auditLog('created_invoice', $invoice);

        return redirect()
            ->route('lawyer.billing.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['client:id,name,email', 'case:id,title,case_number', 'items', 'paymentTransactions']);

        return view('lawyer.invoice-detail', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->is_validated) {
            return back()->withErrors(['status' => 'Cannot edit a validated invoice. All financial data is locked.']);
        }

        $cases = LegalCase::where('lawyer_id', auth()->id())
            ->where('status', '!=', 'resolution')
            ->with('client:id,name,email')
            ->get();

        return view('lawyer.invoice-edit', compact('invoice', 'cases'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if ($invoice->is_validated) {
            return back()->withErrors(['status' => 'Cannot edit a validated invoice. Financial data is locked.']);
        }

        $old      = $invoice->toArray();
        $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $tax      = $request->tax ?? 0;
        $total    = $subtotal + $tax;

        $invoice->update([
            'case_id'     => $request->case_id,
            'client_id'   => $request->client_id,
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'total'       => $total,
            'balance'     => $total - $invoice->amount_paid,
            'issued_date' => $request->issued_date,
            'due_date'    => $request->due_date,
            'notes'       => $request->notes,
        ]);

        $invoice->items()->delete();

        foreach ($request->items as $item) {
            InvoiceItem::create([
                'invoice_id'    => $invoice->id,
                'time_entry_id' => $item['time_entry_id'] ?? null,
                'description'   => $item['description'],
                'quantity'      => $item['quantity'],
                'unit_price'    => $item['unit_price'],
                'total'         => $item['quantity'] * $item['unit_price'],
            ]);

            if (!empty($item['time_entry_id'])) {
                TimeEntry::where('id', $item['time_entry_id'])
                    ->update(['is_billed' => true]);
            }
        }

        $this->auditLog('updated_invoice', $invoice, $old, $invoice->fresh()->toArray());

        return redirect()
            ->route('lawyer.billing.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Record manual / offline / cash payment.
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $data = $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'paid_date' => 'required|date',
        ]);

        if ($data['amount'] > $invoice->balance) {
            return response()->json(['error' => 'Payment amount exceeds outstanding balance.'], 422);
        }

        $newAmountPaid = $invoice->amount_paid + $data['amount'];
        $newBalance    = $invoice->total - $newAmountPaid;
        $status        = $newBalance <= 0 ? 'paid' : 'partial';
        $oldValues     = $invoice->toArray();

        $invoice->update([
            'amount_paid' => $newAmountPaid,
            'balance'     => max(0, $newBalance),
            'status'      => $status,
            'paid_date'   => $status === 'paid' ? $data['paid_date'] : null,
        ]);

        $this->auditLog('payment_recorded', $invoice, $oldValues, $invoice->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'invoice' => [
                'amount_paid' => money_display($invoice->amount_paid),
                'balance'     => money_display($invoice->balance),
                'status'      => $invoice->status,
            ],
        ]);
    }

    /**
     * Validate invoice — locks all financial data after this point.
     */
    public function validateInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $notes  = $request->input('notes');
        $result = InvoiceValidationService::validateInvoice($invoice, auth()->id(), $notes);

        if (!$result['success']) {
            Log::warning('Invoice validation failed', [
                'invoice_id' => $invoice->id,
                'error'      => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json([
                'success' => false,
                'error'   => $result['error'],
                'code'    => $result['code'] ?? 'VALIDATION_FAILED',
            ], 422);
        }

        Log::info('Invoice validated successfully', [
            'invoice_id'   => $invoice->id,
            'amount'       => $invoice->total,
            'validated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice validated successfully. Financial data is now locked.',
            'invoice' => [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'is_validated'   => true,
                'status'         => 'sent',
            ],
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        if ($invoice->is_validated) {
            return back()->withErrors(['status' => 'Cannot delete a validated invoice.']);
        }

        // FIX BUG 1: Capture invoice_number BEFORE delete so it's not lost after soft-delete
        $invoiceNumber = $invoice->invoice_number;

        $this->auditLog('deleted_invoice', $invoice);
        $invoice->delete();

        // FIX BUG 2: Corrected route name from 'lawyer.invoices.index' → 'lawyer.billing.index'
        return redirect()
            ->route('lawyer.billing.index')
            ->with('success', "Invoice #{$invoiceNumber} deleted successfully.");
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['client', 'lawyer', 'items', 'case']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('lawyer.invoice-pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}