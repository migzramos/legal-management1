<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isClient();
    }

    public function rules(): array
    {
        return [
            'invoice_id' => [
                'required',
                Rule::exists('invoices', 'id')->where(fn ($q) => $q->where('client_id', auth()->id())),
            ],
            'gateway' => [
                'required',
                Rule::in(array_keys(config('payment.gateways'))),
            ],
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'gateway.in' => 'The selected payment gateway is not available.',
            'amount.max' => 'The payment amount exceeds the maximum allowed limit.',
            'amount.min' => 'The payment amount must be at least 0.01.',
        ];
    }
}
