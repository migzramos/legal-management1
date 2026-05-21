@extends('layouts.lawyer')

@section('title', 'Billing')
@section('page_title', 'Billing')
@section('page_subtitle', 'Track invoices, payments, and outstanding balances')

@section('topbar_actions')
    <a href="{{ route('lawyer.billing.invoices.create') }}" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Invoice
    </a>
@endsection

@section('content')

{{-- ── KPI CARDS ── --}}
<div class="kpi-grid">

    <div class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Total Invoices</span>
            <div class="kpi-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
        </div>
        <div class="kpi-value">{{ $totalInvoices ?? 0 }}</div>
        <div class="kpi-meta">All time invoices</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Unpaid Balance</span>
            <div class="kpi-icon" style="background:rgba(248,113,113,0.1);color:var(--danger)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
        </div>
        <div class="kpi-value" style="color:var(--danger)">₱{{ number_format($unpaidTotal ?? 0, 2) }}</div>
        <div class="kpi-meta">{{ $unpaidCount ?? 0 }} unpaid {{ Str::plural('invoice', $unpaidCount ?? 0) }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Paid This Month</span>
            <div class="kpi-icon" style="background:rgba(34,211,165,0.1);color:var(--success)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
        <div class="kpi-value" style="color:var(--success)">₱{{ number_format($paidThisMonth ?? 0, 2) }}</div>
        <div class="kpi-meta">{{ now()->format('F Y') }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <span class="kpi-label">Overdue</span>
            <div class="kpi-icon" style="background:rgba(245,158,11,0.1);color:var(--warn)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="kpi-value" style="color:var(--warn)">{{ $overdueCount ?? 0 }}</div>
        <div class="kpi-meta">₱{{ number_format($overdueTotal ?? 0, 2) }} past due</div>
    </div>

</div>

{{-- ── INVOICES TABLE CARD ── --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Invoices</span>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <button onclick="filterTable('all', this)"
                style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;cursor:pointer;border:1px solid var(--p);background:rgba(124,58,237,0.15);color:var(--p3);font-family:'Outfit',sans-serif;transition:all 0.15s"
                class="filter-btn">All</button>
            <button onclick="filterTable('unpaid', this)"
                style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:transparent;color:var(--t3);font-family:'Outfit',sans-serif;transition:all 0.15s"
                class="filter-btn">Unpaid</button>
            <button onclick="filterTable('paid', this)"
                style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:transparent;color:var(--t3);font-family:'Outfit',sans-serif;transition:all 0.15s"
                class="filter-btn">Paid</button>
            <button onclick="filterTable('overdue', this)"
                style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:transparent;color:var(--t3);font-family:'Outfit',sans-serif;transition:all 0.15s"
                class="filter-btn">Overdue</button>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1px solid var(--border)">
                    <th style="padding:10px 18px;text-align:left;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Invoice #</th>
                    <th style="padding:10px 18px;text-align:left;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Client</th>
                    <th style="padding:10px 18px;text-align:left;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Date</th>
                    <th style="padding:10px 18px;text-align:left;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Due Date</th>
                    <th style="padding:10px 18px;text-align:right;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Amount</th>
                    <th style="padding:10px 18px;text-align:center;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Status</th>
                    <th style="padding:10px 18px;text-align:right;font-size:0.65rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap">Action</th>
                </tr>
            </thead>
            <tbody id="invoiceTableBody">
                @forelse($invoices ?? [] as $invoice)
                @php
                    $isOverdue = $invoice->due_date
                        && \Carbon\Carbon::parse($invoice->due_date)->isPast()
                        && $invoice->status !== 'paid';
                    $displayStatus = $isOverdue ? 'overdue' : $invoice->status;
                    $badgeMap = [
                        'paid'    => 'badge-paid',
                        'unpaid'  => 'badge-unpaid',
                        'overdue' => 'badge-overdue',
                        'draft'   => 'badge-draft',
                        'sent'    => 'badge-sent',
                        'pending' => 'badge-pending',
                        'partial' => 'badge-ongoing',
                    ];
                    $badgeClass = $badgeMap[$displayStatus] ?? 'badge-draft';
                @endphp
                <tr class="invoice-row" data-status="{{ $displayStatus }}"
                    style="border-bottom:1px solid var(--border);transition:background 0.15s"
                    onmouseover="this.style.background='rgba(124,58,237,0.05)'"
                    onmouseout="this.style.background='transparent'">

                    <td style="padding:13px 18px">
                        <span style="font-family:monospace;font-size:0.78rem;color:var(--p3);letter-spacing:0.03em">
                            {{ $invoice->invoice_number ?? '#'.str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>

                    <td style="padding:13px 18px">
                        <div style="font-size:0.87rem;font-weight:500;color:var(--t1)">
                            {{ $invoice->client->name ?? '—' }}
                        </div>
                        @if(!empty($invoice->case->title))
                        <div style="font-size:0.72rem;color:var(--t3);margin-top:2px">
                            {{ Str::limit($invoice->case->title, 32) }}
                        </div>
                        @endif
                    </td>

                    <td style="padding:13px 18px;font-size:0.82rem;color:var(--t2);white-space:nowrap">
                        {{ \Carbon\Carbon::parse($invoice->issued_date ?? $invoice->created_at)->format('M d, Y') }}
                    </td>

                    <td style="padding:13px 18px;font-size:0.82rem;white-space:nowrap">
                        @if($invoice->due_date)
                            <span style="color:{{ $isOverdue ? 'var(--danger)' : 'var(--t2)' }}">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                            </span>
                            @if($isOverdue)
                                <div style="font-size:0.68rem;color:var(--danger);margin-top:1px">
                                    {{ \Carbon\Carbon::parse($invoice->due_date)->diffForHumans() }}
                                </div>
                            @endif
                        @else
                            <span style="color:var(--t3)">—</span>
                        @endif
                    </td>

                    <td style="padding:13px 18px;text-align:right;white-space:nowrap">
                        <span style="font-family:'Playfair Display',serif;font-size:0.95rem;font-weight:600;color:var(--t1)">
                            ₱{{ number_format($invoice->total, 2) }}
                        </span>
                    </td>

                    <td style="padding:13px 18px;text-align:center">
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($displayStatus) }}</span>
                    </td>

                    <td style="padding:13px 18px;text-align:right">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                            <a href="{{ route('lawyer.billing.invoices.show', $invoice) }}"
                               style="font-size:0.76rem;color:var(--p3);text-decoration:none;padding:4px 10px;border:1px solid rgba(124,58,237,0.25);border-radius:7px;transition:all 0.15s;white-space:nowrap"
                               onmouseover="this.style.background='rgba(124,58,237,0.12)';this.style.borderColor='var(--p)'"
                               onmouseout="this.style.background='transparent';this.style.borderColor='rgba(124,58,237,0.25)'">
                                View
                            </a>
                            @if(!$invoice->is_validated)
                            <a href="{{ route('lawyer.billing.invoices.edit', $invoice) }}"
                               style="font-size:0.76rem;color:var(--t3);text-decoration:none;padding:4px 10px;border:1px solid var(--border);border-radius:7px;transition:all 0.15s;white-space:nowrap"
                               onmouseover="this.style.background='rgba(255,255,255,0.04)';this.style.color='var(--t1)'"
                               onmouseout="this.style.background='transparent';this.style.color='var(--t3)'">
                                Edit
                            </a>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                 style="width:36px;height:36px;margin:0 auto 10px;opacity:0.3">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            <h4>No invoices yet</h4>
                            <p>Create your first invoice to start tracking payments.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($invoices) && $invoices->count())
    <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:0.75rem;color:var(--t3)">
            Showing <span id="visibleCount">{{ $invoices->count() }}</span> of {{ $invoices->count() }} invoices
        </span>
        <a href="{{ route('lawyer.billing.index') }}" class="card-action">View all invoices →</a>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
function filterTable(status, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.style.background = 'transparent';
        b.style.color = 'var(--t3)';
        b.style.borderColor = 'var(--border)';
    });
    btn.style.background = 'rgba(124,58,237,0.15)';
    btn.style.color = 'var(--p3)';
    btn.style.borderColor = 'var(--p)';

    const rows = document.querySelectorAll('.invoice-row');
    let visible = 0;
    rows.forEach(row => {
        const show = status === 'all' || row.dataset.status === status;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const countEl = document.getElementById('visibleCount');
    if (countEl) countEl.textContent = visible;
}
</script>
@endpush