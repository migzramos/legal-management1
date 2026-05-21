<?php

use App\Models\User;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\PaymentMethodHandler;
use App\Services\CurrencyFormatter;
use Illuminate\Support\Facades\Storage;

describe('Payment Method Handlers', function () {
    beforeEach(function () {
        $this->client = User::factory()->create(['role' => 'client']);
        $this->lawyer = User::factory()->create(['role' => 'lawyer']);
        
        $this->appointment = Appointment::create([
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(1),
            'duration_minutes' => 60,
            'hourly_rate' => 2500.00,
            'purpose' => 'Consultation',
            'status' => 'confirmed',
        ]);

        $this->invoice = Invoice::create([
            'appointment_id' => $this->appointment->id,
            'lawyer_id' => $this->lawyer->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-001',
            'subtotal' => 2500.00,
            'tax_amount' => 0,
            'total' => 2500.00,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->handler = new PaymentMethodHandler();
    });

    test('GCash handler generates QR code and reference', function () {
        Storage::fake('public');

        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'appointment_id' => $this->appointment->id,
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'gateway' => 'gcash',
            'amount' => 2500.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $metadata = ['method' => 'gcash'];
        $this->handler->initializePayment($this->invoice, 'gcash', $metadata);

        $transaction->refresh();
        expect($transaction->reference_number)->not->toBeNull();
        expect($transaction->metadata)->toHaveKey('qr_generated_at');
    });

    test('PayPal handler initializes payment correctly', function () {
        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'appointment_id' => $this->appointment->id,
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'gateway' => 'paypal',
            'amount' => 2500.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->handler->initializePayment($this->invoice, 'paypal', []);

        $transaction->refresh();
        expect($transaction->reference_number)->not->toBeNull();
    });

    test('PayMaya handler initializes payment correctly', function () {
        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'appointment_id' => $this->appointment->id,
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'gateway' => 'paymaya',
            'amount' => 2500.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->handler->initializePayment($this->invoice, 'paymaya', []);

        $transaction->refresh();
        expect($transaction->reference_number)->not->toBeNull();
    });

    test('Bank Transfer handler generates unique reference', function () {
        $transaction1 = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'appointment_id' => $this->appointment->id,
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'gateway' => 'bank_transfer',
            'amount' => 2500.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->handler->initializePayment($this->invoice, 'bank_transfer', []);

        $transaction1->refresh();
        expect($transaction1->reference_number)->toMatch('/^BT-.*-\d{14}-.{6}$/');
    });

    test('Cash handler sets pending status with confirmation flag', function () {
        $transaction = PaymentTransaction::create([
            'invoice_id' => $this->invoice->id,
            'appointment_id' => $this->appointment->id,
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'gateway' => 'cash',
            'amount' => 2500.00,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        $this->handler->initializePayment($this->invoice, 'cash', []);

        $transaction->refresh();
        expect($transaction->status)->toBe('pending');
        expect($transaction->metadata['requires_manual_confirmation'])->toBeTrue();
    });

    test('bank transfer references are collision-resistant', function () {
        // Create multiple transactions to verify uniqueness
        $references = [];
        
        for ($i = 0; $i < 5; $i++) {
            $transaction = PaymentTransaction::create([
                'invoice_id' => $this->invoice->id,
                'appointment_id' => $this->appointment->id,
                'client_id' => $this->client->id,
                'lawyer_id' => $this->lawyer->id,
                'gateway' => 'bank_transfer',
                'amount' => 2500.00,
                'currency' => 'PHP',
                'status' => 'pending',
            ]);

            $this->handler->initializePayment($this->invoice, 'bank_transfer', []);
            $transaction->refresh();
            
            $references[] = $transaction->reference_number;
        }

        expect(count($references))->toBe(count(array_unique($references))); // All unique
    });
});

describe('Currency Formatter', function () {
    test('formats value with peso symbol', function () {
        $formatter = new CurrencyFormatter();
        expect($formatter->format(2500.00))->toBe('₱2,500.00');
    });

    test('formats value without peso symbol', function () {
        $formatter = new CurrencyFormatter();
        expect($formatter->format(2500.00, false))->toBe('2,500.00');
    });

    test('formats display with symbol', function () {
        $formatter = new CurrencyFormatter();
        expect($formatter->formatDisplay(2500.00))->toBe('₱2,500.00');
    });

    test('formats input without symbol', function () {
        $formatter = new CurrencyFormatter();
        expect($formatter->formatInput(2500.00))->toBe('2500.00');
    });

    test('handles decimal places correctly', function () {
        $formatter = new CurrencyFormatter();
        expect($formatter->format(2500.5))->toBe('₱2,500.50');
    });

    test('handles thousands separator', function () {
        $formatter = new CurrencyFormatter();
        expect($formatter->format(125000.00))->toBe('₱125,000.00');
    });

    test('parses peso formatted string', function () {
        $formatter = new CurrencyFormatter();
        $parsed = $formatter->parse('₱2,500.00');
        expect($parsed)->toBe(2500.00);
    });

    test('helper money_display works correctly', function () {
        expect(money_display(2500.00))->toBe('₱2,500.00');
    });

    test('helper money works without symbol', function () {
        expect(money(2500.00, false))->toBe('2,500.00');
    });

    test('helper peso returns correct symbol', function () {
        expect(peso())->toBe('₱');
    });
});
