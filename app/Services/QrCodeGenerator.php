<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * QR Code Generation Service
 * 
 * Handles secure QR code generation and storage for payment transactions
 * Supports GCash, PayPal, and PayMaya with deterministic payloads
 */
class QrCodeGenerator
{
    /**
     * Generate QR code for GCash payment
     * 
     * @param PaymentTransaction $transaction
     * @return array
     */
    public static function generateGCashQr(PaymentTransaction $transaction): array
    {
        $payload = self::buildGCashPayload($transaction);
        return self::storeQrCode($transaction, $payload, 'gcash');
    }

    /**
     * Generate QR code for PayMaya payment
     * 
     * @param PaymentTransaction $transaction
     * @return array
     */
    public static function generatePayMayaQr(PaymentTransaction $transaction): array
    {
        $payload = self::buildPayMayaPayload($transaction);
        return self::storeQrCode($transaction, $payload, 'paymaya');
    }

    /**
     * Generate QR code for PayPal payment
     * 
     * @param PaymentTransaction $transaction
     * @return array
     */
    public static function generatePayPalQr(PaymentTransaction $transaction): array
    {
        $payload = self::buildPayPalPayload($transaction);
        return self::storeQrCode($transaction, $payload, 'paypal');
    }

    /**
     * Build deterministic payload for GCash
     * Contains: appointmentId|clientId|totalAmount|timestamp
     * 
     * @param PaymentTransaction $transaction
     * @return string
     */
    private static function buildGCashPayload(PaymentTransaction $transaction): string
    {
        return implode('|', [
            'GCASH',
            $transaction->appointment_id ?? 'N/A',
            $transaction->client_id,
            number_format($transaction->amount, 2, '.', ''),
            now()->timestamp,
            $transaction->reference_number ?? '',
        ]);
    }

    /**
     * Build deterministic payload for PayMaya
     * Contains: appointmentId|clientId|totalAmount|timestamp
     * 
     * @param PaymentTransaction $transaction
     * @return string
     */
    private static function buildPayMayaPayload(PaymentTransaction $transaction): string
    {
        return implode('|', [
            'PAYMAYA',
            $transaction->appointment_id ?? 'N/A',
            $transaction->client_id,
            number_format($transaction->amount, 2, '.', ''),
            now()->timestamp,
            $transaction->reference_number ?? '',
        ]);
    }

    /**
     * Build deterministic payload for PayPal
     * Contains: appointmentId|clientId|totalAmount|timestamp
     * 
     * @param PaymentTransaction $transaction
     * @return string
     */
    private static function buildPayPalPayload(PaymentTransaction $transaction): string
    {
        return implode('|', [
            'PAYPAL',
            $transaction->appointment_id ?? 'N/A',
            $transaction->client_id,
            number_format($transaction->amount, 2, '.', ''),
            now()->timestamp,
            $transaction->reference_number ?? '',
        ]);
    }

    /**
     * Store QR code and update transaction
     * 
     * @param PaymentTransaction $transaction
     * @param string $payload
     * @param string $gateway
     * @return array
     */
    private static function storeQrCode(PaymentTransaction $transaction, string $payload, string $gateway): array
    {
        try {
            // Generate QR code as SVG/PNG
            $qrCode = QrCode::size(300)
                ->errorCorrection(config('legal.qr_codes.error_correction', 'M'))
                ->format(config('legal.qr_codes.format', 'png'))
                ->generate($payload);

            // Create unique filename
            $filename = sprintf(
                'qrcodes/%s/%d_%s.%s',
                $gateway,
                $transaction->id,
                Str::random(12),
                config('legal.qr_codes.format', 'png')
            );

            // Store in storage/app/public
            \Storage::disk('public')->put($filename, $qrCode);

            // Generate hashed reference for validation
            $qrHash = hash('sha256', $payload . config('app.key'));

            // Update transaction with QR code details
            $transaction->update([
                'qr_payload' => $payload,
                'qr_image_url' => \Storage::disk('public')->url($filename),
                'reference_hash' => substr($qrHash, 0, 32),
                'metadata' => array_merge(
                    $transaction->metadata ?? [],
                    [
                        'qr_generated_at' => now()->toIso8601String(),
                        'qr_filename' => $filename,
                    ]
                ),
            ]);

            return [
                'success' => true,
                'qr_url' => \Storage::disk('public')->url($filename),
                'reference_hash' => substr($qrHash, 0, 32),
                'payload' => $payload,
            ];
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', [
                'transaction_id' => $transaction->id,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate QR code: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate QR code integrity using stored hash
     * 
     * @param PaymentTransaction $transaction
     * @param string $payload
     * @return bool
     */
    public static function validateQrIntegrity(PaymentTransaction $transaction, string $payload): bool
    {
        $expectedHash = hash('sha256', $payload . config('app.key'));
        $expectedHash = substr($expectedHash, 0, 32);

        return hash_equals($transaction->reference_hash, $expectedHash);
    }

    /**
     * Get QR code URL for transaction
     * 
     * @param PaymentTransaction $transaction
     * @return string|null
     */
    public static function getQrUrl(PaymentTransaction $transaction): ?string
    {
        return $transaction->qr_image_url;
    }

    /**
     * Regenerate QR code if needed
     * 
     * @param PaymentTransaction $transaction
     * @return array
     */
    public static function regenerateQrCode(PaymentTransaction $transaction): array
    {
        // Delete old QR code file if exists
        if ($transaction->metadata['qr_filename'] ?? null) {
            \Storage::disk('public')->delete($transaction->metadata['qr_filename']);
        }

        // Generate new QR code based on gateway
        return match ($transaction->gateway) {
            'gcash' => self::generateGCashQr($transaction),
            'paymaya' => self::generatePayMayaQr($transaction),
            'paypal' => self::generatePayPalQr($transaction),
            default => ['success' => false, 'error' => 'Invalid gateway'],
        };
    }

    /**
     * Validate QR code from payment confirmation
     * 
     * @param string $reference_hash
     * @param string $payload
     * @return bool
     */
    public static function validateQrFromPayment(string $reference_hash, string $payload): bool
    {
        $expectedHash = hash('sha256', $payload . config('app.key'));
        $expectedHash = substr($expectedHash, 0, 32);

        return hash_equals($reference_hash, $expectedHash);
    }

    /**
     * Generate a simple invoice verification QR code as base64 SVG.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function generateInvoiceQr(Invoice $invoice): string
    {
        $data = implode("\n", [
            'LegalCase Invoice',
            'Invoice No: ' . $invoice->invoice_number,
            'Client: ' . optional($invoice->client)->name,
            'Amount: ₱' . number_format($invoice->total, 2),
            'Status: ' . ucfirst($invoice->status),
            'Date: ' . optional($invoice->issued_date)->format('F j, Y'),
        ]);

        return base64_encode(QrCode::format('svg')
            ->size(150)
            ->generate($data)
        );
    }
}
