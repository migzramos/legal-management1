<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Message;
use App\Services\MessageThreadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    protected MessageThreadService $service;

    public function __construct(MessageThreadService $service)
    {
        $this->service = $service;
    }

    /**
     * Get messages for an appointment thread (with polling support)
     * GET /api/appointments/{appointment}/messages
     * 
     * @param Appointment $appointment
     * @param Request $request
     * @return JsonResponse
     */
    public function getThread(Appointment $appointment, Request $request): JsonResponse
    {
        $this->authorize('view', $appointment);

        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);
        $sinceId = $request->query('since_id'); // For polling - only get messages after this ID

        $query = Message::where('appointment_id', $appointment->id);

        // Polling support: only get new messages since last fetch
        if ($sinceId) {
            $query->where('id', '>', $sinceId);
        }

        $messages = $query
            ->orderBy('created_at', 'asc')
            ->with('sender:id,name', 'receiver:id,name')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
            'latest_message_id' => $messages->count() > 0 ? $messages->last()->id : null,
        ]);
    }

    /**
     * Send a message in appointment thread
     * POST /api/appointments/{appointment}/messages
     * 
     * @param Appointment $appointment
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Appointment $appointment, Request $request): JsonResponse
    {
        $this->authorize('sendMessage', $appointment);

        $request->validate([
            'body' => 'required|string|min:1|max:5000',
        ]);

        $message = $this->service->sendMessage(
            $appointment,
            auth()->user(),
            $request->input('body')
        );

        return response()->json([
            'success' => true,
            'message' => $message->load('sender:id,name', 'receiver:id,name'),
        ], 201);
    }

    /**
     * Mark messages as read
     * POST /api/appointments/{appointment}/messages/mark-read
     * 
     * @param Appointment $appointment
     * @return JsonResponse
     */
    public function markAsRead(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        $count = $this->service->markThreadAsRead($appointment, auth()->user());

        return response()->json([
            'success' => true,
            'marked_as_read' => $count,
        ]);
    }

    /**
     * Get thread summary (unread counts, last message, etc)
     * GET /api/appointments/{appointment}/messages/summary
     * 
     * @param Appointment $appointment
     * @return JsonResponse
     */
    public function getSummary(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        $summary = $this->service->getThreadSummary($appointment);
        $unreadCount = $this->service->getUnreadCount($appointment, auth()->user());

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get latest messages across all user's appointments (dashboard)
     * GET /api/messages/latest
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatest(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $messages = $this->service->getLatestMessages(auth()->user(), $limit);

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'sender' => [
                        'id' => $msg->sender->id,
                        'name' => $msg->sender->name,
                    ],
                    'appointment_id' => $msg->appointment_id,
                    'created_at' => $msg->created_at,
                    'is_read' => $msg->is_read,
                ];
            }),
        ]);
    }

    /**
     * Delete a message
     * DELETE /api/messages/{message}
     * 
     * @param Message $message
     * @return JsonResponse
     */
    public function delete(Message $message): JsonResponse
    {
        $appointment = $message->appointment;
        $this->authorize('view', $appointment);

        if (!$this->service->deleteMessage($message, auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Only the sender can delete their messages.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.',
        ]);
    }

    /**
     * Restore a deleted message
     * POST /api/messages/{message}/restore
     * 
     * @param Message $message
     * @return JsonResponse
     */
    public function restore(Message $message): JsonResponse
    {
        $appointment = $message->appointment;
        $this->authorize('view', $appointment);

        if (!$this->service->restoreMessage($message, auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Only the sender can restore their messages.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message restored successfully.',
        ]);
    }

    /**
     * Search messages in appointment thread
     * GET /api/appointments/{appointment}/messages/search
     * 
     * @param Appointment $appointment
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Appointment $appointment, Request $request): JsonResponse
    {
        $this->authorize('view', $appointment);

        $request->validate([
            'query' => 'required|string|min:1|max:100',
        ]);

        $results = $this->service->searchThreadMessages(
            $appointment,
            $request->input('query')
        );

        return response()->json([
            'success' => true,
            'results' => $results->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'sender' => [
                        'id' => $msg->sender->id,
                        'name' => $msg->sender->name,
                    ],
                    'created_at' => $msg->created_at,
                ];
            }),
        ]);
    }
}
