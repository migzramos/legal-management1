<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body'          => 'required|string|min:1|max:5000|not_regex:/^\s+$/',
            'receiver_id'   => 'required|exists:users,id',
            'case_id'       => 'nullable|exists:cases,id',
            'appointment_id'=> 'nullable|exists:appointments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'body.required'            => 'Message body is required.',
            'body.max'                 => 'Message cannot exceed 5,000 characters.',
            'body.not_regex'           => 'Message cannot be empty whitespace.',
            'receiver_id.required'     => 'A recipient is required.',
            'case_id.required'         => 'A case or appointment context is required.',
            'appointment_id.exists'    => 'The referenced appointment does not exist.',
        ];
    }
}