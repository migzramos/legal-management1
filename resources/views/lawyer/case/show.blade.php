@extends('layouts.lawyer')
 
@section('title', $case->title)
@section('page_title', $case->title)
@section('page_subtitle', $case->client->name . ' · ' . $case->case_number)
 
@section('topbar_actions')
<a href="{{ route('lawyer.cases.index') }}" class="btn-secondary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back
</a>
<a href="{{ route('lawyer.cases.edit', $case) }}" class="btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    Edit Case
</a>
@endsection
 
@section('content')
 
@php
    $progressSteps = $case->getProgressSteps();
    $statusOrder   = array_keys($progressSteps);
    $currentIndex  = array_search($case->status, $statusOrder);
    $nextStatus    = $statusOrder[$currentIndex + 1] ?? null;
    $statusLabels  = [
        'intake'               => 'Intake',
        'barangay_mediation'   => 'Barangay Mediation',
        'escalation_to_court'  => 'Escalation to Court',
        'active_case'          => 'Active Case',
        'resolution'           => 'Resolution',
    ];
 
    $appointments = \App\Models\Appointment::where('client_id', $case->client_id)
        ->where('lawyer_id', $case->lawyer_id)
        ->orderBy('appointment_at', 'desc')
        ->get();
 
    $st = strtolower(str_replace('_', ' ', $case->status));
    $sc = match($st) {
        'active case', 'active' => 'badge-active',
        'resolution'            => 'badge-completed',
        'closed'                => 'badge-closed',
        default                 => 'badge-pending',
    };
@endphp
 
{{-- ── STATUS STEPPER ── --}}
<div class="sc-stepper-card">
    <div class="sc-stepper-inner">
        <div class="sc-steps">
            @foreach($progressSteps as $key => $step)
            @php
                $si       = array_search($key, $statusOrder);
                $isActive = $key === $case->status;
                $isDone   = $step['completed'] && !$isActive;
            @endphp
            <div class="sc-step {{ $isDone ? 'sc-done' : '' }} {{ $isActive ? 'sc-current' : '' }}">
                <div class="sc-dot">
                    @if($isDone)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                    {{ $si + 1 }}
                    @endif
                </div>
                <div class="sc-step-label">{{ $step['label'] }}</div>
            </div>
            @if($si < count($statusOrder) - 1)
            <div class="sc-connector {{ ($step['completed'] && $currentIndex > $si) ? 'sc-connector-done' : '' }}"></div>
            @endif
            @endforeach
        </div>
 
        @if($nextStatus)
        <form method="POST" action="{{ route('lawyer.cases.status', [$case, $nextStatus]) }}" class="sc-advance-form">
            @csrf @method('PATCH')
            <button type="submit" class="btn-primary sc-advance-btn">
                Move to {{ $statusLabels[$nextStatus] ?? $nextStatus }} →
            </button>
        </form>
        @endif
    </div>
</div>
 
{{-- ── INFO STRIP ── --}}
<div class="sc-strip">
    <div class="sc-strip-item">
        <div class="sc-strip-label">Client</div>
        <div class="sc-strip-val">{{ $case->client->name }}</div>
    </div>
    <div class="sc-strip-item">
        <div class="sc-strip-label">Case Type</div>
        <div class="sc-strip-val">{{ $case->category->name ?? 'N/A' }}</div>
    </div>
    <div class="sc-strip-item">
        <div class="sc-strip-label">Status</div>
        <span class="badge {{ $sc }}">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
    </div>
    <div class="sc-strip-item">
        <div class="sc-strip-label">Next Hearing</div>
        <div class="sc-strip-val">{{ $case->next_hearing_date?->format('M d, Y') ?? 'Not scheduled' }}</div>
    </div>
    <div class="sc-strip-item">
        <div class="sc-strip-label">Court</div>
        <div class="sc-strip-val">{{ $case->courtType->name ?? 'N/A' }}</div>
    </div>
</div>
 
{{-- ── BODY: MAIN + SIDEBAR ── --}}
<div class="sc-body">
 
    {{-- Main column --}}
    <div class="sc-main">
 
        {{-- Case Details --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Case Details</span>
            </div>
            <div class="sc-fields">
                <div class="sc-field-row">
                    <span class="sc-field-key">Description</span>
                    <span class="sc-field-val">{{ $case->description ?: '—' }}</span>
                </div>
                <div class="sc-field-row">
                    <span class="sc-field-key">Filed Date</span>
                    <span class="sc-field-val">{{ $case->filed_date?->format('M d, Y') ?? 'N/A' }}</span>
                </div>
                <div class="sc-field-row">
                    <span class="sc-field-key">Court Type</span>
                    <span class="sc-field-val">{{ $case->courtType->name ?? 'N/A' }}</span>
                </div>
                <div class="sc-field-row">
                    <span class="sc-field-key">Court Name</span>
                    <span class="sc-field-val">{{ $case->court_name ?: '—' }}</span>
                </div>
                <div class="sc-field-row">
                    <span class="sc-field-key">Judge</span>
                    <span class="sc-field-val">{{ $case->judge_name ?: '—' }}</span>
                </div>
                <div class="sc-field-row">
                    <span class="sc-field-key">Opposing Party</span>
                    <span class="sc-field-val">{{ $case->opposing_party ?: '—' }}</span>
                </div>
                <div class="sc-field-row sc-field-last">
                    <span class="sc-field-key">Opposing Counsel</span>
                    <span class="sc-field-val">{{ $case->opposing_counsel ?: '—' }}</span>
                </div>
            </div>
        </div>
 
        {{-- Case Resources --}}
        <div class="card" style="margin-top: 14px;">
            <div class="card-header">
                <span class="card-title">Case Resources</span>
            </div>
            <div class="sc-resources">
                <a href="{{ route('lawyer.documents.index', $case) }}" class="sc-resource-row">
                    <div class="sc-res-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div class="sc-res-info">
                        <div class="sc-res-name">Documents</div>
                        <div class="sc-res-count">{{ $case->documents->count() }} files</div>
                    </div>
                    <svg class="sc-res-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
                <a href="{{ route('lawyer.tasks.index', $case) }}" class="sc-resource-row">
                    <div class="sc-res-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg>
                    </div>
                    <div class="sc-res-info">
                        <div class="sc-res-name">Tasks</div>
                        <div class="sc-res-count">{{ $case->tasks->count() }} items</div>
                    </div>
                    <svg class="sc-res-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
                <a href="{{ route('lawyer.appointments.index') }}" class="sc-resource-row">
                    <div class="sc-res-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="sc-res-info">
                        <div class="sc-res-name">Appointments</div>
                        <div class="sc-res-count">{{ $appointments->count() }} scheduled</div>
                    </div>
                    <svg class="sc-res-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
                <a href="{{ route('lawyer.schedules.index', $case) }}" class="sc-resource-row sc-resource-last">
                    <div class="sc-res-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="sc-res-info">
                        <div class="sc-res-name">Schedule</div>
                        <div class="sc-res-count">{{ $case->schedules->count() }} events</div>
                    </div>
                    <svg class="sc-res-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>
        </div>
 
        {{-- Recent Documents preview --}}
        @if($case->documents->count() > 0)
        <div class="card" style="margin-top: 14px;">
            <div class="card-header">
                <span class="card-title">Recent Documents</span>
                <a href="{{ route('lawyer.documents.index', $case) }}" class="card-action">View all →</a>
            </div>
            @foreach($case->documents->take(4) as $doc)
            <div class="sc-doc-row {{ $loop->last ? 'sc-doc-last' : '' }}">
                <div class="sc-doc-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="sc-doc-info">
                    <div class="sc-doc-title">{{ $doc->title }}</div>
                    <div class="sc-doc-meta">{{ $doc->created_at->diffForHumans() }} · {{ $doc->uploader->name }}</div>
                </div>
                <a href="{{ route('lawyer.documents.show', $doc) }}" class="btn-secondary" style="padding:5px 12px;font-size:0.74rem;">View</a>
            </div>
            @endforeach
        </div>
        @endif
 
    </div>
 
    {{-- Sidebar --}}
    <div class="sc-sidebar">
 
        {{-- Billing summary --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Billing</span>
                <a href="{{ route('lawyer.billing.index') }}" class="card-action">Manage →</a>
            </div>
            <div class="sc-bill-rows">
                <div class="sc-bill-row">
                    <span class="sc-bill-label">Total Billed</span>
                    <span class="sc-bill-val">{{ money_display($case->invoices->sum('total')) }}</span>
                </div>
                <div class="sc-bill-row">
                    <span class="sc-bill-label">Paid</span>
                    <span class="sc-bill-val sc-green">{{ money_display($case->invoices->sum('amount_paid')) }}</span>
                </div>
                <div class="sc-bill-row">
                    <span class="sc-bill-label">Balance</span>
                    <span class="sc-bill-val {{ $case->invoices->sum('balance') > 0 ? 'sc-red' : '' }}">{{ money_display($case->invoices->sum('balance')) }}</span>
                </div>
                <div class="sc-bill-row sc-bill-last">
                    <span class="sc-bill-label">Time Logged</span>
                    <span class="sc-bill-val">{{ $case->timeEntries->sum('hours') }}h</span>
                </div>
            </div>
        </div>
 
        {{-- Recent activity --}}
        <div class="card" style="margin-top: 14px;">
            <div class="card-header">
                <span class="card-title">Recent Activity</span>
            </div>
            @php
                $timeline = \App\Models\AuditLog::where('model_type', \App\Models\LegalCase::class)
                    ->where('model_id', $case->id)
                    ->orderBy('created_at', 'desc')->take(8)->get();
            @endphp
            @forelse($timeline as $event)
            <div class="sc-tl-row {{ $loop->last ? 'sc-tl-last' : '' }}">
                <div class="sc-tl-dot"></div>
                <div>
                    <div class="sc-tl-action">{{ ucfirst(str_replace('_', ' ', $event->action)) }}</div>
                    <div class="sc-tl-meta">{{ $event->user->name ?? 'System' }} · {{ $event->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
            @empty
            <div class="empty" style="padding: 24px 18px;">
                <h4>No activity yet</h4>
            </div>
            @endforelse
        </div>
 
        {{-- Upcoming appointments --}}
        @if($appointments->count() > 0)
        <div class="card" style="margin-top: 14px;">
            <div class="card-header">
                <span class="card-title">Appointments</span>
                <a href="{{ route('lawyer.appointments.index') }}" class="card-action">Manage →</a>
            </div>
            @foreach($appointments->take(4) as $appt)
            @php
                $asc = match($appt->status) {
                    'confirmed' => 'badge-confirmed',
                    'completed' => 'badge-completed',
                    'cancelled' => 'badge-cancelled',
                    default     => 'badge-pending',
                };
            @endphp
            <div class="sc-appt-row {{ $loop->last ? 'sc-appt-last' : '' }}">
                <div>
                    <div class="sc-appt-purpose">{{ $appt->purpose ?? 'Appointment' }}</div>
                    <div class="sc-appt-meta">{{ $appt->appointment_at->format('M d, Y · H:i') }}</div>
                </div>
                <span class="badge {{ $asc }}">{{ ucfirst($appt->status) }}</span>
            </div>
            @endforeach
        </div>
        @endif
 
    </div>
</div>
 
<style>
/* ── STEPPER CARD ── */
.sc-stepper-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 14px;
}
.sc-stepper-inner {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.sc-steps {
    display: flex;
    align-items: center;
    flex: 1;
    overflow-x: auto;
    padding-bottom: 2px;
    gap: 0;
}
.sc-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    min-width: 80px;
}
.sc-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--bg-2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--t3);
    flex-shrink: 0;
    position: relative;
    z-index: 2;
}
.sc-dot svg { width: 13px; height: 13px; }
.sc-done .sc-dot {
    background: var(--p);
    border-color: var(--p);
    color: #fff;
}
.sc-current .sc-dot {
    background: var(--p);
    border-color: var(--p);
    color: #fff;
    box-shadow: 0 0 0 5px var(--p-glow);
}
.sc-step-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--t3);
    text-align: center;
    line-height: 1.3;
}
.sc-current .sc-step-label,
.sc-done .sc-step-label { color: var(--p3); }
 
.sc-connector {
    flex: 1;
    height: 1px;
    background: var(--border);
    min-width: 16px;
    margin-bottom: 22px;
}
.sc-connector-done { background: var(--p); }
 
.sc-advance-btn {
    white-space: nowrap;
    flex-shrink: 0;
}
.sc-advance-form { flex-shrink: 0; }
 
/* ── INFO STRIP ── */
.sc-strip {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 14px;
}
.sc-strip-item {
    padding: 13px 16px;
    border-right: 1px solid var(--border);
}
.sc-strip-item:last-child { border-right: none; }
.sc-strip-label {
    font-size: 0.62rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--t3);
    margin-bottom: 5px;
}
.sc-strip-val {
    font-size: 0.86rem;
    font-weight: 500;
    color: var(--t1);
}
 
/* ── BODY LAYOUT ── */
.sc-body {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 14px;
    align-items: start;
}
.sc-main { min-width: 0; }
.sc-sidebar { min-width: 0; }
 
/* ── CASE DETAILS FIELDS ── */
.sc-fields { padding: 0; }
.sc-field-row {
    display: flex;
    align-items: baseline;
    gap: 16px;
    padding: 10px 18px;
    border-bottom: 1px solid var(--border);
}
.sc-field-last { border-bottom: none; }
.sc-field-key {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--t3);
    min-width: 130px;
    flex-shrink: 0;
    padding-top: 1px;
}
.sc-field-val {
    font-size: 0.86rem;
    color: var(--t1);
    line-height: 1.5;
}
 
/* ── CASE RESOURCES ── */
.sc-resources { padding: 0; }
.sc-resource-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 18px;
    border-bottom: 1px solid var(--border);
    text-decoration: none;
    color: var(--t1);
    transition: background 0.15s;
}
.sc-resource-last { border-bottom: none; }
.sc-resource-row:hover { background: rgba(124,58,237,0.05); }
.sc-res-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(124,58,237,0.1);
    color: var(--p3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sc-res-icon svg { width: 15px; height: 15px; }
.sc-res-info { flex: 1; }
.sc-res-name { font-size: 0.85rem; font-weight: 500; color: var(--t1); }
.sc-res-count { font-size: 0.72rem; color: var(--t3); margin-top: 1px; }
.sc-res-arrow { width: 15px; height: 15px; color: var(--t3); flex-shrink: 0; }
 
/* ── RECENT DOCUMENTS ── */
.sc-doc-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 18px;
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
}
.sc-doc-last { border-bottom: none; }
.sc-doc-row:hover { background: rgba(255,255,255,0.02); }
.sc-doc-icon {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    background: rgba(96,165,250,0.1);
    color: var(--info);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sc-doc-icon svg { width: 14px; height: 14px; }
.sc-doc-info { flex: 1; min-width: 0; }
.sc-doc-title {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--t1);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sc-doc-meta { font-size: 0.72rem; color: var(--t3); margin-top: 1px; }
 
/* ── BILLING SIDEBAR ── */
.sc-bill-rows { padding: 0; }
.sc-bill-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 18px;
    border-bottom: 1px solid var(--border);
}
.sc-bill-last { border-bottom: none; }
.sc-bill-label { font-size: 0.74rem; color: var(--t3); }
.sc-bill-val { font-size: 0.88rem; font-weight: 500; color: var(--t1); }
.sc-green { color: var(--success) !important; }
.sc-red   { color: var(--danger) !important; }
 
/* ── TIMELINE SIDEBAR ── */
.sc-tl-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 18px;
    border-bottom: 1px solid var(--border);
}
.sc-tl-last { border-bottom: none; }
.sc-tl-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--p);
    flex-shrink: 0;
    margin-top: 5px;
}
.sc-tl-action { font-size: 0.84rem; font-weight: 500; color: var(--t1); }
.sc-tl-meta { font-size: 0.72rem; color: var(--t3); margin-top: 2px; }
 
/* ── APPOINTMENTS SIDEBAR ── */
.sc-appt-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 18px;
    border-bottom: 1px solid var(--border);
    gap: 10px;
}
.sc-appt-last { border-bottom: none; }
.sc-appt-purpose { font-size: 0.85rem; font-weight: 500; color: var(--t1); }
.sc-appt-meta { font-size: 0.72rem; color: var(--t3); margin-top: 2px; }
 
/* ── RESPONSIVE ── */
@media (max-width: 1024px) {
    .sc-body { grid-template-columns: 1fr; }
    .sc-sidebar { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .sc-sidebar .card { margin-top: 0 !important; }
}
@media (max-width: 768px) {
    .sc-strip { grid-template-columns: repeat(2, 1fr); }
    .sc-strip-item { border-bottom: 1px solid var(--border); }
    .sc-sidebar { grid-template-columns: 1fr; }
}
@media (max-width: 520px) {
    .sc-stepper-inner { flex-direction: column; align-items: flex-start; }
    .sc-steps { width: 100%; }
    .sc-strip { grid-template-columns: 1fr 1fr; }
}
</style>
 
@endsection
 