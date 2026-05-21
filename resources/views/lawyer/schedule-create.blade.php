@extends('layouts.lawyer')

@section('title', 'Add Calendar Event')

@section('content')
<style>
    .sc-page {
        padding: 2rem 2.5rem;
        max-width: 860px;
    }

    .sc-topbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 2rem;
    }

    .sc-topbar-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #f1f0ff;
        margin: 0 0 0.25rem;
        letter-spacing: -0.02em;
    }

    .sc-topbar-sub {
        font-size: 0.875rem;
        color: #9994c0;
        margin: 0;
    }

    .sc-btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid rgba(138, 116, 249, 0.3);
        background: rgba(138, 116, 249, 0.08);
        color: #b8aeff;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .sc-btn-back:hover {
        background: rgba(138, 116, 249, 0.15);
        border-color: rgba(138, 116, 249, 0.5);
        color: #d4ccff;
    }

    .sc-card {
        background: #16132b;
        border: 1px solid rgba(138, 116, 249, 0.15);
        border-radius: 14px;
        overflow: hidden;
    }

    .sc-card-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid rgba(138, 116, 249, 0.12);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .sc-card-header-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #7c6df0;
        flex-shrink: 0;
    }

    .sc-card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #ddd8ff;
        margin: 0;
    }

    .sc-card-body {
        padding: 1.75rem;
    }

    .sc-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem 1.5rem;
    }

    .sc-field {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .sc-field.sc-full {
        grid-column: 1 / -1;
    }

    .sc-field label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #9994c0;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .sc-field input,
    .sc-field select,
    .sc-field textarea {
        background: #0f0c22;
        border: 1px solid rgba(138, 116, 249, 0.2);
        border-radius: 8px;
        color: #e8e4ff;
        font-size: 0.9rem;
        padding: 0.65rem 0.9rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        outline: none;
        width: 100%;
        box-sizing: border-box;
        font-family: inherit;
        -webkit-appearance: none;
        appearance: none;
    }

    .sc-field select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239994c0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.9rem center;
        padding-right: 2.5rem;
        cursor: pointer;
    }

    .sc-field textarea {
        resize: vertical;
        min-height: 90px;
        line-height: 1.55;
    }

    .sc-field input:focus,
    .sc-field select:focus,
    .sc-field textarea:focus {
        border-color: rgba(138, 116, 249, 0.55);
        box-shadow: 0 0 0 3px rgba(138, 116, 249, 0.1);
    }

    .sc-field input::placeholder,
    .sc-field textarea::placeholder {
        color: #4e4a6a;
    }

    .sc-field input[type="datetime-local"]::-webkit-calendar-picker-indicator {
        filter: invert(0.6) sepia(1) hue-rotate(220deg);
        cursor: pointer;
        opacity: 0.7;
    }

    .sc-divider {
        height: 1px;
        background: rgba(138, 116, 249, 0.1);
        margin: 1.5rem 0;
        grid-column: 1 / -1;
    }

    .sc-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.75rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(138, 116, 249, 0.1);
    }

    .sc-btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.65rem 1.4rem;
        border-radius: 8px;
        border: none;
        background: #7c6df0;
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.1s ease;
        letter-spacing: -0.01em;
    }

    .sc-btn-submit:hover {
        background: #9381f5;
    }

    .sc-btn-submit:active {
        transform: scale(0.98);
    }

    .sc-btn-cancel {
        display: inline-flex;
        align-items: center;
        padding: 0.65rem 1.2rem;
        border-radius: 8px;
        border: 1px solid rgba(138, 116, 249, 0.2);
        background: transparent;
        color: #9994c0;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .sc-btn-cancel:hover {
        background: rgba(138, 116, 249, 0.07);
        color: #c4beff;
        border-color: rgba(138, 116, 249, 0.35);
    }

    /* Alert / Validation errors */
    .sc-alert {
        background: rgba(194, 65, 65, 0.12);
        border: 1px solid rgba(194, 65, 65, 0.3);
        border-radius: 8px;
        padding: 0.85rem 1rem;
        margin-bottom: 1.5rem;
        color: #f4a0a0;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .sc-alert ul {
        margin: 0.4rem 0 0 1rem;
        padding: 0;
    }

    .sc-field-error {
        font-size: 0.78rem;
        color: #f4a0a0;
        margin-top: 0.1rem;
    }

    .sc-field input.is-invalid,
    .sc-field select.is-invalid,
    .sc-field textarea.is-invalid {
        border-color: rgba(194, 65, 65, 0.5);
    }

    @media (max-width: 640px) {
        .sc-page { padding: 1.25rem 1rem; }
        .sc-form-grid { grid-template-columns: 1fr; }
        .sc-field.sc-full { grid-column: 1; }
        .sc-topbar { flex-direction: column; gap: 1rem; }
    }
</style>

<div class="sc-page">

    {{-- Topbar --}}
    <div class="sc-topbar">
        <div>
            <h1 class="sc-topbar-title">Add Calendar Event</h1>
            <p class="sc-topbar-sub">Schedule a new court date, meeting, or deadline.</p>
        </div>
        <a href="{{ route('lawyer.calendar.index') }}" class="sc-btn-back">
            ← Back to Calendar
        </a>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="sc-alert">
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Card --}}
    <div class="sc-card">
        <div class="sc-card-header">
            <div class="sc-card-header-dot"></div>
            <h3 class="sc-card-title">Event Details</h3>
        </div>

        <div class="sc-card-body">
            <form method="POST" action="{{ route('lawyer.schedules.store') }}">
                @csrf

                <div class="sc-form-grid">

                    {{-- Case --}}
                    <div class="sc-field">
                        <label for="case_id">Case</label>
                        <select id="case_id" name="case_id" required class="{{ $errors->has('case_id') ? 'is-invalid' : '' }}">
                            <option value="">Select a case…</option>
                            @foreach($cases as $case)
                                <option value="{{ $case->id }}" {{ old('case_id') == $case->id ? 'selected' : '' }}>
                                    {{ $case->title }} (#{{ $case->case_number ?? $case->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('case_id')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Event Type --}}
                    <div class="sc-field">
                        <label for="type">Event Type</label>
                        <select id="type" name="type" required class="{{ $errors->has('type') ? 'is-invalid' : '' }}">
                            <option value="court_hearing"  {{ old('type') == 'court_hearing'  ? 'selected' : '' }}>Court Hearing</option>
                            <option value="deadline"       {{ old('type') == 'deadline'       ? 'selected' : '' }}>Deadline</option>
                            <option value="appointment"    {{ old('type') == 'appointment'    ? 'selected' : '' }}>Appointment</option>
                            <option value="meeting"        {{ old('type') == 'meeting'        ? 'selected' : '' }}>Meeting</option>
                            <option value="other"          {{ old('type') == 'other'          ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Title --}}
                    <div class="sc-field">
                        <label for="title">Title</label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title') }}"
                            placeholder="e.g. Preliminary Hearing"
                            required
                            class="{{ $errors->has('title') ? 'is-invalid' : '' }}"
                        >
                        @error('title')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Scheduled At --}}
                    <div class="sc-field">
                        <label for="scheduled_at">Scheduled Date & Time</label>
                        <input
                            id="scheduled_at"
                            name="scheduled_at"
                            type="datetime-local"
                            value="{{ old('scheduled_at') }}"
                            required
                            class="{{ $errors->has('scheduled_at') ? 'is-invalid' : '' }}"
                        >
                        @error('scheduled_at')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Location --}}
                    <div class="sc-field sc-full">
                        <label for="location">Location <span style="color:#4e4a6a;font-weight:400;text-transform:none;font-size:0.78rem;">(optional)</span></label>
                        <input
                            id="location"
                            name="location"
                            type="text"
                            value="{{ old('location') }}"
                            placeholder="e.g. Regional Trial Court, Branch 14"
                            class="{{ $errors->has('location') ? 'is-invalid' : '' }}"
                        >
                        @error('location')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="sc-field sc-full">
                        <label for="description">Description <span style="color:#4e4a6a;font-weight:400;text-transform:none;font-size:0.78rem;">(optional)</span></label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Briefly describe the purpose of this event…"
                            class="{{ $errors->has('description') ? 'is-invalid' : '' }}"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="sc-field sc-full">
                        <label for="notes">Notes <span style="color:#4e4a6a;font-weight:400;text-transform:none;font-size:0.78rem;">(optional)</span></label>
                        <textarea
                            id="notes"
                            name="notes"
                            placeholder="Any internal notes or reminders…"
                            class="{{ $errors->has('notes') ? 'is-invalid' : '' }}"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="sc-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="sc-actions">
                    <button type="submit" class="sc-btn-submit">
                        Create Event
                    </button>
                    <a href="{{ route('lawyer.calendar.index') }}" class="sc-btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection