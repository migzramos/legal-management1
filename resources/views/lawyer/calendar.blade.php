@extends('layouts.lawyer')

@section('title', 'Calendar')

@section('content')

<style>
    .page-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        margin-bottom: 28px;
    }
    .page-header h1 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem; font-weight: 600; margin-bottom: 4px;
        color: var(--text-primary);
    }
    .page-header p { color: var(--text-muted); font-size: 0.9rem; }

    /* Toggle */
    .view-toggle {
        display: inline-flex;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 4px;
        gap: 4px;
        margin-bottom: 24px;
    }
    .toggle-btn {
        padding: 8px 20px;
        border-radius: 9px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 7px;
    }
    .toggle-btn.active {
        background: var(--purple-core);
        color: #fff;
        box-shadow: 0 2px 8px rgba(139,92,246,0.35);
    }
    .toggle-btn:not(.active):hover {
        color: var(--text-primary);
        background: rgba(255,255,255,0.04);
    }

    /* Buttons */
    .btn {
        padding: 9px 18px; border-radius: 10px;
        font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 500;
        cursor: pointer; transition: all 0.2s;
        text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
        border: none;
    }
    .btn-primary { background: var(--purple-core); color: #fff; box-shadow: 0 4px 14px rgba(139,92,246,0.3); }
    .btn-primary:hover { opacity: 0.88; }
    .btn-nav {
        background: var(--bg-card); border: 1px solid var(--border);
        color: var(--text-primary); padding: 8px 16px;
        border-radius: 10px; font-size: 0.82rem; font-weight: 500;
        text-decoration: none; transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-nav:hover { border-color: var(--purple-core); color: var(--purple-core); }
    .btn-today {
        background: rgba(139,92,246,0.1);
        border: 1px solid rgba(139,92,246,0.25);
        color: var(--purple-core);
    }
    .btn-today:hover { background: rgba(139,92,246,0.18); }

    /* Alert */
    .alert-success {
        background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25);
        color: #34d399; border-radius: 12px; padding: 12px 16px;
        font-size: 0.875rem; margin-bottom: 20px;
    }

    /* ── Week View ── */
    .week-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
    }
    .week-nav {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }
    .week-nav h2 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem; font-weight: 600;
        color: var(--text-primary); margin: 0;
    }
    .nav-controls { display: flex; gap: 8px; align-items: center; }

    .week-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }
    .week-day-header {
        padding: 14px 8px 10px;
        text-align: center;
        border-right: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }
    .week-day-header:last-child { border-right: none; }
    .wdh-name {
        font-size: 0.72rem; font-weight: 600; letter-spacing: 0.07em;
        text-transform: uppercase; color: var(--text-muted);
        margin-bottom: 6px;
    }
    .wdh-num {
        font-size: 1.3rem; font-weight: 700; color: var(--text-primary);
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto;
    }
    .wdh-num.today {
        background: var(--purple-core);
        color: #fff;
        box-shadow: 0 2px 8px rgba(139,92,246,0.4);
    }

    .week-day-col {
        min-height: 200px;
        padding: 10px 8px;
        border-right: 1px solid var(--border);
        display: flex; flex-direction: column; gap: 6px;
        vertical-align: top;
    }
    .week-day-col:last-child { border-right: none; }
    .week-day-col.today-col {
        background: rgba(139,92,246,0.04);
    }

    .cal-event {
        border-radius: 8px; padding: 7px 9px;
        cursor: pointer; transition: opacity 0.15s;
        font-size: 0.78rem;
    }
    .cal-event:hover { opacity: 0.82; }
    .cal-event.schedule {
        background: rgba(99,102,241,0.14);
        border-left: 3px solid #6366f1;
        color: #a5b4fc;
    }
    .cal-event.appointment {
        background: rgba(16,185,129,0.13);
        border-left: 3px solid #10b981;
        color: #6ee7b7;
    }
    .cal-event .ev-time {
        font-weight: 700; font-size: 0.7rem;
        opacity: 0.8; margin-bottom: 2px;
    }
    .cal-event .ev-title { font-weight: 500; line-height: 1.3; }
    .cal-event .ev-cost {
        font-size: 0.68rem; opacity: 0.75; margin-top: 3px;
    }
    .no-events {
        color: var(--text-muted); font-size: 0.78rem;
        text-align: center; padding-top: 12px; opacity: 0.5;
    }

    /* ── Month View ── */
    .month-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
    }
    .month-nav {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }
    .month-nav h2 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem; font-weight: 600;
        color: var(--text-primary); margin: 0;
    }

    .month-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }
    .month-day-label {
        padding: 12px 8px;
        text-align: center;
        font-size: 0.7rem; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        border-right: 1px solid var(--border);
    }
    .month-day-label:last-child { border-right: none; }

    .month-cell {
        min-height: 110px; padding: 8px;
        border-right: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        display: flex; flex-direction: column; gap: 4px;
    }
    .month-cell:nth-child(7n) { border-right: none; }
    .month-cell.muted { background: rgba(255,255,255,0.01); }
    .month-cell.muted .mcell-num { color: var(--text-muted); opacity: 0.35; }
    .month-cell.today-cell { background: rgba(139,92,246,0.05); }

    .mcell-num {
        font-size: 0.85rem; font-weight: 700;
        color: var(--text-primary); margin-bottom: 4px;
        width: 26px; height: 26px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .mcell-num.today-num {
        background: var(--purple-core); color: #fff;
        box-shadow: 0 2px 6px rgba(139,92,246,0.4);
    }

    .event-pill {
        display: block;
        padding: 3px 8px; border-radius: 5px;
        font-size: 0.68rem; font-weight: 500;
        color: #fff; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
        line-height: 1.5;
    }
    .more-label {
        font-size: 0.68rem; color: var(--purple-core);
        font-weight: 600; padding: 0 4px;
        margin-top: auto;
    }

    .hidden { display: none !important; }
</style>

<div class="page-header">
    <div>
        <h1>Calendar</h1>
        <p>Weekly schedules and upcoming events</p>
    </div>
    <a href="{{ route('lawyer.schedules.create') }}" class="btn btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Add Event
    </a>
</div>

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

<div class="view-toggle">
    <button id="list-view-btn" class="toggle-btn active">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        Week View
    </button>
    <button id="month-view-btn" class="toggle-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Month View
    </button>
</div>

{{-- ── WEEK VIEW ── --}}
<div id="list-view">
    <div class="week-card">
        <div class="week-nav">
            <h2>{{ $weekStart->format('F j') }} – {{ $weekEnd->format('F j, Y') }}</h2>
            <div class="nav-controls">
                <a href="{{ route('lawyer.calendar.index', ['date' => $currentDate->copy()->subWeek()->toDateString()]) }}" class="btn-nav">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
                    Prev
                </a>
                <a href="{{ route('lawyer.calendar.index', ['date' => now()->toDateString()]) }}" class="btn-nav btn-today">Today</a>
                <a href="{{ route('lawyer.calendar.index', ['date' => $currentDate->copy()->addWeek()->toDateString()]) }}" class="btn-nav">
                    Next
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>
        </div>

        {{-- Day Headers --}}
        <div class="week-grid">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $dayName)
            @php $day = $weekStart->copy()->addDays($i); @endphp
            <div class="week-day-header">
                <div class="wdh-name">{{ $dayName }}</div>
                <div class="wdh-num {{ $day->isToday() ? 'today' : '' }}">{{ $day->format('d') }}</div>
            </div>
            @endforeach
        </div>

        {{-- Day Columns --}}
        <div class="week-grid">
            @for($i = 0; $i < 7; $i++)
            @php
                $day = $weekStart->copy()->addDays($i);
                $daySchedules    = $schedules->filter(fn($s) => $s->scheduled_at >= $day->copy()->startOfDay() && $s->scheduled_at <= $day->copy()->endOfDay());
                $dayAppointments = $appointments->filter(fn($a) => $a->appointment_at >= $day->copy()->startOfDay() && $a->appointment_at <= $day->copy()->endOfDay());
                $hasEvents = $daySchedules->count() + $dayAppointments->count() > 0;
            @endphp
            <div class="week-day-col {{ $day->isToday() ? 'today-col' : '' }}">
                @foreach($daySchedules as $schedule)
                <div class="cal-event schedule"
                    onclick="alert('{{ addslashes($schedule->title) }}\n{{ $schedule->scheduled_at->format('g:i A') }}')"
                    title="{{ $schedule->title }}">
                    <div class="ev-time">{{ $schedule->scheduled_at->format('g:i A') }}</div>
                    <div class="ev-title">{{ Str::limit($schedule->title, 22) }}</div>
                </div>
                @endforeach

                @foreach($dayAppointments as $appointment)
                <div class="cal-event appointment"
                    onclick="window.location.href='{{ route('lawyer.appointments.show', $appointment->id) }}'"
                    title="{{ $appointment->purpose ?? 'Appointment' }} — {{ $appointment->client->name }}">
                    <div class="ev-time">{{ $appointment->appointment_at->format('g:i A') }}</div>
                    <div class="ev-title">{{ Str::limit($appointment->purpose ?? 'Appointment', 20) }}</div>
                    <div class="ev-cost">{{ money_display($appointment->hourly_rate * ($appointment->duration_minutes / 60)) }}</div>
                </div>
                @endforeach

                @if(!$hasEvents)
                    <div class="no-events">—</div>
                @endif
            </div>
            @endfor
        </div>
    </div>

    {{-- Legend --}}
    <div style="display:flex; gap:20px; margin-top:14px; padding-left:4px;">
        <div style="display:flex; align-items:center; gap:7px; font-size:0.78rem; color:var(--text-muted);">
            <span style="width:12px; height:12px; border-radius:3px; background:rgba(99,102,241,0.4); border-left:3px solid #6366f1; display:inline-block;"></span>
            Schedule / Event
        </div>
        <div style="display:flex; align-items:center; gap:7px; font-size:0.78rem; color:var(--text-muted);">
            <span style="width:12px; height:12px; border-radius:3px; background:rgba(16,185,129,0.4); border-left:3px solid #10b981; display:inline-block;"></span>
            Appointment
        </div>
    </div>
</div>

{{-- ── MONTH VIEW ── --}}
<div id="month-view" class="hidden">
    <div class="month-card">
        <div class="month-nav">
            <h2>{{ $monthStart->format('F Y') }}</h2>
            <div class="nav-controls">
                <a href="{{ route('lawyer.calendar.index', ['month' => $monthStart->copy()->subMonth()->format('Y-m')]) }}" class="btn-nav">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
                    Prev
                </a>
                <a href="{{ route('lawyer.calendar.index', ['month' => now()->format('Y-m')]) }}" class="btn-nav btn-today">Today</a>
                <a href="{{ route('lawyer.calendar.index', ['month' => $monthStart->copy()->addMonth()->format('Y-m')]) }}" class="btn-nav">
                    Next
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>
        </div>

        <div class="month-grid">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
            <div class="month-day-label">{{ $dayName }}</div>
            @endforeach

            @foreach($monthDays as $day)
            @php $dayEvents = $monthEventsByDate->get($day->toDateString(), collect()); @endphp
            <div class="month-cell
                {{ $day->month !== $monthStart->month ? 'muted' : '' }}
                {{ $day->isToday() ? 'today-cell' : '' }}">
                <div class="mcell-num {{ $day->isToday() ? 'today-num' : '' }}">
                    {{ $day->format('j') }}
                </div>
                @foreach($dayEvents->take(3) as $event)
                    <span class="event-pill" style="background: {{ $event['color'] }};">
                        {{ Str::limit($event['title'], 18) }}
                    </span>
                @endforeach
                @if($dayEvents->count() > 3)
                    <div class="more-label">+{{ $dayEvents->count() - 3 }} more</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const listBtn  = document.getElementById('list-view-btn');
    const monthBtn = document.getElementById('month-view-btn');
    const listView  = document.getElementById('list-view');
    const monthView = document.getElementById('month-view');

    function setView(view) {
        const isList = view === 'list';
        listView.classList.toggle('hidden', !isList);
        monthView.classList.toggle('hidden', isList);
        listBtn.classList.toggle('active', isList);
        monthBtn.classList.toggle('active', !isList);
        localStorage.setItem('calendarView', view);
    }

    listBtn.addEventListener('click',  () => setView('list'));
    monthBtn.addEventListener('click', () => setView('month'));

    // Restore last used view
    const saved = localStorage.getItem('calendarView');
    if (saved === 'month') setView('month');
});
</script>

@endsection