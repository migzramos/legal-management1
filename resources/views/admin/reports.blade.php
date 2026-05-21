{{-- resources/views/admin/reports.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    @include('admin.partials.styles')
    <style>
        /* ─── CSS custom properties (reports-scoped overrides) ─── */
        :root {
            --sidebar-w: 64px;
            --topbar-h:  64px;
            --gap:       20px;
            --radius:    14px;
            --card-bg:   rgba(255,255,255,0.04);
            --card-border: rgba(255,255,255,0.08);
            --purple:    #7c3aed;
            --purple-lt: #a78bfa;
            --green:     #34d399;
            --blue:      #60a5fa;
            --orange:    #fb923c;
            --red:       #f87171;
            --text:      #f1f5f9;
            --muted:     #94a3b8;
            --bg:        #0f0f1a;
            --bg2:       #15152a;
        }

        /* ─── Reset ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ─── App shell ─── */
        body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; min-height: 100vh; }

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ─── Sidebar (fixed, icon-only) ─── */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--bg2);
            border-right: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 0;
            z-index: 100;
            gap: 4px;
        }

        /* ─── Main area offset from sidebar ─── */
        .main-area {
            margin-left: var(--sidebar-w);
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* ─── Topbar ─── */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            height: var(--topbar-h);
            background: rgba(15,15,26,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            gap: 16px;
            flex-shrink: 0;
        }
        .topbar-title { font-size: 1.15rem; font-weight: 600; line-height: 1.2; }
        .topbar-sub   { font-size: 0.78rem; color: var(--muted); margin-top: 1px; }

        /* ─── Scrollable page content ─── */
        .page-content {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: var(--gap);
            overflow-x: hidden;
        }

        /* ─── Export button ─── */
        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            background: var(--purple);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, transform .1s;
        }
        .btn-export:hover  { background: #6d28d9; }
        .btn-export:active { transform: scale(0.97); }

        /* ─── Card base ─── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 22px 24px;
        }
        .card-title {
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: .02em;
            margin-bottom: 4px;
        }
        .card-sub {
            font-size: 0.72rem;
            color: var(--muted);
            margin-bottom: 18px;
        }

        /* ─── KPI grid — 4 equal columns ─── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0,1fr));
            gap: var(--gap);
        }
        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            overflow: hidden;
            transition: border-color .2s, transform .15s;
        }
        .kpi-card:hover { border-color: rgba(124,58,237,.35); transform: translateY(-2px); }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }
        .kpi-card:nth-child(1)::before { background: linear-gradient(90deg,var(--purple),transparent); }
        .kpi-card:nth-child(2)::before { background: linear-gradient(90deg,var(--green),transparent); }
        .kpi-card:nth-child(3)::before { background: linear-gradient(90deg,var(--blue),transparent); }
        .kpi-card:nth-child(4)::before { background: linear-gradient(90deg,var(--orange),transparent); }

        .kpi-icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .kpi-icon.purple { background: rgba(124,58,237,.18); color: var(--purple-lt); }
        .kpi-icon.green  { background: rgba(52,211,153,.15); color: var(--green); }
        .kpi-icon.blue   { background: rgba(96,165,250,.15); color: var(--blue); }
        .kpi-icon.orange { background: rgba(251,146,60,.15); color: var(--orange); }

        .kpi-label {
            font-size: 0.68rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .09em;
        }
        .kpi-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ─── Stat row — 4 equal columns ─── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0,1fr));
            gap: var(--gap);
        }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            transition: border-color .2s;
        }
        .stat-card:hover { border-color: rgba(255,255,255,.14); }
        .stat-label {
            font-size: 0.68rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .09em;
        }
        .stat-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            width: fit-content;
        }
        .badge-ok     { background: rgba(52,211,153,.12); color: var(--green); }
        .badge-warn   { background: rgba(251,191,36,.12);  color: #fbbf24; }
        .badge-danger { background: rgba(248,113,113,.12); color: var(--red); }
        .pulse-dot    { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; animation: rpulse 1.6s ease-in-out infinite; }
        @keyframes rpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.65)} }

        /* ─── Charts row — bar left, pie right ─── */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: var(--gap);
            align-items: start;
        }

        /* ── Bar chart ── */
        .bar-chart-wrap {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            height: 140px;
        }
        .bar-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            height: 100%;
            justify-content: flex-end;
        }
        .bar-pair { display: flex; gap: 3px; align-items: flex-end; }
        .bar {
            border-radius: 4px 4px 0 0;
            min-width: 10px;
            transition: opacity .18s;
            cursor: pointer;
        }
        .bar:hover { opacity: .7; }
        .bar-cases   { background: var(--purple-lt); }
        .bar-revenue { background: var(--green); }
        .bar-x       { font-size: 0.65rem; color: var(--muted); }

        .chart-legend { display: flex; gap: 16px; }
        .legend-item  { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--muted); }
        .legend-dot   { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

        /* ── Pie / donut ── */
        .pie-wrap       { display: flex; flex-direction: column; align-items: center; gap: 20px; }
        .pie-donut-wrap { position: relative; width: 140px; height: 140px; flex-shrink: 0; }
        .pie-donut      { width: 140px; height: 140px; border-radius: 50%; }
        .pie-hole       { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        .pie-hole-inner {
            width: 70px; height: 70px; border-radius: 50%;
            background: var(--bg2);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .pie-center-val { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 700; line-height: 1; }
        .pie-center-lbl { font-size: 0.58rem; color: var(--muted); text-transform: uppercase; letter-spacing: .07em; margin-top: 1px; }
        .pie-legend     { display: flex; flex-direction: column; gap: 9px; width: 100%; }
        .pie-leg-row    { display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; }
        .pie-leg-left   { display: flex; align-items: center; gap: 8px; color: var(--muted); }
        .pie-leg-dot    { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .pie-leg-pct    { font-weight: 600; color: var(--text); }

        /* ─── Lawyer table ─── */
        .table-wrap  { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table        { width: 100%; border-collapse: collapse; min-width: 560px; }
        thead th     {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            padding: 10px 14px;
            border-bottom: 1px solid var(--card-border);
            text-align: left;
            white-space: nowrap;
        }
        tbody td     { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,.04); font-size: 0.84rem; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr     { transition: background .12s; }
        tbody tr:hover { background: rgba(255,255,255,.03); }

        .avatar-sm {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--purple), var(--purple-lt));
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 600;
            flex-shrink: 0;
        }
        .win-track  { height: 4px; background: rgba(255,255,255,.07); border-radius: 2px; margin-top: 5px; overflow: hidden; min-width: 80px; }
        .win-fill   { height: 100%; border-radius: 2px; background: var(--green); transition: width .4s ease; }
        .revenue-val { color: var(--green); font-weight: 600; }

        .empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 24px; color: var(--muted); text-align: center; }
        .empty-state svg { opacity: .2; }
        .empty-state p { font-size: .85rem; }

        /* ─── Responsive breakpoints ─── */
        @media (max-width: 1100px) {
            .kpi-grid  { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .stat-row  { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .charts-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            :root { --sidebar-w: 0px; }
            .sidebar { display: none; }
            .page-content { padding: 16px; }
            .topbar { padding: 0 16px; }
            .kpi-grid  { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
            .stat-row  { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
            .kpi-value { font-size: 1.5rem; }
        }
        @media (max-width: 420px) {
            .kpi-grid  { grid-template-columns: 1fr; }
            .stat-row  { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    @include('admin.partials.sidebar')

    <div class="main-area">

        {{-- ── Topbar ── --}}
        <div class="topbar">
            <div>
                <div class="topbar-title">Analytics &amp; Reports</div>
                <div class="topbar-sub">Comprehensive firm performance metrics</div>
            </div>
            <a href="{{ route('admin.reports.export') }}" class="btn-export">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export PDF
            </a>
        </div>

        {{-- ── Page content ── --}}
        <div class="page-content">

            @php $currency = config('legal.currency_symbol', '₱'); @endphp

            {{-- KPI row --}}
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                            <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <div class="kpi-label">Total Cases</div>
                    <div class="kpi-value">{{ number_format($totalCases) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                    </div>
                    <div class="kpi-label">Total Revenue</div>
                    <div class="kpi-value">{{ $currency }}{{ number_format($totalRevenue, 2) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <div class="kpi-label">Win Rate</div>
                    <div class="kpi-value">{{ $winRate }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        </svg>
                    </div>
                    <div class="kpi-label">Avg Case Value</div>
                    <div class="kpi-value">{{ $currency }}{{ number_format($avgCaseValue, 2) }}</div>
                </div>
            </div>

            {{-- Stat row --}}
            <div class="stat-row">
                <div class="stat-card">
                    <div class="stat-label">Pending Tasks</div>
                    <div class="stat-value">{{ number_format($pendingTasks) }}</div>
                    @if($pendingTasks > 0)
                        <div class="stat-badge badge-warn"><span class="pulse-dot"></span> {{ $pendingTasks }} awaiting</div>
                    @else
                        <div class="stat-badge badge-ok">All clear</div>
                    @endif
                </div>
                <div class="stat-card">
                    <div class="stat-label">Overdue Tasks</div>
                    <div class="stat-value">{{ number_format($overdueTasks) }}</div>
                    @if($overdueTasks > 0)
                        <div class="stat-badge badge-danger"><span class="pulse-dot"></span> {{ $overdueTasks }} overdue</div>
                    @else
                        <div class="stat-badge badge-ok">None overdue</div>
                    @endif
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending Billing</div>
                    <div class="stat-value">{{ $currency }}{{ number_format($pendingAmount, 2) }}</div>
                    @if($pendingAmount > 0)
                        <div class="stat-badge badge-warn">Uncollected</div>
                    @else
                        <div class="stat-badge badge-ok">Fully collected</div>
                    @endif
                </div>
                <div class="stat-card">
                    <div class="stat-label">Closed Cases</div>
                    <div class="stat-value">{{ number_format($closedCases) }}</div>
                    <div class="stat-badge badge-ok">{{ $wonCases }} won &middot; {{ $lostCases }} lost</div>
                </div>
            </div>

            {{-- Charts row --}}
            <div class="charts-row">

                {{-- Bar chart --}}
                <div class="card">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:6px;">
                        <div>
                            <div class="card-title">Monthly Performance</div>
                            <div class="card-sub">Cases filed vs. revenue collected — last 6 months</div>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item"><div class="legend-dot" style="background:var(--purple-lt)"></div> Cases</div>
                            <div class="legend-item"><div class="legend-dot" style="background:var(--green)"></div> Revenue</div>
                        </div>
                    </div>

                    @php
                        $maxCase = max(array_merge($caseCounts, [1]));
                        $maxRev  = max(array_merge($revenues,   [1]));
                        $barH    = 120;
                    @endphp

                    <div class="bar-chart-wrap">
                        @foreach($months as $i => $month)
                            @php
                                $ch = $caseCounts[$i] > 0 ? (int) round(($caseCounts[$i] / $maxCase) * $barH) : 2;
                                $rh = $revenues[$i]   > 0 ? (int) round(($revenues[$i]   / $maxRev)  * $barH) : 2;
                            @endphp
                            <div class="bar-col">
                                <div class="bar-pair">
                                    <div class="bar bar-cases"   style="width:13px;height:{{ $ch }}px" title="{{ $caseCounts[$i] }} cases in {{ $month }}"></div>
                                    <div class="bar bar-revenue" style="width:13px;height:{{ $rh }}px" title="{{ $currency }}{{ number_format($revenues[$i]) }} in {{ $month }}"></div>
                                </div>
                                <div class="bar-x">{{ $month }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Pie / donut --}}
                <div class="card">
                    <div class="card-title">Case Outcomes</div>
                    <div class="card-sub">Distribution of all recorded cases</div>

                    @php
                        $pieTotal     = max(1, $wonCases + $ongoingCases + $lostCases + $dismissedCases);
                        $wonPct       = round($wonCases      / $pieTotal * 100);
                        $ongoingPct   = round($ongoingCases  / $pieTotal * 100);
                        $lostPct      = round($lostCases     / $pieTotal * 100);
                        $dismissedPct = 100 - $wonPct - $ongoingPct - $lostPct;
                        $pieSegments  = [
                            ['label'=>'Won',       'pct'=>$wonPct,       'color'=>'#34d399'],
                            ['label'=>'Ongoing',   'pct'=>$ongoingPct,   'color'=>'#a855f7'],
                            ['label'=>'Lost',      'pct'=>$lostPct,      'color'=>'#f87171'],
                            ['label'=>'Dismissed', 'pct'=>$dismissedPct, 'color'=>'#60a5fa'],
                        ];
                        $grad = ''; $cum = 0;
                        foreach ($pieSegments as $s) {
                            $grad .= "{$s['color']} {$cum}% " . ($cum + $s['pct']) . "%, ";
                            $cum  += $s['pct'];
                        }
                        $grad = rtrim($grad, ', ');
                    @endphp

                    <div class="pie-wrap">
                        <div class="pie-donut-wrap">
                            <div class="pie-donut" style="background:conic-gradient({{ $grad }})"></div>
                            <div class="pie-hole">
                                <div class="pie-hole-inner">
                                    <div class="pie-center-val">{{ number_format($totalCases) }}</div>
                                    <div class="pie-center-lbl">Total</div>
                                </div>
                            </div>
                        </div>
                        <div class="pie-legend">
                            @foreach($pieSegments as $s)
                            <div class="pie-leg-row">
                                <div class="pie-leg-left">
                                    <div class="pie-leg-dot" style="background:{{ $s['color'] }}"></div>
                                    {{ $s['label'] }}
                                </div>
                                <span class="pie-leg-pct">{{ $s['pct'] }}%</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>{{-- /charts-row --}}

            {{-- Lawyer Performance table --}}
            <div class="card">
                <div class="card-title" style="margin-bottom:16px;">Lawyer Performance</div>

                @if($lawyers->isEmpty())
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        <p>No lawyers found in the system.</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Lawyer</th>
                                    <th>Active Cases</th>
                                    <th>Total Cases</th>
                                    <th>Won</th>
                                    <th>Revenue (Paid)</th>
                                    <th>Win Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lawyers as $lawyer)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div class="avatar-sm">{{ strtoupper(mb_substr($lawyer['name'], 0, 1)) }}</div>
                                            <span style="font-weight:500;">{{ $lawyer['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $lawyer['active_cases'] }}</td>
                                    <td>{{ $lawyer['total_cases'] }}</td>
                                    <td>{{ $lawyer['won_cases'] }}</td>
                                    <td class="revenue-val">{{ $currency }}{{ number_format($lawyer['revenue'], 2) }}</td>
                                    <td>
                                        <span style="font-weight:600;">{{ $lawyer['win_rate'] }}%</span>
                                        <div class="win-track">
                                            <div class="win-fill" style="width:{{ $lawyer['win_rate'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>{{-- /page-content --}}
    </div>{{-- /main-area --}}
</div>{{-- /app-shell --}}
</body>
</html>