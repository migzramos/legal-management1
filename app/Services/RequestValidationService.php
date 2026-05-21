<?php

namespace App\Services;

use Illuminate\Validation\Validator;

/**
 * Request Validation Service
 * 
 * Provides standardized validation rules and custom validators
 * Ensures consistent validation across all API requests
 */
class RequestValidationService
{
    /**
     * Get standard currency validation rules
     * 
     * @param string $fieldName
     * @param float|null $maxAmount Optional maximum amount
     * @return array
     */
    public static function currencyRules(string $fieldName = 'amount', ?float $maxAmount = null): array
    {
        $rules = [
            $fieldName => 'required|numeric|min:0.01',
        ];

        if ($maxAmount) {
            $rules[$fieldName] .= "|max:{$maxAmount}";
        }

        return $rules;
    }

    /**
     * Get standard date/time validation rules
     * 
     * @param string $fieldName
     * @param bool $futureOnly Only allow future dates
     * @return array
     */
    public static function dateTimeRules(string $fieldName = 'datetime', bool $futureOnly = true): array
    {
        $rule = 'required|date_format:Y-m-d\TH:i';

        if ($futureOnly) {
            $rule .= '|after:now';
        }

        return [$fieldName => $rule];
    }

    /**
     * Get standard email validation rules
     * 
     * @param string $fieldName
     * @param bool $unique Check uniqueness in users table
     * @param int|null $exceptUserId User ID to exclude from uniqueness check
     * @return array
     */
    public static function emailRules(string $fieldName = 'email', bool $unique = false, ?int $exceptUserId = null): array
    {
        $rule = 'required|email|max:255';

        if ($unique) {
            $rule .= '|unique:users,email';
            if ($exceptUserId) {
                $rule .= "," . $exceptUserId;
            }
        }

        return [$fieldName => $rule];
    }

    /**
     * Get standard phone validation rules
     * 
     * @param string $fieldName
     * @return array
     */
    public static function phoneRules(string $fieldName = 'phone'): array
    {
        return [
            $fieldName => 'required|regex:/^(\+63|0)[0-9]{9,10}$/|max:20',
        ];
    }

    /**
     * Get standard name validation rules
     * 
     * @param string $fieldName
     * @param int $minLength
     * @param int $maxLength
     * @return array
     */
    public static function nameRules(string $fieldName = 'name', int $minLength = 2, int $maxLength = 255): array
    {
        return [
            $fieldName => "required|string|min:{$minLength}|max:{$maxLength}|regex:/^[\p{L}\s'-]+$/u",
        ];
    }

    /**
     * Get standard text validation rules
     * 
     * @param string $fieldName
     * @param int $minLength
     * @param int $maxLength
     * @return array
     */
    public static function textRules(string $fieldName = 'text', int $minLength = 1, int $maxLength = 5000): array
    {
        return [
            $fieldName => "required|string|min:{$minLength}|max:{$maxLength}",
        ];
    }

    /**
     * Get standard enum validation rules
     * 
     * @param string $fieldName
     * @param array $allowedValues
     * @return array
     */
    public static function enumRules(string $fieldName = 'status', array $allowedValues = []): array
    {
        $rule = 'required|string|in:' . implode(',', $allowedValues);

        return [$fieldName => $rule];
    }

    /**
     * Get standard duration validation rules
     * 
     * @param string $fieldName
     * @param array $allowedDurations Minutes allowed (e.g., [30, 60, 90, 120])
     * @return array
     */
    public static function durationRules(string $fieldName = 'duration_minutes', array $allowedDurations = []): array
    {
        if (empty($allowedDurations)) {
            $allowedDurations = [30, 60, 90, 120];
        }

        $rule = 'required|integer|in:' . implode(',', $allowedDurations);

        return [$fieldName => $rule];
    }

    /**
     * Create custom error messages for validation
     * 
     * @param array $fieldNames Key-value pairs of field names and custom messages
     * @return array
     */
    public static function customMessages(array $fieldNames = []): array
    {
        $defaultMessages = [
            'required' => 'This field is required.',
            'email' => 'Please provide a valid email address.',
            'unique' => 'This value is already in use.',
            'numeric' => 'This field must be a number.',
            'min' => 'This field is too short.',
            'max' => 'This field is too long.',
            'regex' => 'This field contains invalid characters.',
            'in' => 'The selected value is not valid.',
            'date_format' => 'Please provide a valid date and time.',
            'after' => 'The date must be in the future.',
        ];

        return array_merge($defaultMessages, $fieldNames);
    }

    /**
     * Validate appointment booking data
     * 
     * @param array $data
     * @return Validator
     */
    public static function validateAppointmentBooking(array $data): Validator
    {
        return validator()->make($data, [
            'lawyer_id' => 'required|exists:users,id',
            'appointment_at' => 'required|date_format:Y-m-d\TH:i|after:now|before:+6 months',
            'duration_minutes' => 'required|integer|in:30,60,90,120',
            'purpose' => 'required|string|max:1000|min:3',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    /**
     * Validate payment data
     * 
     * @param array $data
     * @return Validator
     */
    public static function validatePayment(array $data): Validator
    {
        return validator()->make($data, [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'gateway' => 'required|string|in:gcash,paymaya,paypal,bank_transfer,cash',
        ]);
    }

    /**
     * Validate invoice data
     * 
     * @param array $data
     * @return Validator
     */
    public static function validateInvoice(array $data): Validator
    {
        return validator()->make($data, [
            'case_id' => 'nullable|exists:cases,id',
            'client_id' => 'required|exists:users,id',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0.01',
            'issued_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issued_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);
    }
}
