<!-- Appointments List Partial for Lawyer Views -->
<!-- Pass $appointments collection and $context (case|document) -->

@if(isset($appointments) && $appointments->count() > 0)
<div style="margin-top: 24px; padding: 20px; background: rgba(124,58,237,0.06); border: 1px solid rgba(124,58,237,0.15); border-radius: 14px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--purple-light);">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Related Appointments</h3>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
        @foreach($appointments as $appt)
        <div style="display: flex; flex-direction: column; padding: 14px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; transition: all 0.2s; hover: border-color: var(--purple-core); hover: box-shadow: 0 4px 12px rgba(124,58,237,0.1);">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--purple-light); margin-bottom: 2px;">
                        {{ $appt->appointment_at->format('M d, Y') }}
                    </div>
                    <div style="font-size: 0.9rem; font-weight: 500;">{{ $appt->purpose ?? 'Appointment' }}</div>
                </div>
                <span style="display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; background: 
                    @if($appt->status === 'confirmed') rgba(16,185,129,0.15); color: #10b981;
                    @elseif($appt->status === 'pending') rgba(245,158,11,0.15); color: #f59e0b;
                    @elseif($appt->status === 'cancelled') rgba(248,113,113,0.15); color: #f87171;
                    @else rgba(99,102,241,0.15); color: #6366f1; @endif
                    border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ $appt->status }}
                </span>
            </div>
            
            <!-- Details -->
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>{{ $appt->appointment_at->format('g:i A') }} • {{ $appt->duration_minutes }} min</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; flex-shrink: 0;">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ $appt->client->name ?? 'N/A' }}</span>
                </div>
                @if($appt->case)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; flex-shrink: 0;">
                        <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    <span>{{ $appt->case->case_number }}</span>
                </div>
                @endif
            </div>
            
            <!-- Cost -->
            @php
                $cost = ($appt->hourly_rate ?? 0) * (($appt->duration_minutes ?? 60) / 60);
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid var(--border); margin-bottom: 12px;">
                <span style="color: var(--text-muted); font-size: 0.85rem;">Estimated Cost</span>
                <span style="font-weight: 600; color: var(--text-primary);">{{ money_display($cost) }}</span>
            </div>
            
            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
                @if($appt->status === 'pending')
                <form method="POST" action="{{ route('lawyer.appointments.confirm', $appt->id) }}" style="flex: 1;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" style="width: 100%; padding: 8px 12px; background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.3); border-radius: 6px; font-size: 0.82rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='rgba(16,185,129,0.2)'; this.style.borderColor='rgba(16,185,129,0.5)';"
                        onmouseout="this.style.background='rgba(16,185,129,0.12)'; this.style.borderColor='rgba(16,185,129,0.3)';">
                        Confirm
                    </button>
                </form>
                @endif
                
                <a href="{{ route('lawyer.appointments.show', $appt->id) }}" style="flex: 1; padding: 8px 12px; background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border); border-radius: 6px; font-size: 0.82rem; font-weight: 500; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none;"
                    onmouseover="this.style.borderColor='var(--purple-core)'; this.style.background='rgba(124,58,237,0.06)';"
                    onmouseout="this.style.borderColor='var(--border)'; this.style.background='var(--bg-card)';">
                    View
                </a>
                
                @if($appt->case)
                <a href="{{ route('lawyer.cases.show', $appt->case->id) }}" style="flex: 1; padding: 8px 12px; background: rgba(124,58,237,0.12); color: var(--purple-light); border: 1px solid rgba(124,58,237,0.3); border-radius: 6px; font-size: 0.82rem; font-weight: 500; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none;"
                    onmouseover="this.style.background='rgba(124,58,237,0.2)'; this.style.borderColor='rgba(124,58,237,0.5)';"
                    onmouseout="this.style.background='rgba(124,58,237,0.12)'; this.style.borderColor='rgba(124,58,237,0.3)';">
                    Case
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
