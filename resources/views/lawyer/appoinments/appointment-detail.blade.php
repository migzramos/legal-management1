@extends('lawyer.layout')

@section('title', 'Appointment Details')

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('lawyer.appointments.index') }}" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); text-decoration: none; transition: all 0.2s;"
                onmouseover="this.style.borderColor='var(--purple-core)';"
                onmouseout="this.style.borderColor='var(--border)';">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </a>
            <div>
                <h1>{{ $appointment->purpose ?? 'Appointment' }}</h1>
                <p>{{ $appointment->appointment_at->format('l, F j, Y • g:i A') }}</p>
            </div>
        </div>
    </div>
    <div class="topbar-right">
        @if($appointment->status === 'pending')
        <form method="POST" action="{{ route('lawyer.appointments.confirm', $appointment->id) }}" style="display: inline;">
            @csrf
            @method('PATCH')
            <button type="submit" style="padding: 10px 16px; background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                onmouseover="this.style.background='rgba(16,185,129,0.2)';"
                onmouseout="this.style.background='rgba(16,185,129,0.12)';">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 6px;">
                    <polyline points="20 6 9 17 4 12"/></svg>
                Confirm Appointment
            </button>
        </form>
        @endif
    </div>
</div>

<div class="content">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Main Details -->
        <div>
            <!-- Status Card -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body" style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 4px;">Status</div>
                        <div style="font-size: 1.3rem; font-weight: 600;">{{ ucfirst($appointment->status) }}</div>
                    </div>
                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; background: 
                        @if($appointment->status === 'confirmed') rgba(16,185,129,0.15); color: #10b981;
                        @elseif($appointment->status === 'pending') rgba(245,158,11,0.15); color: #f59e0b;
                        @elseif($appointment->status === 'cancelled') rgba(248,113,113,0.15); color: #f87171;
                        @else rgba(99,102,241,0.15); color: #6366f1; @endif
                        border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                            <circle cx="12" cy="12" r="10"/>
                            @if($appointment->status === 'confirmed')
                            <polyline points="16 12 12 8 8 12"/>
                            @elseif($appointment->status === 'pending')
                            <line x1="12" y1="6" x2="12" y2="18"/>
                            @elseif($appointment->status === 'cancelled')
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            @endif
                        </svg>
                        {{ $appointment->status }}
                    </span>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Appointment Details</span>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <!-- Date & Time -->
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Date & Time</div>
                            <div style="font-size: 1rem; font-weight: 600;">{{ $appointment->appointment_at->format('l, F j, Y') }}</div>
                            <div style="font-size: 0.95rem; color: var(--text-muted); margin-top: 4px;">{{ $appointment->appointment_at->format('g:i A') }}</div>
                        </div>

                        <!-- Duration -->
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Duration</div>
                            <div style="font-size: 1rem; font-weight: 600;">{{ $appointment->duration_minutes }} minutes</div>
                            <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 4px;">{{ floor($appointment->duration_minutes / 60) }}h {{ $appointment->duration_minutes % 60 }}m</div>
                        </div>

                        <!-- Purpose -->
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Purpose</div>
                            <div style="font-size: 1rem; font-weight: 600;">{{ $appointment->purpose ?? 'N/A' }}</div>
                        </div>

                        <!-- Hourly Rate -->
                        <div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Hourly Rate</div>
                            <div style="font-size: 1rem; font-weight: 600;">{{ money_display($appointment->hourly_rate ?? 0) }}</div>
                        </div>
                    </div>

                    <!-- Total Cost -->
                    @php
                        $totalCost = ($appointment->hourly_rate ?? 0) * ($appointment->duration_minutes / 60);
                    @endphp
                    <div style="padding-top: 20px; border-top: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 1rem; color: var(--text-muted);">Estimated Total Cost</span>
                            <span style="font-size: 1.3rem; font-weight: 700; color: var(--purple-light)">{{ money_display($totalCost) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Client Info -->
            <div class="card" style="margin-bottom: 16px;">
                <div class="card-header">
                    <span class="card-title">Client Information</span>
                </div>
                <div class="card-body">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div style="width: 40px; height: 40px; background: rgba(124,58,237,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--purple-light);">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 600;">{{ $appointment->client->name }}</div>
                            <a href="mailto:{{ $appointment->client->email }}" style="font-size: 0.85rem; color: var(--purple-light); text-decoration: none;">{{ $appointment->client->email }}</a>
                        </div>
                    </div>
                    <div style="padding: 12px; background: rgba(124,58,237,0.06); border-radius: 8px; font-size: 0.9rem;">
                        <div style="color: var(--text-muted); margin-bottom: 4px;">Phone</div>
                        <a href="tel:{{ $appointment->client->phone }}" style="color: var(--purple-light); text-decoration: none; font-weight: 500;">{{ $appointment->client->phone ?? 'N/A' }}</a>
                    </div>
                </div>
            </div>


            <!-- Actions -->
            @if(in_array($appointment->status, ['pending', 'confirmed']))
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Actions</span>
                </div>
                <div class="card-body">
                    @if($appointment->status === 'pending')
                    <form method="POST" action="{{ route('lawyer.appointments.confirm', $appointment->id) }}" style="margin-bottom: 8px;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="width: 100%; padding: 10px 12px; background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.2s; font-size: 0.9rem;"
                            onmouseover="this.style.background='rgba(16,185,129,0.2)';"
                            onmouseout="this.style.background='rgba(16,185,129,0.12)';">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 6px;">
                                <polyline points="20 6 9 17 4 12"/></svg>
                            Confirm
                        </button>
                    </form>
                    @endif
                    
                    <form method="POST" action="{{ route('lawyer.appointments.cancel', $appointment->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" onclick="return confirm('Are you sure you want to cancel this appointment?')" style="width: 100%; padding: 10px 12px; background: rgba(248,113,113,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.3); border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.2s; font-size: 0.9rem;"
                            onmouseover="this.style.background='rgba(248,113,113,0.2)';"
                            onmouseout="this.style.background='rgba(248,113,113,0.12)';">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 6px;">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
