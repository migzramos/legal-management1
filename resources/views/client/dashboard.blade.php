@extends('layouts.client')
@section('title', 'Dashboard')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="section-header">
    <div>
        <h1 class="section-title">Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ Auth::user()->name }}</h1>
        <p class="section-subtitle">Here's a summary of your legal matters.</p>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-card-top">
            <span class="kpi-label">Active Cases</span>
            <span class="kpi-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></span>
        </div>
        <div class="kpi-value">{{ $activeCases ?? 0 }}</div>
        <div class="kpi-meta">Cases in progress</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card-top">
            <span class="kpi-label">Appointments</span>
            <span class="kpi-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.5" stroke-linecap="round"/><path stroke-linecap="round" stroke-width="1.5" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        </div>
        <div class="kpi-value">{{ $upcomingAppointments ?? 0 }}</div>
        <div class="kpi-meta">Upcoming</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card-top">
            <span class="kpi-label">Unpaid Invoices</span>
            <span class="kpi-icon" style="background:rgba(248,113,113,0.12);color:var(--danger);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></span>
        </div>
        <div class="kpi-value" style="color:var(--danger);">{{ $unpaidInvoices ?? 0 }}</div>
        <div class="kpi-meta">Require payment</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card-top">
            <span class="kpi-label">Documents</span>
            <span class="kpi-icon" style="background:rgba(52,211,153,0.1);color:var(--success);"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></span>
        </div>
        <div class="kpi-value" style="color:var(--success);">{{ $documentsCount ?? 0 }}</div>
        <div class="kpi-meta">Total files</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card-header">
                <div><div class="card-title">Recent Cases</div><div class="card-subtitle">Your most recently updated matters</div></div>
                <a href="{{ route('client.cases.index') }}" class="btn-link">View all</a>
            </div>
            <div class="card-body-flush">
                @forelse($recentCases ?? [] as $case)
                <div class="list-item">
                    <div class="list-item-left">
                        <div style="font-size:0.88rem;font-weight:500;color:var(--text-primary);margin-bottom:3px;">{{ $case->title }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);">{{ $case->case_number ?? 'No reference' }} · {{ $case->lawyer->name ?? '—' }}</div>
                    </div>
                    <div class="list-item-right"><span class="status-{{ strtolower($case->status) }}">{{ ucfirst($case->status) }}</span></div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-state-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg></div>
                    <div class="empty-state-title">No cases yet</div>
                    <div class="empty-state-text">Your legal cases will appear here once assigned.</div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div><div class="card-title">Upcoming Appointments</div><div class="card-subtitle">Your next scheduled consultations</div></div>
                <a href="{{ route('client.appointments.index') }}" class="btn-link">View all</a>
            </div>
            <div class="card-body-flush">
                @forelse($appointments ?? [] as $appt)
                <div class="list-item">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(99,102,241,0.1);border:1px solid rgba(255,255,255,0.06);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);">{{ \Carbon\Carbon::parse($appt->appointment_at)->format('M') }}</span>
                        <span style="font-size:1.1rem;font-weight:700;color:var(--purple-light);font-family:'Cormorant Garamond',serif;line-height:1;">{{ \Carbon\Carbon::parse($appt->appointment_at)->format('d') }}</span>
                    </div>
                    <div class="list-item-left">
                        <div style="font-size:0.88rem;font-weight:500;color:var(--text-primary);margin-bottom:3px;">{{ $appt->purpose ?? 'Consultation' }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($appt->appointment_at)->format('g:i A') }} · {{ $appt->lawyer->name ?? '—' }}</div>
                    </div>
                    <div class="list-item-right"><span class="status-{{ strtolower($appt->status) }}">{{ ucfirst($appt->status) }}</span></div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-state-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                    <div class="empty-state-title">No upcoming appointments</div>
                    <div class="empty-state-text">Book a consultation with your assigned lawyer.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('client.appointments.index') }}" class="quick-link">
                    <span class="quick-link-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg></span>
                    Book Appointment
                </a>
                <a href="{{ route('client.invoices.index') }}" class="quick-link">
                    <span class="quick-link-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></span>
                    View Invoices
                </a>
                <a href="{{ route('client.messages.list') }}" class="quick-link">
                    <span class="quick-link-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></span>
                    Message Lawyer
                </a>
                <a href="{{ route('client.cases.index') }}" class="quick-link">
                    <span class="quick-link-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></span>
                    My Cases & Docs
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Recent Invoices</div><a href="{{ route('client.invoices.index') }}" class="btn-link">View all</a></div>
            <div class="card-body-flush">
                @forelse($recentInvoices ?? [] as $invoice)
                <div class="list-item-compact">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:0.82rem;font-weight:500;color:var(--text-primary);margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</div>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                        <span style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:600;color:var(--text-primary);">&#8369;{{ number_format($invoice->total, 2) }}</span>
                        <span class="status-{{ strtolower($invoice->status) }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:28px 20px;"><div class="empty-state-text">No invoices yet.</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
