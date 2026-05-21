<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           title="Dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
           title="Users">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </a>
        <a href="{{ route('admin.reports.page') }}"
           class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
           title="Reports">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </a>
        <a href="{{ route('admin.calendar') }}"
           class="nav-item {{ request()->routeIs('admin.calendar') ? 'active' : '' }}"
           title="Calendar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </a>
        <a href="{{ route('admin.messages') }}"
           class="nav-item {{ request()->routeIs('admin.messages*') ? 'active' : '' }}"
           title="Messages">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            @php
                $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
            @endphp
            @if($unreadCount > 0)
                <span style="position:absolute;top:8px;right:8px;width:7px;height:7px;border-radius:50%;background:var(--warning);border:1.5px solid var(--bg-sidebar);"></span>
            @endif
        </a>
    </nav>

    <div class="sidebar-bottom" style="padding: 14px 12px 20px; display: flex; flex-direction: column; align-items: center; gap: 6px; border-top: 1px solid var(--border); margin-top: 8px;">
        <a href="#"
           class="avatar"
           title="{{ auth()->user()->name }}"
           style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--purple-core),var(--purple-light));display:flex;align-items:center;justify-content:center;font-size:0.82rem;font-weight:600;color:#fff;box-shadow:0 2px 10px rgba(124,58,237,0.35);border:2px solid rgba(255,255,255,0.1);text-decoration:none;">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </a>

        {{-- Logout: use a plain anchor with JS submit to avoid any form z-index/pointer issues --}}
        <form id="sidebar-logout-form" method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;width:100%;display:flex;justify-content:center;">
            @csrf
            <button
                type="button"
                onclick="document.getElementById('sidebar-logout-form').submit()"
                title="Logout"
                style="background:transparent;border:none;color:var(--text-muted);cursor:pointer;padding:8px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:color 0.22s,background 0.22s;position:relative;z-index:60;"
                onmouseover="this.style.color='#f87171';this.style.background='rgba(248,113,113,0.1)'"
                onmouseout="this.style.color='var(--text-muted)';this.style.background='transparent'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </form>
    </div>
</aside>