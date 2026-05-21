<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LawyerMessagingController extends Controller
{
    public function sendToAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'category' => 'sometimes|in:general,error_report,concern,billing_issue,appointment_issue,other',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $message = AdminMessage::create([
            'lawyer_id' => auth()->id(),
            'subject' => $request->subject,
            'body' => $request->body,
            'category' => $request->category ?? 'general',
            'priority' => $request->priority ?? 'medium',
        ]);

        return response()->json([
            'message' => 'Message sent to admin successfully.',
            'data' => $message,
        ], 201);
    }

    public function myMessages(Request $request)
    {
        $lawyer = auth()->user();

        $messages = AdminMessage::where('lawyer_id', $lawyer->id)
            ->latest()
            ->paginate(15);

        $unresolvedCount = AdminMessage::where('lawyer_id', $lawyer->id)
            ->unresolved()
            ->count();

        return view('lawyer.admin-messages', compact('messages', 'unresolvedCount'));
    }

    public function show(AdminMessage $message)
    {
        if ($message->lawyer_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($message->load('admin:id,name,email'));
    }
}
