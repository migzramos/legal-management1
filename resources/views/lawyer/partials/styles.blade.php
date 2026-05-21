<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg-deep: #0a0814;
    --bg-card: rgba(255,255,255,0.04);
    --bg-card-hover: rgba(255,255,255,0.07);
    --bg-sidebar: rgba(12,9,28,0.98);
    --border: rgba(255,255,255,0.08);
    --border-hover: rgba(124,58,237,0.5);
    --purple-core: #7c3aed;
    --purple-light: #a855f7;
    --purple-accent: #c084fc;
    --purple-glow: rgba(124,58,237,0.22);
    --purple-light-bg: rgba(124,58,237,0.12);
    --text-primary: #f0ecff;
    --text-muted: rgba(240,236,255,0.42);
    --text-secondary: rgba(240,236,255,0.68);
    --success: #34d399;
    --warning: #fbbf24;
    --danger: #f87171;
    --info: #60a5fa;
    --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
}

::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.3); border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: rgba(124,58,237,0.55); }

html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--bg-deep); color: var(--text-primary); overflow-x: hidden; -webkit-font-smoothing: antialiased; }

.app-layout { display: flex; min-height: 100vh; background: radial-gradient(ellipse 70% 55% at 8% 15%, rgba(124,58,237,0.10) 0%, transparent 65%), radial-gradient(ellipse 50% 45% at 92% 82%, rgba(168,85,247,0.07) 0%, transparent 65%), #0a0814; }

.sidebar { width: 72px; background: var(--bg-sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; align-items: center; padding: 20px 0; position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; backdrop-filter: blur(20px); }
.sidebar-logo { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; margin-bottom: 32px; box-shadow: 0 4px 20px rgba(124,58,237,0.45); cursor: pointer; flex-shrink: 0; }
.sidebar-logo-icon { display: flex; align-items: center; justify-content: center; }
.sidebar-logo-icon svg { width: 20px; height: 20px; color: #fff; }
.sidebar-logo-text { display: none; }
.sidebar-nav { display: flex; flex-direction: column; gap: 4px; flex: 1; width: 100%; padding: 0 12px; overflow-y: auto; }
.nav-item { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); position: relative; text-decoration: none; transition: background var(--transition), color var(--transition), transform var(--transition); flex-shrink: 0; }
.nav-item span { display: none; }
.nav-item svg { width: 20px; height: 20px; flex-shrink: 0; }
.nav-item::after { content: attr(title); position: absolute; left: calc(100% + 14px); background: rgba(20,16,40,0.97); color: var(--text-primary); font-size: 0.78rem; font-weight: 500; white-space: nowrap; padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 8px 24px rgba(0,0,0,0.4); opacity: 0; pointer-events: none; transform: translateX(-6px); transition: opacity 0.18s ease, transform 0.18s ease; z-index: 100; }
.nav-item:hover::after { opacity: 1; transform: translateX(0); }
.nav-item:hover { background: rgba(124,58,237,0.15); color: var(--purple-light); transform: translateX(2px); }
.nav-item.active { background: rgba(124,58,237,0.22); color: var(--purple-light); }
.nav-item.active::before { content: ''; position: absolute; left: -12px; top: 50%; transform: translateY(-50%); width: 3px; height: 26px; background: linear-gradient(180deg, var(--purple-core), var(--purple-light)); border-radius: 0 3px 3px 0; }
.sidebar-bottom { padding: 0 12px 8px; display: flex; flex-direction: column; gap: 8px; align-items: center; }
.notification-wrapper { position: relative; }
.notification-bell { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: transparent; border: 1px solid var(--border); color: var(--text-muted); text-decoration: none; transition: all var(--transition); position: relative; cursor: pointer; }
.notification-bell:hover { background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.3); color: var(--purple-light); }
.notification-badge { position: absolute; top: -3px; right: -3px; width: 8px; height: 8px; border-radius: 50%; background: var(--danger); border: 2px solid var(--bg-sidebar); }
.avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 600; color: #fff; box-shadow: 0 2px 10px rgba(124,58,237,0.35); border: 2px solid rgba(255,255,255,0.1); cursor: pointer; transition: transform var(--transition), box-shadow var(--transition); text-decoration: none; }
.avatar:hover { transform: scale(1.08); box-shadow: 0 4px 16px rgba(124,58,237,0.5); }
.logout-form { margin: 0; padding: 0; }
.btn-logout { background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: color var(--transition), background var(--transition), transform var(--transition); }
.btn-logout:hover { color: var(--danger); background: rgba(248,113,113,0.1); transform: translateX(2px); }
.btn-logout svg { width: 18px; height: 18px; }

.main-area { margin-left: 72px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
.topbar { padding: 16px 28px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: rgba(10,8,20,0.85); backdrop-filter: blur(24px); position: sticky; top: 0; z-index: 40; flex-shrink: 0; }
.topbar-title { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 600; color: var(--text-primary); letter-spacing: -0.01em; }
.topbar-subtitle { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; color: #fff; text-decoration: none; border: 2px solid rgba(255,255,255,0.1); transition: transform var(--transition), box-shadow var(--transition); }
.topbar-avatar:hover { transform: scale(1.08); box-shadow: 0 4px 16px rgba(124,58,237,0.5); }
.page-content { padding: 24px 28px; flex: 1; overflow-y: auto; }

.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.section-title { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 600; color: var(--text-primary); letter-spacing: -0.02em; margin: 0; }
.section-subtitle { font-size: 0.82rem; color: var(--text-muted); margin-top: 4px; }

.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 20px; transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition); position: relative; overflow: hidden; }
.kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(124,58,237,0.5), transparent); opacity: 0; transition: opacity var(--transition); }
.kpi-card:hover { border-color: var(--border-hover); transform: translateY(-3px); box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
.kpi-card:hover::before { opacity: 1; }
.kpi-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.kpi-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); }
.kpi-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(124,58,237,0.12); color: var(--purple-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-icon svg { width: 18px; height: 18px; }
.kpi-value { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 6px; }
.kpi-meta { font-size: 0.75rem; color: var(--text-muted); }

.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: border-color var(--transition), box-shadow var(--transition); }
.card:hover { border-color: rgba(124,58,237,0.2); box-shadow: 0 8px 32px rgba(0,0,0,0.2); }
.card-header { padding: 18px 20px 14px; display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 1px solid var(--border); gap: 12px; }
.card-title { font-family: 'Cormorant Garamond', serif; font-size: 1.05rem; font-weight: 600; color: var(--text-primary); letter-spacing: -0.01em; }
.card-subtitle { font-size: 0.72rem; color: var(--text-muted); margin-top: 3px; }
.card-body { padding: 18px 20px; }
.card-body-flush { padding: 0; }

.list-item { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid var(--border); transition: background var(--transition); }
.list-item:last-child { border-bottom: none; }
.list-item:hover { background: rgba(255,255,255,0.02); }
.list-item-left { flex: 1; min-width: 0; }
.list-item-right { flex-shrink: 0; }
.list-item-compact { display: flex; align-items: center; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--border); transition: background var(--transition); }
.list-item-compact:last-child { border-bottom: none; }
.list-item-compact:hover { background: rgba(255,255,255,0.02); }

.btn-primary { padding: 9px 18px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); border: none; border-radius: 10px; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 0.84rem; font-weight: 500; cursor: pointer; transition: opacity var(--transition), transform var(--transition), box-shadow var(--transition); display: inline-flex; align-items: center; gap: 7px; text-decoration: none; box-shadow: 0 4px 16px rgba(124,58,237,0.35); white-space: nowrap; }
.btn-primary:hover { opacity: 0.92; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,58,237,0.45); }
.btn-primary:active { transform: translateY(0); }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
.btn-primary svg { width: 15px; height: 15px; }
.btn-secondary { padding: 9px 18px; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 10px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.84rem; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: border-color var(--transition), background var(--transition), transform var(--transition); text-decoration: none; white-space: nowrap; }
.btn-secondary:hover { border-color: var(--purple-core); background: rgba(124,58,237,0.08); transform: translateY(-1px); }
.btn-secondary svg { width: 15px; height: 15px; }
.btn-ghost { padding: 9px 18px; background: transparent; border: 1px solid var(--border); border-radius: 10px; color: var(--text-muted); font-family: 'DM Sans', sans-serif; font-size: 0.84rem; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: all var(--transition); text-decoration: none; white-space: nowrap; }
.btn-ghost:hover { color: var(--text-primary); border-color: rgba(255,255,255,0.2); }
.btn-link { font-size: 0.8rem; color: var(--purple-light); text-decoration: none; transition: opacity var(--transition); white-space: nowrap; background: none; border: none; cursor: pointer; padding: 0; font-family: 'DM Sans', sans-serif; }
.btn-link:hover { opacity: 0.75; text-decoration: underline; }
.btn-danger { padding: 7px 14px; background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.22); border-radius: 8px; color: var(--danger); font-family: 'DM Sans', sans-serif; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background var(--transition), transform var(--transition); text-decoration: none; }
.btn-danger:hover { background: rgba(248,113,113,0.16); transform: translateY(-1px); }

.quick-link { display: flex; align-items: center; gap: 10px; padding: 11px 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 10px; color: var(--text-primary); text-decoration: none; font-size: 0.84rem; transition: background var(--transition), border-color var(--transition), transform var(--transition); }
.quick-link:hover { background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.3); transform: translateX(3px); }
.quick-link-icon { width: 30px; height: 30px; border-radius: 8px; background: rgba(124,58,237,0.12); color: var(--purple-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.quick-link-icon svg { width: 15px; height: 15px; }

.status-pending { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(251,191,36,0.12); color: var(--warning); border: 1px solid rgba(251,191,36,0.25); white-space: nowrap; }
.status-confirmed { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(96,165,250,0.12); color: var(--info); border: 1px solid rgba(96,165,250,0.25); white-space: nowrap; }
.status-completed { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(52,211,153,0.12); color: var(--success); border: 1px solid rgba(52,211,153,0.25); white-space: nowrap; }
.status-cancelled { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(248,113,113,0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.25); white-space: nowrap; }
.status-open { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(96,165,250,0.12); color: var(--info); border: 1px solid rgba(96,165,250,0.25); white-space: nowrap; }
.status-ongoing { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(251,191,36,0.12); color: var(--warning); border: 1px solid rgba(251,191,36,0.25); white-space: nowrap; }
.status-closed { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(255,255,255,0.05); color: var(--text-secondary); border: 1px solid var(--border); white-space: nowrap; }
.status-paid { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(52,211,153,0.12); color: var(--success); border: 1px solid rgba(52,211,153,0.25); white-space: nowrap; }
.status-sent { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(96,165,250,0.12); color: var(--info); border: 1px solid rgba(96,165,250,0.25); white-space: nowrap; }
.status-overdue { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(248,113,113,0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.25); white-space: nowrap; }
.status-partial { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(251,191,36,0.12); color: var(--warning); border: 1px solid rgba(251,191,36,0.25); white-space: nowrap; }
.status-draft { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid var(--border); white-space: nowrap; }
.status-active { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; background: rgba(52,211,153,0.12); color: var(--success); border: 1px solid rgba(52,211,153,0.25); white-space: nowrap; }

.badge { padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; display: inline-block; white-space: nowrap; }
.badge-success { background: rgba(52,211,153,0.12); color: var(--success); border: 1px solid rgba(52,211,153,0.22); }
.badge-warning { background: rgba(251,191,36,0.12); color: var(--warning); border: 1px solid rgba(251,191,36,0.22); }
.badge-danger { background: rgba(248,113,113,0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.22); }
.badge-info { background: rgba(96,165,250,0.12); color: var(--info); border: 1px solid rgba(96,165,250,0.22); }
.badge-purple { background: rgba(168,85,247,0.12); color: var(--purple-light); border: 1px solid rgba(168,85,247,0.22); }
.badge-secondary { background: rgba(255,255,255,0.05); color: var(--text-secondary); border: 1px solid var(--border); }

.tabs { display: flex; gap: 0; border-bottom: 1px solid var(--border); overflow-x: auto; margin-bottom: 20px; }
.tab { padding: 10px 16px; font-size: 0.82rem; font-weight: 500; color: var(--text-muted); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: color var(--transition), border-color var(--transition); white-space: nowrap; display: flex; align-items: center; gap: 6px; cursor: pointer; background: none; border-left: none; border-right: none; border-top: none; font-family: 'DM Sans', sans-serif; }
.tab:hover { color: var(--text-primary); }
.tab.active { color: var(--purple-light); border-bottom-color: var(--purple-core); }
.tab-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 99px; font-size: 0.65rem; font-weight: 600; background: rgba(255,255,255,0.07); color: var(--text-muted); }
.tab.active .tab-badge { background: rgba(124,58,237,0.2); color: var(--purple-light); }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 7px; }
.form-control { width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 10px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.88rem; outline: none; transition: border-color var(--transition), box-shadow var(--transition), background var(--transition); }
.form-control:focus { border-color: var(--purple-core); box-shadow: 0 0 0 3px rgba(124,58,237,0.16); background: rgba(255,255,255,0.07); }
.form-control::placeholder { color: var(--text-muted); }
select.form-control option { background: #1a1530; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table thead th { padding: 11px 16px; text-align: left; font-size: 0.68rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border); white-space: nowrap; }
.table tbody td { padding: 14px 16px; font-size: 0.87rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr { transition: background var(--transition); }
.table tbody tr:hover td { background: rgba(124,58,237,0.06); }

.modal-overlay { position: fixed; inset: 0; z-index: 200; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; padding: 24px; }
.modal-overlay.active { display: flex; }
.modal-box { width: 100%; max-width: 520px; background: linear-gradient(160deg, #16122e, #0f0c24); border: 1px solid rgba(124,58,237,0.25); border-radius: 20px; padding: 32px; box-shadow: 0 32px 80px rgba(0,0,0,0.6); position: relative; animation: modalIn 0.28s cubic-bezier(0.16,1,0.3,1) both; }
@keyframes modalIn { from { opacity: 0; transform: translateY(24px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
.modal-close { position: absolute; top: 14px; right: 14px; background: rgba(255,255,255,0.06); border: 1px solid var(--border); border-radius: 8px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-muted); transition: background var(--transition), color var(--transition); font-size: 1rem; line-height: 1; }
.modal-close:hover { background: rgba(255,255,255,0.12); color: var(--text-primary); }
.modal-title { font-family: 'Cormorant Garamond', serif; font-size: 1.45rem; font-weight: 600; margin-bottom: 4px; color: var(--text-primary); }
.modal-sub { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 22px; }

.modal-payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
.payment-method-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 16px 10px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 500; cursor: pointer; transition: border-color var(--transition), background var(--transition); width: 100%; }
.payment-method-btn:hover { border-color: var(--purple-core); background: rgba(124,58,237,0.08); }
.payment-method-btn.selected { border-color: var(--purple-core); background: rgba(124,58,237,0.15); color: var(--purple-light); }

.alert { padding: 11px 16px; border-radius: 10px; font-size: 0.84rem; margin-bottom: 18px; }
.alert-success { background: rgba(52,211,153,0.08); border: 1px solid rgba(52,211,153,0.22); color: var(--success); }
.alert-error { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.22); color: var(--danger); }
.alert-warning { background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.22); color: var(--warning); }
.alert-info { background: rgba(96,165,250,0.08); border: 1px solid rgba(96,165,250,0.22); color: var(--info); }

.empty-state { text-align: center; padding: 48px 24px; color: var(--text-muted); }
.empty-state-icon { width: 52px; height: 52px; border-radius: 14px; background: rgba(124,58,237,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.empty-state-icon svg { width: 24px; height: 24px; color: var(--purple-light); opacity: 0.6; }
.empty-state-title { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 6px; }
.empty-state-text { font-size: 0.82rem; color: var(--text-muted); line-height: 1.6; }

.message-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 600; color: #fff; flex-shrink: 0; }
.message-bubble { display: inline-block; max-width: 100%; padding: 10px 14px; border-radius: 14px; font-size: 0.87rem; line-height: 1.6; word-break: break-word; }
.message-bubble.sent { background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); color: #fff; border-radius: 14px 14px 4px 14px; }
.message-bubble.received { background: rgba(255,255,255,0.06); border: 1px solid var(--border); border-radius: 14px 14px 14px 4px; }
.message-input { flex: 1; padding: 10px 14px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 10px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.88rem; outline: none; resize: none; min-height: 40px; max-height: 120px; transition: border-color var(--transition); }
.message-input:focus { border-color: var(--purple-core); }
.message-input::placeholder { color: var(--text-muted); }
.btn-send { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--purple-core), var(--purple-light)); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #fff; flex-shrink: 0; box-shadow: 0 4px 12px rgba(124,58,237,0.3); transition: opacity var(--transition), transform var(--transition); }
.btn-send:hover { opacity: 0.9; transform: scale(1.05); }
.btn-send svg { width: 16px; height: 16px; }

.notification-dropdown { position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); width: 300px; background: #1e1535; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; box-shadow: 0 20px 45px rgba(0,0,0,0.4); padding: 14px; color: var(--text-primary); z-index: 60; }
.notification-dropdown-header { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 10px; font-weight: 600; font-size: 0.85rem; }
.notification-mark-read { border: 1px solid rgba(255,255,255,0.12); border-radius: 999px; padding: 4px 10px; font-size: 0.72rem; background: transparent; color: var(--text-primary); cursor: pointer; font-family: 'DM Sans', sans-serif; }
.notification-list { max-height: 240px; overflow-y: auto; }
.notification-item { display: flex; flex-direction: column; gap: 3px; padding: 10px 4px; border-bottom: 1px solid rgba(255,255,255,0.06); }
.notification-item:last-child { border-bottom: none; }
.notification-description { font-size: 0.84rem; line-height: 1.4; color: var(--text-primary); }
.notification-time { font-size: 0.72rem; color: var(--text-muted); }
.notification-view-all { display: block; margin-top: 10px; text-align: center; padding: 8px; border-radius: 10px; background: rgba(255,255,255,0.05); color: var(--text-secondary); text-decoration: none; font-size: 0.82rem; transition: background var(--transition); }
.notification-view-all:hover { background: rgba(255,255,255,0.09); }
.notification-empty { color: var(--text-muted); font-size: 0.84rem; padding: 16px 4px; text-align: center; }

.pagination { display: flex; align-items: center; gap: 6px; margin-top: 20px; flex-wrap: wrap; }
.pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px; font-size: 0.82rem; text-decoration: none; transition: all var(--transition); border: 1px solid var(--border); color: var(--text-muted); background: var(--bg-card); }
.pagination a:hover { border-color: var(--purple-core); color: var(--purple-light); background: rgba(124,58,237,0.08); }

@media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 900px) { .page-content { padding: 18px 16px; } }
@media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } .form-grid { grid-template-columns: 1fr; } .modal-payment-methods { grid-template-columns: 1fr; } }
</style>
