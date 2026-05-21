<?php

/**
 * Custom Helper Functions for Legal Management System
 */

use App\Services\CurrencyFormatter;
use App\Models\User;

/**
 * Format a monetary value with Philippine Peso symbol
 * Usage: {{ money(500) }} => ₱500.00
 */
if (!function_exists('money')) {
    function money($value, $includeSymbol = true) {
        return CurrencyFormatter::format($value, $includeSymbol);
    }
}

/**
 * Format currency for display (with symbol)
 * Usage: {{ money_display(500) }} => ₱500.00
 */
if (!function_exists('money_display')) {
    function money_display($value) {
        $symbol = html_entity_decode('&#8369;', ENT_QUOTES | ENT_HTML5);
        $amount = is_numeric($value) ? $value : 0;

        return $symbol . number_format((float) $amount, 2, '.', ',');
    }
}

/**
 * Format currency for input fields (no symbol)
 * Usage: <input value="{{ money_input(500) }}" />
 */
if (!function_exists('money_input')) {
    function money_input($value) {
        return CurrencyFormatter::formatInput($value);
    }
}

/**
 * Get Philippine Peso symbol
 * Usage: {{ peso() }} => ₱
 */
if (!function_exists('peso')) {
    function peso() {
        return html_entity_decode('&#8369;', ENT_QUOTES | ENT_HTML5);
    }
}

/**
 * Format a range of monetary values
 * Usage: {{ money_range(100, 500) }} => ₱100.00 - ₱500.00
 */
if (!function_exists('money_range')) {
    function money_range($min, $max) {
        return CurrencyFormatter::formatRange($min, $max);
    }
}

/**
 * Calculate appointment cost
 * Usage: {{ appointment_cost(2000, 60) }} => 2000
 */
if (!function_exists('appointment_cost')) {
    function appointment_cost($hourlyRate, $durationMinutes) {
        return CurrencyFormatter::calculateAppointmentCost($hourlyRate, $durationMinutes);
    }
}

/**
 * Get the hourly rate for a lawyer from their profile
 * Usage: {{ lawyer_hourly_rate($lawyer) }} => 2000
 */
if (!function_exists('lawyer_hourly_rate')) {
    function lawyer_hourly_rate(User $lawyer) {
        $billingRate = $lawyer->billingRates()
            ->where('is_active', true)
            ->latest('effective_date')
            ->first();
        
        return $billingRate?->hourly_rate ?? config('legal.default_hourly_rate', 2000);
    }
}

/**
 * Get the lawyer's full rate information for display
 * Usage: {{ lawyer_rate_display($lawyer) }} => ₱2,000.00/hour
 */
if (!function_exists('lawyer_rate_display')) {
    function lawyer_rate_display(User $lawyer) {
        $rate = lawyer_hourly_rate($lawyer);
        return money($rate) . '/hour';
    }
}
