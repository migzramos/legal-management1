<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    /**
     * Get all conversations with lawyers
     */
    public function index()
    {
        $admin = auth()->user();
        
        $conversations = AdminMessage::where('admin_id', $admin->id)
            ->with('lawyer', 'admin')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $totalUnread = AdminMessage::where('admin_id', $admin->id)
            ->where('is_read', false)
            ->count();

        $lawyers = User::where('role', 'lawyer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.messages', [
            'conversations' => $conversations,
            'totalUnread' => $totalUnread,
            'lawyers' => $lawyers,
        ]);
    }

    /**
     * Get conversation with specific lawyer
     */
    public function getConversation(User $lawyer)
    {
        $this->authorize('adminMessage', $lawyer);

        $admin = auth()->user();
        
        $messages = AdminMessage::where('lawyer_id', $lawyer->id)
            ->where(function ($q) use ($admin) {
                $q->where('admin_id', $admin->id)
                    ->orWhereNull('admin_id');
            })
            ->orderBy('created_at', 'asc')
            ->with('lawyer', 'admin')
            ->paginate(30);

        // Mark as read
        AdminMessage::where('lawyer_id', $lawyer->id)
            ->where('admin_id', null)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now(), 'admin_id' => $admin->id]);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send message to lawyer
     */
    public function send(User $lawyer, Request $request)
    {
        $this->authorize('adminMessage', $lawyer);

        $request->validate([
            'body' => 'required|string|min:1|max:5000',
        ]);

        $admin = auth()->user();

        $message = AdminMessage::create([
            'lawyer_id' => $lawyer->id,
            'admin_id' => $admin->id,
            'body' => $request->input('body'),
            'category' => 'general',
            'priority' => 'medium',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('lawyer', 'admin'),
        ], 201);
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(User $lawyer)
    {
        $this->authorize('adminMessage', $lawyer);

        $admin = auth()->user();

        $count = AdminMessage::where('lawyer_id', $lawyer->id)
            ->where('admin_id', null)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now(), 'admin_id' => $admin->id]);

        return response()->json([
            'success' => true,
            'marked_count' => $count,
        ]);
    }

    /**
     * Delete a message
     */
    public function delete(AdminMessage $message)
    {
        if ($message->admin_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only sender can delete.',
            ], 403);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }
}
