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

{{-- ── PROGRESS TRACKER ── --}}
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

    // Appointments are no longer linked to cases directly.
    // Query via client instead.
    $appointments = \App\Models\Appointment::where('client_id', $case->client_id)
        ->where('lawyer_id', $case->lawyer_id)
        ->orderBy('appointment_at', 'desc')
        ->get();
@endphp

<div class="cs-progress-card">
    <div class="cs-progress-hd">
        <div>
            <div class="cs-progress-label">Legal Workflow Progress</div>
            <div class="cs-progress-sub">Track the case stage from intake through resolution</div>
        </div>
        @if($nextStatus)
        <button type="button" class="btn-primary" id="statusToggle" onclick="document.getElementById('statusPanel').classList.toggle('cs-hidden')">
            Update Status
        </button>
        @endif
    </div>

    <div class="cs-steps">
        @foreach($progressSteps as $key => $step)
        @php
            $si = array_search($key, $statusOrder);
            $isActive = $key === $case->status;
        @endphp
        <div class="cs-step {{ $step['completed'] ? 'cs-step-done' : '' }} {{ $isActive ? 'cs-step-current' : '' }}">
            <div class="cs-step-circle">{{ $si + 1 }}</div>
            <div class="cs-step-name">{{ $step['label'] }}</div>
        </div>
        @if($si < count($statusOrder) - 1)
        <div class="cs-step-connector {{ ($step['completed'] && $currentIndex > $si) ? 'cs-connector-done' : '' }}"></div>
        @endif
        @endforeach
    </div>

    @if($nextStatus)
    <div id="statusPanel" class="cs-status-panel cs-hidden">
        <form method="POST" action="{{ route('lawyer.cases.update', $case) }}">
            @csrf @method('PUT')
            <label class="cs-status-label">Select next status</label>
            <div class="cs-status-row">
                <select name="status" class="cs-status-select">
                    <option value="{{ $nextStatus }}">{{ $statusLabels[$nextStatus] ?? $nextStatus }}</option>
                </select>
                <button type="submit" class="btn-primary">Save</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('statusPanel').classList.add('cs-hidden')">Cancel</button>
            </div>
        </form>
    </div>
    @endif
</div>

{{-- ── SUMMARY STRIP ── --}}
<div class="cs-summary">
    <div class="cs-summary-item">
        <div class="cs-summary-label">Client</div>
        <div class="cs-summary-val">{{ $case->client->name }}</div>
    </div>
    <div class="cs-summary-item">
        <div class="cs-summary-label">Case Type</div>
        <div class="cs-summary-val">{{ $case->category->name ?? 'N/A' }}</div>
    </div>
    <div class="cs-summary-item">
        <div class="cs-summary-label">Status</div>
        @php
            $st = strtolower(str_replace('_',' ',$case->status));
            $sc = match($st) {
                'active case' => 'badge-active', 'active' => 'badge-active',
                'resolution'  => 'badge-completed',
                'closed'      => 'badge-closed',
                default       => 'badge-pending',
            };
        @endphp
        <span class="badge {{ $sc }}">{{ ucfirst(str_replace('_',' ',$case->status)) }}</span>
    </div>
    <div class="cs-summary-item">
        <div class="cs-summary-label">Next Hearing</div>
        <div class="cs-summary-val">{{ $case->next_hearing_date?->format('M d, Y') ?? 'Not scheduled' }}</div>
    </div>
    <div class="cs-summary-item">
        <div class="cs-summary-label">Filed</div>
        <div class="cs-summary-val">{{ $case->filed_date?->format('M d, Y') ?? 'N/A' }}</div>
    </div>
    <div class="cs-summary-item">
        <div class="cs-summary-label">Court</div>
        <div class="cs-summary-val">{{ $case->courtType->name ?? 'N/A' }}</div>
    </div>
</div>

{{-- ── QUICK LINKS ── --}}
<div class="cs-links">
    <a href="{{ route('lawyer.documents.index', $case) }}" class="cs-link-card">
        <div class="cs-link-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div>
            <div class="cs-link-title">Documents</div>
            <div class="cs-link-sub">{{ $case->documents->count() }} files</div>
        </div>
    </a>
    <a href="{{ route('lawyer.tasks.index', $case) }}" class="cs-link-card">
        <div class="cs-link-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg>
        </div>
        <div>
            <div class="cs-link-title">Tasks</div>
            <div class="cs-link-sub">{{ $case->tasks->count() }} items</div>
        </div>
    </a>
    <a href="#appointments" class="cs-link-card">
        <div class="cs-link-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <div class="cs-link-title">Appointments</div>
            <div class="cs-link-sub">{{ $appointments->count() }} scheduled</div>
        </div>
    </a>
    <a href="{{ route('lawyer.schedules.index', $case) }}" class="cs-link-card">
        <div class="cs-link-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="cs-link-title">Schedule</div>
            <div class="cs-link-sub">{{ $case->schedules->count() }} events</div>
        </div>
    </a>
</div>

{{-- ── STATS ── --}}
<div class="cs-stats">
    <div class="cs-stat">
        <div class="cs-stat-val">{{ $case->timeEntries->sum('hours') }}h</div>
        <div class="cs-stat-label">Time Logged</div>
    </div>
    <div class="cs-stat">
        <div class="cs-stat-val">₱{{ number_format($case->invoices->sum('total'), 0) }}</div>
        <div class="cs-stat-label">Total Invoiced</div>
    </div>
    <div class="cs-stat">
        <div class="cs-stat-val">{{ $case->messages->count() }}</div>
        <div class="cs-stat-label">Messages</div>
    </div>
    <div class="cs-stat">
        <div class="cs-stat-val">{{ $case->documents->count() }}</div>
        <div class="cs-stat-label">Documents</div>
    </div>
</div>

{{-- ── TABS ── --}}
<div class="cs-tabs-wrap">
    <div class="cs-tab-bar">
        <button class="cs-tab active" data-tab="overview">Overview</button>
        <button class="cs-tab" data-tab="documents">
            Documents
            <span class="cs-tab-count">{{ $case->documents->count() }}</span>
        </button>
        <button class="cs-tab" data-tab="timeline">Timeline</button>
        <button class="cs-tab" data-tab="billing">Billing</button>
        <button class="cs-tab" data-tab="appointments">Appointments</button>
    </div>

    {{-- Overview --}}
    <div id="tab-overview" class="cs-pane active">
        <div class="cs-two-col">
            <div class="card">
                <div class="card-header"><span class="card-title">Case Information</span></div>
                <div class="cs-info-list">
                    <div class="cs-info-row">
                        <span class="cs-info-label">Description</span>
                        <span class="cs-info-val">{{ $case->description ?: '—' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="cs-info-label">Filed Date</span>
                        <span class="cs-info-val">{{ $case->filed_date?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="cs-info-label">Court Type</span>
                        <span class="cs-info-val">{{ $case->courtType->name ?? 'N/A' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="cs-info-label">Court Name</span>
                        <span class="cs-info-val">{{ $case->court_name ?: '—' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="cs-info-label">Judge</span>
                        <span class="cs-info-val">{{ $case->judge_name ?: '—' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="cs-info-label">Opposing Party</span>
                        <span class="cs-info-val">{{ $case->opposing_party ?: '—' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="cs-info-label">Opposing Counsel</span>
                        <span class="cs-info-val">{{ $case->opposing_counsel ?: '—' }}</span>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Recent Documents</span>
                    <a href="{{ route('lawyer.documents.index', $case) }}" class="card-action">View all →</a>
                </div>
                @if($case->documents->count() > 0)
                    @foreach($case->documents->take(4) as $doc)
                    <div class="cs-doc-row">
                        <div class="cs-doc-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <div class="cs-doc-info">
                            <div class="cs-doc-title">{{ $doc->title }}</div>
                            <div class="cs-doc-meta">{{ $doc->created_at->diffForHumans() }} · {{ $doc->uploader->name }}</div>
                        </div>
                        <a href="{{ route('lawyer.documents.show', $doc) }}" class="ci-btn-view" style="padding:5px 10px;font-size:0.74rem;">View</a>
                    </div>
                    @endforeach
                @else
                <div class="empty" style="padding:28px 18px;">
                    <h4>No documents yet</h4>
                    <p>Upload case documents to see them here.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div id="tab-documents" class="cs-pane">
        <div class="cs-pane-hd">
            <span class="cs-pane-title">Case Documents</span>
            <a href="{{ route('lawyer.documents.index', $case) }}" class="btn-primary">Manage Documents</a>
        </div>
        @if($case->documents->count() > 0)
        <div class="cs-table-wrap">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Visibility</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($case->documents->take(10) as $document)
                    <tr>
                        <td>
                            <div class="cs-td-title">{{ $document->title }}</div>
                            <div class="cs-td-sub">{{ $document->file_name }}</div>
                        </td>
                        <td><span class="badge badge-info">{{ ucfirst($document->category) }}</span></td>
                        <td class="cs-td-muted">{{ $document->uploader->name }}</td>
                        <td class="cs-td-muted">{{ $document->created_at->format('M d, Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('lawyer.documents.toggle-visibility', $document) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="cs-vis-btn {{ $document->is_visible_to_client ? 'cs-vis-on' : 'cs-vis-off' }}">
                                    {{ $document->is_visible_to_client ? 'Visible' : 'Hidden' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="cs-td-actions">
                                <a href="{{ route('lawyer.documents.show', $document) }}" class="cs-td-link">View</a>
                                <a href="{{ route('lawyer.documents.download', $document) }}" class="cs-td-link">Download</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty"><h4>No documents uploaded yet</h4></div>
        @endif
    </div>

    {{-- Timeline --}}
    <div id="tab-timeline" class="cs-pane">
        <div class="cs-pane-hd"><span class="cs-pane-title">Case Timeline</span></div>
        @php
            $timeline = \App\Models\AuditLog::where('model_type', \App\Models\LegalCase::class)
                ->where('model_id', $case->id)
                ->orderBy('created_at', 'desc')->take(20)->get();
        @endphp
        <div class="cs-timeline">
            @forelse($timeline as $event)
            <div class="cs-tl-item">
                <div class="cs-tl-dot"></div>
                <div class="cs-tl-body">
                    <div class="cs-tl-action">{{ ucfirst(str_replace('_',' ',$event->action)) }}</div>
                    <div class="cs-tl-meta">{{ $event->user->name ?? 'System' }} · {{ $event->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
            @empty
            <div class="empty"><h4>No timeline events yet</h4></div>
            @endforelse
        </div>
    </div>

    {{-- Billing --}}
    <div id="tab-billing" class="cs-pane">
        <div class="cs-pane-hd">
            <span class="cs-pane-title">Billing & Invoices</span>
            <a href="{{ route('lawyer.billing.index') }}" class="btn-primary">Manage Billing</a>
        </div>
        <div class="cs-bill-summary">
            <div class="cs-bill-stat">
                <div class="cs-bill-stat-label">Total Billed</div>
                <div class="cs-bill-stat-val">{{ money_display($case->invoices->sum('total')) }}</div>
            </div>
            <div class="cs-bill-stat">
                <div class="cs-bill-stat-label">Paid</div>
                <div class="cs-bill-stat-val cs-green">{{ money_display($case->invoices->sum('amount_paid')) }}</div>
            </div>
            <div class="cs-bill-stat {{ $case->invoices->sum('balance') > 0 ? 'cs-bill-stat-warn' : '' }}">
                <div class="cs-bill-stat-label">Balance</div>
                <div class="cs-bill-stat-val {{ $case->invoices->sum('balance') > 0 ? 'cs-red' : '' }}">{{ money_display($case->invoices->sum('balance')) }}</div>
            </div>
        </div>
        @forelse($case->invoices->take(5) as $invoice)
        <div class="cs-inv-row">
            <div>
                <div class="cs-inv-num">Invoice #{{ $invoice->id }}</div>
                <div class="cs-inv-meta">{{ $invoice->issued_date->format('M d, Y') }} · {{ money_display($invoice->total) }}</div>
            </div>
            @php
                $ic = match($invoice->status) {
                    'paid'    => 'badge-paid',
                    'overdue' => 'badge-overdue',
                    'draft'   => 'badge-draft',
                    default   => 'badge-pending',
                };
            @endphp
            <span class="badge {{ $ic }}">{{ ucfirst($invoice->status) }}</span>
        </div>
        @empty
        <div class="empty"><h4>No invoices yet</h4></div>
        @endforelse
    </div>

    {{-- Appointments --}}
    <div id="tab-appointments" class="cs-pane" id="appointments">
        <div class="cs-pane-hd">
            <span class="cs-pane-title">Appointments</span>
            <a href="{{ route('lawyer.appointments.index') }}" class="btn-primary">Manage Appointments</a>
        </div>
        @forelse($appointments->take(5) as $appt)
        <div class="cs-inv-row">
            <div>
                <div class="cs-inv-num">{{ $appt->purpose ?? 'Appointment' }}</div>
                <div class="cs-inv-meta">{{ $appt->appointment_at->format('M d, Y H:i') }} · {{ $appt->client->name }}</div>
            </div>
            @php
                $asc = match($appt->status) {
                    'confirmed' => 'badge-completed',
                    'completed' => 'badge-completed',
                    'cancelled' => 'badge-cancelled',
                    default     => 'badge-pending',
                };
            @endphp
            <span class="badge {{ $asc }}">{{ ucfirst($appt->status) }}</span>
        </div>
        @empty
        <div class="empty"><h4>No appointments scheduled</h4></div>
        @endforelse
    </div>
</div>

<style>
/* Progress card */
.cs-progress-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 20px; margin-bottom: 16px; }
.cs-progress-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.cs-progress-label { font-family: 'Playfair Display', serif; font-size: 0.98rem; font-weight: 600; color: var(--t1); }
.cs-progress-sub { font-size: 0.74rem; color: var(--t3); margin-top: 3px; }
.cs-steps { display: flex; align-items: center; overflow-x: auto; gap: 0; padding-bottom: 4px; }
.cs-step { display: flex; flex-direction: column; align-items: center; min-width: 90px; }
.cs-step-circle { width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--border); background: var(--bg-2); display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 600; color: var(--t3); z-index: 2; position: relative; flex-shrink: 0; }
.cs-step-done .cs-step-circle { background: var(--p); border-color: var(--p); color: #fff; }
.cs-step-current .cs-step-circle { background: var(--p); border-color: var(--p); color: #fff; box-shadow: 0 0 0 6px rgba(124,58,237,0.18); }
.cs-step-name { font-size: 0.64rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--t3); margin-top: 7px; text-align: center; line-height: 1.3; }
.cs-step-done .cs-step-name, .cs-step-current .cs-step-name { color: var(--p3); }
.cs-step-connector { flex: 1; height: 2px; background: var(--border); min-width: 20px; margin-bottom: 20px; }
.cs-connector-done { background: var(--p); }
.cs-status-panel { margin-top: 18px; padding: 16px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 10px; }
.cs-hidden { display: none; }
.cs-status-label { font-size: 0.74rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--t3); display: block; margin-bottom: 8px; }
.cs-status-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.cs-status-select { padding: 9px 14px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 9px; color: var(--t1); font-family: 'Outfit', sans-serif; font-size: 0.86rem; outline: none; min-width: 220px; }
.cs-status-select:focus { border-color: var(--p); }

/* Summary strip */
.cs-summary { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0; background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; margin-bottom: 16px; }
.cs-summary-item { padding: 14px 18px; border-right: 1px solid var(--border); }
.cs-summary-item:last-child { border-right: none; }
.cs-summary-label { font-size: 0.64rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--t3); margin-bottom: 5px; }
.cs-summary-val { font-size: 0.88rem; font-weight: 500; color: var(--t1); }

/* Quick links */
.cs-links { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
.cs-link-card { display: flex; align-items: center; gap: 10px; padding: 13px 14px; background: var(--card); border: 1px solid var(--border); border-radius: 11px; text-decoration: none; color: var(--t1); transition: border-color 0.18s, background 0.18s, transform 0.18s; }
.cs-link-card:hover { border-color: rgba(124,58,237,0.35); background: rgba(124,58,237,0.06); transform: translateY(-2px); }
.cs-link-icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(124,58,237,0.1); color: var(--p3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cs-link-icon svg { width: 15px; height: 15px; }
.cs-link-title { font-size: 0.83rem; font-weight: 500; }
.cs-link-sub { font-size: 0.72rem; color: var(--t3); margin-top: 1px; }

/* Stats row */
.cs-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; margin-bottom: 20px; }
.cs-stat { background: var(--card); border: 1px solid var(--border); border-radius: 11px; padding: 14px 16px; text-align: center; }
.cs-stat-val { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: var(--t1); line-height: 1; }
.cs-stat-label { font-size: 0.67rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--t3); margin-top: 5px; }

/* Tabs */
.cs-tabs-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.cs-tab-bar { display: flex; border-bottom: 1px solid var(--border); overflow-x: auto; }
.cs-tab { padding: 12px 18px; font-size: 0.82rem; font-weight: 500; color: var(--t3); background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; font-family: 'Outfit', sans-serif; transition: color 0.15s, border-color 0.15s; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
.cs-tab:hover { color: var(--t1); }
.cs-tab.active { color: var(--p3); border-bottom-color: var(--p); }
.cs-tab-count { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 99px; font-size: 0.64rem; background: rgba(255,255,255,0.07); }
.cs-tab.active .cs-tab-count { background: rgba(124,58,237,0.2); color: var(--p3); }
.cs-pane { display: none; padding: 20px; }
.cs-pane.active { display: block; }
.cs-pane-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; }
.cs-pane-title { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: var(--t1); }

/* Info list */
.cs-info-list { padding: 0 18px 4px; }
.cs-info-row { display: flex; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--border); }
.cs-info-row:last-child { border-bottom: none; }
.cs-info-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--t3); min-width: 120px; padding-top: 1px; }
.cs-info-val { font-size: 0.86rem; color: var(--t1); line-height: 1.5; }

/* Two col overview */
.cs-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

/* Doc rows */
.cs-doc-row { display: flex; align-items: center; gap: 10px; padding: 11px 18px; border-bottom: 1px solid var(--border); transition: background 0.15s; }
.cs-doc-row:last-child { border-bottom: none; }
.cs-doc-row:hover { background: rgba(255,255,255,0.02); }
.cs-doc-icon { width: 30px; height: 30px; border-radius: 7px; background: rgba(96,165,250,0.1); color: var(--info); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cs-doc-icon svg { width: 14px; height: 14px; }
.cs-doc-info { flex: 1; min-width: 0; }
.cs-doc-title { font-size: 0.85rem; font-weight: 500; color: var(--t1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cs-doc-meta { font-size: 0.72rem; color: var(--t3); margin-top: 1px; }

/* Table */
.cs-table-wrap { overflow-x: auto; }
.cs-table { width: 100%; border-collapse: collapse; }
.cs-table thead th { padding: 10px 14px; text-align: left; font-size: 0.67rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--t3); border-bottom: 1px solid var(--border); white-space: nowrap; }
.cs-table tbody td { padding: 13px 14px; font-size: 0.84rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.cs-table tbody tr:last-child td { border-bottom: none; }
.cs-table tbody tr:hover td { background: rgba(124,58,237,0.04); }
.cs-td-title { font-weight: 500; color: var(--t1); font-size: 0.85rem; }
.cs-td-sub { font-size: 0.74rem; color: var(--t3); margin-top: 1px; }
.cs-td-muted { color: var(--t2); }
.cs-td-actions { display: flex; gap: 12px; }
.cs-td-link { font-size: 0.78rem; color: var(--p3); text-decoration: none; }
.cs-td-link:hover { text-decoration: underline; }
.cs-vis-btn { padding: 3px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 600; cursor: pointer; border: none; font-family: 'Outfit', sans-serif; }
.cs-vis-on  { background: rgba(124,58,237,0.15); color: var(--p3); }
.cs-vis-off { background: rgba(255,255,255,0.06); color: var(--t3); }

/* Timeline */
.cs-timeline { display: flex; flex-direction: column; gap: 0; }
.cs-tl-item { display: flex; gap: 14px; padding: 13px 0; border-bottom: 1px solid var(--border); position: relative; }
.cs-tl-item:last-child { border-bottom: none; }
.cs-tl-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--p); flex-shrink: 0; margin-top: 6px; }
.cs-tl-action { font-size: 0.87rem; font-weight: 500; color: var(--t1); }
.cs-tl-meta { font-size: 0.74rem; color: var(--t3); margin-top: 2px; }

/* Billing */
.cs-bill-summary { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 16px; }
.cs-bill-stat { background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; }
.cs-bill-stat-warn { border-color: rgba(248,113,113,0.25); background: rgba(248,113,113,0.05); }
.cs-bill-stat-label { font-size: 0.67rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--t3); margin-bottom: 6px; }
.cs-bill-stat-val { font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; color: var(--t1); }
.cs-green { color: var(--success) !important; }
.cs-red   { color: var(--danger) !important; }

/* Invoice / appt rows */
.cs-inv-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); gap: 10px; }
.cs-inv-row:last-child { border-bottom: none; }
.cs-inv-num { font-size: 0.88rem; font-weight: 500; color: var(--t1); }
.cs-inv-meta { font-size: 0.73rem; color: var(--t3); margin-top: 2px; }

/* Responsive */
@media (max-width: 1000px) { .cs-links { grid-template-columns: repeat(2,1fr); } .cs-two-col { grid-template-columns: 1fr; } }
@media (max-width: 768px)  { .cs-summary { grid-template-columns: repeat(2,1fr); } .cs-stats { grid-template-columns: repeat(2,1fr); } .cs-bill-summary { grid-template-columns: 1fr; } }
@media (max-width: 480px)  { .cs-links { grid-template-columns: 1fr 1fr; } }
</style>

<script>
document.querySelectorAll('.cs-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.cs-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.cs-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab)?.classList.add('active');
    });
});
</script>

@endsection