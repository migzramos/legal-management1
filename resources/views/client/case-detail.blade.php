<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Details — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @include('client.partials.styles')
    <style>
        .info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-bottom: 24px; }
        .info-card { background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 16px; padding: 22px; }
        .info-card h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.25rem; margin-bottom: 10px; }
        .info-card p { color: var(--text-secondary); line-height: 1.7; }
        .case-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 18px; }
        .meta-item label { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 6px; }
        .meta-item span { font-size: 0.95rem; color: var(--text-primary); font-weight: 500; }
        .link-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin: 22px 0; }
        .link-card { display: flex; align-items: center; justify-content: space-between; gap: 14px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 18px; text-decoration: none; color: var(--text-primary); }
        .link-card:hover { border-color: var(--border-hover); }
        .section-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .section-title h3 { font-size: 1rem; margin: 0; }
        .case-status { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .case-status.open { background: rgba(96,165,250,0.12); color: var(--info); border: 1px solid rgba(96,165,250,0.25); }
        .case-status.ongoing { background: rgba(251,191,36,0.12); color: var(--warning); border: 1px solid rgba(251,191,36,0.25); }
        .case-status.won { background: rgba(52,211,153,0.12); color: var(--success); border: 1px solid rgba(52,211,153,0.25); }
        .case-status.closed, .case-status.dismissed, .case-status.lost { background: rgba(255,255,255,0.05); color: var(--text-secondary); border: 1px solid var(--border); }
        .document-list, .schedule-list { display: grid; gap: 14px; }
        .document-item, .schedule-item { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 16px; display: flex; justify-content: space-between; gap: 12px; }
        .document-details p, .schedule-details p { margin: 0; color: var(--text-secondary); }
        .stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 24px; }
        .stat-card { background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 16px; padding: 18px; }
        .stat-card span { display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px; }
        .stat-card strong { font-size: 1.3rem; display: block; }
    </style>
</head>
<body>
<div class="bg-scene"></div>
<div class="app">
    @include('client.partials.sidebar')
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Case Details</h1>
                <p>Review case progress, documents, and upcoming events.</p>
            </div>
            <div class="topbar-right">
                <a href="{{ route('client.cases.index') }}" class="btn-secondary">Back to Cases</a>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @php
                $progressSteps = $case->getProgressSteps();
                $statusOrder = array_keys($progressSteps);
                $currentIndex = array_search($case->status, $statusOrder);
                $statusLabels = [
                    'intake' => 'Intake',
                    'barangay_mediation' => 'Barangay Mediation',
                    'escalation_to_court' => 'Escalation to Court',
                    'active_case' => 'Active Case',
                    'resolution' => 'Resolution',
                ];
                $completedAt = $case->status_updated_at ?? $case->updated_at;
            @endphp
            <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 mb-7">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white">Legal Workflow Progress</h3>
                        <p class="mt-1 text-sm text-slate-400">A read-only stepper showing where your case stands.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-violet-600 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white">
                        {{ $statusLabels[$case->status] ?? ucfirst(str_replace('_', ' ', $case->status)) }}
                    </span>
                </div>

                <div class="mt-6 flex items-center gap-4 overflow-x-auto">
                    @foreach($progressSteps as $key => $step)
                        @php $stepIndex = array_search($key, $statusOrder); $isActive = $key === $case->status; @endphp
                        <div class="flex items-center gap-3 min-w-[140px]">
                            <div class="flex flex-col items-center text-center">
                                <div class="flex items-center justify-center rounded-full border-2 {{ $step['completed'] ? 'bg-violet-500 border-violet-500 text-white' : 'bg-slate-800 border-slate-700 text-slate-400' }} {{ $isActive ? 'w-14 h-14 shadow-[0_0_0_10px_rgba(124,58,237,0.22)] text-base' : 'w-12 h-12 text-sm' }}">
                                    {{ $stepIndex + 1 }}
                                </div>
                                <span class="mt-3 text-[11px] uppercase tracking-[0.18em] {{ $step['completed'] ? 'text-violet-200' : 'text-slate-500' }}">{{ $step['label'] }}</span>
                                @if($step['completed'])
                                    <span class="mt-2 text-[11px] text-slate-400">{{ $completedAt?->format('M d, Y') }}</span>
                                @endif
                            </div>
                            @if($stepIndex < count($statusOrder) - 1)
                                <div class="flex-1 h-1 rounded-full {{ $step['completed'] && $currentIndex > $stepIndex ? 'bg-violet-500' : 'bg-slate-700' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="info-card">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                    <div>
                        <div class="case-number">{{ $case->case_number }}</div>
                        <h2>{{ $case->title }}</h2>
                        <span class="case-status {{ $case->status }}">{{ ucfirst($case->status) }}</span>
                    </div>
                    <div class="case-actions" style="display:flex; gap:12px; flex-wrap:wrap;">
                        <a href="{{ route('client.cases.progress', $case->id) }}" class="btn-primary">View Progress</a>
                        <a href="{{ route('client.documents.index', $case->id) }}" class="btn-secondary">View Documents</a>
                        <a href="{{ route('client.messages.index', $case->id) }}" class="btn-secondary">Messages</a>
                    </div>
                </div>
                <div class="case-meta">
                    <div class="meta-item"><label>Category</label><span>{{ $case->category->name ?? 'General' }}</span></div>
                    <div class="meta-item"><label>Court Type</label><span>{{ $case->courtType->name ?? 'N/A' }}</span></div>
                    <div class="meta-item"><label>Assigned Lawyer</label><span>{{ $case->lawyer->name ?? 'Not assigned' }}</span></div>
                    <div class="meta-item"><label>Filed Date</label><span>{{ $case->filed_date?->format('M d, Y') ?? 'N/A' }}</span></div>
                    <div class="meta-item"><label>Hearing Date</label><span>{{ $case->hearing_date?->format('M d, Y') ?? 'Not set' }}</span></div>
                    <div class="meta-item"><label>Closed Date</label><span>{{ $case->closed_date?->format('M d, Y') ?? 'Still open' }}</span></div>
                </div>
            </div>

            <div class="link-grid">
                <a href="{{ route('client.cases.progress', $case->id) }}" class="link-card">
                    <div>
                        <h3>Progress</h3>
                        <p>{{ isset($progress) ? $progress . '% completed' : $case->tasks->count() . ' tasks' }}</p>
                    </div>
                    <span class="badge badge-purple">Details</span>
                </a>
                <a href="{{ route('client.documents.index', $case->id) }}" class="link-card">
                    <div>
                        <h3>Documents</h3>
                        <p>{{ $case->documents->count() }} visible documents</p>
                    </div>
                    <span class="badge badge-info">Open</span>
                </a>
                <a href="{{ route('client.messages.index', $case->id) }}" class="link-card">
                    <div>
                        <h3>Messages</h3>
                        <p>Chat with your lawyer for updates</p>
                    </div>
                    <span class="badge badge-success">Open</span>
                </a>
            </div>

            <div class="stat-grid">
                <div class="stat-card"><span>Total Tasks</span><strong>{{ $case->tasks->count() }}</strong></div>
                <div class="stat-card"><span>Completed</span><strong>{{ $case->tasks->where('status','completed')->count() }}</strong></div>
                <div class="stat-card"><span>Visible Documents</span><strong>{{ $case->documents->count() }}</strong></div>
            </div>

            @if($case->invoices->count())
            <div class="info-card">
                <div class="section-title"><h3>Unpaid Invoices</h3></div>
                <div class="invoice-list" style="display: grid; gap: 14px;">
                    @foreach($case->invoices as $invoice)
                        <div class="invoice-item" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="margin: 0; font-weight: 500;">Invoice #{{ $invoice->id }}</p>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">{{ config('legal.currency_symbol') }}{{ number_format($invoice->balance, 2) }} due</p>
                            </div>
                            <button class="btn-primary pay-now-btn" data-invoice-id="{{ $invoice->id }}" data-amount="{{ $invoice->balance }}">Pay Now</button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="info-grid">
                <div class="info-card">
                    <div class="section-title"><h3>About the Case</h3></div>
                    <p>{{ $case->description ?? 'No additional case description has been provided.' }}</p>
                </div>
                <div class="info-card">
                    <div class="section-title"><h3>Recent Schedule</h3></div>
                    @php $schedule = $case->schedules->sortBy('scheduled_at')->first(); @endphp
                    @if($schedule)
                        <div class="meta-item"><label>Next Event</label><span>{{ ucfirst($schedule->type) }} on {{ $schedule->scheduled_at->format('M d, Y g:i A') }}</span></div>
                        <div class="meta-item"><label>Status</label><span>{{ ucfirst($schedule->status) }}</span></div>
                    @else
                        <p class="empty-state">No schedules added yet for this case.</p>
                    @endif
                </div>
            </div>

            <div class="info-card">
                <div class="section-title"><h3>Visible Documents</h3></div>
                @if($case->documents->count())
                    <div class="document-list">
                        @foreach($case->documents as $document)
                            <div class="document-item">
                                <div class="document-details">
                                    <p class="case-title">{{ $document->title }}</p>
                                    <p>{{ $document->file_name }} • {{ $document->category ?? 'Document' }}</p>
                                </div>
                                <div>
                                    <a href="{{ route('client.documents.show', $document->id) }}" class="btn-secondary">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No client-visible documents found</h3>
                        <p>Your lawyer has not shared documents with you yet for this case.</p>
                    </div>
                @endif
            </div>

            @if(isset($upcomingSchedules) && $upcomingSchedules->count())
            <div class="info-card">
                <div class="section-title"><h3>Upcoming Schedules</h3></div>
                <div class="schedule-list">
                    @foreach($upcomingSchedules as $schedule)
                        <div class="schedule-item">
                            <div class="schedule-details">
                                <p>{{ ucfirst($schedule->type) }} at {{ $schedule->scheduled_at->format('M d, Y g:i A') }}</p>
                                <p>{{ $schedule->notes ?? 'No additional notes.' }}</p>
                            </div>
                            <span class="badge badge-warning">{{ ucfirst($schedule->status) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @include('client.partials.payment-popup')
    </main>
</div>
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config("services.stripe.key") }}');
const elements = stripe.elements();
let cardElement;
let currentInvoiceId;
let currentAmount;

function openPaymentModal(invoiceId, amount) {
    currentInvoiceId = invoiceId;
    currentAmount = amount;
    document.getElementById('amount-display').textContent = amount;
    document.getElementById('payment-modal').classList.add('active');
    loadSavedMethods();
}

function closePaymentModal() {
    document.getElementById('payment-modal').classList.remove('active');
}

function loadSavedMethods() {
    fetch('/client/payment-methods')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('saved-methods');
            container.innerHTML = '';
            if (data.length === 0) {
                container.innerHTML = '<p>No saved payment methods. Add a new card below.</p>';
                showAddCard();
            } else {
                data.forEach(method => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="payment_method_id" value="${method.id}">
                            ${method.card.brand} **** ${method.card.last4}
                        </label>
                    `;
                    container.appendChild(div);
                });
                const addNew = document.createElement('div');
                addNew.innerHTML = '<button type="button" onclick="showAddCard()" class="btn-secondary">Add New Card</button>';
                container.appendChild(addNew);
            }
        });
}

function showAddCard() {
    document.getElementById('add-card-section').style.display = 'block';
    if (!cardElement) {
        cardElement = elements.create('card');
        cardElement.mount('#card-element');
    }
}

document.getElementById('add-card-btn').addEventListener('click', async () => {
    const { client_secret } = await fetch('/client/payment-methods/setup-intent', { method: 'POST' }).then(r => r.json());
    const { setupIntent, error } = await stripe.confirmCardSetup(client_secret, {
        payment_method: { card: cardElement }
    });
    if (error) {
        alert(error.message);
    } else {
        loadSavedMethods();
        document.getElementById('add-card-section').style.display = 'none';
    }
});

document.getElementById('payment-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const paymentMethodId = formData.get('payment_method_id');
    if (!paymentMethodId) {
        alert('Please select a payment method');
        return;
    }
    const response = await fetch(`/client/invoices/${currentInvoiceId}/pay`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ payment_method_id: paymentMethodId, amount: currentAmount })
    });
    const result = await response.json();
    if (result.message) {
        alert('Payment successful');
        closePaymentModal();
        location.reload();
    } else {
        alert(result.error);
    }
});

// Attach to buttons
document.querySelectorAll('.pay-now-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const invoiceId = btn.dataset.invoiceId;
        const amount = btn.dataset.amount;
        openPaymentModal(invoiceId, amount);
    });
});
</script>
</body>
</html>
