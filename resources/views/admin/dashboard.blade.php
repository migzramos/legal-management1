<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — LegalCase</title>
    @include('admin.partials.styles')
</head>
<body>
<div class="app-layout">

    @include('admin.partials.sidebar')

    <div class="main-area">
        <div class="topbar">
            <div>
                <div class="topbar-title">Firm Overview</div>
                <div class="topbar-subtitle">Complete analytics and management dashboard</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="23" y2="17"/><line x1="20" y1="14" x2="26" y2="14"/></svg>
                    Add User
                </a>
                <a href="{{ route('admin.reports.page') }}" class="btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Generate Report
                </a>
            </div>
        </div>

        <div class="page-content">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- KPI Grid --}}
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-label">Total Revenue</div>
                        <div class="kpi-icon" style="background:rgba(52,211,153,0.12);color:#34d399;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ config('legal.currency_symbol') }}{{ number_format($totalRevenue / 1000, 0) }}K</div>
                    <div class="kpi-meta" style="color:#34d399;">↑ 15% this month</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-label">Active Cases</div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $activeCases }}</div>
                    <div class="kpi-meta" style="color:#34d399;">↑ 8% this month</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-label">Team Members</div>
                        <div class="kpi-icon" style="background:rgba(96,165,250,0.12);color:#60a5fa;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $totalUsers }}</div>
                    <div class="kpi-meta" style="color:#34d399;">↑ 4% this month</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <div class="kpi-label">Success Rate</div>
                        <div class="kpi-icon" style="background:rgba(251,191,36,0.12);color:#fbbf24;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $successRate }}%</div>
                    <div class="kpi-meta" style="color:#f87171;">↓ 3% this month</div>
                </div>
            </div>

            {{-- Main Grid --}}
            <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
                <div style="display:flex;flex-direction:column;gap:20px;">

                    {{-- Revenue Chart --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Revenue Trend</div>
                                <div class="card-subtitle">Last 6 months</div>
                            </div>
                        </div>
                        <div class="card-body">
                            @php $months = ['Oct','Nov','Dec','Jan','Feb','Mar']; $heights = [55,65,60,75,70,85]; @endphp
                            <div style="display:flex;align-items:flex-end;gap:8px;height:160px;padding-bottom:24px;position:relative;">
                                @foreach($months as $i => $month)
                                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;justify-content:flex-end;">
                                    <div style="width:100%;border-radius:6px 6px 0 0;background:linear-gradient(180deg,var(--purple-light),var(--purple-core));height:{{ $heights[$i] }}%;transition:opacity .2s;" onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'"></div>
                                    <div style="font-size:0.68rem;color:var(--text-muted);">{{ $month }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Team Performance --}}
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">Team Performance</div>
                                <div class="card-subtitle">Active lawyers</div>
                            </div>
                            <a href="{{ route('admin.users.index') }}" class="btn-link">Manage Team</a>
                        </div>
                        <div class="card-body-flush">
                            @forelse($lawyers as $lawyer)
                            <div class="list-item">
                                <div class="message-avatar">{{ strtoupper(substr($lawyer->name, 0, 1)) }}</div>
                                <div class="list-item-left">
                                    <div style="font-size:0.9rem;font-weight:500;">{{ $lawyer->name }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">Lawyer</div>
                                </div>
                                <div class="list-item-right" style="text-align:right;">
                                    <div style="font-size:0.88rem;font-weight:500;">{{ $lawyer->active_cases_count }} cases</div>
                                    <span class="status-active">Active</span>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <div class="empty-state-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                                <div class="empty-state-title">No lawyers yet</div>
                                <div class="empty-state-text">Add lawyers to see their performance here.</div>
                            </div>
                            @endforelse
                        </div>
                        <div style="padding:16px 20px;border-top:1px solid var(--border);">
                            <a href="{{ route('admin.users.index') }}" class="btn-secondary" style="width:100%;justify-content:center;">View All Members</a>
                        </div>
                    </div>

                    {{-- System Statistics --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">System Statistics</div>
                        </div>
                        <div class="card-body">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                @foreach([['Total Users',$totalUsers],['Active Cases',$activeCases],['Won Cases',$wonCases],['Success Rate',$successRate.'%']] as [$label,$val])
                                <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:12px;padding:16px;">
                                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">{{ $label }}</div>
                                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:600;">{{ $val }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Column --}}
                <div style="display:flex;flex-direction:column;gap:20px;">

                    {{-- System Alerts --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">System Alerts</div>
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn-link">View All</a>
                        </div>
                        <div class="card-body-flush">
                            @forelse($auditLogs as $log)
                            <div class="list-item-compact">
                                <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:{{ $log->getAlertType() === 'warning' ? 'var(--warning)' : ($log->getAlertType() === 'danger' ? 'var(--danger)' : ($log->getAlertType() === 'success' ? 'var(--success)' : 'var(--info)')) }};margin-top:4px;"></div>
                                <div>
                                    <div style="font-size:0.84rem;color:var(--text-secondary);line-height:1.5;">{{ $log->description }}</div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:2px;">{{ $log->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state"><div class="empty-state-text">No recent activity</div></div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Quick Actions</div>
                        </div>
                        <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                            <a href="{{ route('admin.users.index') }}" class="quick-link">
                                <div class="quick-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                                Add New User
                            </a>
                            <a href="{{ route('admin.reports.page') }}" class="quick-link">
                                <div class="quick-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                                Generate Report
                            </a>
                            <a href="{{ route('admin.categories.index') }}" class="quick-link">
                                <div class="quick-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></div>
                                System Settings
                            </a>
                        </div>
                    </div>

                    {{-- This Week --}}
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">This Week</div>
                        </div>
                        <div class="card-body-flush">
                            @foreach([['Court Hearings',$courtHearingsThisWeek],['Client Meetings',$meetingsThisWeek],['Deadlines',$deadlinesThisWeek]] as [$label,$val])
                            <div class="list-item-compact" style="justify-content:space-between;">
                                <span style="font-size:0.85rem;color:var(--text-secondary);">{{ $label }}</span>
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:600;">{{ $val }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div style="padding:16px 20px;border-top:1px solid var(--border);">
                            <a href="{{ route('admin.calendar') }}" class="btn-primary" style="width:100%;justify-content:center;">View Firm Calendar</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>