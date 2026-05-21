<?php

return [
    'currency' => 'PHP',
    'currency_symbol' => '₱',
    
    'decimal_places' => 2,
    'thousand_separator' => ',',
    'decimal_separator' => '.',
    
    'payment_statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],
    
    'invoice_statuses' => [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
    ],
    
    'appointment_statuses' => [
        'pending' => 'Pending Confirmation',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ],
    
    'messaging' => [
        'auto_read_delay_seconds' => 300,
        'realtime_enabled' => true,
        'max_message_length' => 5000,
    ],
    
    'qr_codes' => [
        'enabled' => true,
        'size' => 300,
        'format' => 'png',
        'error_correction' => 'M',
    ],
];
