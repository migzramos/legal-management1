@extends('layouts.lawyer')

@section('title', 'Messages')
@section('page_title', '')
@section('page_subtitle', '')

@push('styles')
<style>
/* ─────────────────────────────────────────────────────────────────
   ROOT & RESET
───────────────────────────────────────────────────────────────── */
html, body { overflow: hidden; height: 100%; }

/* Hide the global layout topbar */
.topbar, .top-bar, nav.topbar, header.topbar,
[class*="topbar"], [class*="top-bar"],
.navbar-top, .layout-topbar, .app-topbar { display: none !important; }

/* Suppress parent page chrome */
.page-header, .page-title-bar, .page-topbar,
[class*="page-header"], [class*="page-subtitle"],
.page > h1, .page > .header, .page > .title-row { display: none !important; }

.page {
    padding: 0 !important; margin: 0 !important;
    overflow: visible !important; display: block !important;
    height: 0 !important; min-height: 0 !important;
}

/* ─────────────────────────────────────────────────────────────────
   DESIGN TOKENS
───────────────────────────────────────────────────────────────── */
:root {
    --nav-w:     60px;
    --topbar-h:  0px;

    --void:      #06040f;
    --surface:   #0b0818;
    --raised:    #100d1e;
    --overlay:   #15112a;
    --hover:     rgba(109,40,217,0.10);
    --active:    rgba(109,40,217,0.18);

    --a1:        #6d28d9;
    --a2:        #8b5cf6;
    --a3:        #a78bfa;
    --a-glow:    rgba(109,40,217,0.28);

    --b0:        rgba(139,92,246,0.07);
    --b1:        rgba(139,92,246,0.13);
    --b2:        rgba(139,92,246,0.25);
    --b3:        rgba(139,92,246,0.45);

    --t1:        #ede9fe;
    --t2:        rgba(237,233,254,0.72);
    --t3:        rgba(237,233,254,0.42);
    --t4:        rgba(237,233,254,0.22);
    --t5:        rgba(237,233,254,0.10);

    --ok:        #34d399;
    --ok-bg:     rgba(52,211,153,0.08);
    --ok-border: rgba(52,211,153,0.18);
    --err:       #f87171;
    --err-bg:    rgba(248,113,113,0.08);
    --err-border:rgba(248,113,113,0.18);

    --r-xs: 5px;
    --r-sm: 8px;
    --r-md: 11px;
    --r-lg: 14px;
    --r-xl: 18px;

    --sb-w: 256px;
}

/* ─────────────────────────────────────────────────────────────────
   SHELL — fixed, fills everything right of nav rail, top to bottom
───────────────────────────────────────────────────────────────── */
.lx-shell {
    position: fixed;
    top: 0;
    left: var(--nav-w);
    right: 0;
    bottom: 0;
    display: flex;
    background: var(--void);
    font-family: 'Outfit', sans-serif;
    overflow: hidden;
    z-index: 100;
}

/* ═══════════════════════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════════════════════ */
.lx-sb {
    width: var(--sb-w);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    background: var(--surface);
    border-right: 1px solid var(--b1);
    overflow: hidden;
}

.lx-sb-head {
    padding: 16px 14px 12px;
    border-bottom: 1px solid var(--b0);
    flex-shrink: 0;
}

.lx-sb-title {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 11px;
}
.lx-sb-icon {
    width: 22px; height: 22px;
    border-radius: 6px;
    background: var(--active);
    border: 1px solid var(--b2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.lx-sb-heading {
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.11em;
    text-transform: uppercase;
    color: var(--t3);
}

/* Search — full width inside sidebar head */
.lx-search {
    display: flex;
    align-items: center;
    gap: 7px;
    width: 100%;
    box-sizing: border-box;
    background: rgba(255,255,255,0.028);
    border: 1px solid var(--b1);
    border-radius: var(--r-sm);
    padding: 7px 10px;
    transition: border-color 0.15s, background 0.15s;
}
.lx-search:focus-within {
    border-color: var(--b3);
    background: rgba(255,255,255,0.04);
    box-shadow: 0 0 0 3px rgba(109,40,217,0.06);
}
.lx-search svg { color: var(--t4); flex-shrink: 0; }
.lx-search input {
    background: none; border: none; outline: none;
    color: var(--t1); font-family: 'Outfit', sans-serif;
    font-size: 0.78rem; width: 100%; min-width: 0;
}
.lx-search input::placeholder { color: var(--t5); }

/* Case list */
.lx-cases {
    flex: 1 1 0;
    min-height: 0;
    overflow-y: auto;
    padding: 8px 8px 12px;
    scrollbar-width: thin;
    scrollbar-color: rgba(109,40,217,0.18) transparent;
}
.lx-cases::-webkit-scrollbar { width: 3px; }
.lx-cases::-webkit-scrollbar-thumb { background: rgba(109,40,217,0.18); border-radius: 3px; }

.lx-no-cases {
    padding: 28px 10px;
    text-align: center;
    font-size: 0.73rem;
    color: var(--t4);
    line-height: 1.7;
}

.lx-case {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 9px;
    border-radius: var(--r-md);
    text-decoration: none;
    color: var(--t2);
    transition: background 0.12s, color 0.12s;
    margin-bottom: 1px;
    min-width: 0;
    position: relative;
    cursor: pointer;
}
.lx-case:hover  { background: var(--hover); color: var(--t1); }
.lx-case.active { background: var(--active); color: var(--t1); }
.lx-case.active::before {
    content: '';
    position: absolute;
    left: 0; top: 22%; bottom: 22%;
    width: 2.5px;
    border-radius: 0 3px 3px 0;
    background: linear-gradient(180deg, var(--a1), var(--a3));
}

.lx-case-avi {
    width: 34px; height: 34px;
    border-radius: var(--r-sm);
    background: linear-gradient(135deg, #2d1070 0%, var(--a1) 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.66rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
    letter-spacing: 0.04em;
    box-shadow: 0 2px 6px rgba(109,40,217,0.24);
}
.lx-case-info { flex: 1 1 0; min-width: 0; }
.lx-case-name {
    font-size: 0.8rem; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 1px; line-height: 1.2; color: inherit;
}
.lx-case-meta {
    font-size: 0.65rem; color: var(--t4);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    line-height: 1.1;
}

/* ═══════════════════════════════════════════════════════════════
   MAIN PANEL
═══════════════════════════════════════════════════════════════ */
.lx-main {
    flex: 1 1 0;
    min-width: 0; min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--void);
}

.lx-flash {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 20px;
    font-size: 0.77rem; flex-shrink: 0;
}
.lx-flash.ok  { background: var(--ok-bg);  color: var(--ok);  border-bottom: 1px solid var(--ok-border); }
.lx-flash.err { background: var(--err-bg); color: var(--err); border-bottom: 1px solid var(--err-border); }

.lx-bar {
    display: flex; align-items: center; gap: 11px;
    padding: 11px 20px;
    border-bottom: 1px solid var(--b1);
    background: var(--surface);
    flex-shrink: 0;
}
.lx-bar-avi {
    width: 36px; height: 36px;
    border-radius: var(--r-md);
    background: linear-gradient(135deg, #2d1070, var(--a1));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(109,40,217,0.32);
}
.lx-bar-info { flex: 1 1 0; min-width: 0; }
.lx-bar-title {
    font-size: 0.88rem; font-weight: 600; color: var(--t1);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    line-height: 1.25; margin-bottom: 1px;
}
.lx-bar-sub { font-size: 0.67rem; color: var(--t3); }

.lx-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.63rem; font-weight: 600; letter-spacing: 0.04em;
    white-space: nowrap; flex-shrink: 0;
}
.lx-pill.ok {
    background: var(--ok-bg); color: var(--ok);
    border: 1px solid var(--ok-border);
}
.lx-pill-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

.lx-icon-btn {
    width: 30px; height: 30px;
    border-radius: var(--r-sm);
    background: rgba(255,255,255,0.035);
    border: 1px solid var(--b1);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--t3); text-decoration: none; flex-shrink: 0;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
}
.lx-icon-btn:hover { background: var(--hover); border-color: var(--b2); color: var(--t1); }

/* Feed */
.lx-feed {
    flex: 1 1 0;
    min-height: 0;
    overflow-y: auto;
    padding: 18px 22px 12px;
    display: flex; flex-direction: column; gap: 2px;
    scrollbar-width: thin;
    scrollbar-color: rgba(109,40,217,0.14) transparent;
}
.lx-feed::-webkit-scrollbar { width: 3px; }
.lx-feed::-webkit-scrollbar-thumb { background: rgba(109,40,217,0.16); border-radius: 3px; }

.lx-divider {
    display: flex; align-items: center; gap: 10px;
    margin: 12px 0 4px;
}
.lx-divider::before, .lx-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--b1);
}
.lx-divider span {
    font-size: 0.59rem; color: var(--t4);
    font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.09em; white-space: nowrap;
}

.lx-msg {
    display: flex; gap: 8px; align-items: flex-end;
    margin-bottom: 1px;
    animation: lxIn 0.18s cubic-bezier(.22,.68,0,1.12) both;
}
.lx-msg.out { flex-direction: row-reverse; }

@keyframes lxIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: none; }
}

.lx-msg.in  + .lx-msg.in  .lx-avi,
.lx-msg.out + .lx-msg.out .lx-avi { visibility: hidden; }
.lx-msg.in  + .lx-msg.in,
.lx-msg.out + .lx-msg.out { margin-bottom: -2px; }

.lx-avi {
    width: 25px; height: 25px; border-radius: 6px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--b1);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.58rem; font-weight: 700; color: var(--t3);
    flex-shrink: 0;
}
.lx-msg.out .lx-avi {
    background: linear-gradient(135deg, #2d1070, var(--a1));
    color: #fff; border: none;
    box-shadow: 0 2px 7px rgba(109,40,217,0.26);
}

.lx-col { max-width: 60%; min-width: 0; }

.lx-bubble {
    padding: 8px 13px;
    font-size: 0.845rem; line-height: 1.62;
    word-break: break-word;
}
.lx-msg.in .lx-bubble {
    background: rgba(255,255,255,0.045);
    border: 1px solid var(--b1);
    border-radius: var(--r-md) var(--r-md) var(--r-md) 3px;
    color: var(--t1);
}
.lx-msg.out .lx-bubble {
    background: linear-gradient(140deg, #3b1fa3 0%, var(--a1) 100%);
    border-radius: var(--r-md) var(--r-md) 3px var(--r-md);
    color: #fff;
    box-shadow: 0 3px 12px rgba(109,40,217,0.22);
}

.lx-foot {
    display: flex; align-items: center; gap: 5px;
    font-size: 0.59rem; color: var(--t4);
    margin-top: 3px; padding: 0 2px;
}
.lx-msg.out .lx-foot { justify-content: flex-end; }

.lx-role {
    padding: 1px 5px; border-radius: 3px;
    font-size: 0.55rem; font-weight: 700;
    letter-spacing: 0.05em; text-transform: uppercase;
}
.lx-role.l { background: rgba(109,40,217,0.18); color: var(--a3); }
.lx-role.c { background: rgba(255,255,255,0.055); color: var(--t3); }

.lx-pagi {
    flex-shrink: 0; padding: 8px 20px;
    border-top: 1px solid var(--b0);
    background: var(--surface);
    display: flex; justify-content: center;
}

/* Compose */
.lx-compose {
    flex-shrink: 0;
    padding: 12px 16px 14px;
    border-top: 1px solid var(--b1);
    background: var(--surface);
}
.lx-compose-box {
    display: flex; align-items: flex-end; gap: 9px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--b2);
    border-radius: var(--r-lg);
    padding: 8px 9px 8px 14px;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.lx-compose-box:focus-within {
    border-color: var(--b3);
    box-shadow: 0 0 0 3px rgba(109,40,217,0.07);
}
.lx-ta {
    flex: 1 1 0; min-width: 0;
    background: none; border: none; outline: none;
    color: var(--t1); font-family: 'Outfit', sans-serif;
    font-size: 0.86rem; resize: none;
    min-height: 20px; max-height: 108px;
    overflow-y: auto; line-height: 1.55;
}
.lx-ta::placeholder { color: var(--t5); }

.lx-send {
    width: 33px; height: 33px; border-radius: var(--r-sm);
    background: linear-gradient(135deg, var(--a1), var(--a2));
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(109,40,217,0.34);
    transition: opacity 0.13s, transform 0.1s;
}
.lx-send:hover  { opacity: 0.85; }
.lx-send:active { transform: scale(0.89); }

.lx-hint {
    margin-top: 5px; padding-left: 2px;
    font-size: 0.59rem; color: var(--t5);
    display: flex; align-items: center; gap: 4px;
}
.lx-hint kbd {
    display: inline-block; padding: 1px 4px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--b1);
    border-radius: 3px; font-size: 0.57rem;
    font-family: monospace; color: var(--t4); line-height: 1.5;
}

/* ═══════════════════════════════════════════════════════════════
   SPLASH — centered card when no case selected
═══════════════════════════════════════════════════════════════ */
.lx-splash {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 32px 24px; overflow: hidden; position: relative;
}
.lx-splash::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background: radial-gradient(ellipse 52% 40% at 50% 44%,
                rgba(109,40,217,0.09) 0%, transparent 68%);
}
.lx-splash-card {
    position: relative;
    width: 100%; max-width: 340px;
    background: var(--surface);
    border: 1px solid var(--b1);
    border-radius: var(--r-xl);
    padding: 32px 28px 26px;
    text-align: center;
    box-shadow:
        0 0 0 1px var(--b0),
        0 8px 40px rgba(0,0,0,0.55),
        inset 0 1px 0 rgba(255,255,255,0.04);
}
.lx-splash-card::before {
    content: '';
    position: absolute; top: 0; left: 20%; right: 20%; height: 1px;
    background: linear-gradient(90deg, transparent, var(--a2), transparent);
    opacity: 0.4;
}
.lx-splash-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(109,40,217,0.18), rgba(139,92,246,0.10));
    border: 1px solid var(--b2);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    box-shadow: 0 4px 18px rgba(109,40,217,0.18);
}
.lx-splash-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.12rem; font-weight: 700;
    color: var(--t1); margin-bottom: 7px;
    letter-spacing: -0.02em;
}
.lx-splash-card p {
    font-size: 0.775rem; color: var(--t3);
    line-height: 1.7; margin-bottom: 0;
    max-width: 260px; margin-left: auto; margin-right: auto;
}
.lx-splash-cases { margin-top: 20px; text-align: left; }
.lx-splash-cases-label {
    font-size: 0.6rem; text-transform: uppercase;
    letter-spacing: 0.1em; color: var(--t4);
    font-weight: 700; margin-bottom: 8px; padding-left: 1px;
}
.lx-splash-row {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r-md);
    background: rgba(255,255,255,0.022);
    border: 1px solid var(--b0);
    text-decoration: none; color: var(--t2);
    margin-bottom: 5px;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
}
.lx-splash-row:last-child { margin-bottom: 0; }
.lx-splash-row:hover { background: var(--hover); border-color: var(--b2); color: var(--t1); }
.lx-splash-avi {
    width: 28px; height: 28px;
    border-radius: var(--r-sm);
    background: linear-gradient(135deg, #2d1070, var(--a1));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.63rem; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 1px 5px rgba(109,40,217,0.22);
}
.lx-splash-info { flex: 1 1 0; min-width: 0; }
.lx-splash-name {
    font-size: 0.78rem; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 1px; line-height: 1.15;
}
.lx-splash-meta { font-size: 0.62rem; color: var(--t4); }
.lx-splash-arr { color: var(--t4); flex-shrink: 0; }

/* Empty thread */
.lx-empty {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center; padding: 32px 20px;
}
.lx-empty-icon {
    width: 52px; height: 52px; border-radius: 13px;
    background: rgba(109,40,217,0.08);
    border: 1px solid var(--b1);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
}
.lx-empty h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem; font-weight: 700; color: var(--t2);
    margin-bottom: 7px;
}
.lx-empty p {
    font-size: 0.76rem; color: var(--t3);
    max-width: 230px; line-height: 1.68; margin-bottom: 16px;
}
.lx-empty-cta {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 20px;
    background: rgba(109,40,217,0.08);
    border: 1px solid var(--b1);
    font-size: 0.69rem; color: var(--t3);
}

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════════ */
@media (max-width: 860px) {
    :root { --sb-w: 220px; }
    .lx-feed { padding: 14px 14px 10px; }
    .lx-col  { max-width: 72%; }
}
@media (max-width: 640px) {
    :root { --sb-w: 52px; }
    .lx-sb-heading, .lx-search, .lx-case-info { display: none !important; }
    .lx-sb-title { justify-content: center; margin-bottom: 0; }
    .lx-sb-head  { padding: 12px 8px; }
    .lx-case     { justify-content: center; padding: 8px 0; }
    .lx-case.active::before { display: none; }
    .lx-case-avi { width: 30px; height: 30px; font-size: 0.62rem; }
    .lx-bar-sub, .lx-icon-btn { display: none; }
    .lx-feed { padding: 10px 10px; }
    .lx-col  { max-width: 82%; }
    .lx-compose { padding: 8px 8px 10px; }
}
@media (max-width: 420px) {
    .lx-sb { display: none; }
}
</style>
@endpush

<div class="lx-shell">

    {{-- ════ SIDEBAR ════ --}}
    <div class="lx-sb">
        <div class="lx-sb-head">
            <div class="lx-sb-title">
                <div class="lx-sb-icon">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="rgba(139,92,246,0.75)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                </div>
                <span class="lx-sb-heading">Cases</span>
            </div>
            <div class="lx-search">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" placeholder="Search cases…" id="lxSearch" autocomplete="off">
            </div>
        </div>

        <div class="lx-cases" id="lxCaseList">
            @forelse($cases as $c)
                <a href="{{ route('lawyer.messages.index', $c) }}"
                   class="lx-case {{ isset($activeCase) && $activeCase->id === $c->id ? 'active' : '' }}"
                   data-name="{{ strtolower($c->title) }}">
                    <div class="lx-case-avi">{{ strtoupper(substr($c->title,0,2)) }}</div>
                    <div class="lx-case-info">
                        <div class="lx-case-name">{{ $c->title }}</div>
                        <div class="lx-case-meta">{{ $c->case_number }} · {{ $c->client->name }}</div>
                    </div>
                </a>
            @empty
                <div class="lx-no-cases">No cases assigned yet.</div>
            @endforelse
        </div>
    </div>

    {{-- ════ MAIN ════ --}}
    <div class="lx-main">

        @if(session('success'))
            <div class="lx-flash ok">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="lx-flash err">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ session('error') }}
            </div>
        @endif

        @if(isset($activeCase))

            <div class="lx-bar">
                <div class="lx-bar-avi">{{ strtoupper(substr($activeCase->title,0,2)) }}</div>
                <div class="lx-bar-info">
                    <div class="lx-bar-title">{{ $activeCase->title }}</div>
                    <div class="lx-bar-sub">{{ $activeCase->client->name }}&ensp;·&ensp;{{ $activeCase->case_number }}</div>
                </div>
                <div class="lx-pill ok"><span class="lx-pill-dot"></span>Active</div>
                <a href="{{ route('lawyer.cases.show', $activeCase) }}" class="lx-icon-btn" title="Open case">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                </a>
            </div>

            <div class="lx-feed" id="lxFeed">
                @forelse($messages as $msg)
                    @php $out = $msg->sender_id === auth()->id(); @endphp
                    <div class="lx-msg {{ $out ? 'out' : 'in' }}">
                        <div class="lx-avi">{{ strtoupper(substr($msg->sender->name,0,1)) }}</div>
                        <div class="lx-col">
                            <div class="lx-bubble">{{ $msg->body }}</div>
                            <div class="lx-foot">
                                <span class="lx-role {{ $msg->sender->role==='lawyer' ? 'l' : 'c' }}">
                                    {{ $msg->sender->role==='lawyer' ? 'Lawyer' : 'Client' }}
                                </span>
                                {{ $msg->created_at->format('M d, g:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lx-empty">
                        <div class="lx-empty-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(139,92,246,0.5)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                        </div>
                        <h3>No messages yet</h3>
                        <p>Start the conversation with {{ $activeCase->client->name }} about this case.</p>
                        <div class="lx-empty-cta">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polyline points="22,2 15,22 11,13 2,9"/></svg>
                            Type below to send your first message
                        </div>
                    </div>
                @endforelse
            </div>

            @if(isset($messages) && method_exists($messages,'hasPages') && $messages->hasPages())
                <div class="lx-pagi">{{ $messages->links() }}</div>
            @endif

            <div class="lx-compose">
                <form method="POST" action="{{ route('lawyer.messages.store') }}" id="lxForm">
                    @csrf
                    <input type="hidden" name="case_id"     value="{{ $activeCase->id }}">
                    <input type="hidden" name="receiver_id" value="{{ $activeCase->client_id }}">
                    <div class="lx-compose-box">
                        <textarea id="lxTa" name="body" class="lx-ta" rows="1"
                            placeholder="Message {{ $activeCase->client->name }}…" required></textarea>
                        <button type="submit" class="lx-send" aria-label="Send">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polyline points="22,2 15,22 11,13 2,9"/>
                            </svg>
                        </button>
                    </div>
                </form>
                <div class="lx-hint">
                    <kbd>Enter</kbd> send &nbsp;·&nbsp; <kbd>Shift+Enter</kbd> new line
                </div>
            </div>

        @else

            <div class="lx-splash">
                <div class="lx-splash-card">
                    <div class="lx-splash-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="rgba(139,92,246,0.55)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <h2>Select a case to begin</h2>
                    <p>Choose a case from the sidebar to view your message thread with the client.</p>

                    @if($cases->isNotEmpty())
                        <div class="lx-splash-cases">
                            <div class="lx-splash-cases-label">Your cases</div>
                            @foreach($cases->take(4) as $c)
                                <a href="{{ route('lawyer.messages.index', $c) }}" class="lx-splash-row">
                                    <div class="lx-splash-avi">{{ strtoupper(substr($c->title,0,2)) }}</div>
                                    <div class="lx-splash-info">
                                        <div class="lx-splash-name">{{ $c->title }}</div>
                                        <div class="lx-splash-meta">{{ $c->client->name }}</div>
                                    </div>
                                    <svg class="lx-splash-arr" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        @endif

    </div>{{-- /lx-main --}}
</div>{{-- /lx-shell --}}

@push('scripts')
<script>
(function(){
    const feed = document.getElementById('lxFeed');
    if (feed) feed.scrollTop = feed.scrollHeight;

    const ta = document.getElementById('lxTa');
    if (ta) {
        const grow = () => {
            ta.style.height = 'auto';
            ta.style.height = Math.min(ta.scrollHeight, 108) + 'px';
        };
        ta.addEventListener('input', grow);
        ta.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('lxForm')?.submit();
            }
        });
        grow();
    }

    const s = document.getElementById('lxSearch');
    if (s) s.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.lx-case').forEach(el => {
            el.style.display = (el.dataset.name || '').includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush