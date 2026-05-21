@extends('lawyer.layout')

@section('title', 'Appointment Messages')

@section('content')
<div class="topbar">
    <div class="topbar-left">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('lawyer.appointments.show', $appointment->id) }}"
               style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); text-decoration: none; transition: border-color 0.2s;"
               onmouseover="this.style.borderColor='var(--purple-core)';"
               onmouseout="this.style.borderColor='var(--border)';">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
            </a>
            <div>
                <h1>{{ $appointment->purpose ?? 'Appointment Messages' }}</h1>
                <p>{{ $appointment->appointment_at->format('l, F j, Y • g:i A') }} &mdash; {{ $appointment->client->name }}</p>
            </div>
        </div>
    </div>
</div>

<div class="content">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <style>
        .messages-container { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; height: calc(100vh - 220px); }
        .messages-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
        .client-avatar { width: 40px; height: 40px; border-radius: 50%; background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.9rem; flex-shrink: 0; color: var(--purple-light); }
        .messages-body { flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
        .message-bubble { max-width: 70%; display: flex; flex-direction: column; gap: 4px; }
        .message-bubble.sent { align-self: flex-end; align-items: flex-end; }
        .message-bubble.received { align-self: flex-start; align-items: flex-start; }
        .bubble-content { padding: 12px 16px; border-radius: 16px; font-size: 0.88rem; line-height: 1.6; }
        .message-bubble.sent .bubble-content { background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); color: #fff; border-radius: 16px 16px 4px 16px; }
        .message-bubble.received .bubble-content { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px 16px 16px 4px; }
        .bubble-time { font-size: 0.7rem; color: var(--text-muted); padding: 0 4px; }
        .bubble-sender { font-size: 0.72rem; color: var(--text-muted); padding: 0 4px; }
        .messages-input { padding: 16px 24px; border-top: 1px solid var(--border); flex-shrink: 0; }
        .input-row { display: flex; gap: 10px; align-items: flex-end; }
        .message-textarea { flex: 1; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 12px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.9rem; outline: none; resize: none; min-height: 46px; max-height: 120px; transition: border-color 0.2s; }
        .message-textarea:focus { border-color: var(--purple-core); }
        .message-textarea::placeholder { color: var(--text-muted); }
        .btn-send { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 12px rgba(124,58,237,0.3); transition: opacity 0.2s; flex-shrink: 0; }
        .btn-send:hover { opacity: 0.9; }
        .btn-send svg { width: 18px; height: 18px; color: #fff; }
        .appt-info-bar { padding: 10px 24px; background: rgba(124,58,237,0.05); border-bottom: 1px solid var(--border); font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 20px; flex-shrink: 0; }
        .appt-info-bar span { display: flex; align-items: center; gap: 4px; }
    </style>

    <div class="messages-container">
        {{-- Header --}}
        <div class="messages-header">
            <div class="client-avatar">{{ strtoupper(substr($appointment->client->name ?? 'C', 0, 1)) }}</div>
            <div style="flex: 1;">
                <div style="font-size: 0.95rem; font-weight: 500;">{{ $appointment->client->name }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $appointment->client->email }}</div>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.15); border-radius: 20px; padding: 3px 10px;">
                {{ $appointment->appointment_at->format('M d, Y • g:i A') }}
            </div>
        </div>

        {{-- Appointment Info Bar --}}
        <div class="appt-info-bar">
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ $appointment->duration_minutes }} min
            </span>
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                {{ money_display($appointment->total_cost) }}
            </span>
            <span style="color: {{ $appointment->status === 'confirmed' ? 'var(--success)' : 'var(--warning)' }}">
                ● {{ ucfirst($appointment->status) }}
            </span>
        </div>

        {{-- Messages Body --}}
        <div class="messages-body" id="messagesBody">
            @forelse($messages as $message)
            <div class="message-bubble {{ $message->sender_id === auth()->id() ? 'sent' : 'received' }}"
                 data-message-id="{{ $message->id }}">
                @if($message->sender_id !== auth()->id())
                <div class="bubble-sender">{{ $message->sender->name ?? 'Client' }}</div>
                @endif
                <div class="bubble-content">{{ $message->body }}</div>
                <div class="bubble-time">{{ $message->created_at->format('M d, g:i A') }}</div>
            </div>
            @empty
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 16px; color: var(--text-muted);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; opacity: 0.2;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                <p style="font-size: 0.9rem;">No messages yet. Start the conversation.</p>
            </div>
            @endforelse
        </div>

        {{-- Message Input --}}
        <div class="messages-input">
            <form method="POST" action="{{ route('lawyer.messages.store') }}" id="messageForm">
                @csrf
                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                <input type="hidden" name="receiver_id" value="{{ $appointment->client_id }}">
                <div class="input-row">
                    <textarea name="body" class="message-textarea" id="messageBody"
                        placeholder="Type your message to the client..." rows="1" required
                        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('messageForm').submit();}"></textarea>
                    <button type="submit" class="btn-send">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom on load
    const body = document.getElementById('messagesBody');
    if (body) body.scrollTop = body.scrollHeight;

    // Polling fallback for real-time delivery
    let lastMessageId = {{ $messages->last()?->id ?? 0 }};

    function pollMessages() {
        fetch(`{{ route('lawyer.appointments.messages', $appointment->id) }}?after=${lastMessageId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
            if (r.ok && r.headers.get('content-type')?.includes('application/json')) {
                return r.json();
            }
            return null;
        })
        .then(data => {
            if (data?.messages?.length) {
                data.messages.forEach(msg => {
                    if (document.querySelector(`[data-message-id="${msg.id}"]`)) return;
                    lastMessageId = Math.max(lastMessageId, msg.id);
                    const isSent = msg.sender_id === {{ auth()->id() }};
                    const div = document.createElement('div');
                    div.className = `message-bubble ${isSent ? 'sent' : 'received'}`;
                    div.dataset.messageId = msg.id;
                    div.innerHTML = `
                        ${!isSent ? `<div class="bubble-sender">${msg.sender_name}</div>` : ''}
                        <div class="bubble-content">${msg.body}</div>
                        <div class="bubble-time">${msg.created_at}</div>`;
                    body.appendChild(div);
                    body.scrollTop = body.scrollHeight;
                });
            }
        })
        .catch(() => {});
    }

    setInterval(pollMessages, 5000);
</script>
@endsection
