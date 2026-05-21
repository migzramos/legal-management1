<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isLawyer() || auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title'         => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'category_id'   => 'sometimes|exists:case_categories,id',
            'court_type_id' => 'sometimes|exists:court_types,id',
            'client_id'     => 'sometimes|exists:users,id',
            'status'        => 'sometimes|in:open,ongoing,closed,won,lost,dismissed',
            'filed_date'    => 'nullable|date',
            'hearing_date'  => 'nullable|date',
            'closed_date'   => 'nullable|date',
            'notes'         => 'nullable|string',
        ];
    }
}