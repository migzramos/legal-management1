@extends('layouts.client')
@section('title', 'My Cases')
@section('content')

<div class="section-header">
    <div>
        <h1 class="section-title">My Cases</h1>
        <p class="section-subtitle">All your active and past legal matters.</p>
    </div>
</div>

<div class="tabs" style="margin-bottom:20px;">
    @foreach(['all' => 'All Cases', 'active' => 'Active', 'resolved' => 'Resolved'] as $key => $label)
    <a href="{{ route('client.cases.index', ['status' => $key === 'all' ? null : $key]) }}"
       class="tab {{ ($activeTab ?? 'all') === $key ? 'active' : '' }}">
        {{ $label }}<span class="tab-badge">{{ $tabCounts[$key] ?? 0 }}</span>
    </a>
    @endforeach
</div>

<div style="display:flex;flex-direction:column;gap:14px;">
    @forelse($cases as $case)
    <div class="card">
        <div class="card-body">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px;">
                <div style="min-width:0;">
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:600;color:var(--text-primary);margin-bottom:4px;">{{ $case->title }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">
                        Ref: {{ $case->case_number ?? 'N/A' }} &nbsp;·&nbsp;
                        Lawyer: {{ $case->lawyer->name ?? '—' }} &nbsp;·&nbsp;
                        Opened: {{ \Carbon\Carbon::parse($case->created_at)->format('M d, Y') }}
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    @php
                        $sc = match($case->status) {
                            'intake'               => 'badge-pending',
                            'barangay_mediation'   => 'badge-pending',
                            'escalation_to_court'  => 'badge-active',
                            'active_case'          => 'badge-active',
                            'resolution'           => 'badge-completed',
                            default                => 'badge-pending',
                        };
                        $sl = match($case->status) {
                            'intake'               => 'Intake',
                            'barangay_mediation'   => 'Barangay Mediation',
                            'escalation_to_court'  => 'Escalation to Court',
                            'active_case'          => 'Active Case',
                            'resolution'           => 'Resolved',
                            default                => ucfirst($case->status),
                        };
                    @endphp
                    <span class="badge {{ $sc }}">{{ $sl }}</span>
                    <a href="{{ route('client.cases.show', $case->id) }}" class="btn-icon" title="View case">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @if($case->description)
            <p style="font-size:0.82rem;color:var(--text-secondary);line-height:1.6;margin-bottom:14px;">
                {{ Str::limit($case->description, 180) }}
            </p>
            @endif
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding-top:14px;border-top:1px solid var(--border);">
                <div style="text-align:center;">
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--text-primary);">{{ $case->documents_count ?? 0 }}</div>
                    <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin-top:2px;">Documents</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--text-primary);">
                        {{ \App\Models\Appointment::where('client_id', $case->client_id)->where('lawyer_id', $case->lawyer_id)->count() }}
                    </div>
                    <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin-top:2px;">Appointments</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--text-primary);">{{ $case->invoices_count ?? 0 }}</div>
                    <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin-top:2px;">Invoices</div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
            </div>
            <div class="empty-state-title">No cases found</div>
            <div class="empty-state-text">Cases assigned to you by your lawyer will appear here.</div>
        </div>
    </div>
    @endforelse
</div>

{{ $cases->links() }}
@endsection