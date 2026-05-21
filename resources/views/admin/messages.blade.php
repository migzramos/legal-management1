<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @include('admin.partials.styles')
    <style>
        .messages-wrap {
            display: grid;
            grid-template-columns: 300px 1fr;
            height: calc(100vh - 73px);
            overflow: hidden;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        /* ── Sidebar ── */
        .msg-sidebar {
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .msg-sidebar-header {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .msg-sidebar-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        .msg-search-wrap { position: relative; }
        .msg-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 14px;
            height: 14px;
            pointer-events: none;
        }
        .msg-search-input {
            width: 100%;
            padding: 8px 12px 8px 32px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.83rem;
            outline: none;
            transition: border-color 0.22s;
            box-sizing: border-box;
        }
        .msg-search-input:focus { border-color: var(--purple-core); }
        .msg-search-input::placeholder { color: var(--text-muted); }

        .conv-list { flex: 1; overflow-y: auto; }
        .conv-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .conv-item:hover { background: rgba(255,255,255,0.03); }
        .conv-item.active {
            background: rgba(124,58,237,0.1);
            border-left: 3px solid var(--purple-core);
            padding-left: 13px;
        }
        .conv-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 600;
            color: #fff;
            flex-shrink: 0;
        }
        .conv-body { flex: 1; min-width: 0; }
        .conv-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }
        .conv-name {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }
        .conv-time { font-size: 0.68rem; color: var(--text-muted); white-space: nowrap; }
        .conv-role { font-size: 0.7rem; color: var(--text-muted); margin-bottom: 2px; }
        .conv-preview {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .conv-unread {
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: var(--purple-core);
            color: #fff;
            font-size: 0.62rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ── Main chat ── */
        .msg-main {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .msg-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            background: rgba(255,255,255,0.02);
        }
        .msg-header-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.88rem;
            color: #fff;
            flex-shrink: 0;
        }
        .msg-header-name { font-size: 0.92rem; font-weight: 500; color: var(--text-primary); }
        .msg-header-status {
            font-size: 0.72rem;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
        }
        .msg-header-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
        }

        /* ── Bubbles ── */
        .msg-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .bubble-wrap { display: flex; flex-direction: column; }
        .bubble-wrap.sent { align-items: flex-end; }
        .bubble-wrap.received { align-items: flex-start; }

        .bubble {
            max-width: 62%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 0.86rem;
            line-height: 1.6;
            word-break: break-word;
        }
        .bubble.sent {
            background: linear-gradient(135deg, var(--purple-core), var(--purple-light));
            color: #fff;
            border-radius: 16px 16px 4px 16px;
        }
        .bubble.received {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 16px 16px 16px 4px;
        }
        .bubble-time {
            font-size: 0.67rem;
            color: var(--text-muted);
            margin-top: 4px;
            padding: 0 2px;
        }

        /* ── Date divider ── */
        .date-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 0.72rem;
            margin: 6px 0;
        }
        .date-divider::before,
        .date-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Input area ── */
        .msg-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
            background: rgba(255,255,255,0.01);
        }
        .msg-input-row { display: flex; gap: 10px; align-items: flex-end; }
        .msg-textarea {
            flex: 1;
            padding: 10px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.86rem;
            outline: none;
            resize: none;
            transition: border-color 0.22s;
            box-sizing: border-box;
            max-height: 120px;
        }
        .msg-textarea:focus { border-color: var(--purple-core); box-shadow: 0 0 0 3px rgba(124,58,237,0.14); }
        .msg-textarea::placeholder { color: var(--text-muted); }

        /* ── Empty / no-select state ── */
        .msg-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            color: var(--text-muted);
        }
        .msg-empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(124,58,237,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .msg-empty-icon svg { width: 24px; height: 24px; color: var(--purple-light); opacity: 0.6; }
        .msg-empty p { font-size: 0.84rem; }
    </style>
</head>
<body>
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="main-area">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Messages</div>
                <div class="topbar-subtitle">Communicate with your team and clients</div>
            </div>
        </div>

        <div class="page-content" style="padding-bottom: 0; height: calc(100vh - 73px); overflow: hidden;">
            <div class="messages-wrap">

                <!-- Sidebar -->
                <div class="msg-sidebar">
                    <div class="msg-sidebar-header">
                        <div class="msg-sidebar-title">Conversations</div>
                        <div class="msg-search-wrap">
                            <svg class="msg-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input type="text" class="msg-search-input" placeholder="Search conversations..." id="convSearch">
                        </div>
                    </div>

                    <div class="conv-list" id="convList">
                        @php $colors = ['#7c3aed','#059669','#dc2626','#d97706','#0891b2','#db2777']; @endphp
                        @foreach($users as $u)
                        @php
                            $lastMsg  = $conversations->get($u->id)?->first();
                            $unread   = $conversations->get($u->id)?->where('receiver_id', auth()->id())->where('is_read', false)->count() ?? 0;
                            $color    = $colors[ord($u->name[0]) % count($colors)];
                        @endphp
                        <a href="{{ route('admin.messages', ['user' => $u->id]) }}"
                           class="conv-item {{ isset($activeUser) && $activeUser->id === $u->id ? 'active' : '' }}">
                            <div class="conv-avatar" style="background: {{ $color }}">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div class="conv-body">
                                <div class="conv-top">
                                    <span class="conv-name">{{ $u->name }}</span>
                                    <div style="display:flex;align-items:center;gap:5px;">
                                        @if($unread > 0)
                                            <span class="conv-unread">{{ $unread }}</span>
                                        @endif
                                        @if($lastMsg)
                                            <span class="conv-time">{{ $lastMsg->created_at->diffForHumans(null, true) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="conv-role">{{ ucfirst($u->role) }}</div>
                                <div class="conv-preview">{{ $lastMsg?->body ?? 'No messages yet' }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Main Chat -->
                <div class="msg-main">
                    @if(isset($activeUser))
                    @php $activeColor = $colors[ord($activeUser->name[0]) % count($colors)]; @endphp

                    <!-- Header -->
                    <div class="msg-header">
                        <div class="msg-header-avatar" style="background: {{ $activeColor }}">
                            {{ strtoupper(substr($activeUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="msg-header-name">{{ $activeUser->name }}</div>
                            <div class="msg-header-status">{{ ucfirst($activeUser->role) }}</div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="msg-body" id="msgBody">
                        @forelse($messages as $msg)
                        <div class="bubble-wrap {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}">
                            <div class="bubble {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}">
                                {{ $msg->body }}
                            </div>
                            <div class="bubble-time">{{ $msg->created_at->format('g:i A') }}</div>
                        </div>
                        @empty
                        <div class="msg-empty">
                            <div class="msg-empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                                </svg>
                            </div>
                            <p>Start the conversation with {{ $activeUser->name }}</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Input -->
                    <div class="msg-footer">
                        <form method="POST" action="{{ route('admin.messages.store') }}">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $activeUser->id }}">
                            <div class="msg-input-row">
                                <textarea
                                    name="body"
                                    class="msg-textarea"
                                    placeholder="Type a message... (Enter to send)"
                                    rows="1"
                                    required
                                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
                                <button type="submit" class="btn-send">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    @else

                    <!-- No conversation selected -->
                    <div class="msg-empty">
                        <div class="msg-empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                        </div>
                        <p>Select a conversation to start messaging</p>
                    </div>

                    @endif
                </div>

            </div>
        </div>
    </main>
</div>

<script>
    // Auto-scroll to bottom
    const msgBody = document.getElementById('msgBody');
    if (msgBody) msgBody.scrollTop = msgBody.scrollHeight;

    // Live search filter
    document.getElementById('convSearch')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#convList .conv-item').forEach(item => {
            const name = item.querySelector('.conv-name')?.textContent.toLowerCase() ?? '';
            item.style.display = name.includes(q) ? '' : 'none';
        });
    });

    // Auto-resize textarea
    document.querySelector('.msg-textarea')?.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
</script>
</body>
</html>