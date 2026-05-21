<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isLawyer() || auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'case_id'     => 'required|exists:cases,id',
            'assigned_to' => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'status'      => 'sometimes|in:pending,in_progress,completed,cancelled',
            'due_date'    => 'nullable|date|after_or_equal:today',
        ];
    }
}