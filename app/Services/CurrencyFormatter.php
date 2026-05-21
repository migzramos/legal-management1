<?php

namespace App\Services;

/**
 * Currency Formatter Service
 * 
 * Handles all monetary value formatting with Philippine Peso (₱) symbol
 * Ensures consistent formatting across the application
 */
class CurrencyFormatter
{
    private const CURRENCY_SYMBOL = '₱';
    private const DECIMAL_PLACES = 2;
    private const THOUSAND_SEPARATOR = ',';
    private const DECIMAL_SEPARATOR = '.';

    /**
     * Format a numeric value to Philippine Peso format
     * 
     * @param float|int|string $value The numeric value to format
     * @param bool $includeSymbol Whether to include the ₱ symbol
     * @return string Formatted currency string (e.g., "₱2,500.00")
     */
    public static function format($value, bool $includeSymbol = true): string
    {
        if (is_null($value) || $value === '') {
            return $includeSymbol ? self::CURRENCY_SYMBOL . '0.00' : '0.00';
        }

        $numericValue = (float) $value;
        
        if ($numericValue < 0) {
            $sign = '-';
            $numericValue = abs($numericValue);
        } else {
            $sign = '';
        }

        $formatted = number_format(
            $numericValue,
            self::DECIMAL_PLACES,
            self::DECIMAL_SEPARATOR,
            self::THOUSAND_SEPARATOR
        );

        return $sign . ($includeSymbol ? self::CURRENCY_SYMBOL : '') . $formatted;
    }

    /**
     * Format currency for display in tables and lists
     * 
     * @param float|int $value
     * @return string
     */
    public static function formatDisplay($value): string
    {
        return self::format($value, true);
    }

    /**
     * Format currency for input fields (without symbol for editing)
     * 
     * @param float|int $value
     * @return string
     */
    public static function formatInput($value): string
    {
        return number_format(
            (float) $value,
            self::DECIMAL_PLACES,
            self::DECIMAL_SEPARATOR,
            self::THOUSAND_SEPARATOR
        );
    }

    /**
     * Format currency for JSON/API responses
     * 
     * @param float|int $value
     * @return string
     */
    public static function formatJson($value): string
    {
        return self::format($value, false);
    }

    /**
     * Parse a string currency value to numeric
     * 
     * @param string $value Currency string with or without symbol
     * @return float
     */
    public static function parse(string $value): float
    {
        // Remove currency symbol and whitespace
        $value = str_replace(self::CURRENCY_SYMBOL, '', $value);
        $value = trim($value);

        // Remove thousand separators
        $value = str_replace(self::THOUSAND_SEPARATOR, '', $value);

        // Replace decimal separator with standard dot
        if (self::DECIMAL_SEPARATOR !== '.') {
            $value = str_replace(self::DECIMAL_SEPARATOR, '.', $value);
        }

        return (float) $value;
    }

    /**
     * Calculate total from a collection of monetary values
     * 
     * @param array $values Array of numeric values
     * @return string Formatted total
     */
    public static function total(array $values): string
    {
        $sum = array_sum(array_map('floatval', $values));
        return self::format($sum, true);
    }

    /**
     * Calculate appointment cost based on hourly rate and duration
     * 
     * @param float $hourlyRate Rate per hour in Philippine Pesos
     * @param int $durationMinutes Duration in minutes
     * @return float Calculated cost
     */
    public static function calculateAppointmentCost(float $hourlyRate, int $durationMinutes): float
    {
        return ($hourlyRate / 60) * $durationMinutes;
    }

    /**
     * Get currency symbol
     * 
     * @return string
     */
    public static function symbol(): string
    {
        return self::CURRENCY_SYMBOL;
    }

    /**
     * Get currency code
     * 
     * @return string
     */
    public static function code(): string
    {
        return 'PHP';
    }

    /**
     * Format a range of values (e.g., "₱1,500.00 - ₱3,000.00")
     * 
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return string
     */
    public static function formatRange(float $min, float $max): string
    {
        return self::format($min) . ' - ' . self::format($max);
    }

    /**
     * Format percentage with currency symbol context
     * 
     * @param float $percentage Percentage value
     * @return string
     */
    public static function formatPercentage(float $percentage): string
    {
        return number_format($percentage, 2) . '%';
    }
}
