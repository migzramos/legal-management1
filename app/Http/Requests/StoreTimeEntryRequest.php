<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isLawyer() || auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'case_id'     => 'required|exists:cases,id',
            'date'        => 'required|date|before_or_equal:today',
            'hours'       => 'required|numeric|min:0.25|max:24',
            'hourly_rate' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
        ];
    }
}