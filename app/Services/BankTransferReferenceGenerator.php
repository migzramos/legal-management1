<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Bank Transfer Reference Number Generator
 * 
 * Generates unique, collision-free reference numbers for bank transfers
 * Format: BT-[LawyerInitials]-[Timestamp]-[RandomSequence]
 * Example: BT-JAD-20260430143025-A7B2C3
 */
class BankTransferReferenceGenerator
{
    /**
     * Generate a unique bank transfer reference number
     * 
     * @param User $lawyer
     * @param float $amount
     * @param int $appointmentId
     * @return string
     */
    public static function generate(User $lawyer, float $amount, int $appointmentId = null): string
    {
        $reference = self::buildReference($lawyer);

        // Validate uniqueness
        $attempts = 0;
        $maxAttempts = 5;

        while ($attempts < $maxAttempts) {
            if (!self::existsInDatabase($reference)) {
                return $reference;
            }

            // Regenerate if collision detected
            $reference = self::buildReference($lawyer);
            $attempts++;
        }

        // If still colliding, append a microsecond timestamp
        return $reference . '-' . substr(uniqid(), -4);
    }

    /**
     * Build the bank transfer reference number
     * Format: BT-[LawyerInitials]-[Timestamp]-[Random6Chars]
     * 
     * @param User $lawyer
     * @return string
     */
    private static function buildReference(User $lawyer): string
    {
        // Extract initials from lawyer's name
        $initials = self::getInitials($lawyer->name);

        // Current timestamp in yyyymmddhhmmss format
        $timestamp = now()->format('YmdHis');

        // Generate random alphanumeric sequence (6 characters)
        $random = strtoupper(substr(Str::random(8), 0, 6));

        return sprintf('BT-%s-%s-%s', $initials, $timestamp, $random);
    }

    /**
     * Extract initials from name
     * Example: "Juan Alcantara Diaz" => "JAD"
     * 
     * @param string $name
     * @return string
     */
    private static function getInitials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $initials = '';

        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }

        // Ensure we have exactly 3 initials
        if (strlen($initials) < 3) {
            $initials = str_pad($initials, 3, 'X');
        } elseif (strlen($initials) > 3) {
            $initials = substr($initials, 0, 3);
        }

        return $initials;
    }

    /**
     * Check if reference number already exists in database
     * 
     * @param string $reference
     * @return bool
     */
    private static function existsInDatabase(string $reference): bool
    {
        return PaymentTransaction::where('reference_number', $reference)->exists();
    }

    /**
     * Generate and store reference number for payment transaction
     * 
     * @param PaymentTransaction $transaction
     * @return array
     */
    public static function generateAndStore(PaymentTransaction $transaction): array
    {
        try {
            $lawyer = $transaction->lawyer ?? User::findOrFail($transaction->lawyer_id);

            $reference = self::generate(
                $lawyer,
                $transaction->amount,
                $transaction->appointment_id
            );

            // Generate hash for verification
            $referenceHash = hash('sha256', $reference . config('app.key'));
            $referenceHash = substr($referenceHash, 0, 32);

            // Update transaction
            $transaction->update([
                'reference_number' => $reference,
                'reference_hash' => $referenceHash,
                'metadata' => array_merge(
                    $transaction->metadata ?? [],
                    [
                        'reference_generated_at' => now()->toIso8601String(),
                        'lawyer_initials' => self::getInitials($lawyer->name),
                        'lawyer_id_embedded' => $lawyer->id,
                    ]
                ),
            ]);

            return [
                'success' => true,
                'reference_number' => $reference,
                'reference_hash' => $referenceHash,
            ];
        } catch (\Exception $e) {
            \Log::error('Bank transfer reference generation failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate bank transfer reference: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate bank transfer reference number format
     * 
     * @param string $reference
     * @return bool
     */
    public static function validateFormat(string $reference): bool
    {
        return preg_match('/^BT-[A-Z]{3}-\d{14}-[A-Z0-9]{6}/', $reference) === 1;
    }

    /**
     * Validate reference number with hash
     * 
     * @param string $reference
     * @param string $hash
     * @return bool
     */
    public static function validateWithHash(string $reference, string $hash): bool
    {
        $expectedHash = hash('sha256', $reference . config('app.key'));
        $expectedHash = substr($expectedHash, 0, 32);

        return hash_equals($hash, $expectedHash);
    }

    /**
     * Decode reference number to extract information
     * 
     * @param string $reference
     * @return array|null
     */
    public static function decode(string $reference): ?array
    {
        if (!self::validateFormat($reference)) {
            return null;
        }

        $parts = explode('-', $reference);

        return [
            'gateway' => $parts[0] ?? null,
            'lawyer_initials' => $parts[1] ?? null,
            'timestamp' => self::parseTimestamp($parts[2] ?? ''),
            'random_suffix' => $parts[3] ?? null,
        ];
    }

    /**
     * Parse timestamp from reference number
     * Format: YmdHis
     * 
     * @param string $timestamp
     * @return \Carbon\Carbon|null
     */
    private static function parseTimestamp(string $timestamp): ?\Carbon\Carbon
    {
        if (strlen($timestamp) !== 14) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat(
                'YmdHis',
                $timestamp,
                config('app.timezone')
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
