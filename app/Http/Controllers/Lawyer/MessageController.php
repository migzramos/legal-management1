<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Mail\NewMessage;
use App\Models\Appointment;
use App\Models\LegalCase;
use App\Models\Message;
use App\Models\User;
use App\Services\AppointmentMessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    // ─── Case-based messaging ────────────────────────────────────────────────

    /**
     * List all case threads for the authenticated lawyer.
     */
    public function list()
    {
        $lawyer = auth()->user();

        $cases = LegalCase::where('lawyer_id', $lawyer->id)
            ->with('client:id,name')
            ->get();

        $messages = Message::whereHas('case', function ($q) use ($lawyer) {
                $q->where('lawyer_id', $lawyer->id);
            })
            ->where(function ($q) use ($lawyer) {
                $q->where('sender_id', $lawyer->id)
                  ->orWhere('receiver_id', $lawyer->id);
            })
            ->with(['case:id,title,case_number', 'sender:id,name,role', 'receiver:id,name,role'])
            ->latest()
            ->paginate(20);

        // Mark received case messages as read
        Message::whereHas('case', function ($q) use ($lawyer) {
                $q->where('lawyer_id', $lawyer->id);
            })
            ->where('receiver_id', $lawyer->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('lawyer.messages-list', compact('cases', 'messages'));
    }

    /**
     * Show a specific case thread.
     */
    public function index(LegalCase $case)
    {
        $this->authorize('view', $case);

        // Fetch all cases for the sidebar
        $cases = LegalCase::where('lawyer_id', auth()->id())
            ->with('client:id,name')
            ->get();

        // Alias for the active thread
        $activeCase = $case;

        $messages = Message::where('case_id', $case->id)
            ->where(function ($q) {
                $q->where('sender_id', auth()->id())
                  ->orWhere('receiver_id', auth()->id());
            })
            ->with('sender:id,name,role', 'receiver:id,name,role')
            ->oldest()
            ->get();

        // Mark received messages as read
        Message::where('case_id', $case->id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('lawyer.messages', compact('cases', 'activeCase', 'messages'));
    }

    /**
     * Send a message — routes to appointment or case thread based on payload.
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        try {
            if ($request->filled('appointment_id')) {
                // ── Appointment-based thread ──
                $appointment = Appointment::findOrFail($request->appointment_id);

                if ($appointment->lawyer_id !== auth()->id()) {
                    return response()->json(['error' => 'Unauthorized.'], 403);
                }

                $message = AppointmentMessagingService::sendMessage(
                    $appointment,
                    auth()->id(),
                    (int) $request->receiver_id,
                    $request->body
                );
            } else {
                // ── Case-based thread ──
                $case = LegalCase::findOrFail($request->case_id);
                $this->authorize('view', $case);

                $message = Message::create([
                    'case_id'     => $request->case_id,
                    'sender_id'   => auth()->id(),
                    'receiver_id' => $request->receiver_id,
                    'body'        => $request->body,
                    'is_read'     => false,
                ]);
            }

            $receiver = User::find($request->receiver_id);
            if ($receiver && $receiver->email) {
                $link = $request->filled('case_id')
                    ? route('client.messages.index', $request->case_id)
                    : route('client.appointments.messages', $appointment->id ?? null);

                Mail::to($receiver->email)->queue(new NewMessage($message, $link));
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent.',
                'data'    => $message->load('sender:id,name', 'receiver:id,name'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Lawyer message send failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Failed to send message. Please try again.',
            ], 500);
        }
    }

    // ─── Appointment-based messaging ─────────────────────────────────────────

    /**
     * View appointment message thread (web) or poll new messages (AJAX/JSON).
     */
    public function appointmentThread(Appointment $appointment, \Illuminate\Http\Request $request)
    {
        if ($appointment->lawyer_id !== auth()->id()) {
            abort(403);
        }

        // AJAX polling: return only messages newer than `after` ID
        if ($request->ajax() || $request->wantsJson()) {
            $after = (int) $request->query('after', 0);

            $newMessages = $appointment->messages()
                ->with('sender:id,name')
                ->where('id', '>', $after)
                ->get()
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'sender_id'   => $m->sender_id,
                    'sender_name' => $m->sender->name ?? 'Unknown',
                    'body'        => e($m->body),
                    'created_at'  => $m->created_at->format('M d, g:i A'),
                ]);

            return response()->json(['messages' => $newMessages]);
        }

        // Full page load
        $messages = AppointmentMessagingService::getConversationThread($appointment);

        AppointmentMessagingService::markAsRead($appointment, auth()->id());

        return view('lawyer.appointment-messages', compact('appointment', 'messages'));
    }
}