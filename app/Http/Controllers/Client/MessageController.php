<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Mail\NewMessage;
use App\Models\Appointment;
use App\Models\LegalCase;
use App\Models\Message;
use App\Models\User;
use App\Services\AppointmentMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    // ─── Case-based messaging ────────────────────────────────────────────────

    /**
     * Show case thread selection or a specific case thread.
     */
    public function index(Request $request, LegalCase $case = null)
    {
        $user = auth()->user();

        if ($case && $case->client_id !== $user->id) {
            abort(403);
        }

        $caseLawyerIds = LegalCase::where('client_id', $user->id)
            ->whereNotNull('lawyer_id')
            ->pluck('lawyer_id')
            ->unique();

        $messageLawyerIds = Message::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->get()
            ->map(function ($message) use ($user) {
                return $message->sender_id === $user->id
                    ? $message->receiver_id
                    : $message->sender_id;
            })
            ->unique();

        $allLawyerIds = $caseLawyerIds->merge($messageLawyerIds)->unique();

        $contacts = User::whereIn('id', $allLawyerIds)
            ->where('role', 'lawyer')
            ->get()
            ->map(function ($lawyer) use ($user) {
                $lastMessage = Message::where(function ($q) use ($user, $lawyer) {
                        $q->where('sender_id', $user->id)
                          ->where('receiver_id', $lawyer->id);
                    })
                    ->orWhere(function ($q) use ($user, $lawyer) {
                        $q->where('sender_id', $lawyer->id)
                          ->where('receiver_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                $lawyer->last_message = $lastMessage;
                $lawyer->last_message_at = $lastMessage?->created_at;
                $lawyer->unread_count = Message::where('sender_id', $lawyer->id)
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                return $lawyer;
            })
            ->sortByDesc('last_message_at');

        $selectedLawyerId = $request->query('with') ?? $case?->lawyer_id;
        $selectedLawyer = null;
        $selectedCase = $case;
        $messages = collect();

        if ($selectedLawyerId) {
            $selectedLawyer = User::where('role', 'lawyer')
                ->find($selectedLawyerId);

            if ($selectedLawyer) {
                $messages = Message::where(function ($q) use ($user, $selectedLawyerId) {
                        $q->where('sender_id', $user->id)
                          ->where('receiver_id', $selectedLawyerId);
                    })
                    ->orWhere(function ($q) use ($user, $selectedLawyerId) {
                        $q->where('sender_id', $selectedLawyerId)
                          ->where('receiver_id', $user->id);
                    })
                    ->when($selectedCase, function ($q) use ($selectedCase) {
                        $q->where('case_id', $selectedCase->id);
                    })
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get();

                Message::where('sender_id', $selectedLawyerId)
                    ->where('receiver_id', $user->id)
                    ->when($selectedCase, function ($q) use ($selectedCase) {
                        $q->where('case_id', $selectedCase->id);
                    })
                    ->whereNull('read_at')
                    ->update(['is_read' => true, 'read_at' => now()]);
            }
        }

        return view('client.messages', compact('contacts', 'messages', 'selectedLawyer', 'selectedCase'));
    }

    // ─── Appointment-based messaging ─────────────────────────────────────────

    /**
     * Show appointment message thread.
     * Triggered after lawyer confirms appointment — thread is pre-seeded.
     */
    public function appointmentThread(Appointment $appointment)
    {
        $client = auth()->user();

        if ($appointment->client_id !== $client->id) {
            abort(403);
        }

        $appointment->load('lawyer:id,name,email');

        $messages = AppointmentMessagingService::getConversationThread($appointment);

        AppointmentMessagingService::markAsRead($appointment, $client->id);

        return view('client.appointment-messages', compact('appointment', 'messages'));
    }

    // ─── Shared store ────────────────────────────────────────────────────────

    /**
     * Store a message — routes to appointment thread or case thread.
     */
    public function store(StoreMessageRequest $request)
    {
        $client = auth()->user();

        try {
            if ($request->filled('appointment_id')) {
                $appointment = Appointment::findOrFail($request->appointment_id);

                if ($appointment->client_id !== $client->id) {
                    return redirect()->back()->withErrors(['auth' => 'Unauthorized.']);
                }

                $message = AppointmentMessagingService::sendMessage(
                    $appointment,
                    $client->id,
                    (int) $request->receiver_id,
                    $request->body
                );

                $receiver = User::find($request->receiver_id);
                if ($receiver && $receiver->email) {
                    Mail::to($receiver->email)->queue(new NewMessage($message, route('messages.list')));
                }

                return redirect()
                    ->route('client.appointments.messages', $appointment->id)
                    ->with('success', 'Message sent.');
            }

            if ($request->filled('case_id')) {
                $case = LegalCase::where('client_id', $client->id)
                    ->findOrFail($request->case_id);

                $message = Message::create([
                    'case_id'     => $request->case_id,
                    'sender_id'   => $client->id,
                    'receiver_id' => $request->receiver_id,
                    'body'        => $request->body,
                    'is_read'     => false,
                ]);

                $receiver = User::find($request->receiver_id);
                if ($receiver && $receiver->email) {
                    Mail::to($receiver->email)->queue(new NewMessage($message, route('messages.list')));
                }

                return redirect()
                    ->route('client.messages.index', ['case' => $request->case_id, 'with' => $request->receiver_id])
                    ->with('success', 'Message sent.');
            }

            $message = Message::create([
                'sender_id'   => $client->id,
                'receiver_id' => $request->receiver_id,
                'body'        => $request->body,
                'is_read'     => false,
            ]);

            $receiver = User::find($request->receiver_id);
            if ($receiver && $receiver->email) {
                Mail::to($receiver->email)->queue(new NewMessage($message, route('messages.list')));
            }

            return redirect()
                ->route('client.messages.list', ['with' => $request->receiver_id])
                ->with('success', 'Message sent.');
        } catch (\Exception $e) {
            Log::error('Client message send failed', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to send message. Please try again.']);
        }
    }
}