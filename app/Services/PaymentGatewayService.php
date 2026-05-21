<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\RevenueService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public static function buildPayload(Invoice $invoice, string $gateway, float $amount): array
    {
        $config = config("payment.gateways.{$gateway}");

        if (!$config || !$config['enabled']) {
            throw new \InvalidArgumentException("Payment gateway '{$gateway}' is not enabled or configured.");
        }

        $basePayload = [
            'status' => 'pending',
            'payment_details' => [
                'gateway' => $gateway,
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'lawyer_id' => $invoice->lawyer_id,
                'amount' => $amount,
                'currency' => config('payment.default_currency'),
                'currency_symbol' => config('legal.currency_symbol', '₱'),
                'created_at' => now()->toIso8601String(),
            ],
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'client_name' => $invoice->client->name,
                'lawyer_name' => $invoice->lawyer->name,
            ],
            'payload_id' => (string) Str::uuid(),
        ];

        return match ($gateway) {
            'gcash' => self::buildGCashPayload($invoice, $amount, $basePayload),
            'paymaya' => self::buildPayMayaPayload($invoice, $amount, $basePayload),
            'paypal' => self::buildPayPalPayload($invoice, $amount, $basePayload),
            'bank_transfer' => self::buildBankTransferPayload($invoice, $amount, $basePayload),
            'cash' => self::buildCashPayload($invoice, $amount, $basePayload),
            default => throw new \InvalidArgumentException("Unknown gateway: {$gateway}"),
        };
    }

    private static function buildGCashPayload(Invoice $invoice, float $amount, array $basePayload): array
    {
        $referenceNumber = PaymentTransaction::generateReferenceNumber('gcash');
        $referenceHash = self::generateReferenceHash($referenceNumber, $invoice, 'gcash');
        $qrId = (string) Str::uuid();
        $qrData = self::generateQRData($invoice, $amount, 'gcash', $referenceNumber, $referenceHash, $qrId);

        return array_merge($basePayload, [
            'reference_number' => $referenceNumber,
            'reference_hash' => $referenceHash,
            'qr_id' => $qrId,
            'qr_payload' => $qrData,
            'payment_details' => array_merge($basePayload['payment_details'], [
                'qr_id' => $qrId,
                'reference_number' => $referenceNumber,
                'reference_hash' => $referenceHash,
                'qr_enabled' => true,
            ]),
        ]);
    }

    private static function buildPayMayaPayload(Invoice $invoice, float $amount, array $basePayload): array
    {
        $referenceNumber = PaymentTransaction::generateReferenceNumber('paymaya');
        $referenceHash = self::generateReferenceHash($referenceNumber, $invoice, 'paymaya');
        $qrId = (string) Str::uuid();
        $qrData = self::generateQRData($invoice, $amount, 'paymaya', $referenceNumber, $referenceHash, $qrId);

        return array_merge($basePayload, [
            'reference_number' => $referenceNumber,
            'reference_hash' => $referenceHash,
            'qr_id' => $qrId,
            'qr_payload' => $qrData,
            'payment_details' => array_merge($basePayload['payment_details'], [
                'qr_id' => $qrId,
                'reference_number' => $referenceNumber,
                'reference_hash' => $referenceHash,
                'qr_enabled' => true,
            ]),
        ]);
    }

    private static function buildPayPalPayload(Invoice $invoice, float $amount, array $basePayload): array
    {
        $referenceNumber = PaymentTransaction::generateReferenceNumber('paypal');
        $referenceHash = self::generateReferenceHash($referenceNumber, $invoice, 'paypal');
        $qrId = (string) Str::uuid();
        $qrData = self::generateQRData($invoice, $amount, 'paypal', $referenceNumber, $referenceHash, $qrId);

        return array_merge($basePayload, [
            'reference_number' => $referenceNumber,
            'reference_hash' => $referenceHash,
            'qr_id' => $qrId,
            'qr_payload' => $qrData,
            'payment_details' => array_merge($basePayload['payment_details'], [
                'qr_id' => $qrId,
                'reference_number' => $referenceNumber,
                'reference_hash' => $referenceHash,
                'qr_enabled' => true,
                'paypal_email' => 'payments@legalmanagement.ph',
            ]),
        ]);
    }

    private static function buildBankTransferPayload(Invoice $invoice, float $amount, array $basePayload): array
    {
        $lawyer = $invoice->lawyer;
        $referenceNumber = PaymentTransaction::generateReferenceNumber('bank_transfer', $lawyer);
        $referenceHash = self::generateReferenceHash($referenceNumber, $invoice, 'bank_transfer');

        return array_merge($basePayload, [
            'reference_number' => $referenceNumber,
            'reference_hash' => $referenceHash,
            'payment_details' => array_merge($basePayload['payment_details'], [
                'reference_number' => $referenceNumber,
                'reference_hash' => $referenceHash,
                'account_name' => config('payment.gateways.bank_transfer.account_name'),
                'account_number' => config('payment.gateways.bank_transfer.account_number'),
                'bank_name' => config('payment.gateways.bank_transfer.bank_name'),
                'routing_number' => config('payment.gateways.bank_transfer.routing_number'),
                'lawyer_name' => $lawyer->name,
                'amount_php' => self::formatCurrency($amount),
            ]),
        ]);
    }

    private static function buildCashPayload(Invoice $invoice, float $amount, array $basePayload): array
    {
        $referenceNumber = PaymentTransaction::generateReferenceNumber('cash');
        $referenceHash = self::generateReferenceHash($referenceNumber, $invoice, 'cash');

        return array_merge($basePayload, [
            'status' => 'pending',
            'reference_number' => $referenceNumber,
            'reference_hash' => $referenceHash,
            'payment_details' => array_merge($basePayload['payment_details'], [
                'reference_number' => $referenceNumber,
                'reference_hash' => $referenceHash,
                'requires_manual_confirmation' => true,
                'awaiting_lawyer_confirmation' => true,
                'amount_php' => self::formatCurrency($amount),
            ]),
        ]);
    }

    private static function generateQRData(Invoice $invoice, float $amount, string $gateway, string $referenceNumber, string $referenceHash, string $qrId): string
    {
        $qrContent = json_encode([
            'qr_id' => $qrId,
            'gateway' => $gateway,
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'lawyer_id' => $invoice->lawyer_id,
            'amount' => $amount,
            'currency' => config('payment.default_currency'),
            'reference_number' => $referenceNumber,
            'reference_hash' => $referenceHash,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $qrContent;
    }

    private static function generateReferenceHash(string $referenceNumber, Invoice $invoice, string $gateway): string
    {
        return hash('sha256', implode('|', [
            $gateway,
            $invoice->id,
            $referenceNumber,
            $invoice->client_id,
            $invoice->lawyer_id,
            now()->timestamp,
        ]));
    }

    public static function generateQRCode(string $payload): string
    {
        // TODO: Implement QR code image generation when QR code package is available
        // For now, return the payload data. In production, this should generate an actual QR code image.
        // Example implementation:
        // return QrCode::size(config('legal.qr_codes.size'))
        //     ->format(config('legal.qr_codes.format'))
        //     ->errorCorrection(config('legal.qr_codes.error_correction'))
        //     ->generate($payload);

        return $payload; // Placeholder: return payload data instead of image
    }

    public static function formatCurrency(float $amount, string $currency = null): string
    {
        $currency = $currency ?? config('payment.default_currency');
        $symbol = config('legal.currency_symbol', '₱');

        return $symbol . number_format($amount, config('legal.decimal_places', 2), '.', ',');
    }

    public static function createPaymentLink(Invoice $invoice, string $method): array
    {
        $paymongoConfig = config('payment.paymongo');
        if (empty($paymongoConfig['secret_key'])) {
            throw new \RuntimeException('PayMongo secret key is not configured.');
        }

        $supported = ['gcash', 'paymaya', 'card', 'dob', 'billease'];
        if (!in_array($method, $supported, true)) {
            throw new \InvalidArgumentException("Unsupported PayMongo method: {$method}");
        }

        $paymongoType = $method === 'bank_transfer' ? 'dob' : $method;
        if ($paymongoType === 'bank_transfer') {
            $paymongoType = 'dob';
        }

        $amount = (int) round($invoice->balance * 100);
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'type' => $paymongoType,
                    'description' => "Invoice #{$invoice->invoice_number} - {$invoice->client->name}",
                    'redirect' => [
                        'success' => route('client.payments.success', ['invoice_id' => $invoice->id, 'amount' => $invoice->balance]),
                        'failed' => route('client.payments.failed', ['invoice_id' => $invoice->id]),
                    ],
                    'billing' => [
                        'name' => $invoice->client->name,
                        'email' => $invoice->client->email,
                    ],
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'client_name' => $invoice->client->name,
                        'lawyer_id' => $invoice->lawyer_id,
                    ],
                ],
            ],
        ];

        $response = Http::withBasicAuth($paymongoConfig['secret_key'], '')
            ->acceptJson()
            ->post("{$paymongoConfig['base_url']}/sources", $payload);

        if (!$response->successful()) {
            Log::error('PayMongo create payment link failed', [
                'invoice_id' => $invoice->id,
                'method' => $method,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Unable to create PayMongo payment link.');
        }

        $data = $response->json('data');
        $checkoutUrl = data_get($data, 'attributes.redirect.checkout_url') ?: data_get($data, 'attributes.redirect.url');

        if (empty($checkoutUrl)) {
            throw new \RuntimeException('PayMongo checkout URL not returned.');
        }

        return [
            'checkout_url' => $checkoutUrl,
            'source_id' => $data['id'] ?? null,
            'response' => $response->json(),
        ];
    }

    public static function handleWebhook(array $payload): ?Invoice
    {
        $event = data_get($payload, 'type');
        if ($event !== 'payment.paid') {
            return null;
        }

        $metadata = data_get($payload, 'data.attributes.metadata', []);
        $referenceNumber = data_get($metadata, 'reference_number') ?: data_get($payload, 'data.attributes.reference_number');
        $invoiceId = data_get($metadata, 'invoice_id');

        $transaction = PaymentTransaction::query()
            ->when($referenceNumber, fn($query) => $query->where('reference_number', $referenceNumber))
            ->when($invoiceId, fn($query) => $query->orWhere('invoice_id', $invoiceId))
            ->latest()
            ->first();

        if (!$transaction || $transaction->status === 'completed') {
            return null;
        }

        $amount = data_get($payload, 'data.attributes.amount');
        $paidAmount = is_numeric($amount) ? ($amount / 100) : $transaction->amount;

        $transaction->update([
            'status' => 'completed',
            'confirmed_at' => now(),
            'payment_details' => array_merge($transaction->payment_details ?? [], ['paymongo_webhook' => $payload]),
            'amount' => $transaction->amount ?: $paidAmount,
        ]);

        $invoice = $transaction->invoice;
        if (!$invoice) {
            return null;
        }

        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->amount_paid + $paidAmount,
            'balance' => max(0, $invoice->balance - $paidAmount),
        ]);

        RevenueService::createRevenueFromInvoice($invoice, $transaction->id);

        return $invoice;
    }
}
