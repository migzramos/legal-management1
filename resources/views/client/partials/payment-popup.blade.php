{{-- Payment Modal --}}
<div id="paymentModal" class="modal-overlay" onclick="if(event.target===this)closePaymentModal()">
    <div class="modal-box" style="max-width:500px;">
        <button class="modal-close" onclick="closePaymentModal()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div class="modal-title">Pay Invoice</div>
        <div class="modal-sub">Scan the QR code below, pay, then upload your screenshot proof.</div>

        {{-- Payment Method Tabs --}}
        <div style="display:flex;gap:8px;margin:20px 0 16px;">
            <button type="button" class="pay-tab active" onclick="switchTab('gcash')" id="tab-gcash"
                style="flex:1;padding:10px;border-radius:10px;border:1px solid rgba(124,58,237,0.4);background:rgba(124,58,237,0.15);color:var(--purple-light);font-weight:600;font-size:0.82rem;cursor:pointer;transition:all .2s;">
                GCash
            </button>
            <button type="button" class="pay-tab" onclick="switchTab('paypal')" id="tab-paypal"
                style="flex:1;padding:10px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--text-muted);font-weight:600;font-size:0.82rem;cursor:pointer;transition:all .2s;">
                PayPal
            </button>
            <button type="button" class="pay-tab" onclick="switchTab('rcbc')" id="tab-rcbc"
                style="flex:1;padding:10px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--text-muted);font-weight:600;font-size:0.82rem;cursor:pointer;transition:all .2s;">
                RCBC
            </button>
        </div>

        {{-- QR Code Display --}}
        <div id="qr-gcash" class="qr-panel" style="text-align:center;margin-bottom:20px;">
            <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:10px;">Scan with your GCash app</p>
            <img src="{{ asset('images/qr-gcash.jpeg') }}" alt="GCash QR"
                 style="width:200px;height:200px;object-fit:contain;border-radius:12px;border:1px solid var(--border);">
        </div>
        <div id="qr-paypal" class="qr-panel" style="text-align:center;margin-bottom:20px;display:none;">
            <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:10px;">Scan with your PayPal app</p>
            <img src="{{ asset('images/qr-paypal.jpg') }}" alt="PayPal QR"
                 style="width:200px;height:200px;object-fit:contain;border-radius:12px;border:1px solid var(--border);">
        </div>
        <div id="qr-rcbc" class="qr-panel" style="text-align:center;margin-bottom:20px;display:none;">
            <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:10px;">Scan with your RCBC app</p>
            <img src="{{ asset('images/qr-rcbc.jpeg') }}" alt="RCBC QR"
                 style="width:200px;height:200px;object-fit:contain;border-radius:12px;border:1px solid var(--border);">
        </div>

        {{-- Upload Form --}}
        <form id="payment-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="invoice-id-field" name="invoice_id">
            <input type="hidden" id="payment-method-field" name="payment_method" value="gcash">

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:0.72rem;font-weight:600;letter-spacing:0.07em;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">
                    Amount to Pay
                </label>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:700;color:var(--purple-light);">
                    ₱<span id="amount-display">0.00</span>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:0.72rem;font-weight:600;letter-spacing:0.07em;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">
                    Upload Payment Screenshot *
                </label>
                <div id="upload-area"
                     style="border:2px dashed var(--border);border-radius:12px;padding:24px;text-align:center;cursor:pointer;transition:border-color .2s;"
                     onclick="document.getElementById('proof-file').click()"
                     ondragover="event.preventDefault();this.style.borderColor='var(--purple-core)'"
                     ondragleave="this.style.borderColor='var(--border)'"
                     ondrop="handleDrop(event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32"
                         style="color:var(--text-muted);margin:0 auto 8px;display:block;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p id="upload-label" style="font-size:0.82rem;color:var(--text-muted);">
                        Click or drag your screenshot here
                    </p>
                    <p style="font-size:0.72rem;color:var(--text-muted);margin-top:4px;">PNG, JPG up to 5MB</p>
                </div>
                <input type="file" id="proof-file" name="proof_image" accept="image/*"
                       style="display:none;" onchange="previewFile(this)" required>
                <div id="image-preview" style="display:none;margin-top:10px;text-align:center;">
                    <img id="preview-img" src="" alt="Preview"
                         style="max-height:120px;border-radius:8px;border:1px solid var(--border);">
                    <button type="button" onclick="clearFile()"
                            style="display:block;margin:6px auto 0;font-size:0.75rem;color:var(--danger);background:none;border:none;cursor:pointer;">
                        Remove
                    </button>
                </div>
            </div>

            <div id="payment-status" style="margin-bottom:12px;font-size:0.82rem;color:var(--danger);"></div>

            <div style="display:flex;gap:10px;">
                <button type="button" class="btn-secondary" onclick="closePaymentModal()" style="flex:1;">
                    Cancel
                </button>
                <button type="submit" id="pay-btn" class="btn-primary" style="flex:1;justify-content:center;">
                    Submit Payment Proof
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(method) {
    // Hide all QR panels
    document.querySelectorAll('.qr-panel').forEach(p => p.style.display = 'none');
    // Show selected
    document.getElementById('qr-' + method).style.display = 'block';
    // Update hidden field
    document.getElementById('payment-method-field').value = method;
    // Update tab styles
    document.querySelectorAll('.pay-tab').forEach(t => {
        t.style.background = 'transparent';
        t.style.borderColor = 'var(--border)';
        t.style.color = 'var(--text-muted)';
    });
    const active = document.getElementById('tab-' + method);
    active.style.background = 'rgba(124,58,237,0.15)';
    active.style.borderColor = 'rgba(124,58,237,0.4)';
    active.style.color = 'var(--purple-light)';
}

function previewFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('upload-area').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearFile() {
    document.getElementById('proof-file').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('upload-area').style.display = 'block';
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('upload-area').style.borderColor = 'var(--border)';
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        const input = document.getElementById('proof-file');
        input.files = dt.files;
        previewFile(input);
    }
}

document.getElementById('payment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('pay-btn');
    const status = document.getElementById('payment-status');
    const invoiceId = document.getElementById('invoice-id-field').value;

    const file = document.getElementById('proof-file').files[0];
    if (!file) {
        status.textContent = 'Please upload your payment screenshot.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Submitting...';
    status.textContent = '';

    const formData = new FormData(this);

    try {
        const response = await fetch(`/client/invoices/${invoiceId}/pay`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Failed to submit payment proof.');
        }

        closePaymentModal();
        window.location.reload();
    } catch (error) {
        status.textContent = error.message;
        btn.disabled = false;
        btn.textContent = 'Submit Payment Proof';
    }
});
</script>
