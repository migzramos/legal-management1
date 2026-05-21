@extends('layouts.lawyer')

@section('title', 'Cases & Documents')
@section('page_title', 'Cases & Documents')
@section('page_subtitle', 'Manage and track all your legal cases and documents')

@section('topbar_actions')
<a href="{{ route('lawyer.cases.create') }}" class="btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    New Case
</a>
@endsection

@section('content')

@if(session('success'))
<div class="ci-alert ci-alert-success">{{ session('success') }}</div>
@endif

{{-- ── APPOINTMENTS ── --}}
@php
    $allAppointments = \App\Models\Appointment::where('lawyer_id', auth()->id())
        ->with('client:id,name')
        ->orderBy('appointment_at', 'desc')
        ->limit(8)->get();
@endphp

@if($allAppointments->count() > 0)
<div class="ci-section">
    <div class="ci-section-hd">
        <div class="ci-section-hd-left">
            <div class="ci-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <h2 class="ci-section-title">Upcoming Appointments</h2>
                <p class="ci-section-sub">{{ $allAppointments->count() }} scheduled</p>
            </div>
        </div>
        <a href="{{ route('lawyer.appointments.index') }}" class="ci-link">View all →</a>
    </div>

    <div class="ci-appt-grid">
        @foreach($allAppointments as $appt)
        <div class="ci-appt-card">
            <div class="ci-appt-top">
                <div>
                    <div class="ci-appt-date">{{ $appt->appointment_at->format('M d, Y') }}</div>
                    <div class="ci-appt-title">{{ Str::limit($appt->purpose ?? 'Appointment', 28) }}</div>
                </div>
                @php
                    $ac = match($appt->status) {
                        'confirmed' => 'badge-completed',
                        'cancelled' => 'badge-cancelled',
                        default     => 'badge-pending',
                    };
                @endphp
                <span class="badge {{ $ac }}">{{ $appt->status }}</span>
            </div>
            <div class="ci-appt-meta">
                <div class="ci-appt-meta-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>{{ $appt->appointment_at->format('g:i A') }} · {{ $appt->duration_minutes }}m</span>
                </div>
                <div class="ci-appt-meta-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ $appt->client->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="ci-appt-actions">
                @if($appt->status === 'pending')
                <form method="POST" action="{{ route('lawyer.appointments.confirm', $appt->id) }}" style="flex:1">
                    @csrf @method('PATCH')
                    <button type="submit" class="ci-appt-btn ci-appt-btn-confirm">Confirm</button>
                </form>
                @endif
                <a href="{{ route('lawyer.appointments.show', $appt->id) }}" class="ci-appt-btn ci-appt-btn-view" style="flex:1;text-align:center;text-decoration:none;">View</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── FILTER BAR ── --}}
<div class="ci-filter-bar">
    <button class="ci-filter active" data-filter="all">All</button>
    <button class="ci-filter" data-filter="case">Cases</button>
    <button class="ci-filter" data-filter="document">Documents</button>
    <button class="ci-filter" data-filter="archived">Archived</button>
    <div class="ci-filter-spacer"></div>
    <div class="ci-search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="caseSearch" class="ci-search" placeholder="Search cases…">
    </div>
</div>

{{-- ── CASES GRID ── --}}
<div class="ci-grid" id="casesGrid">

    @forelse($cases as $case)
    <div class="ci-card" data-type="case" data-title="{{ strtolower($case->title) }} {{ strtolower($case->client->name ?? '') }}">
        <div class="ci-card-top">
            <div class="ci-card-icon ci-icon-case">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
            </div>
            @php
                $st = strtolower(str_replace('_',' ',$case->status));
                $sc = match($st) {
                    'active'  => 'badge-active',
                    'ongoing' => 'badge-ongoing',
                    'open'    => 'badge-open',
                    'closed'  => 'badge-closed',
                    'pending' => 'badge-pending',
                    default   => 'badge-draft',
                };
            @endphp
            <span class="badge {{ $sc }}">{{ ucfirst($st) }}</span>
        </div>

        <div class="ci-card-body">
            <div class="ci-card-num">Case #{{ $case->case_number }}</div>
            <div class="ci-card-title">{{ $case->title }}</div>
            <div class="ci-card-cat">{{ $case->category->name ?? 'Uncategorised' }}</div>
        </div>

        <div class="ci-card-meta">
            <div class="ci-card-meta-item">
                <span class="ci-card-meta-label">Client</span>
                <span class="ci-card-meta-val">{{ $case->client->name }}</span>
            </div>
            <div class="ci-card-meta-item">
                <span class="ci-card-meta-label">Court</span>
                <span class="ci-card-meta-val">{{ $case->courtType->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="ci-card-footer">
            <a href="{{ route('lawyer.cases.show', $case) }}" class="ci-btn-view">View Details</a>
            <a href="{{ route('lawyer.cases.edit', $case) }}" class="ci-btn-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
        </div>
    </div>
    @empty
    @endforelse

    @forelse($documents as $document)
    <div class="ci-card" data-type="document" data-title="{{ strtolower($document->title) }}">
        <div class="ci-card-top">
            <div class="ci-card-icon ci-icon-doc">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <span class="badge {{ $document->is_visible_to_client ? 'badge-active' : 'badge-draft' }}">
                {{ $document->is_visible_to_client ? 'Public' : 'Private' }}
            </span>
        </div>

        <div class="ci-card-body">
            <div class="ci-card-num">Document</div>
            <div class="ci-card-title">{{ $document->title }}</div>
            <div class="ci-card-cat">{{ $document->case->title ?? 'General' }}</div>
        </div>

        <div class="ci-card-meta">
            <div class="ci-card-meta-item">
                <span class="ci-card-meta-label">Type</span>
                <span class="ci-card-meta-val">{{ strtoupper($document->file_type) }}</span>
            </div>
            <div class="ci-card-meta-item">
                <span class="ci-card-meta-label">Uploaded</span>
                <span class="ci-card-meta-val">{{ $document->created_at->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="ci-card-footer">
            <a href="{{ route('lawyer.documents.show', $document) }}" class="ci-btn-view">View</a>
            <a href="{{ route('lawyer.documents.download', $document) }}" class="ci-btn-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
        </div>
    </div>
    @empty
    @endforelse

</div>

@if($cases->isEmpty() && $documents->isEmpty())
<div class="ci-empty">
    <div class="ci-empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
    </div>
    <h3 class="ci-empty-title">No cases or documents yet</h3>
    <p class="ci-empty-text">Start by creating a new case or uploading a document.</p>
    <a href="{{ route('lawyer.cases.create') }}" class="btn-primary" style="margin-top:16px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Create First Case
    </a>
</div>
@endif

{{-- ── STYLES ── --}}
<style>
/* Alert */
.ci-alert { padding: 11px 16px; border-radius: 10px; font-size: 0.84rem; margin-bottom: 18px; }
.ci-alert-success { background: rgba(34,211,165,0.08); border: 1px solid rgba(34,211,165,0.22); color: var(--success); }

/* Section */
.ci-section { margin-bottom: 28px; }
.ci-section-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; gap: 12px; flex-wrap: wrap; }
.ci-section-hd-left { display: flex; align-items: center; gap: 10px; }
.ci-section-icon { width: 34px; height: 34px; border-radius: 9px; background: rgba(124,58,237,0.12); color: var(--p3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ci-section-icon svg { width: 17px; height: 17px; }
.ci-section-title { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: var(--t1); }
.ci-section-sub { font-size: 0.73rem; color: var(--t3); margin-top: 1px; }
.ci-link { font-size: 0.78rem; color: var(--p3); text-decoration: none; transition: opacity 0.15s; white-space: nowrap; }
.ci-link:hover { opacity: 0.75; }

/* Appointment cards */
.ci-appt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
.ci-appt-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px; transition: border-color 0.2s, box-shadow 0.2s; }
.ci-appt-card:hover { border-color: rgba(124,58,237,0.35); box-shadow: 0 4px 16px rgba(0,0,0,0.2); }
.ci-appt-top { display: flex; justify-content: space-between; align-items: flex-start; }
.ci-appt-date { font-size: 0.71rem; color: var(--t3); margin-bottom: 2px; }
.ci-appt-title { font-size: 0.88rem; font-weight: 500; color: var(--t1); }
.ci-appt-meta { display: flex; flex-direction: column; gap: 5px; }
.ci-appt-meta-row { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: var(--t3); }
.ci-appt-meta-row svg { width: 12px; height: 12px; flex-shrink: 0; }
.ci-appt-actions { display: flex; gap: 6px; margin-top: auto; }
.ci-appt-btn { padding: 6px 10px; border-radius: 7px; font-size: 0.74rem; font-weight: 500; cursor: pointer; border: none; font-family: 'Outfit', sans-serif; transition: background 0.15s; display: block; }
.ci-appt-btn-confirm { background: rgba(34,211,165,0.1); color: var(--success); border: 1px solid rgba(34,211,165,0.25); }
.ci-appt-btn-confirm:hover { background: rgba(34,211,165,0.18); }
.ci-appt-btn-view { background: rgba(124,58,237,0.1); color: var(--p3); border: 1px solid rgba(124,58,237,0.25); }
.ci-appt-btn-view:hover { background: rgba(124,58,237,0.18); }

/* Filter bar */
.ci-filter-bar { display: flex; align-items: center; gap: 6px; margin-bottom: 18px; flex-wrap: wrap; }
.ci-filter { padding: 7px 14px; border-radius: 8px; font-size: 0.79rem; font-weight: 500; cursor: pointer; background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: var(--t3); font-family: 'Outfit', sans-serif; transition: all 0.18s; }
.ci-filter:hover { border-color: rgba(124,58,237,0.35); color: var(--t1); }
.ci-filter.active { background: rgba(124,58,237,0.15); border-color: rgba(124,58,237,0.4); color: var(--p3); }
.ci-filter-spacer { flex: 1; }
.ci-search-wrap { position: relative; }
.ci-search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--t3); }
.ci-search { padding: 7px 12px 7px 32px; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 8px; color: var(--t1); font-family: 'Outfit', sans-serif; font-size: 0.82rem; outline: none; width: 200px; transition: border-color 0.18s; }
.ci-search:focus { border-color: var(--p); }
.ci-search::placeholder { color: var(--t3); }

/* Cases grid */
.ci-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }

/* Case card */
.ci-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px; display: flex; flex-direction: column; gap: 14px; transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s; }
.ci-card:hover { border-color: rgba(124,58,237,0.35); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.22); }
.ci-card-top { display: flex; align-items: center; justify-content: space-between; }
.ci-card-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ci-card-icon svg { width: 17px; height: 17px; }
.ci-icon-case { background: rgba(124,58,237,0.12); color: var(--p3); }
.ci-icon-doc  { background: rgba(96,165,250,0.12); color: var(--info); }
.ci-card-body { flex: 1; }
.ci-card-num { font-size: 0.66rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--t3); margin-bottom: 4px; }
.ci-card-title { font-size: 0.95rem; font-weight: 600; color: var(--t1); line-height: 1.3; margin-bottom: 4px; }
.ci-card-cat { font-size: 0.76rem; color: var(--t3); }
.ci-card-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 12px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
.ci-card-meta-item { display: flex; flex-direction: column; gap: 2px; }
.ci-card-meta-label { font-size: 0.63rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em; color: var(--t3); }
.ci-card-meta-val { font-size: 0.8rem; color: var(--t2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ci-card-footer { display: flex; align-items: center; gap: 8px; }
.ci-btn-view { flex: 1; padding: 8px 12px; background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.25); border-radius: 8px; color: var(--p3); font-size: 0.8rem; font-weight: 500; text-align: center; text-decoration: none; transition: background 0.15s; }
.ci-btn-view:hover { background: rgba(124,58,237,0.18); }
.ci-btn-edit { width: 34px; height: 34px; border-radius: 8px; background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: var(--t3); display: flex; align-items: center; justify-content: center; text-decoration: none; transition: border-color 0.15s, color 0.15s; flex-shrink: 0; }
.ci-btn-edit svg { width: 14px; height: 14px; }
.ci-btn-edit:hover { border-color: rgba(124,58,237,0.35); color: var(--p3); }

/* Empty */
.ci-empty { text-align: center; padding: 56px 24px; display: flex; flex-direction: column; align-items: center; }
.ci-empty-icon { width: 54px; height: 54px; border-radius: 14px; background: rgba(124,58,237,0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
.ci-empty-icon svg { width: 24px; height: 24px; color: var(--p3); opacity: 0.6; }
.ci-empty-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; color: var(--t2); margin-bottom: 6px; }
.ci-empty-text { font-size: 0.81rem; color: var(--t3); line-height: 1.6; }

@media (max-width: 640px) {
    .ci-appt-grid { grid-template-columns: 1fr; }
    .ci-grid { grid-template-columns: 1fr; }
    .ci-filter-spacer { display: none; }
    .ci-search { width: 100%; }
}
</style>

<script>
// Filter tabs
document.querySelectorAll('.ci-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.ci-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const f = btn.dataset.filter;
        document.querySelectorAll('.ci-card').forEach(card => {
            if (f === 'all') { card.style.display = ''; return; }
            card.style.display = (card.dataset.type === f || (f === 'archived' && card.dataset.status === 'closed')) ? '' : 'none';
        });
    });
});

// Search
document.getElementById('caseSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.ci-card').forEach(card => {
        card.style.display = card.dataset.title?.includes(q) ? '' : 'none';
    });
});
</script>

@endsection