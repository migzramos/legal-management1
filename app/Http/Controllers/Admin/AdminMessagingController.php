<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminMessagingController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->user();

        if (!$admin->isAdmin()) {
            abort(403, 'Only admins can access this feature.');
        }

        $messages = AdminMessage::with(['lawyer:id,name,email', 'admin:id,name'])
            ->latest()
            ->paginate(20);

        $unreadCount = AdminMessage::unread()->count();
        $unresolvedCount = AdminMessage::unresolved()->count();

        return view('admin.messaging', compact('messages', 'unreadCount', 'unresolvedCount'));
    }

    public function show(AdminMessage $message)
    {
        if (auth()->user()->isAdmin()) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }

        $message->load(['lawyer:id,name,email,phone', 'admin:id,name']);

        return response()->json($message);
    }

    public function reply(Request $request, AdminMessage $message): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'resolution_notes' => 'required|string|max:5000',
        ]);

        $message->update([
            'admin_id' => auth()->id(),
            'resolution_notes' => $request->resolution_notes,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Message resolved successfully.',
            'data' => $message,
        ]);
    }
}
