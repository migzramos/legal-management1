<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isLawyer();
    }

    public function rules(): array
    {
        return [
            'confirmed' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
