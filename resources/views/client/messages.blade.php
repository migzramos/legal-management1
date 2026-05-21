@extends('layouts.client')
@section('title', 'Messages')
@section('content')

<div class="section-header">
    <div>
        <h1 class="section-title">Messages</h1>
        <p class="section-subtitle">Communicate directly with your legal team.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:18px;height:calc(100vh - 220px);min-height:500px;">

    {{-- Contacts sidebar --}}
    <aside class="card" style="display:flex;flex-direction:column;overflow:hidden;">
        <div class="card-header" style="padding:14px 18px;">
            <div class="card-title" style="font-size:0.95rem;">Lawyers</div>
        </div>
        <div style="flex:1;overflow-y:auto;">
            @forelse($contacts ?? [] as $lawyer)
            <a href="{{ route('client.messages.list', ['with' => $lawyer->id]) }}"
               style="display:flex;gap:12px;align-items:center;padding:12px 16px;border-bottom:1px solid var(--border);text-decoration:none;transition:background 0.2s;{{ ($selectedLawyer->id ?? null) === $lawyer->id ? 'background:var(--purple-dim);' : '' }}"
               onmouseover="if('{{ ($selectedLawyer->id ?? null) === $lawyer->id ? '1' : '0' }}'!='1')this.style.background='rgba(255,255,255,0.03)';"
               onmouseout="if('{{ ($selectedLawyer->id ?? null) === $lawyer->id ? '1' : '0' }}'!='1')this.style.background='';">
                <div class="message-avatar" style="width:36px;height:36px;font-size:0.82rem;flex-shrink:0;">
                    {{ strtoupper(substr($lawyer->name,0,1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.85rem;font-weight:500;color:var(--text-primary);margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lawyer->name }}</div>
                    @if($lawyer->last_message)
                    <div style="font-size:0.72rem;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ Str::limit($lawyer->last_message->body ?? $lawyer->last_message->message ?? '', 36) }}
                    </div>
                    @else
                    <div style="font-size:0.72rem;color:var(--text-muted);">{{ $lawyer->email }}</div>
                    @endif
                </div>
                @if(($lawyer->unread_count ?? 0) > 0)
                <span style="min-width:18px;height:18px;border-radius:99px;background:var(--purple-core);color:#fff;font-size:0.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 5px;flex-shrink:0;">{{ $lawyer->unread_count }}</span>
                @endif
            </a>
            @empty
            <div class="empty-state" style="padding:32px 16px;">
                <div class="empty-state-text">No lawyers assigned yet.</div>
            </div>
            @endforelse
        </div>
    </aside>

    {{-- Chat area --}}
    <section class="card" style="display:flex;flex-direction:column;overflow:hidden;">
        @if(isset($selectedLawyer))
        <div class="card-header" style="padding:14px 20px;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="message-avatar">{{ strtoupper(substr($selectedLawyer->name,0,1)) }}</div>
                <div>
                    <div style="font-weight:600;font-size:0.9rem;color:var(--text-primary);">{{ $selectedLawyer->name }}</div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">{{ $selectedLawyer->email }}</div>
                </div>
            </div>
        </div>

        <div id="messageThread" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:14px;">
            @forelse($messages as $msg)
            @php $isMine = $msg->sender_id === Auth::id(); @endphp
            <div style="display:flex;align-items:flex-end;gap:10px;{{ $isMine ? 'flex-direction:row-reverse;' : '' }}">
                @if(!$isMine)
                <div class="message-avatar" style="width:30px;height:30px;font-size:0.72rem;flex-shrink:0;">{{ strtoupper(substr($selectedLawyer->name,0,1)) }}</div>
                @endif
                <div style="max-width:65%;">
                    <div class="message-bubble {{ $isMine ? 'sent' : 'received' }}">{{ $msg->body ?? $msg->message }}</div>
                    <div class="message-time" style="{{ $isMine ? 'text-align:right;' : '' }}">{{ \Carbon\Carbon::parse($msg->created_at)->format('g:i A') }}</div>
                </div>
            </div>
            @empty
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:10px;padding:40px;">
                <div style="width:48px;height:48px;border-radius:12px;background:var(--purple-dim);display:flex;align-items:center;justify-content:center;color:var(--purple-light);">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--text-primary);">Start the conversation</div>
                <div style="font-size:0.78rem;color:var(--text-muted);">Send a message to {{ $selectedLawyer->name }}</div>
            </div>
            @endforelse
        </div>

        <div style="padding:14px 18px;border-top:1px solid var(--border);display:flex;gap:10px;align-items:flex-end;flex-shrink:0;">
            <form method="POST" action="{{ route('client.messages.store') }}" style="display:flex;gap:10px;width:100%;align-items:flex-end;">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $selectedLawyer->id }}">
                <textarea name="body" class="message-input" placeholder="Type a message…" rows="1"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.closest('form').submit();}"></textarea>
                <button type="submit" class="btn-send" title="Send">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>

        @else
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:12px;padding:40px;">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--purple-dim);border:1px solid var(--purple-glow);display:flex;align-items:center;justify-content:center;color:var(--purple-light);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:22px;height:22px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:var(--text-primary);">Select a Lawyer</div>
            <div style="font-size:0.82rem;color:var(--text-muted);max-width:260px;">Choose a lawyer from the list to start a conversation.</div>
        </div>
        @endif
    </section>
</div>

<script>
const thread = document.getElementById('messageThread');
if (thread) thread.scrollTop = thread.scrollHeight;
</script>
@endsection
