<?php

namespace App\Listeners;

use App\Events\AppointmentConfirmed;
use Illuminate\Support\Facades\Log;

/**
 * AppointmentConfirmed Event Listener
 * 
 * Handles actions triggered after an appointment is confirmed:
 * - Logs the confirmation event to audit trail
 * - Triggers any dependent workflows
 */
class HandleAppointmentConfirmed
{
    /**
     * Handle the event
     */
    public function handle(AppointmentConfirmed $event): void
    {
        $appointment = $event->appointment;
        $invoice = $event->invoice;

        try {
            // Log the appointment confirmation to audit trail
            \App\Models\AuditLog::create([
                'user_id' => auth()->id() ?? $appointment->lawyer_id,
                'action' => 'appointment_confirmed',
                'model_type' => \App\Models\Appointment::class,
                'model_id' => $appointment->id,
                'new_values' => [
                    'status' => 'confirmed',
                    'invoice_id' => $invoice->id,
                    'invoice_amount' => $invoice->total,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('Appointment confirmed and invoice created', [
                'appointment_id' => $appointment->id,
                'invoice_id' => $invoice->id,
                'client_id' => $appointment->client_id,
                'amount' => $invoice->total,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle appointment confirmed event', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
