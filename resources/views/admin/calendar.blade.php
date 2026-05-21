{{-- resources/views/admin/calendar.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar — LegalCase</title>
    @include('admin.partials.styles')
    <style>
        .cal-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 960px) { .cal-layout { grid-template-columns: 1fr; } }
 
        /* Week panel */
        .week-card, .events-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }
        .week-header, .events-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .week-title, .events-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .week-subtitle, .events-count { font-size: 0.75rem; color: var(--text-muted); }
 
        .week-days { padding: 12px; display: flex; flex-direction: column; gap: 6px; }
        .day-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 11px 14px; border-radius: 10px;
            border: 1px solid var(--border); cursor: pointer;
            transition: border-color var(--transition), background var(--transition);
        }
        .day-row:hover { border-color: var(--border-hover); background: rgba(124,58,237,0.05); }
        .day-row.today { background: var(--warning); border-color: var(--warning); }
        .day-row.today .day-name, .day-row.today .day-num { color: #1a1200; }
        .day-name { font-size: 0.86rem; font-weight: 500; }
        .day-right { display: flex; align-items: center; gap: 10px; }
        .day-num  { font-size: 0.86rem; color: var(--text-muted); }
        .day-count {
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--purple-core);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.68rem; font-weight: 600; color: #fff;
        }
        .day-count.zero { background: transparent; }
 
        /* Events list */
        .events-list { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
        .event-item {
            padding: 15px 18px; background: rgba(255,255,255,0.02);
            border: 1px solid var(--border); border-radius: 12px;
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 16px;
            transition: border-color var(--transition), background var(--transition);
        }
        .event-item:hover { border-color: var(--border-hover); background: rgba(124,58,237,0.04); }
        .event-left { flex: 1; min-width: 0; }
        .event-right { flex-shrink: 0; text-align: right; }
        .event-name { font-size: 0.9rem; font-weight: 500; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .event-case { font-size: 0.75rem; color: var(--text-muted); }
        .event-date { font-size: 0.84rem; font-weight: 600; color: var(--warning); margin-bottom: 4px; }
        .event-time { font-size: 0.74rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px; justify-content: flex-end; }
        .event-time svg { width: 12px; height: 12px; }
 
        .event-type-badge { padding: 2px 8px; border-radius: 20px; font-size: 0.66rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .type-hearing    { background: rgba(248,113,113,0.12); color: var(--danger);       border: 1px solid rgba(248,113,113,0.22); }
        .type-meeting    { background: rgba(96,165,250,0.12);  color: var(--info);         border: 1px solid rgba(96,165,250,0.22); }
        .type-deadline   { background: rgba(251,191,36,0.12);  color: var(--warning);      border: 1px solid rgba(251,191,36,0.22); }
        .type-deposition { background: rgba(168,85,247,0.12);  color: var(--purple-light); border: 1px solid rgba(168,85,247,0.22); }
        .type-other      { background: rgba(52,211,153,0.12);  color: var(--success);      border: 1px solid rgba(52,211,153,0.22); }
 
        /* Empty state */
        .cal-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 52px 24px; color: var(--text-muted); text-align: center; }
        .cal-empty-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(124,58,237,0.1); display: flex; align-items: center; justify-content: center; }
        .cal-empty-icon svg { width: 22px; height: 22px; color: var(--purple-light); opacity: 0.6; }
        .cal-empty-title { font-family: 'Cormorant Garamond', serif; font-size: 1.05rem; color: var(--text-secondary); }
        .cal-empty-text  { font-size: 0.8rem; line-height: 1.6; }
 
        /* Modal */
        .modal-backdrop {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
            display: none; align-items: center; justify-content: center; padding: 24px;
        }
        .modal-backdrop.open { display: flex; }
        .modal-box {
            width: 100%; max-width: 500px;
            background: linear-gradient(160deg, #16122e, #0f0c24);
            border: 1px solid rgba(124,58,237,0.25);
            border-radius: 20px; padding: 28px 32px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6);
            animation: modalIn 0.25s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 22px; }
        .modal-title { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; color: var(--text-primary); }
        .modal-close-btn {
            background: rgba(255,255,255,0.06); border: 1px solid var(--border);
            border-radius: 8px; width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text-muted);
            transition: background 0.2s, color 0.2s;
            flex-shrink: 0;
        }
        .modal-close-btn:hover { background: rgba(255,255,255,0.12); color: var(--text-primary); }
        .modal-close-btn svg { width: 16px; height: 16px; }
 
        .modal-field { margin-bottom: 16px; }
        .modal-label { display: block; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 7px; }
        .modal-input, .modal-select {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border); border-radius: 10px;
            color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .modal-input:focus, .modal-select:focus { border-color: var(--purple-core); box-shadow: 0 0 0 3px rgba(124,58,237,0.16); }
        .modal-select option { background: #1a1530; }
        .modal-textarea { resize: vertical; min-height: 80px; }
 
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
 
        .alert-success {
            padding: 11px 16px; border-radius: 10px; font-size: 0.84rem;
            background: rgba(52,211,153,0.08); border: 1px solid rgba(52,211,153,0.22);
            color: var(--success); margin-bottom: 18px;
        }
        .alert-error {
            padding: 11px 16px; border-radius: 10px; font-size: 0.84rem;
            background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.22);
            color: var(--danger); margin-bottom: 18px;
        }
        .alert-error ul { padding-left: 16px; margin: 0; }
    </style>
</head>
<body>
<div class="app-layout">
    @include('admin.partials.sidebar')
 
    <div class="main-area">
        <div class="topbar">
            <div>
                <div class="topbar-title">Calendar</div>
                <div class="topbar-subtitle">{{ now()->format('F Y') }}</div>
            </div>
            <div class="topbar-right">
                <button class="btn-primary" onclick="document.getElementById('newEventModal').classList.add('open')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    New Event
                </button>
            </div>
        </div>
 
        <div class="page-content">
 
            @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
            @endif
 
            @if($errors->any())
            <div class="alert-error">
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
 
            <div class="cal-layout">
 
                {{-- Week panel --}}
                <div class="week-card">
                    <div class="week-header">
                        <div>
                            <div class="week-title">This Week</div>
                            <div class="week-subtitle">{{ now()->startOfWeek()->format('M d') }} — {{ now()->endOfWeek()->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="week-days">
                        @foreach($weekDays as $day)
                        <div class="day-row {{ $day['date']->isToday() ? 'today' : '' }}">
                            <span class="day-name">{{ $day['date']->format('l') }}</span>
                            <div class="day-right">
                                <span class="day-num">{{ $day['date']->format('j') }}</span>
                                @if($day['count'] > 0)
                                    <span class="day-count">{{ $day['count'] }}</span>
                                @else
                                    <span class="day-count zero"></span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
 
                {{-- Events panel --}}
                <div class="events-card">
                    <div class="events-header">
                        <span class="events-title">Upcoming Events</span>
                        <span class="events-count">{{ $schedules->count() }} {{ Str::plural('event', $schedules->count()) }}</span>
                    </div>
 
                    @if($schedules->isEmpty())
                    <div class="cal-empty">
                        <div class="cal-empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div class="cal-empty-title">No Upcoming Events</div>
                        <div class="cal-empty-text">Click "New Event" to schedule one.</div>
                    </div>
                    @else
                    <div class="events-list">
                        @foreach($schedules as $schedule)
                        @php
                            $typeClass = match($schedule->type) {
                                'court_hearing' => 'type-hearing',
                                'meeting'       => 'type-meeting',
                                'deadline'      => 'type-deadline',
                                'deposition'    => 'type-deposition',
                                default         => 'type-other',
                            };
                            $typeLabel = match($schedule->type) {
                                'court_hearing' => 'Hearing',
                                'meeting'       => 'Meeting',
                                'deadline'      => 'Deadline',
                                'deposition'    => 'Deposition',
                                'appointment'   => 'Appointment',
                                default         => ucfirst(str_replace('_', ' ', $schedule->type)),
                            };
                        @endphp
                        <div class="event-item">
                            <div class="event-left">
                                <div class="event-name">
                                    {{ $schedule->title }}
                                    <span class="event-type-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                </div>
                                <div class="event-case">{{ $schedule->case->case_number ?? 'No case' }}</div>
                            </div>
                            <div class="event-right">
                                <div class="event-date">{{ $schedule->scheduled_at->format('M j') }}</div>
                                <div class="event-time">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ $schedule->scheduled_at->format('g:i A') }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
 
            </div>
        </div>
    </div>
</div>
 
{{-- ── New Event Modal ── --}}
<div class="modal-backdrop" id="newEventModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">New Event</div>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('newEventModal').classList.remove('open')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
 
        <form method="POST" action="{{ route('admin.calendar.store') }}">
            @csrf
 
            <div class="modal-field">
                <label class="modal-label" for="ev_title">Event Title *</label>
                <input class="modal-input" type="text" id="ev_title" name="title"
                       value="{{ old('title') }}" placeholder="e.g. Court hearing for Cruz vs. Santos" required>
            </div>
 
            <div class="modal-field">
                <label class="modal-label" for="ev_type">Event Type *</label>
                <select class="modal-select" id="ev_type" name="type" required>
                    <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select type…</option>
                    <option value="court_hearing" {{ old('type') === 'court_hearing' ? 'selected' : '' }}>Court Hearing</option>
                    <option value="meeting"       {{ old('type') === 'meeting'       ? 'selected' : '' }}>Meeting</option>
                    <option value="deadline"      {{ old('type') === 'deadline'      ? 'selected' : '' }}>Deadline</option>
                    <option value="deposition"    {{ old('type') === 'deposition'    ? 'selected' : '' }}>Deposition</option>
                    <option value="appointment"   {{ old('type') === 'appointment'   ? 'selected' : '' }}>Appointment</option>
                    <option value="other"         {{ old('type') === 'other'         ? 'selected' : '' }}>Other</option>
                </select>
            </div>
 
            <div class="modal-field">
                <label class="modal-label" for="ev_case">Related Case (optional)</label>
                <select class="modal-select" id="ev_case" name="case_id">
                    <option value="">— No case —</option>
                    @foreach($cases as $case)
                    <option value="{{ $case->id }}" {{ old('case_id') == $case->id ? 'selected' : '' }}>
                        {{ $case->case_number }} — {{ $case->title }}
                    </option>
                    @endforeach
                </select>
            </div>
 
            <div class="modal-field">
                <label class="modal-label" for="ev_date">Date & Time *</label>
                <input class="modal-input" type="datetime-local" id="ev_date" name="scheduled_at"
                       value="{{ old('scheduled_at') }}" required>
            </div>
 
            <div class="modal-field">
                <label class="modal-label" for="ev_notes">Notes (optional)</label>
                <textarea class="modal-input modal-textarea" id="ev_notes" name="notes"
                          placeholder="Additional details…">{{ old('notes') }}</textarea>
            </div>
 
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="document.getElementById('newEventModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn-primary">Save Event</button>
            </div>
        </form>
    </div>
</div>
 
<script>
    {{-- Re-open modal if validation failed --}}
    @if($errors->any())
    document.getElementById('newEventModal').classList.add('open');
    @endif
 
    {{-- Close on backdrop click --}}
    document.getElementById('newEventModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
</script>
</body>
</html>