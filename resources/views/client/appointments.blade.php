@extends('layouts.client')
@section('title', 'My Appointments')
@section('content')
 
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
 
<div class="section-header">
    <div>
        <h1 class="section-title">My Appointments</h1>
        <p class="section-subtitle">View and manage your consultations.</p>
    </div>
    <button type="button" class="btn-primary" onclick="document.getElementById('bookingModal').classList.add('active')">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:15px;height:15px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Book Appointment
    </button>
</div>
 
<div class="tabs" style="margin-bottom:20px;">
    @foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $key=>$label)
    <a href="{{ route('client.appointments.index', ['filter'=>$key==='all'?null:$key]) }}"
       class="tab {{ ($activeFilter??'all')===$key ? 'active' : '' }}">
        {{ $label }}<span class="tab-badge">{{ $tabCounts[$key]??0 }}</span>
    </a>
    @endforeach
</div>
 
<div style="display:flex;flex-direction:column;gap:12px;">
    @forelse($appointments as $appt)
    <div class="card">
        <div class="card-body" style="display:flex;align-items:center;gap:20px;">
            <div style="width:52px;height:52px;border-radius:10px;background:rgba(99,102,241,0.1);border:1px solid rgba(255,255,255,0.06);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);">
                    {{ \Carbon\Carbon::parse($appt->appointment_at)->format('M') }}
                </span>
                <span style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:700;color:var(--purple-light);line-height:1;">
                    {{ \Carbon\Carbon::parse($appt->appointment_at)->format('d') }}
                </span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:0.92rem;color:var(--text-primary);margin-bottom:4px;">
                    {{ $appt->purpose ?? 'Consultation' }}
                </div>
                <div style="font-size:0.78rem;color:var(--text-muted);display:flex;gap:16px;flex-wrap:wrap;">
                    <span>{{ \Carbon\Carbon::parse($appt->appointment_at)->format('g:i A · M d, Y') }}</span>
                    <span>{{ $appt->lawyer->name ?? '—' }}</span>
                </div>
                @if($appt->notes)
                    <div style="font-size:0.78rem;color:var(--text-secondary);margin-top:6px;font-style:italic;">
                        {{ Str::limit($appt->notes, 100) }}
                    </div>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                <span class="status-{{ strtolower($appt->status) }}">{{ ucfirst($appt->status) }}</span>
                <a href="{{ route('client.appointments.show', $appt->id) }}" class="btn-icon" title="View details">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @if(in_array($appt->status, ['pending','confirmed']))
                <form method="POST" action="{{ route('client.appointments.cancel', $appt->id) }}" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-icon" title="Cancel"
                            onclick="return confirm('Cancel this appointment?')"
                            style="color:var(--danger);border-color:rgba(248,113,113,0.3);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.5"/>
                    <path stroke-linecap="round" stroke-width="1.5" d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
            <div class="empty-state-title">No appointments yet</div>
            <div class="empty-state-text">Book your first consultation using the button above.</div>
        </div>
    </div>
    @endforelse
</div>
 
{{-- Pagination --}}
@if($appointments->hasPages())
    <div style="margin-top:20px;">
        {{ $appointments->links() }}
    </div>
@endif
 
{{-- Booking Modal --}}
<div class="modal-overlay" id="bookingModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">Book Appointment</div>
                <div class="modal-sub">Schedule a consultation with your lawyer.</div>
            </div>
            <button type="button" class="modal-close"
                    onclick="document.getElementById('bookingModal').classList.remove('active')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
 
            {{-- Show validation / booking errors inside the modal --}}
            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:16px;">
                    <strong>Booking failed:</strong> {{ $errors->first() }}
                </div>
            @endif
 
            <form method="POST" action="{{ route('client.appointments.store') }}">
                @csrf
 
                {{-- Lawyer Select --}}
                <div class="form-group">
                    <label class="form-label">Select Lawyer</label>
                    <select name="lawyer_id" class="form-control" required id="lawyerSelect">
                        <option value="">— Choose a lawyer —</option>
                        @foreach($lawyers ?? [] as $lawyer)
                            <option value="{{ $lawyer->id }}"
                                data-rate="{{ $lawyer->billingRate->hourly_rate ?? 0 }}"
                                {{ old('lawyer_id') == $lawyer->id ? 'selected' : '' }}>
                                {{ $lawyer->name }}
                            </option>
                        @endforeach
                    </select>
                    <div id="hourlyRateDisplay" style="margin-top:8px;font-size:0.82rem;color:var(--text-muted);display:none;">
                        Hourly Rate: <strong id="hourlyRateValue" style="color:var(--purple-light);"></strong>
                    </div>
                </div>
 
                {{-- Date & Duration --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group">
                        <label class="form-label">Date &amp; Time</label>
                        <input type="datetime-local" name="appointment_at" class="form-control" required
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               value="{{ old('appointment_at') ? \Carbon\Carbon::parse(old('appointment_at'))->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <select name="duration_minutes" class="form-control" required>
                            <option value="30"  {{ old('duration_minutes') == 30  ? 'selected' : '' }}>30 min</option>
                            <option value="60"  {{ old('duration_minutes', 60) == 60 ? 'selected' : '' }}>1 hour</option>
                            <option value="90"  {{ old('duration_minutes') == 90  ? 'selected' : '' }}>1.5 hours</option>
                            <option value="120" {{ old('duration_minutes') == 120 ? 'selected' : '' }}>2 hours</option>
                        </select>
                    </div>
                </div>
 
                {{-- Purpose Dropdown --}}
                <div class="form-group">
                    <label class="form-label">Purpose</label>
                    <select name="purpose" class="form-control" required id="purposeSelect">
                        <option value="">— Select a purpose —</option>
                        <option value="Initial Consultation"   {{ old('purpose') == 'Initial Consultation'   ? 'selected' : '' }}>Initial Consultation</option>
                        <option value="Follow-up Consultation" {{ old('purpose') == 'Follow-up Consultation' ? 'selected' : '' }}>Follow-up Consultation</option>
                        <option value="Case Evaluation"        {{ old('purpose') == 'Case Evaluation'        ? 'selected' : '' }}>Case Evaluation</option>
                        <option value="Legal Advice"           {{ old('purpose') == 'Legal Advice'           ? 'selected' : '' }}>Legal Advice</option>
                        <option value="Document Review"        {{ old('purpose') == 'Document Review'        ? 'selected' : '' }}>Document Review</option>
                        <option value="Document Preparation"   {{ old('purpose') == 'Document Preparation'   ? 'selected' : '' }}>Document Preparation</option>
                        <option value="Case Filing"            {{ old('purpose') == 'Case Filing'            ? 'selected' : '' }}>Case Filing</option>
                        <option value="Court Representation"   {{ old('purpose') == 'Court Representation'   ? 'selected' : '' }}>Court Representation</option>
                        <option value="Mediation / Settlement" {{ old('purpose') == 'Mediation / Settlement' ? 'selected' : '' }}>Mediation / Settlement</option>
                        <option value="Notarial Services"      {{ old('purpose') == 'Notarial Services'      ? 'selected' : '' }}>Notarial Services</option>
                        <option value="Billing Inquiry"        {{ old('purpose') == 'Billing Inquiry'        ? 'selected' : '' }}>Billing Inquiry</option>
                        <option value="Case Update"            {{ old('purpose') == 'Case Update'            ? 'selected' : '' }}>Case Update</option>
                        <option value="Other"                  {{ old('purpose') == 'Other'                  ? 'selected' : '' }}>Other (Specify)</option>
                    </select>
                    <input type="text" id="purposeOther" name="purpose_other" class="form-control"
                           placeholder="Please describe your purpose..."
                           value="{{ old('purpose_other') }}"
                           style="margin-top:8px;display:{{ old('purpose') == 'Other' ? 'block' : 'none' }};">
                </div>
 
                {{-- Additional Notes --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Any additional details...">{{ old('notes') }}</textarea>
                </div>
 
                <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:18px;margin-top:18px;border-top:1px solid var(--border);">
                    <button type="button" class="btn-secondary"
                            onclick="document.getElementById('bookingModal').classList.remove('active')">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
 
<script>
    // Close modal when clicking backdrop
    document.getElementById('bookingModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
 
    // Auto re-open modal if there were validation errors so user sees the message
    @if($errors->any())
        document.getElementById('bookingModal').classList.add('active');
    @endif
 
    // Show hourly rate when lawyer is selected
    const lawyerSelect = document.getElementById('lawyerSelect');
    const rateDisplay  = document.getElementById('hourlyRateDisplay');
    const rateValue    = document.getElementById('hourlyRateValue');
 
    lawyerSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const rate = parseFloat(selected.dataset.rate || 0);
        if (this.value && rate > 0) {
            rateValue.textContent = '₱' + rate.toFixed(2) + ' / hour';
            rateDisplay.style.display = 'block';
        } else if (this.value) {
            rateValue.textContent = 'Not yet configured';
            rateDisplay.style.display = 'block';
        } else {
            rateDisplay.style.display = 'none';
        }
    });
 
    // Show/hide "Other" text input for purpose
    const purposeSelect = document.getElementById('purposeSelect');
    const purposeOther  = document.getElementById('purposeOther');
 
    purposeSelect.addEventListener('change', function () {
        if (this.value === 'Other') {
            purposeOther.style.display = 'block';
            purposeOther.required = true;
        } else {
            purposeOther.style.display = 'none';
            purposeOther.required = false;
            purposeOther.value = '';
        }
    });
</script>
 
@endsection