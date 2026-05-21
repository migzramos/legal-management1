@extends('layouts.lawyer')

@section('title', 'Create New Invoice')

@section('content')
<div class="page-header">
    <div>
        <h1>Create New Invoice</h1>
        <p>Create an invoice for your client</p>
    </div>
    <a href="{{ route('lawyer.billing.index') }}" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Back to Billing
    </a>
</div>

<style>
    .page-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        margin-bottom: 32px;
    }
    .page-header h1 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem; font-weight: 600; margin-bottom: 4px;
        color: var(--text-primary);
    }
    .page-header p { color: var(--text-muted); font-size: 0.9rem; }

    .invoice-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }
    .invoice-layout .full-width { grid-column: 1 / -1; }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 28px;
    }
    .card-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.15rem; font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }
    .card-title svg { opacity: 0.5; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }

    .form-group label {
        display: block;
        font-size: 0.72rem; font-weight: 600;
        letter-spacing: 0.07em; text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .form-group label .required { color: var(--purple-core); margin-left: 2px; }

    .form-control {
        width: 100%; box-sizing: border-box;
        background: var(--bg-input, rgba(255,255,255,0.04));
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 14px;
        color: var(--text-primary);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .form-control:focus {
        border-color: var(--purple-core);
        box-shadow: 0 0 0 3px rgba(139,92,246,0.12);
    }
    .form-control[readonly] {
        opacity: 0.6; cursor: default;
    }
    select.form-control { cursor: pointer; }
    textarea.form-control { resize: vertical; min-height: 90px; }

    .client-display {
        display: flex; align-items: center; gap: 10px;
        background: var(--bg-input, rgba(255,255,255,0.04));
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 14px;
        min-height: 42px;
    }
    .client-display .avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, var(--purple-core), #6366f1);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 700; color: #fff;
        flex-shrink: 0;
    }
    .client-display .client-info { flex: 1; }
    .client-display .client-name { font-size: 0.875rem; color: var(--text-primary); font-weight: 500; }
    .client-display .client-email { font-size: 0.75rem; color: var(--text-muted); }
    .client-display.empty { color: var(--text-muted); font-size: 0.875rem; font-style: italic; }

    .error-msg { color: var(--danger); font-size: 0.78rem; margin-top: 5px; display: flex; align-items: center; gap: 4px; }

    /* Items */
    .item-row {
        background: rgba(255,255,255,0.025);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 12px;
        position: relative;
    }
    .item-row:last-child { margin-bottom: 0; }
    .item-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 14px;
    }
    .item-label {
        font-size: 0.75rem; font-weight: 600; letter-spacing: 0.06em;
        text-transform: uppercase; color: var(--text-muted);
    }

    /* Totals */
    .totals-list { display: flex; flex-direction: column; gap: 12px; }
    .total-row {
        display: flex; justify-content: space-between; align-items: center;
    }
    .total-row .t-label { font-size: 0.85rem; color: var(--text-muted); }
    .total-row .t-value { font-size: 0.95rem; color: var(--text-primary); font-weight: 500; }
    .total-row.grand-total {
        border-top: 1px solid var(--border);
        padding-top: 14px; margin-top: 4px;
    }
    .total-row.grand-total .t-label { font-size: 1rem; font-weight: 600; color: var(--text-primary); }
    .total-row.grand-total .t-value { font-size: 1.2rem; font-weight: 700; color: var(--purple-core); }
    .tax-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .tax-row .t-label { font-size: 0.85rem; color: var(--text-muted); white-space: nowrap; }
    .tax-input { width: 120px !important; }

    /* Buttons */
    .btn {
        padding: 9px 18px; border-radius: 10px;
        font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 500;
        cursor: pointer; transition: all 0.2s;
        text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
        border: none;
    }
    .btn-primary {
        background: var(--purple-core); color: #fff;
        box-shadow: 0 4px 14px rgba(139,92,246,0.3);
    }
    .btn-primary:hover { opacity: 0.88; box-shadow: 0 6px 18px rgba(139,92,246,0.4); }
    .btn-ghost {
        background: transparent; border: 1px solid var(--border);
        color: var(--text-muted);
    }
    .btn-ghost:hover { border-color: var(--purple-core); color: var(--text-primary); }
    .btn-outline {
        background: transparent; border: 1px solid var(--border);
        color: var(--text-primary);
    }
    .btn-outline:hover { border-color: var(--purple-core); background: rgba(139,92,246,0.06); }
    .btn-danger-soft {
        background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2);
        color: var(--danger); font-size: 0.78rem; padding: 5px 10px; border-radius: 7px;
    }
    .btn-danger-soft:hover { background: rgba(248,113,113,0.15); }
    .btn-sm { padding: 6px 12px; font-size: 0.8rem; }

    .form-actions {
        display: flex; justify-content: flex-end; gap: 12px;
        margin-top: 28px; padding-top: 24px;
        border-top: 1px solid var(--border);
        grid-column: 1 / -1;
    }

    .add-item-btn {
        width: 100%; margin-top: 14px;
        border: 1px dashed var(--border);
        background: transparent; color: var(--text-muted);
        border-radius: 10px; padding: 10px;
        font-size: 0.85rem; cursor: pointer;
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-family: 'DM Sans', sans-serif;
    }
    .add-item-btn:hover {
        border-color: var(--purple-core);
        color: var(--purple-core);
        background: rgba(139,92,246,0.04);
    }
</style>

<form action="{{ route('lawyer.billing.invoices.store') }}" method="POST" id="invoice-form">
    @csrf
    <div class="invoice-layout">

        {{-- LEFT: Invoice Info --}}
        <div class="card">
            <div class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Invoice Information
            </div>

            <div class="form-group">
                <label for="case_id">Case <span class="required">*</span></label>
                <select id="case_id" name="case_id" class="form-control" required onchange="populateClient(this)">
                    <option value="">Select a case...</option>
                    @foreach($cases as $case)
                        <option
                            value="{{ $case->id }}"
                            data-client-id="{{ $case->client->id ?? '' }}"
                            data-client-name="{{ $case->client->name ?? '' }}"
                            data-client-email="{{ $case->client->email ?? '' }}"
                            {{ old('case_id') == $case->id ? 'selected' : '' }}
                        >
                            {{ $case->title }} — {{ $case->case_number }}
                        </option>
                    @endforeach
                </select>
                @error('case_id')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Client</label>
                <div class="client-display empty" id="client_display_box">
                    <span id="client_placeholder">Auto-filled when you select a case</span>
                    <div id="client_info" style="display:none; display:flex; align-items:center; gap:10px; width:100%;">
                        <div class="avatar" id="client_avatar"></div>
                        <div class="client-info">
                            <div class="client-name" id="client_name_text"></div>
                            <div class="client-email" id="client_email_text"></div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="client_id" name="client_id" value="{{ old('client_id') }}">
                @error('client_id')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="issued_date">Issued Date <span class="required">*</span></label>
                    <input type="date" id="issued_date" name="issued_date" class="form-control"
                        value="{{ old('issued_date', date('Y-m-d')) }}" required>
                    @error('issued_date')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="due_date">Due Date <span class="required">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control"
                        value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    @error('due_date')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"
                    placeholder="Any additional notes for the client...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- RIGHT: Invoice Items --}}
        <div class="card">
            <div class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Invoice Items
            </div>

            <div id="items-container">
                <div class="item-row" data-index="0">
                    <div class="item-header">
                        <span class="item-label">Item #1</span>
                        <button type="button" class="btn-danger-soft remove-item" onclick="removeItem(this)" style="display:none;">
                            Remove
                        </button>
                    </div>
                    <div class="form-group">
                        <label>Description <span class="required">*</span></label>
                        <input type="text" name="items[0][description]" class="form-control"
                            placeholder="e.g. Legal consultation, Filing fee..." required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantity <span class="required">*</span></label>
                            <input type="number" name="items[0][quantity]" class="form-control"
                                step="0.01" min="0.01" value="1" required onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label>Unit Price <span class="required">*</span></label>
                            <input type="number" name="items[0][unit_price]" class="form-control"
                                step="0.01" min="0" placeholder="0.00" required onchange="calculateTotal()">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Line Total</label>
                        <input type="number" name="items[0][total]" class="form-control"
                            step="0.01" readonly placeholder="0.00">
                    </div>
                </div>
            </div>

            <button type="button" class="add-item-btn" onclick="addItem()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Add Another Item
            </button>
        </div>

        {{-- BOTTOM: Totals --}}
        <div class="card full-width" style="max-width: 420px; margin-left: auto;">
            <div class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Summary
            </div>
            <div class="totals-list">
                <div class="total-row">
                    <span class="t-label">Subtotal</span>
                    <span class="t-value" id="subtotal">{{ config('legal.currency_symbol') }}0.00</span>
                </div>
                <div class="tax-row">
                    <span class="t-label">Tax</span>
                    <input type="number" id="tax" name="tax" class="form-control tax-input"
                        step="0.01" min="0" value="{{ old('tax', 0) }}" onchange="calculateTotal()">
                </div>
                <div class="total-row grand-total">
                    <span class="t-label">Total Due</span>
                    <span class="t-value" id="total">{{ config('legal.currency_symbol') }}0.00</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-actions">
            <a href="{{ route('lawyer.billing.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Create Invoice
            </button>
        </div>

    </div>
</form>

<script>
let itemIndex = 1;

function populateClient(select) {
    const option = select.options[select.selectedIndex];
    const clientId    = option.dataset.clientId   || '';
    const clientName  = option.dataset.clientName  || '';
    const clientEmail = option.dataset.clientEmail || '';

    document.getElementById('client_id').value = clientId;

    const box         = document.getElementById('client_display_box');
    const placeholder = document.getElementById('client_placeholder');
    const info        = document.getElementById('client_info');

    if (clientId) {
        box.classList.remove('empty');
        placeholder.style.display = 'none';
        info.style.display = 'flex';
        document.getElementById('client_avatar').textContent   = clientName.charAt(0).toUpperCase();
        document.getElementById('client_name_text').textContent  = clientName;
        document.getElementById('client_email_text').textContent = clientEmail;
    } else {
        box.classList.add('empty');
        placeholder.style.display = 'inline';
        info.style.display = 'none';
        document.getElementById('client_avatar').textContent   = '';
        document.getElementById('client_name_text').textContent  = '';
        document.getElementById('client_email_text').textContent = '';
    }
}

function addItem() {
    const container = document.getElementById('items-container');
    const count = container.querySelectorAll('.item-row').length + 1;
    const idx = itemIndex;

    const div = document.createElement('div');
    div.className = 'item-row';
    div.dataset.index = idx;
    div.innerHTML = `
        <div class="item-header">
            <span class="item-label">Item #${count}</span>
            <button type="button" class="btn-danger-soft remove-item" onclick="removeItem(this)">Remove</button>
        </div>
        <div class="form-group">
            <label>Description <span class="required">*</span></label>
            <input type="text" name="items[${idx}][description]" class="form-control"
                placeholder="e.g. Legal consultation, Filing fee..." required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="items[${idx}][quantity]" class="form-control"
                    step="0.01" min="0.01" value="1" required onchange="calculateTotal()">
            </div>
            <div class="form-group">
                <label>Unit Price <span class="required">*</span></label>
                <input type="number" name="items[${idx}][unit_price]" class="form-control"
                    step="0.01" min="0" placeholder="0.00" required onchange="calculateTotal()">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Line Total</label>
            <input type="number" name="items[${idx}][total]" class="form-control" step="0.01" readonly placeholder="0.00">
        </div>
    `;
    container.appendChild(div);
    itemIndex++;
    refreshRemoveButtons();
}

function removeItem(btn) {
    btn.closest('.item-row').remove();
    refreshItemLabels();
    refreshRemoveButtons();
    calculateTotal();
}

function refreshRemoveButtons() {
    const btns = document.querySelectorAll('.remove-item');
    btns.forEach(b => b.style.display = btns.length > 1 ? 'inline-flex' : 'none');
}

function refreshItemLabels() {
    document.querySelectorAll('.item-row').forEach((row, i) => {
        const label = row.querySelector('.item-label');
        if (label) label.textContent = `Item #${i + 1}`;
    });
}

function calculateTotal() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('input[name*="[quantity]"]').value)   || 0;
        const price = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
        const line  = qty * price;
        row.querySelector('input[name*="[total]"]').value = line.toFixed(2);
        subtotal += line;
    });

    const currency = '{{ config('legal.currency_symbol') }}';
    const tax      = parseFloat(document.getElementById('tax').value) || 0;

    document.getElementById('subtotal').textContent = currency + subtotal.toFixed(2);
    document.getElementById('total').textContent    = currency + (subtotal + tax).toFixed(2);
}

document.addEventListener('DOMContentLoaded', () => {
    calculateTotal();
    const caseSelect = document.getElementById('case_id');
    if (caseSelect.value) populateClient(caseSelect);
});
</script>
@endsection