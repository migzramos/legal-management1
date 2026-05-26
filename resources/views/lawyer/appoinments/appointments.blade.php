@extends('layouts.lawyer')

@section('title', 'Appointments')

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <h1>Appointments</h1>
        <p>View and manage all your client appointments</p>
    </div>
    <div class="topbar-right">
        <a href="{{ route('lawyer.calendar.index') }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            View Calendar
        </a>
    </div>
</div>

<div class="content">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 20px;">
        <!-- Appointments List -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">All Appointments</span>
                <span style="background: rgba(124,58,237,0.15); color: var(--purple-light); padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">{{ $appointments->count() }}</span>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($appointments as $appt)
                <div style="display: flex; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--border); align-items: flex-start; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(124,58,237,0.03)';"
                    onmouseout="this.style.background='transparent';">
                    
                    <!-- Date Box -->
                    <div style="background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.2); border-radius: 10px; padding: 10px 12px; text-align: center; flex-shrink: 0; min-width: 56px;">
                        <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--purple-light); font-weight: 600;">{{ $appt->appointment_at->format('M') }}</div>
                        <div style="font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 600; line-height: 1;">{{ $appt->appointment_at->format('d') }}</div>
                    </div>
                    
                    <!-- Info -->
                    <div style="flex: 1;">
                        <div style="font-size: 0.95rem; font-weight: 600; margin-bottom: 4px;">{{ $appt->purpose ?? 'Appointment' }}</div>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; font-size: 0.8rem; color: var(--text-muted);">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $appt->appointment_at->format('g:i A') }}
                            </div>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ $appt->client->name ?? 'N/A' }}
                            </div>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $appt->duration_minutes }} min
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div style="display: flex; gap: 8px;">
                            <span style="display: inline-block; padding: 4px 8px; background: 
                                @if($appt->status === 'confirmed') rgba(16,185,129,0.15); color: #10b981;
                                @elseif($appt->status === 'pending') rgba(245,158,11,0.15); color: #f59e0b;
                                @elseif($appt->status === 'cancelled') rgba(248,113,113,0.15); color: #f87171;
                                @else rgba(99,102,241,0.15); color: #6366f1; @endif
                                border-radius: 6px; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                                {{ $appt->status }}
                            </span>
                            
                            @if($appt->status === 'pending')
                            <form method="POST" action="{{ route('lawyer.appointments.confirm', $appt->id) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="padding: 4px 10px; background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.3); border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='rgba(16,185,129,0.2)';"
                                    onmouseout="this.style.background='rgba(16,185,129,0.12)';">Confirm</button>
                            </form>
                            @endif
                            
                            @if(in_array($appt->status, ['pending', 'confirmed']))
                            <form method="POST" action="{{ route('lawyer.appointments.cancel', $appt->id) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" onclick="return confirm('Cancel this appointment?')" style="padding: 4px 10px; background: rgba(248,113,113,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.3); border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='rgba(248,113,113,0.2)';"
                                    onmouseout="this.style.background='rgba(248,113,113,0.12)';">Cancel</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding: 40px 20px; text-align: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin: 0 auto 12px; opacity: 0.5;">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <h3 style="margin: 0 0 8px 0; font-size: 1rem;">No appointments</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Your appointments will appear here</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Stats Sidebar -->
        <div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Statistics</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    @php
                        $pending = $appointments->where('status', 'pending')->count();
                        $confirmed = $appointments->where('status', 'confirmed')->count();
                        $cancelled = $appointments->where('status', 'cancelled')->count();
                        $total = $appointments->count();
                    @endphp
                    
                    <div style="padding: 16px; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.9rem; color: var(--text-muted);">Total</span>
                            <span style="font-size: 1.3rem; font-weight: 700; color: var(--text-primary);">{{ $total }}</span>
                        </div>
                    </div>
                    
                    <div style="padding: 16px; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.9rem; color: #f59e0b; font-weight: 500;">Pending</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: #f59e0b;">{{ $pending }}</span>
                        </div>
                        <div style="width: 100%; background: rgba(245,158,11,0.1); height: 4px; border-radius: 2px; overflow: hidden;">
                            <div style="background: #f59e0b; height: 100%; width: {{ $total > 0 ? ($pending / $total) * 100 : 0 }}%; border-radius: 2px;"></div>
                        </div>
                    </div>
                    
                    <div style="padding: 16px; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.9rem; color: #10b981; font-weight: 500;">Confirmed</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: #10b981;">{{ $confirmed }}</span>
                        </div>
                        <div style="width: 100%; background: rgba(16,185,129,0.1); height: 4px; border-radius: 2px; overflow: hidden;">
                            <div style="background: #10b981; height: 100%; width: {{ $total > 0 ? ($confirmed / $total) * 100 : 0 }}%; border-radius: 2px;"></div>
                        </div>
                    </div>
                    
                    <div style="padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.9rem; color: #f87171; font-weight: 500;">Cancelled</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: #f87171;">{{ $cancelled }}</span>
                        </div>
                        <div style="width: 100%; background: rgba(248,113,113,0.1); height: 4px; border-radius: 2px; overflow: hidden;">
                            <div style="background: #f87171; height: 100%; width: {{ $total > 0 ? ($cancelled / $total) * 100 : 0 }}%; border-radius: 2px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 16px;">
                <div class="card-header">
                    <span class="card-title">Quick Actions</span>
                </div>
                <div class="card-body">
                    <a href="{{ route('lawyer.calendar.index') }}" style="display: block; padding: 12px; background: rgba(124,58,237,0.12); color: var(--purple-light); border: 1px solid rgba(124,58,237,0.3); border-radius: 8px; text-align: center; text-decoration: none; font-weight: 500; font-size: 0.9rem; margin-bottom: 8px; transition: all 0.2s;"
                        onmouseover="this.style.background='rgba(124,58,237,0.2)';"
                        onmouseout="this.style.background='rgba(124,58,237,0.12)';">
                        View Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
