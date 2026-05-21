<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\LawyerAvailability;
use App\Models\LegalCase;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(LegalCase $case): JsonResponse
    {
        $this->authorize('view', $case);

        $schedules = Schedule::where('case_id', $case->id)
            ->orderBy('scheduled_at')
            ->get();

        return response()->json($schedules);
    }

    public function create(): \Illuminate\View\View
    {
        $cases = LegalCase::where('lawyer_id', auth()->id())
            ->orderBy('title')
            ->get();

        return view('lawyer.schedule-create', compact('cases'));
    }

    public function store(StoreScheduleRequest $request)
    {
        $case = LegalCase::findOrFail($request->case_id);
        $this->authorize('view', $case);

        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);

        $conflict = Schedule::whereHas('case', function ($query) use ($case) {
                $query->where('lawyer_id', $case->lawyer_id);
            })
            ->where(function($q) use ($scheduledAt) {
                $q->where('scheduled_at', '<=', $scheduledAt)
                  ->where('scheduled_at', '>', $scheduledAt->copy()->subHour());
            })
            ->orWhere(function($q) use ($scheduledAt) {
                $q->where('scheduled_at', '<', $scheduledAt->copy()->addHour())
                  ->where('scheduled_at', '>=', $scheduledAt);
            })
            ->whereHas('case', function ($query) use ($case) {
                $query->where('lawyer_id', $case->lawyer_id);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'The selected lawyer has a conflicting schedule at this time. Please choose a different date or time.',
            ], 422);
        }

        $schedule = Schedule::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Schedule created successfully.',
                'schedule' => $schedule,
            ], 201);
        }

        return redirect()->route('lawyer.calendar.index', ['date' => now()->toDateString()])
            ->with('success', 'Schedule created successfully.');
    }

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule->case);

        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'sometimes|in:court_hearing,deadline,appointment,meeting,other',
            'scheduled_at' => 'sometimes|date',
            'location'     => 'nullable|string|max:255',
            'status'       => 'sometimes|in:upcoming,completed,cancelled,postponed',
            'notes'        => 'nullable|string',
        ]);

        if (array_key_exists('scheduled_at', $data)) {
            $newScheduledAt = \Carbon\Carbon::parse($data['scheduled_at']);

            $conflict = Schedule::whereHas('case', function ($query) use ($schedule) {
                    $query->where('lawyer_id', $schedule->case->lawyer_id);
                })
                ->where('id', '!=', $schedule->id)
                ->where(function($q) use ($newScheduledAt) {
                    $q->where('scheduled_at', '<=', $newScheduledAt)
                      ->where('scheduled_at', '>', $newScheduledAt->copy()->subHour());
                })
                ->orWhere(function($q) use ($newScheduledAt) {
                    $q->where('scheduled_at', '<', $newScheduledAt->copy()->addHour())
                      ->where('scheduled_at', '>=', $newScheduledAt);
                })
                ->whereHas('case', function ($query) use ($schedule) {
                    $query->where('lawyer_id', $schedule->case->lawyer_id);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'The requested time conflicts with an existing schedule for this lawyer.',
                ], 422);
            }
        }

        $schedule->update($data);

        return response()->json([
            'message'  => 'Schedule updated successfully.',
            'schedule' => $schedule->fresh(),
        ]);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule->case);
        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully.']);
    }

    // Lawyer Availability Methods
    public function availabilityIndex()
    {
        $availabilities = LawyerAvailability::where('lawyer_id', auth()->id())
            ->orderBy('available_date')
            ->orderBy('start_time')
            ->get();

        return view('lawyer.availability.index', compact('availabilities'));
    }

    public function storeAvailability(Request $request)
    {
        $request->validate([
            'available_date' => 'required|date|after:today',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
        ]);

        LawyerAvailability::create([
            'lawyer_id'      => auth()->id(),
            'available_date' => $request->available_date,
            'start_time'     => $request->start_time,
            'end_time'       => $request->end_time,
        ]);

        return redirect()->back()->with('success', 'Availability slot added successfully.');
    }

    public function destroyAvailability(LawyerAvailability $availability)
    {
        if ($availability->lawyer_id !== auth()->id()) {
            abort(403);
        }

        if ($availability->is_booked) {
            return redirect()->back()->with('error', 'Cannot delete a booked availability slot.');
        }

        $availability->delete();

        return redirect()->back()->with('success', 'Availability slot removed successfully.');
    }
}