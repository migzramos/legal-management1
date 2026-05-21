@extends('layouts.lawyer')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of your cases, calendar, and billing')

@section('topbar_actions')
<a href="{{ route('lawyer.cases.create') }}" class="btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>
    New Case
</a>
@endsection

@section('content')
<style>
    /* ── KPI Grid ── */
    .dash-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }
    .dash-kpi-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }
    .kpi-card {
        border-radius: 14px;
        padding: 18px 20px;
        border: 1px solid rgba(255,255,255,0.07);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, border-color 0.2s;
    }
    .kpi-card:hover { transform: translateY(-2px); }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 14px 14px 0 0;
    }

    /* Card accent colors */
    .kpi-purple { background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.2); }
    .kpi-purple::before { background: #7c3aed; }
    .kpi-purple .kpi-icon-wrap { background: rgba(124,58,237,0.18); color: #a78bfa; }
    .kpi-purple .kpi-value { color: #c4b5fd; }

    .kpi-teal { background: rgba(20,184,166,0.1); border-color: rgba(20,184,166,0.2); }
    .kpi-teal::before { background: #14b8a6; }
    .kpi-teal .kpi-icon-wrap { background: rgba(20,184,166,0.18); color: #5eead4; }
    .kpi-teal .kpi-value { color: #5eead4; }

    .kpi-amber { background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.2); }
    .kpi-amber::before { background: #f59e0b; }
    .kpi-amber .kpi-icon-wrap { background: rgba(245,158,11,0.18); color: #fcd34d; }
    .kpi-amber .kpi-value { color: #fcd34d; }

    .kpi-blue { background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.2); }
    .kpi-blue::before { background: #3b82f6; }
    .kpi-blue .kpi-icon-wrap { background: rgba(59,130,246,0.18); color: #93c5fd; }
    .kpi-blue .kpi-value { color: #93c5fd; }

    .kpi-green { background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.2); }
    .kpi-green::before { background: #22c55e; }
    .kpi-green .kpi-icon-wrap { background: rgba(34,197,94,0.18); color: #86efac; }
    .kpi-green .kpi-value { color: #86efac; }

    .kpi-rose { background: rgba(244,63,94,0.1); border-color: rgba(244,63,94,0.2); }
    .kpi-rose::before { background: #f43f5e; }
    .kpi-rose .kpi-icon-wrap { background: rgba(244,63,94,0.18); color: #fda4af; }
    .kpi-rose .kpi-value { color: #fda4af; }

    .kpi-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .kpi-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: rgba(240,236,255,0.5);
    }
    .kpi-icon-wrap {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .kpi-icon-wrap svg { width: 17px; height: 17px; }
    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 5px;
        font-family: 'Cormorant Garamond', serif;
    }
    .kpi-meta { font-size: 0.72rem; color: rgba(240,236,255,0.4); }

    /* ── Dashboard Grid ── */
    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 18px;
        align-items: start;
    }
    @media (max-width: 1100px) { .dash-grid { grid-template-columns: 1fr; } }

    /* ── Cards ── */
    .d-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 16px;
        transition: border-color 0.2s;
    }
    .d-card:hover { border-color: rgba(124,58,237,0.25); }
    .d-card:last-child { margin-bottom: 0; }
    .d-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 18px 12px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .d-card-title {
        font-size: 0.88rem;
        font-weight: 600;
        color: #f0ecff;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .d-card-title-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .d-card-action {
        font-size: 0.73rem;
        color: #a78bfa;
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .d-card-action:hover { opacity: 0.7; }
    .d-card-body { padding: 6px 0; }

    /* ── Case Rows ── */
    .case-row {
        padding: 13px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .case-row:last-child { border-bottom: none; }
    .case-row:hover { background: rgba(255,255,255,0.025); }
    .case-row-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .case-title { font-size: 0.87rem; font-weight: 600; color: #f0ecff; margin-bottom: 2px; }
    .case-sub { font-size: 0.75rem; color: rgba(240,236,255,0.45); }
    .case-meta { display: flex; gap: 16px; margin-top: 9px; }
    .case-meta-item label { display: block; font-size: 0.67rem; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(240,236,255,0.35); margin-bottom: 2px; }
    .case-meta-item span { font-size: 0.78rem; color: rgba(240,236,255,0.7); }

    /* ── Bill Rows ── */
    .bill-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .bill-row:last-child { border-bottom: none; }
    .bill-row:hover { background: rgba(255,255,255,0.025); }
    .bill-client { font-size: 0.85rem; font-weight: 500; color: #f0ecff; margin-bottom: 2px; }
    .bill-detail { font-size: 0.73rem; color: rgba(240,236,255,0.4); }
    .bill-right { text-align: right; }
    .bill-amount { font-size: 0.9rem; font-weight: 600; color: #f0ecff; margin-bottom: 4px; }

    /* ── Schedule Rows ── */
    .sch-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .sch-row:last-child { border-bottom: none; }
    .sch-row:hover { background: rgba(255,255,255,0.025); }
    .sch-date {
        width: 40px; min-width: 40px;
        height: 44px;
        border-radius: 10px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: rgba(124,58,237,0.18);
        border: 1px solid rgba(124,58,237,0.25);
    }
    .sch-date .month { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; color: #a78bfa; letter-spacing: 0.05em; }
    .sch-date .day { font-size: 1.1rem; font-weight: 700; color: #c4b5fd; line-height: 1.1; }
    .sch-title { font-size: 0.85rem; font-weight: 500; color: #f0ecff; margin-bottom: 2px; }
    .sch-sub { font-size: 0.73rem; color: rgba(240,236,255,0.42); }

    /* ── Task Rows ── */
    .task-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .task-row:last-child { border-bottom: none; }
    .task-row:hover { background: rgba(255,255,255,0.025); }
    .task-dot { width: 7px; height: 7px; border-radius: 50%; background: #a78bfa; flex-shrink: 0; }
    .task-title { font-size: 0.84rem; color: #f0ecff; margin-bottom: 1px; }
    .task-sub { font-size: 0.72rem; color: rgba(240,236,255,0.4); }

    /* ── Appointment Rows ── */
    .appt-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .appt-row:last-child { border-bottom: none; }
    .appt-row:hover { background: rgba(255,255,255,0.025); }
    .appt-title { font-size: 0.84rem; font-weight: 500; color: #f0ecff; margin-bottom: 2px; }
    .appt-sub { font-size: 0.72rem; color: rgba(240,236,255,0.42); }

    /* ── Urgent Card ── */
    .urgent-card { border-color: rgba(244,63,94,0.25) !important; }
    .urgent-card .d-card-header { border-bottom-color: rgba(244,63,94,0.15); }
    .urgent-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .urgent-item:last-child { border-bottom: none; }
    .urgent-label { font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; padding: 3px 8px; border-radius: 6px; white-space: nowrap; margin-top: 2px; flex-shrink: 0; }
    .urgent-label-overdue { background: rgba(244,63,94,0.15); color: #fda4af; border: 1px solid rgba(244,63,94,0.25); }
    .urgent-label-unpaid  { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.25); }
    .urgent-text { font-size: 0.84rem; color: #f0ecff; margin-bottom: 1px; }
    .urgent-sub  { font-size: 0.73rem; color: rgba(240,236,255,0.45); }

    /* ── Badges ── */
    .db-badge {
        display: inline-flex; align-items: center;
        padding: 3px 9px; border-radius: 20px;
        font-size: 0.67rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.06em;
        white-space: nowrap; flex-shrink: 0;
    }
    .badge-active, .badge-open     { background: rgba(59,130,246,0.15);  color: #93c5fd; border: 1px solid rgba(59,130,246,0.25); }
    .badge-ongoing                 { background: rgba(124,58,237,0.15); color: #c4b5fd; border: 1px solid rgba(124,58,237,0.25); }
    .badge-pending                 { background: rgba(245,158,11,0.15);  color: #fcd34d; border: 1px solid rgba(245,158,11,0.25); }
    .badge-closed                  { background: rgba(255,255,255,0.06); color: rgba(240,236,255,0.5); border: 1px solid rgba(255,255,255,0.1); }
    .badge-paid, .badge-completed  { background: rgba(34,197,94,0.15);   color: #86efac; border: 1px solid rgba(34,197,94,0.25); }
    .badge-sent, .badge-confirmed  { background: rgba(59,130,246,0.15);  color: #93c5fd; border: 1px solid rgba(59,130,246,0.25); }
    .badge-overdue, .badge-cancelled { background: rgba(244,63,94,0.15); color: #fda4af; border: 1px solid rgba(244,63,94,0.25); }
    .badge-draft                   { background: rgba(255,255,255,0.06); color: rgba(240,236,255,0.45); border: 1px solid rgba(255,255,255,0.08); }

    /* ── Empty State ── */
    .d-empty { padding: 28px 18px; text-align: center; color: rgba(240,236,255,0.3); font-size: 0.82rem; }
    .d-empty h4 { font-size: 0.9rem; color: rgba(240,236,255,0.45); margin-bottom: 4px; }
</style>

{{-- ── KPI ROW 1 ── --}}
<div class="dash-kpi-grid">
    <div class="kpi-card kpi-purple">
        <div class="kpi-top">
            <span class="kpi-label">Active Cases</span>
            <span class="kpi-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                    <path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value">{{ $activeCases }}</div>
        <div class="kpi-meta">Currently active</div>
    </div>

    <div class="kpi-card kpi-teal">
        <div class="kpi-top">
            <span class="kpi-label">Revenue This Month</span>
            <span class="kpi-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 100 7h5a3.5 3.5 0 110 7H6"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value">{{ money_display($revenueThisMonth) }}</div>
        <div class="kpi-meta">{{ now()->format('F Y') }}</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-top">
            <span class="kpi-label">Billable Hours</span>
            <span class="kpi-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value">{{ $billableHours }}</div>
        <div class="kpi-meta">Hours logged</div>
    </div>

    <div class="kpi-card kpi-blue">
        <div class="kpi-top">
            <span class="kpi-label">Total Clients</span>
            <span class="kpi-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value">{{ $totalClients }}</div>
        <div class="kpi-meta">All time</div>
    </div>
</div>

{{-- ── KPI ROW 2 ── --}}
<div class="dash-kpi-grid-2">
    <div class="kpi-card kpi-green">
        <div class="kpi-top">
            <span class="kpi-label">Total Revenue</span>
            <span class="kpi-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value">{{ money_display($totalRevenue) }}</div>
        <div class="kpi-meta">All time earnings</div>
    </div>

    <div class="kpi-card kpi-rose">
        <div class="kpi-top">
            <span class="kpi-label">Pending Invoices</span>
            <span class="kpi-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value">{{ $pendingInvoices }}</div>
        <div class="kpi-meta">Awaiting payment</div>
    </div>
</div>

{{-- ── MAIN GRID ── --}}
<div class="dash-grid">

    {{-- LEFT --}}
    <div>
        {{-- Active Cases --}}
        <div class="d-card">
            <div class="d-card-header">
                <span class="d-card-title">
                    <span class="d-card-title-dot" style="background:#7c3aed;"></span>
                    Active Cases
                </span>
                <a href="{{ route('lawyer.cases.index') }}" class="d-card-action">Manage all →</a>
            </div>
            <div class="d-card-body">
                @forelse($cases as $case)
                @php
                    $st = strtolower(str_replace('_', ' ', $case->status));
                    $bc = match($st) {
                        'active'  => 'badge-active',
                        'ongoing' => 'badge-ongoing',
                        'open'    => 'badge-open',
                        'closed'  => 'badge-closed',
                        'pending' => 'badge-pending',
                        default   => 'badge-draft',
                    };
                @endphp
                <div class="case-row">
                    <div class="case-row-top">
                        <div>
                            <div class="case-title">{{ $case->title }}</div>
                            <div class="case-sub">{{ $case->client->name }} · {{ $case->case_number }}</div>
                        </div>
                        <span class="db-badge {{ $bc }}">{{ ucfirst($st) }}</span>
                    </div>
                    <div class="case-meta">
                        <div class="case-meta-item">
                            <label>Next hearing</label>
                            <span>{{ optional($case->hearing_date)->format('M d, Y') ?? 'TBD' }}</span>
                        </div>
                        <div class="case-meta-item">
                            <label>Category</label>
                            <span>{{ $case->category->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="d-empty"><h4>No active cases</h4><p>Your active cases will appear here.</p></div>
                @endforelse
            </div>
        </div>

        {{-- Urgent Attention --}}
        @if(isset($overdueTasks) && ($overdueTasks->count() > 0 || (isset($unpaidInvoices) && $unpaidInvoices->count() > 0)))
        <div class="d-card urgent-card">
            <div class="d-card-header">
                <span class="d-card-title">
                    <span class="d-card-title-dot" style="background:#f43f5e;"></span>
                    Urgent Attention
                </span>
                <span class="db-badge badge-overdue">Action Required</span>
            </div>
            <div class="d-card-body">
                @foreach($overdueTasks as $task)
                <div class="urgent-item">
                    <span class="urgent-label urgent-label-overdue">Overdue</span>
                    <div>
                        <div class="urgent-text">{{ $task->title }}</div>
                        <div class="urgent-sub">{{ $task->case->title ?? 'No case assigned' }}</div>
                    </div>
                </div>
                @endforeach
                @if(isset($unpaidInvoices))
                @foreach($unpaidInvoices as $invoice)
                <div class="urgent-item">
                    <span class="urgent-label urgent-label-unpaid">Unpaid</span>
                    <div>
                        <div class="urgent-text">Invoice #{{ $invoice->id }} · {{ $invoice->client->name }}</div>
                        <div class="urgent-sub">{{ money_display($invoice->balance) }}</div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
        @endif

        {{-- Recent Billing --}}
        <div class="d-card">
            <div class="d-card-header">
                <span class="d-card-title">
                    <span class="d-card-title-dot" style="background:#22c55e;"></span>
                    Recent Billing
                </span>
                <a href="{{ route('lawyer.billing.index') }}" class="d-card-action">View all →</a>
            </div>
            <div class="d-card-body">
                @forelse($recentInvoices as $invoice)
                @php
                    $bs = strtolower($invoice->status);
                    $bc = match($bs) {
                        'paid'    => 'badge-paid',
                        'sent'    => 'badge-sent',
                        'overdue' => 'badge-overdue',
                        'pending' => 'badge-pending',
                        default   => 'badge-draft',
                    };
                @endphp
                <div class="bill-row">
                    <div>
                        <div class="bill-client">{{ $invoice->client->name }}</div>
                        <div class="bill-detail">Invoice #{{ $invoice->id }}</div>
                    </div>
                    <div class="bill-right">
                        <div class="bill-amount">{{ money_display($invoice->total) }}</div>
                        <span class="db-badge {{ $bc }}">{{ ucfirst($bs) }}</span>
                    </div>
                </div>
                @empty
                <div class="d-empty"><h4>No recent invoices</h4><p>Your invoices will appear here.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT --}}
    <div>
        {{-- Upcoming Events --}}
        <div class="d-card">
            <div class="d-card-header">
                <span class="d-card-title">
                    <span class="d-card-title-dot" style="background:#a78bfa;"></span>
                    Upcoming Events
                </span>
                <a href="{{ route('lawyer.calendar.index') }}" class="d-card-action">Calendar →</a>
            </div>
            <div class="d-card-body">
                @forelse($upcomingSchedules as $schedule)
                <div class="sch-row">
                    <div class="sch-date">
                        <div class="month">{{ $schedule->scheduled_at->format('M') }}</div>
                        <div class="day">{{ $schedule->scheduled_at->format('d') }}</div>
                    </div>
                    <div>
                        <div class="sch-title">{{ $schedule->title }}</div>
                        <div class="sch-sub">{{ $schedule->case->title ?? 'Case event' }} · {{ $schedule->scheduled_at->format('h:i A') }}</div>
                    </div>
                </div>
                @empty
                <div class="d-empty"><h4>No upcoming events</h4><p>Scheduled events will appear here.</p></div>
                @endforelse
            </div>
        </div>

        {{-- Appointments --}}
        <div class="d-card">
            <div class="d-card-header">
                <span class="d-card-title">
                    <span class="d-card-title-dot" style="background:#3b82f6;"></span>
                    Appointments
                </span>
                <span style="font-size:0.72rem;color:rgba(240,236,255,0.4);">Recent requests</span>
            </div>
            <div class="d-card-body">
                @forelse($upcomingAppointments as $appt)
                @php
                    $as = strtolower($appt->status);
                    $ac = match($as) {
                        'confirmed' => 'badge-confirmed',
                        'completed' => 'badge-completed',
                        'cancelled' => 'badge-cancelled',
                        default     => 'badge-pending',
                    };
                @endphp
                <div class="appt-row">
                    <div>
                        <div class="appt-title">{{ $appt->purpose ?? 'Appointment Request' }}</div>
                        <div class="appt-sub">{{ $appt->appointment_at->format('M d, Y h:i A') }} · {{ $appt->client->name }}</div>
                    </div>
                    <span class="db-badge {{ $ac }}">{{ ucfirst($as) }}</span>
                </div>
                @empty
                <div class="d-empty"><h4>No appointments</h4><p>Client appointments will appear here.</p></div>
                @endforelse
            </div>
        </div>

        {{-- Today's Tasks --}}
        <div class="d-card">
            <div class="d-card-header">
                <span class="d-card-title">
                    <span class="d-card-title-dot" style="background:#f59e0b;"></span>
                    Today's Tasks
                </span>
                <a href="{{ route('lawyer.cases.index') }}" class="d-card-action">All tasks →</a>
            </div>
            <div class="d-card-body">
                @forelse($todayTasks as $task)
                <div class="task-row">
                    <div class="task-dot"></div>
                    <div>
                        <div class="task-title">{{ $task->title }}</div>
                        <div class="task-sub">{{ $task->case->title ?? 'No case assigned' }}</div>
                    </div>
                </div>
                @empty
                <div class="d-empty"><h4>No tasks for today</h4><p>Your daily workflow will appear here.</p></div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection