<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Appointment Messaging Service
 * 
 * Manages persistent message threads tied to appointments
 * Ensures secure storage, proper indexing, and instant delivery
 * Supports event broadcasting or polling fallback
 */
class AppointmentMessagingService
{
    /**
     * Create initial message thread for appointment
     * Called automatically after appointment confirmation
     * 
     * @param Appointment $appointment
     * @param int $lawyerId
     * @return Message
     */
    public static function createInitialThread(Appointment $appointment, int $lawyerId): Message
    {
        return DB::transaction(function () use ($appointment, $lawyerId) {
            $message = Message::create([
                'appointment_id' => $appointment->id,
                'sender_id' => $lawyerId,
                'receiver_id' => $appointment->client_id,
                'body' => sprintf(
                    "Your appointment has been confirmed for %s. This is your secure conversation thread with %s. Feel free to ask any questions about the appointment or next steps.",
                    $appointment->appointment_at->format('F d, Y \a\t g:i A'),
                    $appointment->lawyer->name
                ),
                'is_read' => false,
            ]);

            Log::info('Appointment message thread created', [
                'appointment_id' => $appointment->id,
                'message_id' => $message->id,
            ]);

            // Trigger event for real-time delivery
            event(new MessageSent($message));

            return $message;
        });
    }

    /**
     * Send a message within appointment thread
     * 
     * @param Appointment $appointment
     * @param int $senderId
     * @param int $receiverId
     * @param string $body
     * @return Message
     */
    public static function sendMessage(Appointment $appointment, int $senderId, int $receiverId, string $body): Message
    {
        // Validate message length
        if (strlen($body) > config('legal.messaging.max_message_length', 5000)) {
            throw new \InvalidArgumentException('Message exceeds maximum length.');
        }

        return DB::transaction(function () use ($appointment, $senderId, $receiverId, $body) {
            $message = Message::create([
                'appointment_id' => $appointment->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'body' => $body,
                'is_read' => false,
            ]);

            Log::info('Appointment message sent', [
                'appointment_id' => $appointment->id,
                'message_id' => $message->id,
                'from_user' => $senderId,
                'to_user' => $receiverId,
            ]);

            // Trigger real-time event
            event(new MessageSent($message));

            return $message;
        });
    }

    /**
     * Get conversation thread for appointment
     * 
     * @param Appointment $appointment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getConversationThread(Appointment $appointment)
    {
        return $appointment->messages()
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Mark messages as read
     * 
     * @param Appointment $appointment
     * @param int $userId
     * @return int Count of marked messages
     */
    public static function markAsRead(Appointment $appointment, int $userId): int
    {
        $delay = config('legal.messaging.auto_read_delay_seconds', 300);

        return $appointment->messages()
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread message count for user
     * 
     * @param int $userId
     * @return int
     */
    public static function getUnreadCount(int $userId): int
    {
        return Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get unread messages for user
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUnreadMessages(int $userId)
    {
        return Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->with(['appointment', 'sender:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }

    /**
     * Get all appointment message threads for user
     * 
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\Paginator
     */
    public static function getAllThreads(int $userId, int $perPage = 15)
    {
        return Message::whereIn('sender_id', [$userId])
            ->orWhere('receiver_id', $userId)
            ->distinct()
            ->with(['appointment:id,appointment_at', 'sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search messages within appointment thread
     * 
     * @param Appointment $appointment
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchMessages(Appointment $appointment, string $searchTerm)
    {
        return $appointment->messages()
            ->where('body', 'LIKE', '%' . $searchTerm . '%')
            ->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
