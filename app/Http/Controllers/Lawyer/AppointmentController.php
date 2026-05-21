<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BillingRate;
use App\Models\AuditLog;
use App\Events\AppointmentConfirmed;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Message;
use App\Models\Schedule;
use App\Services\AppointmentMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * Display all appointments for lawyer
     */
    public function index()
    {
        $appointments = Appointment::where('lawyer_id', auth()->id())
            ->with('client:id,name')
            ->orderBy('appointment_at', 'desc')
            ->get();

        return view('lawyer.appointments', compact('appointments'));
    }

    /**
     * Display a specific appointment
     */
    public function show(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== auth()->id()) {
            abort(403);
        }
        
        $appointment->load([
            'client:id,name,email,phone',
            'messages' => fn($q) => $q->with('sender:id,name')->orderBy('created_at', 'asc')
        ]);
        
        return view('lawyer.appointment-detail', compact('appointment'));
    }

    /**
     * Confirm an appointment and create invoice automatically
     * 
     * Transactional workflow:
     * 1. Validate appointment state and hourly rate
     * 2. Update appointment status to confirmed
     * 3. Create invoice with computed payment values
     * 4. Create invoice line item
     * 5. Create initial message thread
     * 6. Fire AppointmentConfirmed event for observers
     * 7. Log action for audit trail
     */
    public function confirm(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== auth()->id()) {
            abort(403);
        }

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending appointments can be confirmed.']);
        }

        $lawyer = auth()->user();
        
        // CRITICAL: Fetch hourly rate from configuration only
        // Never allow client override or manual input
        $hourlyRate = BillingRate::where('lawyer_id', $lawyer->id)
            ->latest('effective_date')
            ->value('hourly_rate');

        if ($hourlyRate === null || $hourlyRate <= 0) {
            return back()->withErrors([
                'status' => 'Hourly rate is not configured for this lawyer. Please contact administration.'
            ]);
        }

        // Calculate invoice amount with strict precision
        $invoiceAmount = round(($hourlyRate * $appointment->duration_minutes) / 60, 2);

        try {
            DB::transaction(function () use ($appointment, $lawyer, $hourlyRate, $invoiceAmount) {
                // 1. Update appointment status
                $appointment->update(['status' => 'confirmed']);

                // 2. Create invoice with non-editable computed values
                $invoice = Invoice::create([
                    'invoice_number'   => 'INV-' . strtoupper(Str::random(8)),
                    'case_id'          => null,
                    'appointment_id'   => $appointment->id,
                    'client_id'        => $appointment->client_id,
                    'lawyer_id'        => $lawyer->id,
                    'subtotal'         => $invoiceAmount,
                    'tax'              => 0,
                    'total'            => $invoiceAmount,
                    'amount_paid'      => 0,
                    'balance'          => $invoiceAmount,
                    'status'           => 'sent',
                    'is_validated'     => false,
                    'issued_date'      => now()->toDateString(),
                    'due_date'         => now()->addDays(7)->toDateString(),
                    'notes'            => 'Auto-generated invoice after appointment confirmation.',
                ]);

                // 3. Create invoice line item with appointment details
                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'time_entry_id' => null,
                    'description'   => sprintf(
                        'Professional services - Appointment on %s with client %s (Duration: %d minutes)',
                        $appointment->appointment_at->format('F d, Y \a\t g:i A'),
                        $appointment->client->name,
                        $appointment->duration_minutes
                    ),
                    'quantity'      => round($appointment->duration_minutes / 60, 2),
                    'unit_price'    => $hourlyRate,
                    'total'         => $invoiceAmount,
                ]);

                // 4. Create initial message thread for appointment
                AppointmentMessagingService::createInitialThread($appointment, $lawyer->id);

                // 5. Log appointment confirmation
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'appointment_confirmed',
                    'model_type' => Appointment::class,
                    'model_id' => $appointment->id,
                    'description' => "Appointment confirmed for {$appointment->client->name} - Invoice #{$invoice->invoice_number}",
                    'new_values' => [
                        'status' => 'confirmed',
                        'invoice_id' => $invoice->id,
                        'invoice_amount' => $invoiceAmount,
                        'hourly_rate' => $hourlyRate,
                        'duration_minutes' => $appointment->duration_minutes,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // 6. Fire event for observers and listeners
                event(new AppointmentConfirmed($appointment, $invoice));

                Log::info('Appointment confirmed with invoice created', [
                    'appointment_id' => $appointment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $invoiceAmount,
                    'lawyer_id' => $lawyer->id,
                    'client_id' => $appointment->client_id,
                ]);
            });

            return back()->with('success', sprintf(
                'Appointment confirmed successfully. Invoice #%s has been created and sent to the client.',
                $appointment->invoice->invoice_number ?? 'INV-****'
            ));
        } catch (\Exception $e) {
            Log::error('Appointment confirmation failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'status' => 'An error occurred while confirming the appointment. Please try again.'
            ]);
        }
    }

    /**
     * Cancel an appointment
     * Handles status transition and cleanup
     */
    public function cancel(Appointment $appointment)
    {
        if ($appointment->lawyer_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['status' => 'Cannot cancel this appointment.']);
        }

        try {
            DB::transaction(function () use ($appointment) {
                $oldStatus = $appointment->status;
                $appointment->update(['status' => 'cancelled']);
                
                // Cancel related reminder schedule
                Schedule::where('type', 'appointment')
                    ->where('scheduled_at', $appointment->appointment_at)
                    ->where('created_by', auth()->id())
                    ->delete();

                // Log cancellation
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'appointment_cancelled',
                    'model_type' => Appointment::class,
                    'model_id' => $appointment->id,
                    'description' => "Appointment cancelled - Client: {$appointment->client->name}",
                    'old_values' => ['status' => $oldStatus],
                    'new_values' => ['status' => 'cancelled'],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                Log::info('Appointment cancelled', [
                    'appointment_id' => $appointment->id,
                    'lawyer_id' => auth()->id(),
                ]);
            });

            return back()->with('success', 'Appointment cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Appointment cancellation failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['status' => 'Failed to cancel appointment.']);
        }
    }
}
