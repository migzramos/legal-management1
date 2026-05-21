@extends('layouts.lawyer')

@section('title', 'Create New Case')
@section('page_title', 'Create New Case')
@section('page_subtitle', 'Start a new legal case for your client')

@section('topbar_actions')
<a href="{{ route('lawyer.cases.index') }}" class="btn-secondary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back to Cases
</a>
@endsection

@push('styles')
<style>
    .cf-alert { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .875rem; }
    .cf-alert-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
    .cf-alert ul { margin: .5rem 0 0 1.25rem; }

    .cf-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; margin-bottom: 1.5rem; overflow: hidden; }
    .cf-card-hd { display: flex; align-items: center; gap: .875rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.06); }
    .cf-card-hd-icon { width: 36px; height: 36px; min-width: 36px; display: flex; align-items: center; justify-content: center; background: rgba(139,92,246,.15); border-radius: 8px; color: #a78bfa; }
    .cf-card-hd-icon svg { width: 18px; height: 18px; }
    .cf-section-title { font-size: .9375rem; font-weight: 600; color: #f1f5f9; }
    .cf-section-sub { font-size: .8125rem; color: #94a3b8; margin-top: 2px; }
    .cf-card-body { padding: 1.5rem; }

    .cf-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
    .cf-field { display: flex; flex-direction: column; gap: .375rem; }
    .cf-field-full { grid-column: 1 / -1; }

    .cf-label { font-size: .8125rem; font-weight: 500; color: #cbd5e1; }
    .cf-req { color: #f87171; }
    .cf-input { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 8px; padding: .625rem .875rem; color: #f1f5f9; font-size: .875rem; width: 100%; transition: border-color .2s; outline: none; }
    .cf-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
    .cf-input::placeholder { color: #475569; }
    .cf-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; background-size: 16px; padding-right: 2.5rem; }
    .cf-select option { background: #1e1b2e; color: #f1f5f9; }
    .cf-textarea { resize: vertical; min-height: 100px; }
    .cf-error { font-size: .75rem; color: #f87171; margin-top: 2px; }

    .cf-actions { display: flex; gap: 1rem; align-items: center; padding-top: .5rem; margin-bottom: 2rem; }

    @media (max-width: 640px) {
        .cf-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

@if($errors->any())
<div class="cf-alert cf-alert-error">
    <strong>Please fix the following errors:</strong>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('lawyer.cases.store') }}" method="POST">
    @csrf

    {{-- ── PRIMARY INFO ── --}}
    <div class="cf-card">
        <div class="cf-card-hd">
            <div class="cf-card-hd-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
            </div>
            <div>
                <div class="cf-section-title">Primary Information</div>
                <div class="cf-section-sub">Basic case details and client assignment</div>
            </div>
        </div>
        <div class="cf-card-body">
            <div class="cf-grid">
                <div class="cf-field">
                    <label class="cf-label" for="title">Case Title <span class="cf-req">*</span></label>
                    <input type="text" id="title" name="title" class="cf-input" placeholder="Enter case title" value="{{ old('title') }}" required>
                    @error('title')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="case_category_id">Case Category <span class="cf-req">*</span></label>
                    <select id="case_category_id" name="case_category_id" class="cf-input cf-select" required>
                        <option value="">Select a category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('case_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('case_category_id')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="court_type_id">Court Type <span class="cf-req">*</span></label>
                    <select id="court_type_id" name="court_type_id" class="cf-input cf-select" required>
                        <option value="">Select court type</option>
                        @foreach($courtTypes as $ct)
                        <option value="{{ $ct->id }}" {{ old('court_type_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                        @endforeach
                    </select>
                    @error('court_type_id')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="client_id">Client <span class="cf-req">*</span></label>
                    <select id="client_id" name="client_id" class="cf-input cf-select" required>
                        <option value="">Select a client</option>
                        @foreach($clients as $cl)
                        <option value="{{ $cl->id }}" {{ old('client_id') == $cl->id ? 'selected' : '' }}>{{ $cl->name }} ({{ $cl->email }})</option>
                        @endforeach
                    </select>
                    @error('client_id')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field cf-field-full">
                    <label class="cf-label" for="description">Case Description</label>
                    <textarea id="description" name="description" class="cf-input cf-textarea" placeholder="Detailed description of the case" rows="4">{{ old('description') }}</textarea>
                    @error('description')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── DATES ── --}}
    <div class="cf-card">
        <div class="cf-card-hd">
            <div class="cf-card-hd-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <div class="cf-section-title">Important Dates</div>
                <div class="cf-section-sub">Filing and hearing schedule</div>
            </div>
        </div>
        <div class="cf-card-body">
            <div class="cf-grid">
                <div class="cf-field">
                    <label class="cf-label" for="filing_date">Filing Date</label>
                    <input type="date" id="filing_date" name="filing_date" class="cf-input" value="{{ old('filing_date') }}">
                    @error('filing_date')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="hearing_date">Hearing Date</label>
                    <input type="date" id="hearing_date" name="hearing_date" class="cf-input" value="{{ old('hearing_date') }}">
                    @error('hearing_date')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── COURT & PARTIES ── --}}
    <div class="cf-card">
        <div class="cf-card-hd">
            <div class="cf-card-hd-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 22V12h4v10M10 22V6h4v16M17 22v-6h4v6M1 22h22"/></svg>
            </div>
            <div>
                <div class="cf-section-title">Court & Legal Parties</div>
                <div class="cf-section-sub">Court assignment and opposing party details</div>
            </div>
        </div>
        <div class="cf-card-body">
            <div class="cf-grid">
                <div class="cf-field">
                    <label class="cf-label" for="court_name">Court Name</label>
                    <input type="text" id="court_name" name="court_name" class="cf-input" placeholder="e.g. Regional Trial Court" value="{{ old('court_name') }}">
                    @error('court_name')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="judge_name">Assigned Judge</label>
                    <input type="text" id="judge_name" name="judge_name" class="cf-input" placeholder="Judge name (if assigned)" value="{{ old('judge_name') }}">
                    @error('judge_name')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="opposing_party">Opposing Party</label>
                    <input type="text" id="opposing_party" name="opposing_party" class="cf-input" placeholder="Name of opposing party" value="{{ old('opposing_party') }}">
                    @error('opposing_party')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field">
                    <label class="cf-label" for="opposing_counsel">Opposing Counsel</label>
                    <input type="text" id="opposing_counsel" name="opposing_counsel" class="cf-input" placeholder="Opposing counsel name" value="{{ old('opposing_counsel') }}">
                    @error('opposing_counsel')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
                <div class="cf-field cf-field-full">
                    <label class="cf-label" for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" class="cf-input cf-textarea" placeholder="Any additional notes or context" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<span class="cf-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="cf-actions">
        <button type="submit" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Create Case
        </button>
        <a href="{{ route('lawyer.cases.index') }}" class="btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Cancel
        </a>
    </div>
</form>

@endsection