<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\LegalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with('case')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        $weekStart = now()->startOfWeek();
        $weekDays  = collect(range(0, 6))->map(function ($i) use ($weekStart, $schedules) {
            $date = $weekStart->copy()->addDays($i);
            return [
                'date'  => $date,
                'count' => $schedules->filter(fn($s) =>
                    Carbon::parse($s->scheduled_at)->isSameDay($date)
                )->count(),
            ];
        });

        $cases = LegalCase::whereNull('deleted_at')
            ->orderBy('title')
            ->get(['id', 'title', 'case_number']);

        return view('admin.calendar', compact('schedules', 'weekDays', 'cases'));
    }

    public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'title'        => 'required|string|max:255',
        'type'         => 'required|in:court_hearing,meeting,deadline,deposition,appointment,other',
        'case_id'      => 'nullable|exists:cases,id',
        'scheduled_at' => 'required|date|after:now',
        'notes'        => 'nullable|string|max:1000',
    ]);

    $validated['created_by'] = auth()->id(); // 👈 PUT IT HERE

    Schedule::create($validated);

    return redirect()
        ->route('admin.calendar')
        ->with('success', 'Event created successfully.');
}
}