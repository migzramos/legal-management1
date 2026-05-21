{{-- resources/views/admin/reports-pdf.blade.php --}}
{{-- Used by ReportController::export() via barryvdh/laravel-dompdf --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        /* ── Reset & base ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #fff;
            padding: 36px 40px;
        }

        /* ── Header ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 2px solid #1a1a1a;
            margin-bottom: 28px;
        }
        .firm-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .report-title {
            font-size: 13px;
            color: #555;
            margin-top: 4px;
        }
        .report-meta {
            text-align: right;
            font-size: 11px;
            color: #777;
            line-height: 1.7;
        }

        /* ── Section heading ── */
        h2 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #1a1a1a;
            margin: 28px 0 14px;
            padding-bottom: 6px;
            border-bottom: 1px solid #ddd;
        }

        /* ── KPI summary grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .kpi-box {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 14px 16px;
            background: #fafafa;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #888;
            margin-bottom: 6px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 700;
            color: #111;
            line-height: 1;
        }

        /* ── Billing & task row ── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .stat-box {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .stat-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 5px; }
        .stat-value { font-size: 15px; font-weight: 700; color: #111; }
        .stat-note  { font-size: 9px; color: #aaa; margin-top: 4px; }
        .text-danger { color: #e74c3c; }
        .text-warn   { color: #e67e22; }
        .text-ok     { color: #27ae60; }

        /* ── Case outcomes table ── */
        .outcomes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .outcomes-table th,
        .outcomes-table td {
            border: 1px solid #e0e0e0;
            padding: 9px 14px;
            font-size: 12px;
            text-align: left;
        }
        .outcomes-table th {
            background: #f4f4f4;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .outcomes-table tr:nth-child(even) td { background: #fafafa; }

        /* ── Lawyer performance table ── */
        .lawyer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .lawyer-table th,
        .lawyer-table td {
            border: 1px solid #e0e0e0;
            padding: 9px 14px;
            font-size: 12px;
            text-align: left;
        }
        .lawyer-table th {
            background: #f4f4f4;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lawyer-table tr:nth-child(even) td { background: #fafafa; }
        .revenue-cell { color: #27ae60; font-weight: 700; }

        /* ── Monthly chart (text-based) ── */
        .month-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .month-table th,
        .month-table td {
            border: 1px solid #e0e0e0;
            padding: 9px 14px;
            font-size: 12px;
            text-align: left;
        }
        .month-table th {
            background: #f4f4f4;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .month-table tr:nth-child(even) td { background: #fafafa; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 36px;
            padding-top: 14px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #aaa;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="report-header">
        <div>
            <div class="firm-name">LegalCase</div>
            <div class="report-title">Firm Performance Report</div>
        </div>
        <div class="report-meta">
            Generated: {{ now()->format('F j, Y \a\t H:i') }}<br>
            Period: Last 6 months<br>
            Confidential — Internal Use Only
        </div>
    </div>

    {{-- ── KPIs ── --}}
    @php $currency = config('legal.currency_symbol', '₱'); @endphp
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="kpi-label">Total Cases</div>
            <div class="kpi-value">{{ number_format($totalCases) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">{{ $currency }}{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Win Rate</div>
            <div class="kpi-value">{{ $winRate }}%</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-label">Avg Case Value</div>
            <div class="kpi-value">{{ $currency }}{{ number_format($avgCaseValue, 2) }}</div>
        </div>
    </div>

    {{-- ── Tasks & Billing ── --}}
    <h2>Tasks &amp; Billing Overview</h2>
    <div class="stat-row">
        <div class="stat-box">
            <div class="stat-label">Pending Tasks</div>
            <div class="stat-value {{ $pendingTasks > 0 ? 'text-warn' : 'text-ok' }}">
                {{ number_format($pendingTasks) }}
            </div>
            <div class="stat-note">{{ $pendingTasks > 0 ? 'Awaiting action' : 'All clear' }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Overdue Tasks</div>
            <div class="stat-value {{ $overdueTasks > 0 ? 'text-danger' : 'text-ok' }}">
                {{ number_format($overdueTasks) }}
            </div>
            <div class="stat-note">{{ $overdueTasks > 0 ? 'Requires immediate attention' : 'None overdue' }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Pending Billing</div>
            <div class="stat-value {{ $pendingAmount > 0 ? 'text-warn' : 'text-ok' }}">
                {{ $currency }}{{ number_format($pendingAmount, 2) }}
            </div>
            <div class="stat-note">Uncollected outstanding</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Closed Cases</div>
            <div class="stat-value">{{ number_format($closedCases) }}</div>
            <div class="stat-note">{{ $wonCases }} won · {{ $lostCases }} lost</div>
        </div>
    </div>

    {{-- ── Case Outcome Breakdown ── --}}
    <h2>Case Outcome Breakdown</h2>
    @php
        $pieTotal     = max(1, $wonCases + $ongoingCases + $lostCases + $dismissedCases);
        $outcomes = [
            ['label' => 'Won',       'count' => $wonCases,      'pct' => round($wonCases      / $pieTotal * 100)],
            ['label' => 'Ongoing',   'count' => $ongoingCases,  'pct' => round($ongoingCases  / $pieTotal * 100)],
            ['label' => 'Lost',      'count' => $lostCases,     'pct' => round($lostCases     / $pieTotal * 100)],
            ['label' => 'Dismissed', 'count' => $dismissedCases,'pct' => round($dismissedCases/ $pieTotal * 100)],
            ['label' => 'Total',     'count' => $totalCases,    'pct' => 100],
        ];
    @endphp
    <table class="outcomes-table">
        <thead>
            <tr>
                <th>Outcome</th>
                <th>Case Count</th>
                <th>Share</th>
            </tr>
        </thead>
        <tbody>
            @foreach($outcomes as $outcome)
            <tr>
                <td>{{ $outcome['label'] }}</td>
                <td>{{ number_format($outcome['count']) }}</td>
                <td>{{ $outcome['label'] === 'Total' ? '—' : $outcome['pct'].'%' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Lawyer Performance ── --}}
    <h2>Lawyer Performance</h2>
    @if($lawyers->isEmpty())
        <p style="color:#aaa;font-style:italic;margin-bottom:28px;">No lawyer records found.</p>
    @else
        <table class="lawyer-table">
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
                    <td>{{ $lawyer['name'] }}</td>
                    <td>{{ $lawyer['active_cases'] }}</td>
                    <td>{{ $lawyer['total_cases'] }}</td>
                    <td>{{ $lawyer['won_cases'] }}</td>
                    <td class="revenue-cell">{{ $currency }}{{ number_format($lawyer['revenue'], 2) }}</td>
                    <td>{{ $lawyer['win_rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── Monthly Breakdown ── --}}
    <h2>Monthly Performance</h2>
    <table class="month-table">
        <thead>
            <tr>
                <th>Month</th>
                <th>Cases Filed</th>
                <th>Revenue Collected</th>
            </tr>
        </thead>
        <tbody>
            @foreach($months as $i => $month)
            <tr>
                <td>{{ $month }}</td>
                <td>{{ number_format($caseCounts[$i]) }}</td>
                <td>{{ $currency }}{{ number_format($revenues[$i], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Footer ── --}}
    <div class="report-footer">
        <span>LegalCase — Firm Performance Report</span>
        <span>Generated {{ now()->format('Y-m-d H:i') }} · Confidential</span>
    </div>

</body>
</html>