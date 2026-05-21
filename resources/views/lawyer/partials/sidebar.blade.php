<aside class="sidebar" aria-label="Lawyer navigation">
    <div class="sidebar-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5"/>
            <path d="M2 12l10 5 10-5"/>
        </svg>
    </div>

    <nav class="sidebar-nav" aria-label="Primary navigation">
        <a href="{{ route('lawyer.dashboard') }}" title="Dashboard"
           class="nav-item {{ request()->routeIs('lawyer.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
        </a>

        <a href="{{ route('lawyer.cases.index') }}" title="Cases & Documents"
           class="nav-item {{ request()->routeIs('lawyer.cases*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                <path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/>
            </svg>
        </a>

        <a href="{{ route('lawyer.billing.index') }}" title="Billing & Invoices"
           class="nav-item {{ request()->routeIs('lawyer.billing*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </a>

        <a href="{{ route('lawyer.calendar.index') }}" title="Calendar"
           class="nav-item {{ request()->routeIs('lawyer.calendar*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </a>

        <a href="{{ route('lawyer.messages.list') }}" title="Messages"
           class="nav-item {{ request()->routeIs('lawyer.messages*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
        </a>

        <a href="{{ route('lawyer.profile') }}" title="Profile"
           class="nav-item {{ request()->routeIs('lawyer.profile*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </a>
    </nav>

    <div class="sidebar-bottom">
        {{-- Notification bell --}}
        <div class="lawyer-notif-wrap">
            <button type="button" id="notificationToggle"
                    aria-expanded="false" aria-label="Notifications"
                    class="lawyer-notif-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.916V4a1 1 0 10-2 0v1.084A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                </svg>
                <span id="notificationBadge" class="notif-badge" hidden>0</span>
            </button>

            <div id="notificationDropdown" class="lawyer-notif-dropdown" hidden>
                <div class="notif-header">
                    <span>Recent Alerts</span>
                    <button type="button" id="markAllReadButton" class="notif-mark-read">Mark all read</button>
                </div>
                <div id="notificationList" class="notif-list">
                    <div class="notif-empty">No new alerts</div>
                </div>
                <a href="{{ route('lawyer.notifications') }}" class="notif-view-all">View All</a>
            </div>
        </div>

        <div class="avatar" title="{{ auth()->user()->name }}">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
            @csrf
            <button type="submit" class="btn-logout" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </div>
</aside>

<style>
    .sidebar {
        width: 68px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 18px 0 16px;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        z-index: 50;
        gap: 0;
    }

    .sidebar-logo {
        width: 40px; height: 40px;
        border-radius: 11px;
        background: linear-gradient(135deg, var(--purple-core, #7c3aed), var(--purple-light, #a855f7));
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 24px;
        flex-shrink: 0;
        box-shadow: 0 4px 18px rgba(124,58,237,0.4);
    }
    .sidebar-logo svg { width: 18px; height: 18px; color: #fff; }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        flex: 1;
        width: 100%;
        padding: 0 10px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .nav-item {
        width: 44px; height: 44px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        color: var(--text-muted, rgba(240,236,255,0.42));
        position: relative;
        text-decoration: none;
        transition: background 0.2s, color 0.2s, transform 0.2s;
        flex-shrink: 0;
    }
    .nav-item svg { width: 19px; height: 19px; flex-shrink: 0; }

    .nav-item::after {
        content: attr(title);
        position: absolute;
        left: calc(100% + 12px);
        background: rgba(18,14,38,0.97);
        color: var(--text-primary, #f0ecff);
        font-size: 0.76rem;
        font-weight: 500;
        white-space: nowrap;
        padding: 5px 11px;
        border-radius: 7px;
        border: 1px solid rgba(255,255,255,0.08);
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        opacity: 0;
        pointer-events: none;
        transform: translateX(-4px);
        transition: opacity 0.15s, transform 0.15s;
        z-index: 100;
    }
    .nav-item:hover::after { opacity: 1; transform: translateX(0); }
    .nav-item:hover { background: rgba(124,58,237,0.14); color: var(--purple-light, #a855f7); transform: translateX(2px); }
    .nav-item.active { background: rgba(124,58,237,0.2); color: var(--purple-light, #a855f7); }
    .nav-item.active::before {
        content: '';
        position: absolute;
        left: -10px; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 22px;
        background: linear-gradient(180deg, var(--purple-core, #7c3aed), var(--purple-light, #a855f7));
        border-radius: 0 3px 3px 0;
    }

    .sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 12px 10px 4px;
        width: 100%;
        border-top: 1px solid rgba(255,255,255,0.07);
        margin-top: 8px;
    }

    /* Notification */
    .lawyer-notif-wrap { position: relative; }
    .lawyer-notif-btn {
        width: 34px; height: 34px;
        border-radius: 9px;
        border: 1px solid rgba(255,255,255,0.08);
        background: transparent;
        color: var(--text-muted, rgba(240,236,255,0.42));
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        position: relative;
        transition: background 0.2s, color 0.2s;
    }
    .lawyer-notif-btn:hover { background: rgba(124,58,237,0.12); color: var(--purple-light, #a855f7); border-color: rgba(124,58,237,0.3); }
    .lawyer-notif-btn svg { width: 17px; height: 17px; }

    .notif-badge {
        position: absolute;
        top: -3px; right: -3px;
        min-width: 16px; height: 16px;
        padding: 0 3px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center; justify-content: center;
        border: 2px solid var(--bg-sidebar, #0c091c);
    }

    .lawyer-notif-dropdown {
        position: absolute;
        bottom: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%);
        width: 300px;
        background: #2f1f47;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        box-shadow: 0 20px 45px rgba(0,0,0,0.35);
        padding: 14px;
        color: #f8f4ff;
        z-index: 200;
    }
    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .notif-mark-read {
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 0.72rem;
        background: transparent;
        color: #f8f4ff;
        cursor: pointer;
        font-family: inherit;
    }
    .notif-list { max-height: 240px; overflow-y: auto; }
    .notif-item {
        display: flex; flex-direction: column; gap: 3px;
        padding: 10px 4px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        font-size: 0.84rem;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-time { font-size: 0.72rem; color: #c9b8ff; }
    .notif-empty { color: #c9b8ff; font-size: 0.84rem; padding: 16px 4px; text-align: center; }
    .notif-view-all {
        display: block; margin-top: 10px; text-align: center;
        padding: 8px; border-radius: 10px;
        background: rgba(255,255,255,0.05);
        color: #e9defd; text-decoration: none; font-size: 0.82rem;
    }
    .notif-view-all:hover { background: rgba(255,255,255,0.09); }

    .avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--purple-core, #7c3aed), var(--purple-light, #a855f7));
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 600; color: #fff;
        border: 2px solid rgba(255,255,255,0.1);
        box-shadow: 0 2px 10px rgba(124,58,237,0.3);
        flex-shrink: 0;
    }

    .btn-logout {
        background: transparent; border: none;
        color: var(--text-muted, rgba(240,236,255,0.42));
        cursor: pointer;
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        transition: color 0.2s, background 0.2s;
        padding: 0;
    }
    .btn-logout:hover { color: #f87171; background: rgba(248,113,113,0.1); }
    .btn-logout svg { width: 17px; height: 17px; }
</style>

<script>
(function () {
    const toggle   = document.getElementById('notificationToggle');
    const dropdown = document.getElementById('notificationDropdown');
    const badge    = document.getElementById('notificationBadge');
    const list     = document.getElementById('notificationList');
    const markBtn  = document.getElementById('markAllReadButton');
    const fetchUrl    = @json(route('lawyer.notifications'));
    const markReadUrl = @json(route('lawyer.notifications.read'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function updateBadge(count) {
        if (!badge) return;
        if (count > 0) { badge.textContent = count; badge.hidden = false; }
        else { badge.hidden = true; }
    }

    function renderList(items) {
        if (!list) return;
        if (!items.length) { list.innerHTML = '<div class="notif-empty">No new alerts</div>'; return; }
        list.innerHTML = items.map(i =>
            `<div class="notif-item"><div>${i.description}</div><div class="notif-time">${i.time_ago}</div></div>`
        ).join('');
    }

    async function load() {
        try {
            const r = await fetch(fetchUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!r.ok) return;
            const d = await r.json();
            renderList(d.notifications || []);
            updateBadge(d.unread_count || 0);
        } catch (e) { console.error(e); }
    }

    async function markRead() {
        if (!csrf) return;
        try {
            await fetch(markReadUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: '{}', credentials: 'same-origin'
            });
            await load();
        } catch (e) { console.error(e); }
    }

    toggle?.addEventListener('click', e => {
        e.stopPropagation();
        const open = !dropdown.hidden;
        dropdown.hidden = open;
        toggle.setAttribute('aria-expanded', String(!open));
        if (!open) load();
    });

    markBtn?.addEventListener('click', markRead);

    document.addEventListener('click', e => {
        if (!dropdown || !toggle) return;
        if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
            dropdown.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', load)
        : load();
})();
</script>