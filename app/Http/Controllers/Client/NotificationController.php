<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\LegalCase;
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

        return redirect()->route('client.dashboard');
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

        return redirect()->route('client.dashboard');
    }

    protected function notificationQuery()
    {
        $user = auth()->user();

        $invoiceIds = $user->invoices()->pluck('id')->all();
        $appointmentIds = $user->appointments()->pluck('id')->all();
        $caseIds = $user->clientCases()->pluck('id')->all();

        if (empty($invoiceIds) && empty($appointmentIds) && empty($caseIds)) {
            return AuditLog::whereRaw('0 = 1');
        }

        return AuditLog::where(function ($query) {
            $query->where('description', 'like', '%Invoice #% created%')
                ->orWhere('description', 'like', '%appointment%')
                ->orWhere('description', 'like', '%New message%')
                ->orWhere('description', 'like', '%status changed%');
        })->where(function ($query) use ($invoiceIds, $appointmentIds, $caseIds) {
            if (!empty($invoiceIds)) {
                $query->orWhere(function ($sub) use ($invoiceIds) {
                    $sub->where('model_type', Invoice::class)
                        ->whereIn('model_id', $invoiceIds);
                });
            }

            if (!empty($appointmentIds)) {
                $query->orWhere(function ($sub) use ($appointmentIds) {
                    $sub->where('model_type', Appointment::class)
                        ->whereIn('model_id', $appointmentIds);
                });
            }

            if (!empty($caseIds)) {
                $query->orWhere(function ($sub) use ($caseIds) {
                    $sub->where('model_type', LegalCase::class)
                        ->whereIn('model_id', $caseIds);
                });
            }
        });
    }
}
