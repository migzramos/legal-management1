<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Appointment;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MessageThreadService
{
    /**
     * Send a message in an appointment thread
     * 
     * @param Appointment $appointment
     * @param User $sender
     * @param string $body
     * @return Message
     */
    public function sendMessage(Appointment $appointment, User $sender, string $body): Message
    {
        // Determine receiver based on sender role
        $receiver = $sender->id === $appointment->client_id 
            ? $appointment->lawyer 
            : $appointment->client;

        return DB::transaction(function () use ($appointment, $sender, $receiver, $body) {
            $message = Message::create([
                'appointment_id' => $appointment->id,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'body' => $body,
                'is_read' => false,
            ]);

            // Broadcast event for real-time delivery
            broadcast(new MessageSent($message, $appointment))->toOthers();

            // Log the message action
            \App\Models\AuditLog::create([
                'user_id' => $sender->id,
                'entity_type' => 'Message',
                'entity_id' => $message->id,
                'action' => 'send',
                'description' => "Sent message in appointment thread {$appointment->id}",
                'old_values' => [],
                'new_values' => [
                    'body' => substr($body, 0, 100),
                    'receiver_id' => $receiver->id,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $message;
        });
    }

    /**
     * Get all messages for an appointment thread (with pagination)
     * 
     * @param Appointment $appointment
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getThreadMessages(Appointment $appointment, int $perPage = 20, int $page = 1)
    {
        return Message::where('appointment_id', $appointment->id)
            ->orderBy('created_at', 'asc')
            ->with('sender', 'receiver')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get unread message count for user in specific appointment
     * 
     * @param Appointment $appointment
     * @param User $user
     * @return int
     */
    public function getUnreadCount(Appointment $appointment, User $user): int
    {
        return Message::where('appointment_id', $appointment->id)
            ->where('receiver_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Mark messages as read for a user in appointment thread
     * 
     * @param Appointment $appointment
     * @param User $user
     * @return int Number of messages marked as read
     */
    public function markThreadAsRead(Appointment $appointment, User $user): int
    {
        return Message::where('appointment_id', $appointment->id)
            ->where('receiver_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get latest messages for all user's appointments (dashboard)
     * 
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getLatestMessages(User $user, int $limit = 10): Collection
    {
        return Message::whereIn('appointment_id', function ($query) use ($user) {
            $query->select('id')
                ->from('appointments')
                ->where(function ($q) use ($user) {
                    $q->where('client_id', $user->id)
                        ->orWhere('lawyer_id', $user->id);
                });
        })
            ->orderBy('created_at', 'desc')
            ->with('appointment', 'sender', 'receiver')
            ->limit($limit)
            ->get();
    }

    /**
     * Search messages in an appointment thread
     * 
     * @param Appointment $appointment
     * @param string $query
     * @return Collection
     */
    public function searchThreadMessages(Appointment $appointment, string $query): Collection
    {
        return Message::where('appointment_id', $appointment->id)
            ->where('body', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->with('sender', 'receiver')
            ->get();
    }

    /**
     * Delete a message (soft delete)
     * 
     * @param Message $message
     * @param User $user
     * @return bool
     */
    public function deleteMessage(Message $message, User $user): bool
    {
        // Only sender can delete their own message
        if ($message->sender_id !== $user->id) {
            return false;
        }

        return (bool) $message->delete();
    }

    /**
     * Restore a soft-deleted message
     * 
     * @param Message $message
     * @param User $user
     * @return bool
     */
    public function restoreMessage(Message $message, User $user): bool
    {
        // Only sender can restore their message
        if ($message->sender_id !== $user->id) {
            return false;
        }

        return (bool) $message->restore();
    }

    /**
     * Get message thread summary for appointment
     * 
     * @param Appointment $appointment
     * @return array
     */
    public function getThreadSummary(Appointment $appointment): array
    {
        $messages = Message::where('appointment_id', $appointment->id)
            ->get();

        return [
            'total_messages' => $messages->count(),
            'unread_for_client' => $messages->where('receiver_id', $appointment->client_id)->where('is_read', false)->count(),
            'unread_for_lawyer' => $messages->where('receiver_id', $appointment->lawyer_id)->where('is_read', false)->count(),
            'last_message_at' => $messages->max('created_at'),
            'participants' => [
                'client_id' => $appointment->client_id,
                'lawyer_id' => $appointment->lawyer_id,
            ],
        ];
    }
}
