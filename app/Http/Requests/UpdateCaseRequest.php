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
            'title'             => 'sometimes|string|max:255',
            'description'       => 'nullable|string',
            'category_id'       => 'sometimes|exists:case_categories,id',
            'court_type_id'     => 'sometimes|exists:court_types,id',
            'client_id'         => 'sometimes|exists:users,id',
            'status'            => 'sometimes|in:intake,barangay_mediation,escalation_to_court,active_case,resolution',
            'filed_date'        => 'nullable|date',
            'hearing_date'      => 'nullable|date',
            'next_hearing_date' => 'nullable|date',
            'closed_date'       => 'nullable|date',
            'court_name'        => 'nullable|string|max:255',
            'judge_name'        => 'nullable|string|max:255',
            'opposing_party'    => 'nullable|string|max:255',
            'opposing_counsel'  => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
        ];
    }
}