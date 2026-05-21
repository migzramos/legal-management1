<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->notificationQuery();

        $notifications = $query->latest()->take(10)->get();
        $unreadCount = $query->where(function ($query) {
            $query->where('is_read', false)
                  ->orWhereNull('is_read');
        })->count();

        $payload = [
            'notifications' => $notifications->map(function (AuditLog $log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'time_ago' => $log->created_at->diffForHumans(),
                    'is_read' => $log->is_read,
                ];
            }),
            'unread_count' => $unreadCount,
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return redirect()->route('admin.reports.audit-logs.index');
    }

    public function markRead(Request $request)
    {
        $this->notificationQuery()
            ->where(function ($query) {
                $query->where('is_read', false)
                      ->orWhereNull('is_read');
            })
            ->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.reports.audit-logs.index');
    }

    protected function notificationQuery()
    {
        return AuditLog::where(function ($query) {
            $query->where('description', 'like', '%New user%')
                ->orWhere('description', 'like', '%New case%')
                ->orWhere('description', 'like', '%overdue%')
                ->orWhere('description', 'like', '%Payment received%');
        });
    }
}
