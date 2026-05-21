<div class="modal-overlay" id="payment-modal" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="modal-box">
        <button class="modal-close" onclick="closePaymentModal()">✕</button>
        <div class="modal-title">Select Payment Method</div>
        <div class="modal-sub">Choose a Philippine payment option for this invoice.</div>
        <div id="payment-amount" style="text-align: center; margin: 16px 0; font-size: 1.2rem; color: var(--primary);">Amount: {{ config('legal.currency_symbol') }}<span id="amount-display"></span></div>
        <form id="payment-form">
            <input type="hidden" name="invoice_id" id="invoice-id-field">
            <div class="payment-method-grid" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px;">
                <label class="payment-option" style="border: 1px solid var(--border); border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 8px; cursor: pointer;">
                    <input type="radio" name="payment_method" value="gcash" style="margin-right: 8px;" checked>
                    <strong>GCash</strong>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">Mobile wallet checkout via PayMongo</span>
                </label>
                <label class="payment-option" style="border: 1px solid var(--border); border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 8px; cursor: pointer;">
                    <input type="radio" name="payment_method" value="paymaya" style="margin-right: 8px;">
                    <strong>PayMaya</strong>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">PayMaya wallet checkout</span>
                </label>
                <label class="payment-option" style="border: 1px solid var(--border); border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 8px; cursor: pointer;">
                    <input type="radio" name="payment_method" value="card" style="margin-right: 8px;">
                    <strong>Credit/Debit Card</strong>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">Visa, Mastercard, or other cards</span>
                </label>
                <label class="payment-option" style="border: 1px solid var(--border); border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 8px; cursor: pointer;">
                    <input type="radio" name="payment_method" value="dob" style="margin-right: 8px;">
                    <strong>Bank Transfer / InstaPay</strong>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">Philippine bank transfer via PayMongo</span>
                </label>
            </div>
            <div id="payment-status" style="margin-bottom: 16px; min-height: 24px; color: var(--text-muted); font-size: 0.95rem;"></div>
            <button type="submit" class="btn-primary" style="width: 100%;">Pay {{ config('legal.currency_symbol') }}<span id="payment-total"></span></button>
        </form>
    </div>
</div>