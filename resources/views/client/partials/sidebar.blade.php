@php
    $currentUser = auth()->user();
    $firstCase = null;
    try {
        $firstCase = $currentUser?->cases()->whereNull('deleted_at')->orderBy('updated_at', 'desc')->first();
    } catch (\Exception $e) {
        $firstCase = null;
    }
@endphp

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0.5">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
        <span class="sidebar-logo-text">LegalCase</span>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('client.dashboard') }}" title="Dashboard"
           class="nav-item{{ request()->routeIs('client.dashboard') ? ' active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('client.cases.index') }}" title="My Cases"
           class="nav-item{{ request()->routeIs('client.cases.*') ? ' active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                <path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/>
            </svg>
            <span>My Cases</span>
        </a>

        <a href="{{ route('client.invoices.index') }}" title="Invoices"
           class="nav-item{{ request()->routeIs('client.invoices.*') ? ' active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            <span>Invoices</span>
        </a>

        <a href="{{ route('client.messages.list') }}" title="Messages"
           class="nav-item{{ request()->routeIs('client.messages.*') ? ' active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
            <span>Messages</span>
        </a>

        <a href="{{ $firstCase ? route('client.documents.index', $firstCase) : route('client.cases.index') }}"
           title="Documents"
           class="nav-item{{ request()->routeIs('client.documents.*') ? ' active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span>Documents</span>
        </a>

        <a href="{{ route('client.appointments.index') }}" title="Appointments"
           class="nav-item{{ request()->routeIs('client.appointments.*') ? ' active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>Appointments</span>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="{{ route('client.profile') }}" title="Profile"
           class="nav-item{{ request()->routeIs('client.profile') ? ' active' : '' }}"
           style="width:44px;height:44px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Profile</span>
        </a>

        <div class="sidebar-avatar" title="{{ $currentUser?->name ?? 'Client' }}">
            {{ strtoupper(substr($currentUser?->name ?? 'C', 0, 1)) }}
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
            @csrf
            <button type="submit" class="btn-logout" title="Sign Out">
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
        background: var(--bg-sidebar);
        border-right: 1px solid var(--border);
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
        background: linear-gradient(135deg, var(--purple-core), var(--purple-light));
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 24px;
        flex-shrink: 0;
        box-shadow: 0 4px 18px rgba(124,58,237,0.4);
    }
    .sidebar-logo-icon svg { width: 18px; height: 18px; color: #fff; }
    .sidebar-logo-text { display: none; }

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
        color: var(--text-muted);
        position: relative;
        text-decoration: none;
        transition: background 0.2s, color 0.2s, transform 0.2s;
        flex-shrink: 0;
    }
    .nav-item span { display: none; }
    .nav-item svg { width: 19px; height: 19px; flex-shrink: 0; }

    .nav-item::after {
        content: attr(title);
        position: absolute;
        left: calc(100% + 12px);
        background: rgba(18,14,38,0.97);
        color: var(--text-primary);
        font-size: 0.76rem;
        font-weight: 500;
        white-space: nowrap;
        padding: 5px 11px;
        border-radius: 7px;
        border: 1px solid var(--border);
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        opacity: 0;
        pointer-events: none;
        transform: translateX(-4px);
        transition: opacity 0.15s, transform 0.15s;
        z-index: 100;
    }
    .nav-item:hover::after { opacity: 1; transform: translateX(0); }
    .nav-item:hover { background: rgba(124,58,237,0.14); color: var(--purple-light); transform: translateX(2px); }
    .nav-item.active { background: rgba(124,58,237,0.2); color: var(--purple-light); }
    .nav-item.active::before {
        content: '';
        position: absolute;
        left: -10px; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 22px;
        background: linear-gradient(180deg, var(--purple-core), var(--purple-light));
        border-radius: 0 3px 3px 0;
    }

    .sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 12px 10px 4px;
        width: 100%;
        border-top: 1px solid var(--border);
        margin-top: 8px;
    }

    .sidebar-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--purple-core), var(--purple-light));
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 600; color: #fff;
        border: 2px solid rgba(255,255,255,0.1);
        box-shadow: 0 2px 10px rgba(124,58,237,0.3);
        flex-shrink: 0;
    }

    .btn-logout {
        background: transparent; border: none;
        color: var(--text-muted); cursor: pointer;
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        transition: color 0.2s, background 0.2s;
        padding: 0;
    }
    .btn-logout:hover { color: var(--danger); background: rgba(248,113,113,0.1); }
    .btn-logout svg { width: 17px; height: 17px; }
</style>