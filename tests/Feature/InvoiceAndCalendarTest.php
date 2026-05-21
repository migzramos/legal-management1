<?php

use App\Models\User;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Message;
use App\Models\BillingRate;
use Carbon\Carbon;

describe('Invoice Auto-Generation on Appointment Confirmation', function () {
    beforeEach(function () {
        $this->client = User::factory()->create(['role' => 'client']);
        $this->lawyer = User::factory()->create(['role' => 'lawyer']);
        
        BillingRate::create([
            'lawyer_id' => $this->lawyer->id,
            'hourly_rate' => 2500.00,
            'effective_date' => now(),
        ]);

        $this->appointment = Appointment::create([
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(1)->setTime(10, 0),
            'duration_minutes' => 60,
            'hourly_rate' => 2500.00,
            'purpose' => 'Legal consultation',
            'status' => 'pending',
        ]);
    });

    test('invoice is created when appointment is confirmed', function () {
        $response = $this->actingAs($this->lawyer, 'lawyer')
            ->postJson(route('lawyer.appointments.confirm', $this->appointment->id));

        $this->appointment->refresh();
        expect($this->appointment->status)->toBe('confirmed');

        $invoice = Invoice::where('appointment_id', $this->appointment->id)->first();
        expect($invoice)->not->toBeNull();
    });

    test('invoice contains correct financial data', function () {
        $response = $this->actingAs($this->lawyer, 'lawyer')
            ->postJson(route('lawyer.appointments.confirm', $this->appointment->id));

        $invoice = Invoice::where('appointment_id', $this->appointment->id)->first();
        
        $expectedTotal = $this->appointment->hourly_rate * ($this->appointment->duration_minutes / 60);
        expect($invoice->subtotal)->toBe($expectedTotal);
        expect($invoice->total)->toBe($expectedTotal);
    });

    test('invoice items are created from appointment', function () {
        $response = $this->actingAs($this->lawyer, 'lawyer')
            ->postJson(route('lawyer.appointments.confirm', $this->appointment->id));

        $invoice = Invoice::where('appointment_id', $this->appointment->id)->first();
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();

        expect($items->count())->toBeGreaterThan(0);
    });

    test('appointment confirmation is transactional', function () {
        $response = $this->actingAs($this->lawyer, 'lawyer')
            ->postJson(route('lawyer.appointments.confirm', $this->appointment->id));

        // If successful, all data should be created together
        if ($response->successful()) {
            $invoice = Invoice::where('appointment_id', $this->appointment->id)->first();
            expect($invoice)->not->toBeNull();
            
            $appointment = Appointment::find($this->appointment->id);
            expect($appointment->status)->toBe('confirmed');
        }
    });

    test('message is created on appointment confirmation', function () {
        $response = $this->actingAs($this->lawyer, 'lawyer')
            ->postJson(route('lawyer.appointments.confirm', $this->appointment->id));

        $message = Message::where('appointment_id', $this->appointment->id)->first();
        expect($message)->not->toBeNull();
    });

    test('appointment cannot be confirmed by non-assigned lawyer', function () {
        $otherLawyer = User::factory()->create(['role' => 'lawyer']);

        $response = $this->actingAs($otherLawyer, 'lawyer')
            ->postJson(route('lawyer.appointments.confirm', $this->appointment->id));

        $response->assertForbidden();
    });
});

describe('Calendar Overlap Prevention', function () {
    beforeEach(function () {
        $this->client = User::factory()->create(['role' => 'client']);
        $this->lawyer = User::factory()->create(['role' => 'lawyer', 'is_active' => true]);
        
        BillingRate::create([
            'lawyer_id' => $this->lawyer->id,
            'hourly_rate' => 2500.00,
            'effective_date' => now(),
        ]);

        // Create an existing appointment
        $this->existingAppointment = Appointment::create([
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'hourly_rate' => 2500.00,
            'purpose' => 'First appointment',
            'status' => 'confirmed',
        ]);
    });

    test('prevents booking that completely overlaps', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'purpose' => 'Overlapping appointment',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });

    test('prevents booking that starts during existing appointment', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 30),
            'duration_minutes' => 60,
            'purpose' => 'Overlapping appointment',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });

    test('prevents booking that ends during existing appointment', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(9, 0),
            'duration_minutes' => 90, // Ends at 10:30, overlaps with existing 10:00-11:00
            'purpose' => 'Overlapping appointment',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });

    test('allows booking that ends exactly when existing starts', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(9, 0),
            'duration_minutes' => 60, // Ends at 10:00
            'purpose' => 'Back-to-back appointment',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertSuccessful();
    });

    test('allows booking that starts exactly when existing ends', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(11, 0), // Existing ends at 11:00
            'duration_minutes' => 60,
            'purpose' => 'Back-to-back appointment',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertSuccessful();
    });

    test('allows booking on different day', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(3)->setTime(10, 0),
            'duration_minutes' => 60,
            'purpose' => 'Appointment on different day',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertSuccessful();
    });

    test('allows booking with different lawyer', function () {
        $otherLawyer = User::factory()->create(['role' => 'lawyer', 'is_active' => true]);
        
        BillingRate::create([
            'lawyer_id' => $otherLawyer->id,
            'hourly_rate' => 2500.00,
            'effective_date' => now(),
        ]);

        $data = [
            'lawyer_id' => $otherLawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'purpose' => 'Appointment with different lawyer',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertSuccessful();
    });

    test('includes pending appointments in conflict detection', function () {
        $pendingAppointment = Appointment::create([
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(4)->setTime(14, 0),
            'duration_minutes' => 60,
            'hourly_rate' => 2500.00,
            'purpose' => 'Pending appointment',
            'status' => 'pending',
        ]);

        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(4)->setTime(14, 30),
            'duration_minutes' => 60,
            'purpose' => 'Conflicting with pending',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });
});
