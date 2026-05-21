<?php

return [
    'default_currency' => env('PAYMENT_DEFAULT_CURRENCY', 'PHP'),

    'currency_code' => 'PHP',

    'paymongo' => [
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'base_url' => 'https://api.paymongo.com/v1',
    ],

    'gateways' => [
        'gcash' => [
            'enabled' => env('GCASH_ENABLED', true),
            'label' => 'GCash',
            'description' => 'Mobile payment via GCash',
            'qr_enabled' => true,
            'reference_format' => 'GCX-{timestamp}-{random}',
        ],
        'paymaya' => [
            'enabled' => env('PAYMAYA_ENABLED', true),
            'label' => 'PayMaya',
            'description' => 'Digital wallet payment via PayMaya',
            'qr_enabled' => true,
            'reference_format' => 'PMY-{timestamp}-{random}',
        ],
        'card' => [
            'enabled' => env('CARD_ENABLED', true),
            'label' => 'Credit/Debit Card',
            'description' => 'Pay with Visa, Mastercard, or other cards',
            'qr_enabled' => false,
            'reference_format' => 'CRD-{timestamp}-{random}',
        ],
        'dob' => [
            'enabled' => env('DOB_ENABLED', true),
            'label' => 'Bank Transfer / InstaPay',
            'description' => 'Philippine bank transfer or InstaPay via PayMongo',
            'qr_enabled' => false,
            'reference_format' => 'DOB-{timestamp}-{random}',
        ],
        'billease' => [
            'enabled' => env('BILLEASE_ENABLED', true),
            'label' => 'Billease',
            'description' => 'Installment payments via Billease',
            'qr_enabled' => false,
            'reference_format' => 'BIL-{timestamp}-{random}',
        ],
        'paypal' => [
            'enabled' => env('PAYPAL_ENABLED', true),
            'label' => 'PayPal',
            'description' => 'International payment via PayPal',
            'qr_enabled' => true,
            'reference_format' => 'PPL-{timestamp}-{random}',
        ],
        'bank_transfer' => [
            'enabled' => env('BANK_TRANSFER_ENABLED', true),
            'label' => 'Bank Transfer',
            'description' => 'Direct bank account transfer',
            'qr_enabled' => false,
            'reference_format' => 'BT-{lawyer_initials}-{timestamp}',
            'account_name' => env('BANK_ACCOUNT_NAME', 'Legal Management'),
            'account_number' => env('BANK_ACCOUNT_NUMBER'),
            'bank_name' => env('BANK_NAME'),
            'routing_number' => env('BANK_ROUTING_NUMBER'),
        ],
        'cash' => [
            'enabled' => env('CASH_ENABLED', true),
            'label' => 'Cash',
            'description' => 'Direct cash payment',
            'qr_enabled' => false,
            'reference_format' => 'CASH-{timestamp}',
            'requires_manual_confirmation' => true,
        ],
    ],

    'payment_timeout_seconds' => env('PAYMENT_TRANSACTION_TIMEOUT', 3600),
    'auto_confirm_cash' => env('AUTO_CONFIRM_CASH_PAYMENTS', false),
];
