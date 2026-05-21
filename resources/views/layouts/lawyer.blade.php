<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — LexCore</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #07060f;
            --bg-2:      #0d0b1a;
            --bg-3:      #120f22;
            --sidebar:   #0a0817;
            --card:      rgba(255,255,255,0.035);
            --card-h:    rgba(255,255,255,0.06);
            --border:    rgba(255,255,255,0.07);
            --border-h:  rgba(139,92,246,0.45);
            --p:         #7c3aed;
            --p2:        #9d62f5;
            --p3:        #c4a1ff;
            --p-glow:    rgba(124,58,237,0.18);
            --t1:        #ede8ff;
            --t2:        rgba(237,232,255,0.65);
            --t3:        rgba(237,232,255,0.38);
            --success:   #22d3a5;
            --warn:      #f59e0b;
            --danger:    #f87171;
            --info:      #60a5fa;
            --ease:      cubic-bezier(0.4,0,0.2,1);
            --sidebar-w: 68px;
        }

        html, body {
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--t1);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.28); border-radius: 9px; }

        /* ── APP SHELL ── */
        .shell { display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 18px 0 16px;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 50;
        }

        .sb-logo {
            width: 38px; height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--p), var(--p2));
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
            box-shadow: 0 6px 22px rgba(124,58,237,0.45);
            flex-shrink: 0;
            cursor: pointer;
        }
        .sb-logo svg { width: 18px; height: 18px; color: #fff; }

        .sb-nav {
            display: flex; flex-direction: column;
            gap: 2px; flex: 1; width: 100%;
            padding: 0 10px;
            overflow-y: auto;
        }

        .nav-link {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: var(--t3);
            position: relative;
            text-decoration: none;
            transition: background 0.2s var(--ease), color 0.2s var(--ease), transform 0.18s var(--ease);
            flex-shrink: 0;
        }
        .nav-link svg { width: 19px; height: 19px; }

        .nav-link::after {
            content: attr(title);
            position: absolute;
            left: calc(100% + 12px);
            background: #1a1530;
            color: var(--t1);
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            padding: 5px 11px;
            border-radius: 7px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            opacity: 0; pointer-events: none;
            transform: translateX(-5px);
            transition: opacity 0.16s, transform 0.16s;
            z-index: 100;
        }
        .nav-link:hover::after { opacity: 1; transform: translateX(0); }
        .nav-link:hover { background: rgba(124,58,237,0.14); color: var(--p3); transform: translateX(2px); }
        .nav-link.active {
            background: rgba(124,58,237,0.2);
            color: var(--p2);
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: -10px; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 24px;
            background: linear-gradient(180deg, var(--p), var(--p2));
            border-radius: 0 3px 3px 0;
        }

        .sb-bottom {
            padding: 0 10px 4px;
            display: flex; flex-direction: column;
            gap: 6px; align-items: center;
        }

        .sb-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--p), var(--p2));
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 600; color: #fff;
            text-decoration: none;
            border: 2px solid rgba(255,255,255,0.09);
            transition: transform 0.2s var(--ease), box-shadow 0.2s var(--ease);
            cursor: pointer;
        }
        .sb-avatar:hover { transform: scale(1.07); box-shadow: 0 4px 16px rgba(124,58,237,0.45); }

        .sb-logout {
            background: transparent; border: none;
            color: var(--t3); cursor: pointer;
            padding: 8px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            transition: color 0.2s, background 0.2s, transform 0.2s;
        }
        .sb-logout:hover { color: var(--danger); background: rgba(248,113,113,0.09); transform: translateX(2px); }
        .sb-logout svg { width: 17px; height: 17px; }

        /* ── MAIN ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── TOPBAR ── */
        .topbar {
            padding: 12px 24px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid var(--border);
            background: rgba(7,6,15,0.9);
            backdrop-filter: blur(20px);
            position: sticky; top: 0; z-index: 40;
            flex-shrink: 0;
            gap: 12px;
        }
        .topbar-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem; font-weight: 600;
            color: var(--t1); letter-spacing: -0.01em;
            line-height: 1.2;
        }
        .topbar-left p { font-size: 0.73rem; color: var(--t3); margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }

        /* ── PAGE CONTENT ── */
        .page { padding: 20px 24px; flex: 1; }

        /* ── BUTTONS ── */
        .btn-primary {
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--p), var(--p2));
            border: none; border-radius: 9px;
            color: #fff; font-family: 'Outfit', sans-serif;
            font-size: 0.83rem; font-weight: 500;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(124,58,237,0.35);
            transition: opacity 0.18s, transform 0.18s, box-shadow 0.18s;
            white-space: nowrap;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(124,58,237,0.45); }
        .btn-primary svg { width: 14px; height: 14px; }

        .btn-secondary {
            padding: 8px 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border); border-radius: 9px;
            color: var(--t1); font-family: 'Outfit', sans-serif;
            font-size: 0.83rem; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none;
            transition: border-color 0.18s, background 0.18s, transform 0.18s;
            white-space: nowrap;
        }
        .btn-secondary:hover { border-color: var(--p); background: rgba(124,58,237,0.08); transform: translateY(-1px); }

        /* ── KPI GRID ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        .kpi-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 20px;
            position: relative; overflow: hidden;
            transition: border-color 0.2s var(--ease), transform 0.2s var(--ease), box-shadow 0.2s var(--ease);
        }
        .kpi-card::after {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, var(--p), transparent);
            opacity: 0; transition: opacity 0.2s;
        }
        .kpi-card:hover { border-color: var(--border-h); transform: translateY(-3px); box-shadow: 0 10px 34px rgba(0,0,0,0.28); }
        .kpi-card:hover::after { opacity: 1; }
        .kpi-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .kpi-label { font-size: 0.67rem; font-weight: 600; letter-spacing: 0.09em; text-transform: uppercase; color: var(--t3); }
        .kpi-icon { width: 34px; height: 34px; border-radius: 9px; background: rgba(124,58,237,0.12); color: var(--p3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .kpi-icon svg { width: 16px; height: 16px; }
        .kpi-value { font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 700; color: var(--t1); line-height: 1; margin-bottom: 4px; }
        .kpi-meta { font-size: 0.72rem; color: var(--t3); }

        /* ── CARD ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .card:hover { border-color: rgba(124,58,237,0.18); box-shadow: 0 6px 28px rgba(0,0,0,0.18); }
        .card + .card { margin-top: 18px; }
        .card-header {
            padding: 15px 18px 12px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid var(--border);
            gap: 10px;
        }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: var(--t1); }
        .card-action { font-size: 0.76rem; color: var(--p3); text-decoration: none; transition: opacity 0.15s; white-space: nowrap; }
        .card-action:hover { opacity: 0.75; text-decoration: underline; }
        .card-body { padding: 4px 0; }

        /* ── STATUS BADGES ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 20px;
            font-size: 0.64rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.06em;
            white-space: nowrap;
        }
        .badge-pending  { background: rgba(245,158,11,0.12); color: var(--warn);    border: 1px solid rgba(245,158,11,0.22); }
        .badge-active   { background: rgba(34,211,165,0.12); color: var(--success); border: 1px solid rgba(34,211,165,0.22); }
        .badge-paid     { background: rgba(34,211,165,0.12); color: var(--success); border: 1px solid rgba(34,211,165,0.22); }
        .badge-open     { background: rgba(96,165,250,0.12); color: var(--info);    border: 1px solid rgba(96,165,250,0.22); }
        .badge-ongoing  { background: rgba(245,158,11,0.12); color: var(--warn);    border: 1px solid rgba(245,158,11,0.22); }
        .badge-overdue  { background: rgba(248,113,113,0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.22); }
        .badge-closed   { background: rgba(255,255,255,0.05); color: var(--t2);     border: 1px solid var(--border); }
        .badge-draft    { background: rgba(255,255,255,0.05); color: var(--t3);     border: 1px solid var(--border); }
        .badge-sent     { background: rgba(96,165,250,0.12); color: var(--info);    border: 1px solid rgba(96,165,250,0.22); }
        .badge-unpaid   { background: rgba(248,113,113,0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.22); }
        .badge-confirmed{ background: rgba(96,165,250,0.12); color: var(--info);    border: 1px solid rgba(96,165,250,0.22); }
        .badge-completed{ background: rgba(34,211,165,0.12); color: var(--success); border: 1px solid rgba(34,211,165,0.22); }
        .badge-cancelled{ background: rgba(248,113,113,0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.22); }

        /* ── EMPTY STATE ── */
        .empty {
            padding: 32px 20px;
            text-align: center;
            color: var(--t3);
        }
        .empty h4 { font-family: 'Playfair Display', serif; font-size: 0.95rem; color: var(--t2); margin-bottom: 4px; }
        .empty p { font-size: 0.78rem; line-height: 1.55; }

        /* ── NOTIFICATION DROPDOWN ── */
        .notif-wrapper { position: relative; }
        .notif-btn {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            color: var(--t2);
            cursor: pointer;
            position: relative;
            transition: all 0.2s var(--ease);
        }
        .notif-btn:hover { background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.3); color: var(--p3); }
        .notif-btn svg { width: 17px; height: 17px; }
        .notif-dot {
            position: absolute; top: -2px; right: -2px;
            width: 8px; height: 8px;
            border-radius: 50%; background: var(--danger);
            border: 2px solid var(--bg);
        }
        .notif-drop {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 300px;
            background: #14112a;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
            padding: 14px;
            z-index: 200;
            display: none;
        }
        .notif-drop.open { display: block; animation: dropIn 0.2s var(--ease); }
        @keyframes dropIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .notif-drop-hd { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 0.84rem; font-weight: 600; }
        .notif-mark { border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 3px 9px; font-size: 0.7rem; background: transparent; color: var(--t1); cursor: pointer; font-family: 'Outfit', sans-serif; }
        .notif-list { max-height: 220px; overflow-y: auto; }
        .notif-item { padding: 9px 4px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .notif-item:last-child { border-bottom: none; }
        .notif-desc { font-size: 0.82rem; color: var(--t1); line-height: 1.4; }
        .notif-time { font-size: 0.7rem; color: var(--t3); margin-top: 2px; }
        .notif-all { display: block; margin-top: 10px; text-align: center; padding: 7px; border-radius: 9px; background: rgba(255,255,255,0.04); color: var(--t2); text-decoration: none; font-size: 0.8rem; transition: background 0.15s; }
        .notif-all:hover { background: rgba(255,255,255,0.08); }
        .notif-empty { color: var(--t3); font-size: 0.82rem; padding: 14px 4px; text-align: center; }

        /* ── RESPONSIVE ── */
        @media (max-width: 1100px) {
            .kpi-grid { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 640px) {
            :root { --sidebar-w: 0px; }
            .sidebar { display: none; }
            .kpi-grid { grid-template-columns: 1fr 1fr; }
            .page { padding: 14px 14px; }
            .topbar { padding: 10px 14px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="shell">

    {{-- ── SIDEBAR ── --}}
    <aside class="sidebar" aria-label="Main navigation">
        <a href="{{ route('lawyer.dashboard') }}" class="sb-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </a>

        <nav class="sb-nav" aria-label="Primary">
            <a href="{{ route('lawyer.dashboard') }}" title="Dashboard"
               class="nav-link {{ request()->routeIs('lawyer.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </a>
            <a href="{{ route('lawyer.cases.index') }}" title="Cases"
               class="nav-link {{ request()->routeIs('lawyer.cases*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                    <path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/>
                </svg>
            </a>
            <a href="{{ route('lawyer.billing.index') }}" title="Billing"
               class="nav-link {{ request()->routeIs('lawyer.billing*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </a>
            <a href="{{ route('lawyer.calendar.index') }}" title="Calendar"
               class="nav-link {{ request()->routeIs('lawyer.calendar*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </a>
            <a href="{{ route('lawyer.messages.list') }}" title="Messages"
               class="nav-link {{ request()->routeIs('lawyer.messages*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                </svg>
            </a>
            <a href="{{ route('lawyer.profile') }}" title="Profile"
               class="nav-link {{ request()->routeIs('lawyer.profile*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </a>
        </nav>

        <div class="sb-bottom">
            <a href="{{ route('lawyer.profile') }}" class="sb-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="sb-logout" title="Logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN AREA ── --}}
    <div class="main">

        {{-- ── TOPBAR ── --}}
        <header class="topbar">
            <div class="topbar-left">
                <h1>@yield('page_title', 'Dashboard')</h1>
                <p>@yield('page_subtitle', 'Overview of your cases, calendar, and billing')</p>
            </div>
            <div class="topbar-right">
                {{-- Notification bell --}}
                <div class="notif-wrapper">
                    <button class="notif-btn" id="notifToggle" aria-label="Notifications" aria-expanded="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.916V4a1 1 0 10-2 0v1.084A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                        </svg>
                        <span id="notifBadgeDot" class="notif-dot" hidden></span>
                    </button>
                    <div id="notifDrop" class="notif-drop">
                        <div class="notif-drop-hd">
                            <span>Alerts</span>
                            <button class="notif-mark" id="markReadBtn">Mark all read</button>
                        </div>
                        <div id="notifList" class="notif-list">
                            <div class="notif-empty">No new alerts</div>
                        </div>
                        <a href="{{ route('lawyer.notifications') }}" class="notif-all">View all notifications</a>
                    </div>
                </div>

                @yield('topbar_actions')
            </div>
        </header>

        {{-- ── PAGE CONTENT ── --}}
        <main class="page">
            @yield('content')
        </main>
    </div>
</div>

<script>
(function () {
    const toggle  = document.getElementById('notifToggle');
    const drop    = document.getElementById('notifDrop');
    const dot     = document.getElementById('notifBadgeDot');
    const list    = document.getElementById('notifList');
    const markBtn = document.getElementById('markReadBtn');
    const fetchUrl = @json(route('lawyer.notifications'));
    const markUrl  = @json(route('lawyer.notifications.read'));
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content;

    function setBadge(n) { if (dot) dot.hidden = n < 1; }

    function render(items) {
        if (!list) return;
        if (!items.length) { list.innerHTML = '<div class="notif-empty">No new alerts</div>'; return; }
        list.innerHTML = items.map(i =>
            `<div class="notif-item"><div class="notif-desc">${i.description}</div><div class="notif-time">${i.time_ago}</div></div>`
        ).join('');
    }

    async function load() {
        try {
            const r = await fetch(fetchUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!r.ok) return;
            const d = await r.json();
            render(d.notifications || []);
            setBadge(d.unread_count || 0);
        } catch {}
    }

    async function markRead() {
        if (!csrf) return;
        try {
            await fetch(markUrl, { method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:'{}', credentials:'same-origin' });
            await load();
        } catch {}
    }

    toggle?.addEventListener('click', e => {
        e.stopPropagation();
        const open = drop.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
        if (open) load();
    });

    markBtn?.addEventListener('click', markRead);

    document.addEventListener('click', e => {
        if (!drop?.contains(e.target) && !toggle?.contains(e.target)) {
            drop?.classList.remove('open');
            toggle?.setAttribute('aria-expanded', 'false');
        }
    });

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', load)
        : load();
})();
</script>
@stack('scripts')
</body>
</html>