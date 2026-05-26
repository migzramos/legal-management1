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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
 
class MessageController extends Controller
{
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
 
        Message::whereHas('case', function ($q) use ($lawyer) {
                $q->where('lawyer_id', $lawyer->id);
            })
            ->where('receiver_id', $lawyer->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
 
        return view('lawyer.messages-list', compact('cases', 'messages'));
    }
 
    public function index(LegalCase $case, Request $request)
    {
        $this->authorize('view', $case);
 
        $clientId = $case->client_id;
        $lawyerId = auth()->id();
 
        if ($request->ajax() || $request->wantsJson()) {
            $after = (int) $request->query('after', 0);
 
            $newMessages = Message::where('id', '>', $after)
                ->where(function ($q) use ($case, $clientId, $lawyerId) {
                    $q->where('case_id', $case->id)
                      ->orWhere(function ($q2) use ($clientId, $lawyerId) {
                          $q2->whereNull('case_id')
                             ->where(function ($q3) use ($clientId, $lawyerId) {
                                 $q3->where('sender_id', $clientId)
                                    ->where('receiver_id', $lawyerId);
                             })
                             ->orWhere(function ($q3) use ($clientId, $lawyerId) {
                                 $q3->where('sender_id', $lawyerId)
                                    ->where('receiver_id', $clientId);
                             });
                      });
                })
                ->with('sender:id,name,role')
                ->oldest()
                ->get()
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'sender_id'   => $m->sender_id,
                    'sender_name' => $m->sender->name ?? 'Unknown',
                    'sender_role' => $m->sender->role ?? 'client',
                    'body'        => e($m->body),
                    'created_at'  => $m->created_at->format('M d, g:i A'),
                ]);
 
            Message::where('receiver_id', $lawyerId)
                ->where('id', '>', $after)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
 
            return response()->json(['messages' => $newMessages]);
        }
 
        $cases = LegalCase::where('lawyer_id', $lawyerId)
            ->with('client:id,name')
            ->get();
 
        $activeCase = $case;
 
        $messages = Message::where(function ($q) use ($case, $clientId, $lawyerId) {
                $q->where('case_id', $case->id)
                  ->orWhere(function ($q2) use ($clientId, $lawyerId) {
                      $q2->whereNull('case_id')
                         ->where(function ($q3) use ($clientId, $lawyerId) {
                             $q3->where('sender_id', $clientId)
                                ->where('receiver_id', $lawyerId);
                         })
                         ->orWhere(function ($q3) use ($clientId, $lawyerId) {
                             $q3->where('sender_id', $lawyerId)
                                ->where('receiver_id', $clientId);
                         });
                  });
            })
            ->with('sender:id,name,role', 'receiver:id,name,role')
            ->oldest()
            ->get();
 
        Message::where('receiver_id', $lawyerId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
 
        return view('lawyer.messages', compact('cases', 'activeCase', 'messages'));
    }
 
    public function store(StoreMessageRequest $request)
    {
        try {
            if ($request->filled('appointment_id')) {
                $appointment = Appointment::findOrFail($request->appointment_id);
 
                if ($appointment->lawyer_id !== auth()->id()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['error' => 'Unauthorized.'], 403);
                    }
                    abort(403);
                }
 
                $message = AppointmentMessagingService::sendMessage(
                    $appointment,
                    auth()->id(),
                    (int) $request->receiver_id,
                    $request->body
                );
            } else {
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
 
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent.',
                    'data'    => $message->load('sender:id,name,role', 'receiver:id,name,role'),
                ], 201);
            }
 
            if ($request->filled('case_id')) {
                return redirect()
                    ->route('lawyer.messages.index', $request->case_id)
                    ->with('success', 'Message sent.');
            }
 
            return redirect()->back()->with('success', 'Message sent.');
 
        } catch (\Exception $e) {
            Log::error('Lawyer message send failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
 
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Failed to send message. Please try again.',
                ], 500);
            }
 
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send message. Please try again.');
        }
    }
 
    public function appointmentThread(Appointment $appointment, Request $request)
    {
        if ($appointment->lawyer_id !== auth()->id()) {
            abort(403);
        }
 
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
 
        $messages = AppointmentMessagingService::getConversationThread($appointment);
        AppointmentMessagingService::markAsRead($appointment, auth()->id());
 
        return view('lawyer.appointment-messages', compact('appointment', 'messages'));
    }
}
 