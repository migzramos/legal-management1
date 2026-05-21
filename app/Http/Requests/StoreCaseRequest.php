<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isLawyer() || auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'case_category_id' => 'required|exists:case_categories,id',
            'court_type_id'    => 'required|exists:court_types,id',
            'client_id'        => 'required|exists:users,id',
            'status'           => 'sometimes|in:intake,barangay_mediation,escalation_to_court,active_case,resolution',
            'filing_date'      => 'nullable|date',
            'hearing_date'     => 'nullable|date|after_or_equal:filing_date',
            'court_name'       => 'nullable|string|max:255',
            'judge_name'       => 'nullable|string|max:255',
            'opposing_party'   => 'nullable|string|max:255',
            'opposing_counsel' => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
        ];
    }
}