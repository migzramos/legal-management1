<?php

namespace App\Services;

use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin Messaging Service
 * 
 * Manages direct communication between admin and lawyers
 * Supports secure storage, real-time delivery, and proper indexing
 */
class AdminMessagingService
{
    /**
     * Send a message from admin to lawyer or vice versa
     * 
     * @param int $senderId
     * @param int $recipientId
     * @param string $body
     * @param string $category
     * @param string $priority
     * @return AdminMessage
     */
    public static function sendMessage(int $senderId, int $recipientId, string $body, string $category = 'general', string $priority = 'normal'): AdminMessage
    {
        // Validate priority
        if (!in_array($priority, ['low', 'normal', 'high', 'urgent'])) {
            $priority = 'normal';
        }

        // Validate category
        if (!in_array($category, ['billing', 'appointment', 'case', 'payment', 'general', 'technical'])) {
            $category = 'general';
        }

        return DB::transaction(function () use ($senderId, $recipientId, $body, $category, $priority) {
            $message = AdminMessage::create([
                'lawyer_id' => $recipientId, // Storing which lawyer the message is for
                'admin_id' => $senderId,     // Who sent it
                'body' => $body,
                'category' => $category,
                'priority' => $priority,
                'is_read' => false,
            ]);

            Log::info('Admin message sent', [
                'message_id' => $message->id,
                'from' => $senderId,
                'to' => $recipientId,
                'category' => $category,
                'priority' => $priority,
            ]);

            return $message;
        });
    }

    /**
     * Get conversation between admin and lawyer
     * 
     * @param int $lawyerId
     * @param int $adminId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getConversation(int $lawyerId, int $adminId)
    {
        return AdminMessage::where(function ($query) use ($lawyerId, $adminId) {
            $query->where('lawyer_id', $lawyerId)
                  ->where('admin_id', $adminId);
        })
        ->orWhere(function ($query) use ($lawyerId, $adminId) {
            $query->where('lawyer_id', $adminId)
                  ->where('admin_id', $lawyerId);
        })
        ->with(['lawyer:id,name,email', 'admin:id,name,email'])
        ->orderBy('created_at', 'asc')
        ->get();
    }

    /**
     * Mark messages as read
     * 
     * @param int $lawyerId
     * @param int $adminId
     * @return int
     */
    public static function markAsRead(int $lawyerId, int $adminId): int
    {
        return AdminMessage::where('lawyer_id', $lawyerId)
            ->where('admin_id', $adminId)
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
        // Get messages where user is the recipient
        $unreadAdmin = AdminMessage::where('admin_id', $userId)
            ->where('is_read', false)
            ->count();

        $unreadLawyer = AdminMessage::where('lawyer_id', $userId)
            ->where('is_read', false)
            ->count();

        return $unreadAdmin + $unreadLawyer;
    }

    /**
     * Get all conversations for admin
     * 
     * @param int $adminId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAdminConversations(int $adminId)
    {
        return AdminMessage::where('admin_id', $adminId)
            ->with(['lawyer:id,name,email'])
            ->distinct()
            ->get()
            ->groupBy('lawyer_id')
            ->map(function ($messages, $lawyerId) {
                $lastMessage = $messages->last();
                return [
                    'lawyer_id' => $lawyerId,
                    'lawyer_name' => $lastMessage->lawyer->name,
                    'lawyer_email' => $lastMessage->lawyer->email,
                    'last_message' => $lastMessage->body,
                    'last_message_at' => $lastMessage->created_at,
                    'unread_count' => $messages->where('is_read', false)->count(),
                ];
            });
    }

    /**
     * Get all conversations for lawyer
     * 
     * @param int $lawyerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLawyerConversations(int $lawyerId)
    {
        return AdminMessage::where('lawyer_id', $lawyerId)
            ->with(['admin:id,name,email'])
            ->distinct()
            ->get()
            ->groupBy('admin_id')
            ->map(function ($messages, $adminId) {
                $lastMessage = $messages->last();
                return [
                    'admin_id' => $adminId,
                    'admin_name' => $lastMessage->admin->name,
                    'admin_email' => $lastMessage->admin->email,
                    'last_message' => $lastMessage->body,
                    'last_message_at' => $lastMessage->created_at,
                    'unread_count' => $messages->where('is_read', false)->count(),
                ];
            });
    }

    /**
     * Get messages by category
     * 
     * @param int $lawyerId
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByCategory(int $lawyerId, string $category)
    {
        return AdminMessage::where('lawyer_id', $lawyerId)
            ->where('category', $category)
            ->with(['lawyer:id,name', 'admin:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get high priority unresolved messages
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHighPriorityMessages(int $userId)
    {
        return AdminMessage::where(function ($query) use ($userId) {
            $query->where('admin_id', $userId)
                  ->orWhere('lawyer_id', $userId);
        })
        ->whereIn('priority', ['high', 'urgent'])
        ->where('is_read', false)
        ->with(['lawyer:id,name', 'admin:id,name'])
        ->orderBy('priority')
        ->orderBy('created_at', 'desc')
        ->get();
    }
}
