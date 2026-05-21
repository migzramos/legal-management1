<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->query('month');

        if ($selectedMonth) {
            $currentDate = Carbon::parse(sprintf('%s-01', $selectedMonth));
        } else {
            $currentDate = Carbon::parse($request->query('date', now()->toDateString()));
        }

        $weekStart = $currentDate->copy()->startOfWeek();
        $weekEnd = $currentDate->copy()->endOfWeek();

        $monthStart = $currentDate->copy()->startOfMonth();
        $monthEnd = $currentDate->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $schedules = Schedule::whereHas('case', function ($q) {
                $q->where('lawyer_id', auth()->id());
            })
            ->whereBetween('scheduled_at', [$calendarStart, $calendarEnd])
            ->orderBy('scheduled_at')
            ->get();

        $appointments = Appointment::where('lawyer_id', auth()->id())
            ->whereBetween('appointment_at', [$calendarStart, $calendarEnd])
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('client:id,name')
            ->orderBy('appointment_at')
            ->get();

        $eventColors = [
            'HEARING' => '#ef4444',
            'MEETING' => '#3b82f6',
            'DEPOSITION' => '#f97316',
            'DEADLINE' => '#f59e0b',
            'CONFERENCE' => '#10b981',
        ];

        $allEvents = collect()
            ->merge($schedules->map(function ($s) use ($eventColors) {
                $type = strtoupper($s->type ?? 'MEETING');
                $color = $eventColors[$type] ?? '#8b5cf6';

                return [
                    'type' => 'schedule',
                    'id' => $s->id,
                    'title' => $s->title,
                    'start' => $s->scheduled_at->toIso8601String(),
                    'end' => $s->scheduled_at->copy()->addHours(1)->toIso8601String(),
                    'description' => $s->description,
                    'status' => $s->status,
                    'event_type' => $type,
                    'color' => $color,
                ];
            }))
            ->merge($appointments->map(function ($a) use ($eventColors) {
                return [
                    'type' => 'appointment',
                    'id' => $a->id,
                    'title' => 'Appointment: ' . ($a->purpose ?? 'Client Meeting'),
                    'start' => $a->appointment_at->toIso8601String(),
                    'end' => $a->appointment_at->copy()->addMinutes($a->duration_minutes)->toIso8601String(),
                    'client' => $a->client->name,
                    'status' => $a->status,
                    'event_type' => 'MEETING',
                    'color' => $eventColors['MEETING'],
                ];
            }));

        $monthEventsByDate = $allEvents->groupBy(function ($event) {
            return Carbon::parse($event['start'])->toDateString();
        });

        $monthDays = [];
        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $monthDays[] = $date->copy();
        }

        return view('lawyer.calendar', compact(
            'schedules',
            'appointments',
            'currentDate',
            'weekStart',
            'weekEnd',
            'allEvents',
            'monthStart',
            'monthEnd',
            'calendarStart',
            'calendarEnd',
            'monthDays',
            'monthEventsByDate',
            'selectedMonth'
        ));
    }
}
