<?php

use App\Models\User;
use App\Models\Appointment;
use App\Models\BillingRate;
use Carbon\Carbon;

describe('Appointment Booking Module', function () {
    beforeEach(function () {
        $this->client = User::factory()->create(['role' => 'client']);
        $this->lawyer = User::factory()->create(['role' => 'lawyer', 'is_active' => true]);
        
        BillingRate::create([
            'lawyer_id' => $this->lawyer->id,
            'hourly_rate' => 2500.00,
            'effective_date' => now(),
        ]);
    });

    test('client can book appointment with valid data', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'purpose' => 'Legal consultation',
            'notes' => 'Important case matter',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertSuccessful();
        expect($response->json('success'))->toBeTrue();
        $this->assertDatabaseHas('appointments', [
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'status' => 'pending',
        ]);
    });

    test('appointment validates required fields', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => '',
            'duration_minutes' => '',
            'purpose' => '',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
        expect($response->json('errors'))->toHaveKeys(['appointment_at', 'duration_minutes', 'purpose']);
    });

    test('appointment rejects invalid duration', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 45, // Not in enum [30, 60, 90, 120]
            'purpose' => 'Legal consultation',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
        expect($response->json('errors.duration_minutes'))->toBeTruthy();
    });

    test('appointment detects overlapping appointments', function () {
        // Create first appointment
        $firstAppointment = Appointment::create([
            'client_id' => $this->client->id,
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'hourly_rate' => 2500.00,
            'purpose' => 'First consultation',
            'status' => 'pending',
        ]);

        // Try to book overlapping appointment
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 30), // Within first appointment
            'duration_minutes' => 60,
            'purpose' => 'Second consultation',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
        expect($response->json('message'))->toContain('overlap');
    });

    test('appointment fetches hourly_rate from BillingRate only', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'purpose' => 'Consultation',
            'hourly_rate' => 5000.00, // Client tries to override
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        if ($response->successful()) {
            $appointment = Appointment::latest()->first();
            expect($appointment->hourly_rate)->toBe(2500.00); // Not 5000.00
        }
    });

    test('appointment validates date is in future', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->subDays(1),
            'duration_minutes' => 60,
            'purpose' => 'Consultation',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });

    test('appointment validates date is within 6 months', function () {
        $data = [
            'lawyer_id' => $this->lawyer->id,
            'appointment_at' => now()->addMonths(7),
            'duration_minutes' => 60,
            'purpose' => 'Consultation',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });

    test('appointment requires active lawyer', function () {
        $inactiveLawyer = User::factory()->create(['role' => 'lawyer', 'is_active' => false]);

        $data = [
            'lawyer_id' => $inactiveLawyer->id,
            'appointment_at' => now()->addDays(2)->setTime(10, 0),
            'duration_minutes' => 60,
            'purpose' => 'Consultation',
        ];

        $response = $this->actingAs($this->client, 'client')
            ->postJson(route('client.appointments.store'), $data);

        $response->assertUnprocessable();
    });
});
