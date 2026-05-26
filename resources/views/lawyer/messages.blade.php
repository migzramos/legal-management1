
@extends('layouts.lawyer')
 
@section('title', 'Messages')
@section('page_title', '')
@section('page_subtitle', '')
 
@push('styles')
<style>
/* ─────────────────────────────────────────────────────────────────
   RESET — kill layout chrome so our shell owns the viewport
───────────────────────────────────────────────────────────────── */
html, body { overflow: hidden; height: 100%; }
 
/* Hide global topbar */
.topbar, .top-bar, nav.topbar, header.topbar,
[class*="topbar"], [class*="top-bar"],
.navbar-top, .layout-topbar, .app-topbar { display: none !important; }
 
/* Hide page title row injected by the layout */
.page-header, .page-title-bar, .page-topbar,
[class*="page-header"], [class*="page-subtitle"],
.page > h1, .page > .header, .page > .title-row { display: none !important; }
 
/* Collapse .page wrapper so it doesn't push anything */
.page {
    padding: 0 !important; margin: 0 !important;
    overflow: visible !important; display: block !important;
    height: 0 !important; min-height: 0 !important;
}
 
/* ─────────────────────────────────────────────────────────────────
   TOKENS
───────────────────────────────────────────────────────────────── */
:root {
    --nav-w: 60px;          /* left icon rail width  */
 
    --void:    #07051a;
    --surface: #0d0b1c;
    --raised:  #110e26;
 
    --p:  #7c3aed;
    --p2: #a855f7;
    --p3: #c084fc;
 
    --b0: rgba(139,92,246,0.07);
    --b1: rgba(139,92,246,0.13);
    --b2: rgba(139,92,246,0.24);
    --b3: rgba(139,92,246,0.42);
 
    --t1: rgba(237,232,255,0.96);
    --t2: rgba(237,232,255,0.65);
    --t3: rgba(237,232,255,0.38);
    --t4: rgba(237,232,255,0.18);
 
    --success: #34d399;
    --danger:  #f87171;
 
    --r-sm: 8px;
    --r-md: 11px;
    --r-lg: 14px;
 
    --sb-w: 260px;
}
 
/* ─────────────────────────────────────────────────────────────────
   SHELL — fixed, starts at top-left of content area (right of nav)
───────────────────────────────────────────────────────────────── */
.ms-shell {
    position: fixed;
    top: 0;
    left: var(--nav-w);
    right: 0;
    bottom: 0;
    display: flex;
    overflow: hidden;
    background: var(--void);
    font-family: 'Outfit', sans-serif;
    z-index: 100;
}
 
/* ═══════════════════════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════════════════════ */
.ms-sidebar {
    width: var(--sb-w);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    background: var(--surface);
    border-right: 1px solid var(--b1);
    overflow: hidden;
}
 
.ms-sb-head {
    padding: 16px 14px 12px;
    border-bottom: 1px solid var(--b0);
    flex-shrink: 0;
}
 
.ms-sb-title {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 11px;
}
.ms-sb-icon {
    width: 22px; height: 22px;
    border-radius: 6px;
    background: rgba(124,58,237,0.18);
    border: 1px solid var(--b2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ms-sb-label {
    font-size: 0.63rem;
    font-weight: 700;
    letter-spacing: 0.11em;
    text-transform: uppercase;
    color: var(--t3);
}
 
.ms-search {
    display: flex;
    align-items: center;
    gap: 7px;
    width: 100%;
    box-sizing: border-box;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--b1);
    border-radius: var(--r-sm);
    padding: 7px 10px;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.ms-search:focus-within {
    border-color: var(--b3);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.06);
}
.ms-search svg { color: var(--t4); flex-shrink: 0; }
.ms-search-input {
    background: none; border: none; outline: none;
    color: var(--t1); font-family: 'Outfit', sans-serif;
    font-size: 0.78rem; width: 100%; min-width: 0;
}
.ms-search-input::placeholder { color: var(--t4); }
 
.ms-cases-list {
    flex: 1 1 0;
    min-height: 0;
    overflow-y: auto;
    padding: 7px 7px 12px;
    scrollbar-width: thin;
    scrollbar-color: rgba(124,58,237,0.18) transparent;
}
.ms-cases-list::-webkit-scrollbar { width: 3px; }
.ms-cases-list::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.18); border-radius: 3px; }
 
.ms-no-cases {
    padding: 28px 10px;
    text-align: center;
    font-size: 0.73rem;
    color: var(--t4);
    line-height: 1.7;
}
 
.ms-case-link {
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
.ms-case-link:hover  { background: rgba(124,58,237,0.09); color: var(--t1); }
.ms-case-link.active { background: rgba(124,58,237,0.16); color: var(--t1); }
.ms-case-link.active::before {
    content: '';
    position: absolute;
    left: 0; top: 22%; bottom: 22%;
    width: 2.5px;
    border-radius: 0 3px 3px 0;
    background: linear-gradient(180deg, var(--p), var(--p2));
}
 
.ms-case-avi {
    width: 34px; height: 34px;
    border-radius: var(--r-sm);
    background: linear-gradient(135deg, #2e1a6e, var(--p));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.66rem; font-weight: 700; color: #fff;
    flex-shrink: 0; letter-spacing: 0.04em;
    box-shadow: 0 2px 6px rgba(124,58,237,0.24);
}
.ms-case-info { flex: 1 1 0; min-width: 0; }
.ms-case-name {
    font-size: 0.8rem; font-weight: 500; color: inherit;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 1px; line-height: 1.2;
}
.ms-case-meta {
    font-size: 0.65rem; color: var(--t4);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
 
/* ═══════════════════════════════════════════════════════════════
   MAIN PANEL
═══════════════════════════════════════════════════════════════ */
.ms-main {
    flex: 1 1 0;
    min-width: 0; min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--void);
}
 
/* Flash */
.ms-flash {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 20px; font-size: 0.77rem; flex-shrink: 0;
}
.ms-flash.ok  { background: rgba(52,211,153,0.08); color: var(--success); border-bottom: 1px solid rgba(52,211,153,0.18); }
.ms-flash.err { background: rgba(248,113,113,0.08); color: var(--danger);  border-bottom: 1px solid rgba(248,113,113,0.18); }
 
/* Thread bar */
.ms-thread-bar {
    display: flex; align-items: center; gap: 11px;
    padding: 11px 20px;
    border-bottom: 1px solid var(--b1);
    background: var(--surface);
    flex-shrink: 0;
}
.ms-thread-avi {
    width: 36px; height: 36px; border-radius: var(--r-md);
    background: linear-gradient(135deg, #2e1a6e, var(--p));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(124,58,237,0.3);
}
.ms-thread-info { flex: 1 1 0; min-width: 0; }
.ms-thread-title {
    font-size: 0.88rem; font-weight: 600; color: var(--t1);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    line-height: 1.25; margin-bottom: 1px;
}
.ms-thread-sub { font-size: 0.67rem; color: var(--t3); }
 
.ms-status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.63rem; font-weight: 600; letter-spacing: 0.04em;
    background: rgba(52,211,153,0.08); color: var(--success);
    border: 1px solid rgba(52,211,153,0.18);
    white-space: nowrap; flex-shrink: 0;
}
.ms-status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
 
.ms-icon-btn {
    width: 30px; height: 30px; border-radius: var(--r-sm);
    background: rgba(255,255,255,0.035); border: 1px solid var(--b1);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--t3); text-decoration: none; flex-shrink: 0;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
}
.ms-icon-btn:hover { background: rgba(124,58,237,0.1); border-color: var(--b2); color: var(--t1); }
 
/* Feed */
.ms-feed {
    flex: 1 1 0; min-height: 0;
    overflow-y: auto;
    padding: 18px 22px 12px;
    display: flex; flex-direction: column; gap: 2px;
    scrollbar-width: thin;
    scrollbar-color: rgba(124,58,237,0.14) transparent;
}
.ms-feed::-webkit-scrollbar { width: 3px; }
.ms-feed::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.16); border-radius: 3px; }
 
/* Date divider */
.ms-date-divider {
    display: flex; align-items: center; gap: 10px;
    margin: 12px 0 4px;
}
.ms-date-divider::before, .ms-date-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--b1);
}
.ms-date-divider span {
    font-size: 0.59rem; color: var(--t4);
    font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.09em; white-space: nowrap;
}
 
/* Message row */
.ms-msg {
    display: flex; gap: 8px; align-items: flex-end;
    margin-bottom: 1px;
    animation: msgPop 0.17s cubic-bezier(.22,.68,0,1.12) both;
}
.ms-msg.out { flex-direction: row-reverse; }
@keyframes msgPop {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: none; }
}
 
/* Collapse repeat avatars */
.ms-msg.in  + .ms-msg.in  .ms-msg-avi,
.ms-msg.out + .ms-msg.out .ms-msg-avi { visibility: hidden; }
.ms-msg.in  + .ms-msg.in,
.ms-msg.out + .ms-msg.out { margin-bottom: -2px; }
 
.ms-msg-avi {
    width: 25px; height: 25px; border-radius: 6px;
    background: rgba(255,255,255,0.04); border: 1px solid var(--b1);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.58rem; font-weight: 700; color: var(--t3);
    flex-shrink: 0;
}
.ms-msg.out .ms-msg-avi {
    background: linear-gradient(135deg, #2e1a6e, var(--p));
    color: #fff; border: none;
    box-shadow: 0 2px 7px rgba(124,58,237,0.26);
}
 
.ms-msg-body { max-width: 60%; min-width: 0; }
 
.ms-bubble {
    padding: 8px 13px;
    font-size: 0.845rem; line-height: 1.6; word-break: break-word;
}
.ms-msg.in .ms-bubble {
    background: rgba(255,255,255,0.045);
    border: 1px solid var(--b1);
    border-radius: var(--r-md) var(--r-md) var(--r-md) 3px;
    color: var(--t1);
}
.ms-msg.out .ms-bubble {
    background: linear-gradient(140deg, #3b1fa3, var(--p));
    border-radius: var(--r-md) var(--r-md) 3px var(--r-md);
    color: #fff;
    box-shadow: 0 3px 12px rgba(124,58,237,0.22);
}
 
.ms-msg-foot {
    display: flex; align-items: center; gap: 5px;
    font-size: 0.59rem; color: var(--t4);
    margin-top: 3px; padding: 0 2px;
}
.ms-msg.out .ms-msg-foot { justify-content: flex-end; }
 
.ms-badge-role {
    padding: 1px 5px; border-radius: 3px;
    font-size: 0.55rem; font-weight: 700;
    letter-spacing: 0.05em; text-transform: uppercase;
}
.ms-badge-role.lawyer { background: rgba(124,58,237,0.18); color: var(--p3); }
.ms-badge-role.client { background: rgba(255,255,255,0.055); color: var(--t3); }
 
/* Empty thread */
.ms-empty {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center; padding: 32px 20px;
}
.ms-empty-ico {
    width: 52px; height: 52px; border-radius: 13px;
    background: rgba(124,58,237,0.07); border: 1px solid var(--b1);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
}
.ms-empty h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem; font-weight: 700; color: var(--t2);
    margin-bottom: 7px;
}
.ms-empty p {
    font-size: 0.76rem; color: var(--t3);
    max-width: 230px; line-height: 1.68; margin-bottom: 16px;
}
.ms-empty-cta {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 20px;
    background: rgba(124,58,237,0.08); border: 1px solid var(--b1);
    font-size: 0.69rem; color: var(--t3);
}
 
/* Pagination */
.ms-pagi {
    flex-shrink: 0; padding: 8px 20px;
    border-top: 1px solid var(--b0); background: var(--surface);
    display: flex; justify-content: center;
}
 
/* Compose */
.ms-compose {
    flex-shrink: 0;
    padding: 12px 16px 14px;
    border-top: 1px solid var(--b1);
    background: var(--surface);
}
.ms-compose-form {
    display: flex; align-items: flex-end; gap: 9px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--b2);
    border-radius: var(--r-lg);
    padding: 8px 9px 8px 14px;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.ms-compose-form:focus-within {
    border-color: var(--b3);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.07);
}
.ms-compose-ta {
    flex: 1 1 0; min-width: 0;
    background: none; border: none; outline: none;
    color: var(--t1); font-family: 'Outfit', sans-serif;
    font-size: 0.86rem; resize: none;
    min-height: 20px; max-height: 110px;
    overflow-y: auto; line-height: 1.55;
}
.ms-compose-ta::placeholder { color: rgba(237,232,255,0.18); }
.ms-compose-btn {
    width: 33px; height: 33px; border-radius: var(--r-sm);
    background: linear-gradient(135deg, var(--p), var(--p2));
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(124,58,237,0.34);
    transition: opacity 0.13s, transform 0.1s;
}
.ms-compose-btn:hover  { opacity: 0.85; }
.ms-compose-btn:active { transform: scale(0.89); }
 
.ms-compose-hint {
    margin-top: 5px; padding-left: 2px;
    font-size: 0.59rem; color: rgba(237,232,255,0.16);
    display: flex; align-items: center; gap: 4px;
}
.ms-compose-hint kbd {
    display: inline-block; padding: 1px 4px;
    background: rgba(255,255,255,0.04); border: 1px solid var(--b1);
    border-radius: 3px; font-size: 0.57rem;
    font-family: monospace; color: var(--t3); line-height: 1.5;
}
 
/* ═══════════════════════════════════════════════════════════════
   SPLASH — centered card when no case is selected
═══════════════════════════════════════════════════════════════ */
.ms-splash {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 32px 24px; position: relative; overflow: hidden;
}
.ms-splash::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background: radial-gradient(ellipse 50% 38% at 50% 44%,
                rgba(124,58,237,0.08) 0%, transparent 68%);
}
.ms-splash-card {
    position: relative;
    width: 100%; max-width: 340px;
    background: var(--surface);
    border: 1px solid var(--b1);
    border-radius: 18px;
    padding: 32px 28px 26px;
    text-align: center;
    box-shadow: 0 0 0 1px var(--b0), 0 8px 40px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.04);
}
.ms-splash-card::before {
    content: '';
    position: absolute; top: 0; left: 20%; right: 20%; height: 1px;
    background: linear-gradient(90deg, transparent, var(--p2), transparent);
    opacity: 0.4;
}
.ms-splash-ico {
    width: 56px; height: 56px; border-radius: 14px;
    background: rgba(124,58,237,0.12); border: 1px solid var(--b2);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    box-shadow: 0 4px 18px rgba(124,58,237,0.16);
}
.ms-splash-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.12rem; font-weight: 700; color: var(--t1);
    margin-bottom: 7px; letter-spacing: -0.02em;
}
.ms-splash-card p {
    font-size: 0.775rem; color: var(--t3); line-height: 1.7;
    max-width: 260px; margin: 0 auto;
}
.ms-splash-cases { margin-top: 20px; text-align: left; }
.ms-splash-cases-label {
    font-size: 0.6rem; text-transform: uppercase;
    letter-spacing: 0.1em; color: var(--t4);
    font-weight: 700; margin-bottom: 8px; padding-left: 1px;
}
.ms-splash-row {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r-md);
    background: rgba(255,255,255,0.02); border: 1px solid var(--b0);
    text-decoration: none; color: var(--t2); margin-bottom: 5px;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
}
.ms-splash-row:last-child { margin-bottom: 0; }
.ms-splash-row:hover { background: rgba(124,58,237,0.1); border-color: var(--b2); color: var(--t1); }
.ms-splash-avi {
    width: 28px; height: 28px; border-radius: var(--r-sm);
    background: linear-gradient(135deg, #2e1a6e, var(--p));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.63rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.ms-splash-info { flex: 1 1 0; min-width: 0; }
.ms-splash-name {
    font-size: 0.78rem; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 1px;
}
.ms-splash-meta { font-size: 0.62rem; color: var(--t4); }
.ms-splash-arr { color: var(--t4); flex-shrink: 0; }
 
/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════════ */
@media (max-width: 860px) {
    :root { --sb-w: 220px; }
    .ms-feed { padding: 14px 14px 10px; }
    .ms-msg-body { max-width: 74%; }
}
@media (max-width: 640px) {
    :root { --sb-w: 52px; }
    .ms-sb-label, .ms-search, .ms-case-info { display: none !important; }
    .ms-sb-title { justify-content: center; margin-bottom: 0; }
    .ms-sb-head  { padding: 12px 8px; }
    .ms-case-link { justify-content: center; padding: 8px 0; }
    .ms-case-link.active::before { display: none; }
    .ms-case-avi { width: 30px; height: 30px; font-size: 0.62rem; }
    .ms-thread-sub, .ms-icon-btn { display: none; }
    .ms-feed { padding: 10px 10px; }
    .ms-msg-body { max-width: 84%; }
    .ms-compose { padding: 8px 8px 10px; }
}
@media (max-width: 420px) { .ms-sidebar { display: none; } }
</style>
@endpush
 
<div class="ms-shell">
 
    {{-- ══ SIDEBAR ══ --}}
    <div class="ms-sidebar">
        <div class="ms-sb-head">
            <div class="ms-sb-title">
                <div class="ms-sb-icon">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="rgba(167,139,250,0.8)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                </div>
                <span class="ms-sb-label">Cases</span>
            </div>
            <div class="ms-search">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input class="ms-search-input" type="text" placeholder="Search cases…" id="msCaseSearch" autocomplete="off">
            </div>
        </div>
 
        <div class="ms-cases-list">
            @forelse($cases as $caseItem)
                <a href="{{ route('lawyer.messages.index', $caseItem) }}"
                   class="ms-case-link {{ isset($activeCase) && $activeCase->id === $caseItem->id ? 'active' : '' }}"
                   data-name="{{ strtolower($caseItem->title) }}">
                    <div class="ms-case-avi">{{ strtoupper(substr($caseItem->title, 0, 2)) }}</div>
                    <div class="ms-case-info">
                        <div class="ms-case-name">{{ $caseItem->title }}</div>
                        <div class="ms-case-meta">{{ $caseItem->case_number }} · {{ $caseItem->client->name }}</div>
                    </div>
                </a>
            @empty
                <div class="ms-no-cases">No cases assigned yet.</div>
            @endforelse
        </div>
    </div>
 
    {{-- ══ MAIN PANEL ══ --}}
    <div class="ms-main">
 
        @if(session('success'))
            <div class="ms-flash ok">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="ms-flash err">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ session('error') }}
            </div>
        @endif
 
        @if(isset($activeCase))
 
            {{-- Thread bar --}}
            <div class="ms-thread-bar">
                <div class="ms-thread-avi">{{ strtoupper(substr($activeCase->title, 0, 2)) }}</div>
                <div class="ms-thread-info">
                    <div class="ms-thread-title">{{ $activeCase->title }}</div>
                    <div class="ms-thread-sub">{{ $activeCase->client->name }} · {{ $activeCase->case_number }}</div>
                </div>
                <div class="ms-status-pill">
                    <span class="ms-status-dot"></span> Active
                </div>
                <a href="{{ route('lawyer.cases.show', $activeCase) }}" class="ms-icon-btn" title="Open case">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                </a>
            </div>
 
            {{-- Feed --}}
            <div class="ms-feed" id="msFeed">
                @forelse($messages as $message)
                    @php $sent = $message->sender_id === auth()->id(); @endphp
                    <div class="ms-msg {{ $sent ? 'out' : 'in' }}" data-msg-id="{{ $message->id }}">
                        <div class="ms-msg-avi">{{ strtoupper(substr($message->sender->name, 0, 1)) }}</div>
                        <div class="ms-msg-body">
                            <div class="ms-bubble">{{ $message->body }}</div>
                            <div class="ms-msg-foot">
                                <span class="ms-badge-role {{ $message->sender->role === 'lawyer' ? 'lawyer' : 'client' }}">
                                    {{ $message->sender->role === 'lawyer' ? 'Lawyer' : 'Client' }}
                                </span>
                                {{ $message->created_at->format('M d, g:i A') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="ms-empty">
                        <div class="ms-empty-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(167,139,250,0.45)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                        </div>
                        <h3>No messages yet</h3>
                        <p>Send your first message to begin communicating with your client about this case.</p>
                        <div class="ms-empty-cta">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polyline points="22,2 15,22 11,13 2,9"/></svg>
                            Type below to send your first message
                        </div>
                    </div>
                @endforelse
            </div>
 
            @if(isset($messages) && method_exists($messages, 'hasPages') && $messages->hasPages())
                <div class="ms-pagi">{{ $messages->links() }}</div>
            @endif
 
            {{-- Compose --}}
            <div class="ms-compose">
                <form class="ms-compose-form" method="POST" action="{{ route('lawyer.messages.store') }}" id="msForm">
                    @csrf
                    <input type="hidden" name="case_id"     value="{{ $activeCase->id }}">
                    <input type="hidden" name="receiver_id" value="{{ $activeCase->client_id }}">
                    <textarea
                        id="msCompose"
                        name="body"
                        class="ms-compose-ta"
                        placeholder="Message {{ $activeCase->client->name }}…"
                        rows="1"
                        required
                    ></textarea>
                    <button type="submit" class="ms-compose-btn" aria-label="Send">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polyline points="22,2 15,22 11,13 2,9"/>
                        </svg>
                    </button>
                </form>
                <div class="ms-compose-hint">
                    <kbd>Enter</kbd> send &nbsp;·&nbsp; <kbd>Shift+Enter</kbd> new line
                </div>
            </div>
 
        @else
 
            {{-- No case selected — centered card --}}
            <div class="ms-splash">
                <div class="ms-splash-card">
                    <div class="ms-splash-ico">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="rgba(167,139,250,0.5)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <h2>Select a case to begin</h2>
                    <p>Pick a case from the sidebar to view and continue the conversation with your client.</p>
 
                    @if($cases->isNotEmpty())
                        <div class="ms-splash-cases">
                            <div class="ms-splash-cases-label">Your cases</div>
                            @foreach($cases->take(4) as $caseItem)
                                <a href="{{ route('lawyer.messages.index', $caseItem) }}" class="ms-splash-row">
                                    <div class="ms-splash-avi">{{ strtoupper(substr($caseItem->title, 0, 2)) }}</div>
                                    <div class="ms-splash-info">
                                        <div class="ms-splash-name">{{ $caseItem->title }}</div>
                                        <div class="ms-splash-meta">{{ $caseItem->client->name }}</div>
                                    </div>
                                    <svg class="ms-splash-arr" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
 
        @endif
 
    </div>{{-- /ms-main --}}
</div>{{-- /ms-shell --}}
 
@push('scripts')
<script>
(function () {
    const feed    = document.getElementById('msFeed');
    const ta      = document.getElementById('msCompose');
    const form    = document.getElementById('msForm');
    const search  = document.getElementById('msCaseSearch');
    const POLL_MS = 3000;

    function scrollBottom() { if (feed) feed.scrollTop = feed.scrollHeight; }
    scrollBottom();

    let lastId = 0;
    document.querySelectorAll('[data-msg-id]').forEach(el => {
        const id = parseInt(el.dataset.msgId, 10);
        if (id > lastId) lastId = id;
    });

    const ME = {{ auth()->id() }};

    function buildBubble(msg) {
        const out  = msg.sender_id === ME;
        const role = (msg.sender_role === 'lawyer') ? 'lawyer' : 'client';
        const lbl  = (msg.sender_role === 'lawyer') ? 'Lawyer' : 'Client';
        const init = msg.sender_name.charAt(0).toUpperCase();
        const row  = document.createElement('div');
        row.className = 'ms-msg ' + (out ? 'out' : 'in');
        row.dataset.msgId = msg.id;
        row.innerHTML = '<div class="ms-msg-avi">' + init + '</div>'
            + '<div class="ms-msg-body">'
            + '<div class="ms-bubble">' + msg.body + '</div>'
            + '<div class="ms-msg-foot">'
            + '<span class="ms-badge-role ' + role + '">' + lbl + '</span> '
            + msg.created_at
            + '</div></div>';
        return row;
    }

    function clearEmpty() {
        const e = feed && feed.querySelector('.ms-empty');
        if (e) e.remove();
    }

    // AJAX send
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const body = ta.value.trim();
            if (!body) return;
            const data = new FormData(form);
            const btn  = form.querySelector('.ms-compose-btn');
            if (btn) btn.disabled = true;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: data,
            })
            .then(r => r.json())
            .then(json => {
                if (json.success && json.data) {
                    clearEmpty();
                    const m = json.data;
                    const msg = {
                        id:          m.id,
                        sender_id:   m.sender_id,
                        sender_name: m.sender ? m.sender.name : 'Me',
                        sender_role: m.sender ? m.sender.role : 'lawyer',
                        body:        m.body.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                        created_at:  new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}),
                    };
                    if (msg.id > lastId) lastId = msg.id;
                    feed.appendChild(buildBubble(msg));
                    scrollBottom();
                    ta.value = '';
                    ta.style.height = 'auto';
                }
            })
            .catch(() => {})
            .finally(() => { if (btn) btn.disabled = false; });
        });
    }

    // Auto-grow textarea + Enter to send
    if (ta) {
        const grow = () => { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 110) + 'px'; };
        ta.addEventListener('input', grow);
        ta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form && form.dispatchEvent(new Event('submit', {bubbles:true, cancelable:true}));
            }
        });
        grow();
    }

    // Polling every 3s
    @isset($activeCase)
    const pollUrl = '{{ route("lawyer.messages.index", $activeCase) }}';
    const csrf2   = document.querySelector('meta[name="csrf-token"]').content;
    function poll() {
        fetch(pollUrl + '?after=' + lastId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf2 },
        })
        .then(r => r.json())
        .then(json => {
            if (!json.messages || !json.messages.length) return;
            clearEmpty();
            const atBottom = feed.scrollHeight - feed.scrollTop - feed.clientHeight < 60;
            json.messages.forEach(msg => {
                if (msg.id > lastId) lastId = msg.id;
                feed.appendChild(buildBubble(msg));
            });
            if (atBottom) scrollBottom();
        })
        .catch(() => {});
    }
    setInterval(poll, POLL_MS);
    @endisset

    // Live case search
    if (search) {
        search.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.ms-case-link').forEach(el => {
                el.style.display = (el.dataset.name || '').includes(q) ? '' : 'none';
            });
        });
    }
})();
</script>
@endpush
