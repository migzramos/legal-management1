<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isLawyer() || auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'client_id'      => 'required|exists:users,id',
            'issued_date'    => 'required|date',
            'due_date'       => 'required|date|after_or_equal:issued_date',
            'tax'            => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ];
    }
}