<?php
namespace App\Http\Controllers\Client;
 
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BillingRate;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
 
class AppointmentController extends Controller
{
    public function index()
    {
        $activeFilter = request('filter', 'all');
 
        $baseQuery = Appointment::where('client_id', auth()->id());
 
        $tabCounts = [
            'all'       => (clone $baseQuery)->count(),
            'pending'   => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
 
        $query = (clone $baseQuery)
            ->with('lawyer:id,name,email,phone', 'invoice:id,appointment_id')
            ->orderBy('appointment_at', 'desc');
 
        if ($activeFilter !== 'all') {
            $query->where('status', $activeFilter);
        }
 
        $appointments = $query->paginate(10)->withQueryString();
 
        $lawyers = User::where('role', 'lawyer')
            ->where('is_active', true)
            ->orderBy('name')
            ->with(['billingRate' => fn($q) => $q->latest('effective_date')])
            ->get(['id', 'name']);
 
        return view('client.appointments', compact('appointments', 'lawyers', 'activeFilter', 'tabCounts'));
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'lawyer_id'        => 'required|exists:users,id',
            'appointment_at'   => 'required|date|after:now',
            'duration_minutes' => 'required|integer|in:30,60,90,120',
            'purpose'          => 'required|string|max:255',
            'purpose_other'    => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:1000',
        ]);
 
        $lawyer = User::where('role', 'lawyer')
            ->where('is_active', true)
            ->findOrFail($request->lawyer_id);
 
        $appointmentAt = Carbon::parse($request->appointment_at);
 
        // Use configured hourly rate, default to 0 if not set
        $hourlyRate = BillingRate::where('lawyer_id', $lawyer->id)
            ->latest('effective_date')
            ->value('hourly_rate') ?? 0;
 
        try {
            $appointment = DB::transaction(function () use ($request, $lawyer, $hourlyRate, $appointmentAt) {
                $appointment = Appointment::create([
                    'client_id'        => auth()->id(),
                    'lawyer_id'        => $lawyer->id,
                    'appointment_at'   => $appointmentAt,
                    'duration_minutes' => (int) $request->duration_minutes,
                    'hourly_rate'      => $hourlyRate,
                    'purpose'          => $request->purpose === 'Other' ? $request->purpose_other : $request->purpose,
                    'notes'            => $request->notes,
                    'status'           => 'pending',
                ]);
 
                AuditLog::create([
                    'user_id'     => auth()->id(),
                    'action'      => 'appointment_created',
                    'model_type'  => Appointment::class,
                    'model_id'    => $appointment->id,
                    'description' => "Appointment created with {$lawyer->name} for {$appointmentAt->format('F d, Y \a\t g:i A')}",
                    'new_values'  => [
                        'lawyer_id'        => $lawyer->id,
                        'appointment_at'   => $appointmentAt->toIso8601String(),
                        'duration_minutes' => $appointment->duration_minutes,
                        'hourly_rate'      => $hourlyRate,
                        'status'           => 'pending',
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
 
                Log::info('Appointment booked', [
                    'appointment_id' => $appointment->id,
                    'client_id'      => auth()->id(),
                    'lawyer_id'      => $lawyer->id,
                    'appointment_at' => $appointmentAt->toIso8601String(),
                ]);
 
                return $appointment;
            });
 
            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Appointment booked successfully. Your lawyer will review and confirm soon.',
                    'appointment' => [
                        'id'               => $appointment->id,
                        'appointment_at'   => $appointment->appointment_at->toIso8601String(),
                        'duration_minutes' => $appointment->duration_minutes,
                        'hourly_rate'      => money_display($appointment->hourly_rate),
                        'estimated_cost'   => money_display(($appointment->hourly_rate * $appointment->duration_minutes) / 60),
                        'status'           => $appointment->status,
                    ],
                ], 201);
            }
 
            return redirect()->route('client.appointments.index')
                ->with('success', 'Appointment booked successfully. Your lawyer will review and confirm soon.');
 
        } catch (\Exception $e) {
            Log::error('Appointment creation failed', [
                'client_id' => auth()->id(),
                'lawyer_id' => $lawyer->id,
                'error'     => $e->getMessage(),
            ]);
 
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'An error occurred while booking the appointment. Please try again.',
                ], 500);
            }
 
            return back()
                ->withInput()
                ->withErrors(['appointment_at' => 'An error occurred while booking the appointment. Please try again.']);
        }
    }
 
    public function show(Appointment $appointment)
    {
        if ($appointment->client_id !== auth()->id()) {
            abort(403);
        }
 
        $appointment->load('lawyer:id,name,email,phone');
 
        return view('client.appointment-detail', compact('appointment'));
    }
 
    public function cancel(Appointment $appointment)
    {
        if ($appointment->client_id !== auth()->id()) {
            abort(403);
        }
 
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['status' => 'Cannot cancel this appointment.']);
        }
 
        try {
            DB::transaction(function () use ($appointment) {
                $oldStatus = $appointment->status;
                $appointment->update(['status' => 'cancelled']);
 
                AuditLog::create([
                    'user_id'     => auth()->id(),
                    'action'      => 'appointment_cancelled',
                    'model_type'  => Appointment::class,
                    'model_id'    => $appointment->id,
                    'description' => "Appointment cancelled with {$appointment->lawyer->name}",
                    'old_values'  => ['status' => $oldStatus],
                    'new_values'  => ['status' => 'cancelled'],
                    'ip_address'  => request()->ip(),
                    'user_agent'  => request()->userAgent(),
                ]);
 
                Log::info('Appointment cancelled by client', [
                    'appointment_id' => $appointment->id,
                    'client_id'      => auth()->id(),
                ]);
            });
 
            return redirect()->route('client.appointments.index')
                ->with('success', 'Appointment cancelled successfully.');
 
        } catch (\Exception $e) {
            Log::error('Appointment cancellation failed', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);
 
            return back()->withErrors(['status' => 'Failed to cancel appointment.']);
        }
    }
}
 