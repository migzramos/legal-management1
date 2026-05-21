<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Invoice Validation Service
 * 
 * Handles the controlled invoice validation workflow for lawyers
 * Ensures invoices are properly confirmed before payment processing
 * Maintains data integrity and provides audit trail
 */
class InvoiceValidationService
{
    /**
     * Validate and confirm an invoice
     * 
     * @param Invoice $invoice
     * @param int $userId The user confirming the invoice (lawyer ID)
     * @param string|null $notes Optional validation notes
     * @return array Success/failure result
     */
    public static function validateInvoice(Invoice $invoice, int $userId, ?string $notes = null): array
    {
        try {
            // Validate invoice state
            if ($invoice->is_validated) {
                return [
                    'success' => false,
                    'error' => 'Invoice has already been validated.',
                    'code' => 'ALREADY_VALIDATED',
                ];
            }

            if (!in_array($invoice->status, ['draft', 'sent'])) {
                return [
                    'success' => false,
                    'error' => 'Only draft or sent invoices can be validated.',
                    'code' => 'INVALID_STATUS',
                ];
            }

            if ($invoice->total <= 0) {
                return [
                    'success' => false,
                    'error' => 'Invoice total must be greater than zero.',
                    'code' => 'INVALID_AMOUNT',
                ];
            }

            // Validate invoice items exist
            if ($invoice->items()->count() === 0) {
                return [
                    'success' => false,
                    'error' => 'Invoice must contain at least one line item.',
                    'code' => 'NO_ITEMS',
                ];
            }

            // Validate financial accuracy
            $itemsTotal = $invoice->items()->sum(DB::raw('quantity * unit_price'));
            if (abs($itemsTotal - $invoice->subtotal) > 0.01) {
                return [
                    'success' => false,
                    'error' => 'Invoice subtotal does not match line items total.',
                    'code' => 'CALCULATION_MISMATCH',
                ];
            }

            // Perform validation in transaction
            $result = DB::transaction(function () use ($invoice, $userId, $notes) {
                $oldValues = $invoice->toArray();

                // Update invoice validation status
                $invoice->update([
                    'is_validated' => true,
                    'status' => 'sent', // Mark as sent for client notification
                ]);

                // Log the validation action
                AuditLog::create([
                    'user_id' => $userId,
                    'action' => 'invoice_validated',
                    'model_type' => Invoice::class,
                    'model_id' => $invoice->id,
                    'description' => "Invoice #{$invoice->invoice_number} validated - Amount: ₱" . 
                                   number_format($invoice->total, 2),
                    'old_values' => [
                        'is_validated' => $oldValues['is_validated'],
                        'status' => $oldValues['status'],
                    ],
                    'new_values' => [
                        'is_validated' => true,
                        'status' => 'sent',
                        'validation_notes' => $notes,
                        'validated_at' => now()->toIso8601String(),
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                Log::info('Invoice validated', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->total,
                    'validated_by' => $userId,
                    'notes' => $notes,
                ]);

                return [
                    'success' => true,
                    'message' => 'Invoice validated successfully.',
                    'invoice' => $invoice->fresh(),
                ];
            });

            return $result;
        } catch (\Exception $e) {
            Log::error('Invoice validation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while validating the invoice.',
                'code' => 'VALIDATION_ERROR',
            ];
        }
    }

    /**
     * Reject an invoice validation
     * 
     * @param Invoice $invoice
     * @param int $userId
     * @param string $reason
     * @return array
     */
    public static function rejectInvoice(Invoice $invoice, int $userId, string $reason): array
    {
        try {
            if ($invoice->is_validated) {
                return [
                    'success' => false,
                    'error' => 'Cannot reject an already validated invoice.',
                    'code' => 'ALREADY_VALIDATED',
                ];
            }

            return DB::transaction(function () use ($invoice, $userId, $reason) {
                AuditLog::create([
                    'user_id' => $userId,
                    'action' => 'invoice_rejected',
                    'model_type' => Invoice::class,
                    'model_id' => $invoice->id,
                    'description' => "Invoice #{$invoice->invoice_number} rejected",
                    'new_values' => [
                        'rejection_reason' => $reason,
                        'rejected_at' => now()->toIso8601String(),
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Invoice validation rejected.',
                ];
            });
        } catch (\Exception $e) {
            Log::error('Invoice rejection failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to reject invoice.',
            ];
        }
    }

    /**
     * Get validation summary for invoice
     * 
     * @param Invoice $invoice
     * @return array
     */
    public static function getValidationSummary(Invoice $invoice): array
    {
        return [
            'invoice_number' => $invoice->invoice_number,
            'is_validated' => $invoice->is_validated,
            'status' => $invoice->status,
            'total_amount' => money_display($invoice->total),
            'items_count' => $invoice->items()->count(),
            'client_name' => $invoice->client->name,
            'amount_paid' => money_display($invoice->amount_paid),
            'balance' => money_display($invoice->balance),
            'issued_date' => $invoice->issued_date?->format('M d, Y'),
            'due_date' => $invoice->due_date?->format('M d, Y'),
            'validation_status' => $invoice->is_validated ? 'Confirmed' : 'Awaiting Confirmation',
            'can_validate' => !$invoice->is_validated && in_array($invoice->status, ['draft', 'sent']),
            'payment_transactions_count' => $invoice->paymentTransactions()->count(),
        ];
    }

    /**
     * Get all pending validation invoices for a lawyer
     * 
     * @param int $lawyerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingValidationInvoices(int $lawyerId)
    {
        return Invoice::where('lawyer_id', $lawyerId)
            ->where('is_validated', false)
            ->whereIn('status', ['draft', 'sent'])
            ->with(['client:id,name,email', 'appointment:id,appointment_at,duration_minutes', 'items'])
            ->latest()
            ->get();
    }
}
