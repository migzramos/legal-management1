<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Payment Method Handler Service
 * 
 * Manages payment processing for all payment methods:
 * - GCash (with QR)
 * - PayPal (with QR)
 * - PayMaya (with QR)
 * - Bank Transfer (with reference number)
 * - Cash (manual confirmation)
 * 
 * Ensures transactional integrity and proper state management
 */
class PaymentMethodHandler
{
    /**
     * Initialize payment for a given method
     * 
     * @param Invoice $invoice
     * @param string $paymentMethod
     * @param array $metadata
     * @return PaymentTransaction|array
     */
    public static function initializePayment(Invoice $invoice, string $paymentMethod, array $metadata = []): PaymentTransaction|array
    {
        // Validate payment method is enabled
        $gatewayConfig = config("payment.gateways.{$paymentMethod}");
        
        if (!$gatewayConfig || !$gatewayConfig['enabled']) {
            return [
                'success' => false,
                'error' => 'Payment method is not available.',
            ];
        }

        return DB::transaction(function () use ($invoice, $paymentMethod, $gatewayConfig, $metadata) {
            $appointment = $invoice->appointment;

            // Create payment transaction
            $transaction = PaymentTransaction::create([
                'invoice_id' => $invoice->id,
                'appointment_id' => $appointment?->id,
                'client_id' => $invoice->client_id,
                'lawyer_id' => $invoice->lawyer_id,
                'gateway' => $paymentMethod,
                'amount' => $invoice->total_amount,
                'currency' => config('legal.currency', 'PHP'),
                'status' => 'pending',
                'metadata' => array_merge($metadata, [
                    'created_at_user' => auth()->id(),
                    'payment_method_label' => $gatewayConfig['label'],
                ]),
            ]);

            // Process based on payment method
            $result = match ($paymentMethod) {
                'gcash' => self::handleGCash($transaction),
                'paymaya' => self::handlePayMaya($transaction),
                'paypal' => self::handlePayPal($transaction),
                'bank_transfer' => self::handleBankTransfer($transaction),
                'cash' => self::handleCash($transaction),
                default => ['success' => false, 'error' => 'Invalid payment method.'],
            };

            if (is_array($result) && !$result['success']) {
                // Delete transaction if processing failed
                $transaction->delete();
                return $result;
            }

            // Log payment initialization
            Log::info('Payment initialized', [
                'transaction_id' => $transaction->id,
                'method' => $paymentMethod,
                'amount' => $transaction->amount,
            ]);

            return $transaction;
        });
    }

    /**
     * Handle GCash payment method
     * 
     * @param PaymentTransaction $transaction
     * @return PaymentTransaction|array
     */
    private static function handleGCash(PaymentTransaction $transaction): PaymentTransaction|array
    {
        // Generate reference number
        $referenceConfig = config('payment.gateways.gcash.reference_format');
        $reference = self::formatReference($referenceConfig, $transaction);

        $transaction->update(['reference_number' => $reference]);

        // Generate QR code
        $qrResult = QrCodeGenerator::generateGCashQr($transaction);

        if (!$qrResult['success']) {
            return $qrResult;
        }

        // Update transaction with QR details
        $transaction->update([
            'metadata' => array_merge($transaction->metadata, [
                'qr_generated' => true,
                'qr_url' => $qrResult['qr_url'],
            ]),
        ]);

        return $transaction;
    }

    /**
     * Handle PayMaya payment method
     * 
     * @param PaymentTransaction $transaction
     * @return PaymentTransaction|array
     */
    private static function handlePayMaya(PaymentTransaction $transaction): PaymentTransaction|array
    {
        // Generate reference number
        $referenceConfig = config('payment.gateways.paymaya.reference_format');
        $reference = self::formatReference($referenceConfig, $transaction);

        $transaction->update(['reference_number' => $reference]);

        // Generate QR code
        $qrResult = QrCodeGenerator::generatePayMayaQr($transaction);

        if (!$qrResult['success']) {
            return $qrResult;
        }

        // Update transaction with QR details
        $transaction->update([
            'metadata' => array_merge($transaction->metadata, [
                'qr_generated' => true,
                'qr_url' => $qrResult['qr_url'],
            ]),
        ]);

        return $transaction;
    }

    /**
     * Handle PayPal payment method
     * 
     * @param PaymentTransaction $transaction
     * @return PaymentTransaction|array
     */
    private static function handlePayPal(PaymentTransaction $transaction): PaymentTransaction|array
    {
        // Generate reference number
        $referenceConfig = config('payment.gateways.paypal.reference_format');
        $reference = self::formatReference($referenceConfig, $transaction);

        $transaction->update(['reference_number' => $reference]);

        // Generate QR code for PayPal
        $qrResult = QrCodeGenerator::generatePayPalQr($transaction);

        if (!$qrResult['success']) {
            return $qrResult;
        }

        // Update transaction with QR details
        $transaction->update([
            'metadata' => array_merge($transaction->metadata, [
                'qr_generated' => true,
                'qr_url' => $qrResult['qr_url'],
            ]),
        ]);

        return $transaction;
    }

    /**
     * Handle Bank Transfer payment method
     * 
     * @param PaymentTransaction $transaction
     * @return PaymentTransaction|array
     */
    private static function handleBankTransfer(PaymentTransaction $transaction): PaymentTransaction|array
    {
        // Generate unique bank transfer reference
        $result = BankTransferReferenceGenerator::generateAndStore($transaction);

        if (!$result['success']) {
            return $result;
        }

        // Store bank account details in metadata
        $transaction->update([
            'metadata' => array_merge($transaction->metadata, [
                'bank_account_name' => config('payment.gateways.bank_transfer.account_name'),
                'bank_account_number' => config('payment.gateways.bank_transfer.account_number'),
                'bank_name' => config('payment.gateways.bank_transfer.bank_name'),
                'bank_routing_number' => config('payment.gateways.bank_transfer.routing_number'),
                'reference_number_base' => $result['reference_number'],
            ]),
        ]);

        return $transaction;
    }

    /**
     * Handle Cash payment method
     * 
     * @param PaymentTransaction $transaction
     * @return PaymentTransaction|array
     */
    private static function handleCash(PaymentTransaction $transaction): PaymentTransaction|array
    {
        // For cash, set status to pending and mark as requiring manual confirmation
        // Lawyer must manually verify and confirm receipt within 3 days
        $transaction->update([
            'status' => 'pending',
            'metadata' => array_merge($transaction->metadata, [
                'payment_method_type' => 'cash',
                'requires_manual_confirmation' => true,
                'manual_confirmation_deadline' => now()->addDays(3)->toIso8601String(),
                'confirmation_initiated_by' => auth()->id(),
                'instructions' => 'Cash payment received. Lawyer must manually verify and confirm receipt.',
            ]),
        ]);

        return $transaction;
    }

    /**
     * Format reference number with template variables
     * Supported: {timestamp}, {random}, {lawyer_initials}, {appointment_id}, {client_id}
     * 
     * @param string $format
     * @param PaymentTransaction $transaction
     * @return string
     */
    private static function formatReference(string $format, PaymentTransaction $transaction): string
    {
        $lawyer = $transaction->lawyer;
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(\Str::random(8), 0, 6));
        $lawyerInitials = substr($lawyer->name, 0, 1) . 
                         substr(explode(' ', $lawyer->name)[1] ?? '', 0, 1) .
                         substr(explode(' ', $lawyer->name)[2] ?? '', 0, 1);

        return str_replace(
            ['{timestamp}', '{random}', '{lawyer_initials}', '{appointment_id}', '{client_id}'],
            [$timestamp, $random, strtoupper($lawyerInitials), $transaction->appointment_id, $transaction->client_id],
            $format
        );
    }

    /**
     * Confirm payment and update invoice/revenue
     * 
     * @param PaymentTransaction $transaction
     * @param bool $approved
     * @param array $confirmationData
     * @return array
     */
    public static function confirmPayment(PaymentTransaction $transaction, bool $approved = true, array $confirmationData = []): array
    {
        return DB::transaction(function () use ($transaction, $approved, $confirmationData) {
            if (!$approved) {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata, [
                        'rejection_reason' => $confirmationData['reason'] ?? 'Lawyer rejected payment',
                        'rejected_at' => now()->toIso8601String(),
                        'rejected_by' => auth()->id(),
                    ]),
                ]);

                Log::warning('Payment rejected', [
                    'transaction_id' => $transaction->id,
                    'reason' => $confirmationData['reason'] ?? 'Unknown',
                ]);

                return [
                    'success' => false,
                    'message' => 'Payment was rejected.',
                ];
            }

            // Update invoice status
            $invoice = $transaction->invoice;
            $invoice->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Create revenue record
            Revenue::create([
                'lawyer_id' => $transaction->lawyer_id,
                'appointment_id' => $transaction->appointment_id,
                'invoice_id' => $invoice->id,
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'recorded_at' => now(),
            ]);

            // Update payment transaction
            $transaction->update([
                'status' => 'completed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
                'metadata' => array_merge($transaction->metadata, [
                    'confirmation_data' => $confirmationData,
                    'confirmed_at' => now()->toIso8601String(),
                ]),
            ]);

            // Dispatch event for real-time updates
            \Event::dispatch(new \App\Events\PaymentConfirmed($transaction, $invoice));

            Log::info('Payment confirmed and processed', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'amount' => $transaction->amount,
            ]);

            return [
                'success' => true,
                'message' => 'Payment confirmed successfully.',
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Refund a payment transaction
     * 
     * @param PaymentTransaction $transaction
     * @param array $refundData
     * @return array
     */
    public static function refundPayment(PaymentTransaction $transaction, array $refundData = []): array
    {
        return DB::transaction(function () use ($transaction, $refundData) {
            if (!in_array($transaction->status, ['completed', 'partial'])) {
                return [
                    'success' => false,
                    'error' => 'Can only refund completed or partial payments.',
                ];
            }

            // Update transaction status
            $transaction->update([
                'status' => 'refunded',
                'metadata' => array_merge($transaction->metadata, [
                    'refund_reason' => $refundData['reason'] ?? 'Customer requested refund',
                    'refunded_amount' => $refundData['amount'] ?? $transaction->amount,
                    'refunded_at' => now()->toIso8601String(),
                    'refunded_by' => auth()->id(),
                ]),
            ]);

            // Update invoice status
            $invoice = $transaction->invoice;
            if ($invoice) {
                $invoice->update(['payment_status' => 'refunded']);
            }

            // Create revenue reversal
            if ($transaction->appointment_id) {
                Revenue::create([
                    'lawyer_id' => $transaction->lawyer_id,
                    'appointment_id' => $transaction->appointment_id,
                    'invoice_id' => $invoice?->id,
                    'transaction_id' => $transaction->id,
                    'amount' => -($refundData['amount'] ?? $transaction->amount),
                    'currency' => $transaction->currency,
                    'recorded_at' => now(),
                ]);
            }

            Log::info('Payment refunded', [
                'transaction_id' => $transaction->id,
                'refund_amount' => $refundData['amount'] ?? $transaction->amount,
            ]);

            return [
                'success' => true,
                'message' => 'Payment refunded successfully.',
            ];
        });
    }
}
