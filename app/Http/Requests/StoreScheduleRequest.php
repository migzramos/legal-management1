<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isLawyer() || auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'case_id'      => 'required|exists:cases,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:court_hearing,deadline,appointment,meeting,other',
            'scheduled_at' => 'required|date|after:now',
            'location'     => 'nullable|string|max:255',
            'status'       => 'sometimes|in:upcoming,completed,cancelled,postponed',
            'notes'        => 'nullable|string',
        ];
    }
}