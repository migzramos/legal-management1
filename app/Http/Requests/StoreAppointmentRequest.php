<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isClient();
    }

    public function rules(): array
    {
        return [
            'lawyer_id'        => ['required', 'exists:users,id'],
            'scheduled_date'   => ['required', 'date', 'after:today'],
            'scheduled_time'   => ['required', 'string'],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90,120'],
            'purpose'          => ['required', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'lawyer_id.required'        => 'Please select a lawyer.',
            'lawyer_id.exists'          => 'The selected lawyer is not available.',
            'scheduled_date.required'   => 'Please select a date.',
            'scheduled_date.after'      => 'The appointment date must be in the future.',
            'scheduled_time.required'   => 'Please select a time slot.',
            'duration_minutes.required' => 'Please specify the appointment duration.',
            'duration_minutes.in'       => 'Please select a valid duration (30, 60, 90, or 120 minutes).',
            'purpose.required'          => 'Please describe the purpose of the appointment.',
            'purpose.max'               => 'Purpose description is too long.',
            'notes.max'                 => 'Notes are too long.',
        ];
    }

    /**
     * Combine scheduled_date + scheduled_time into appointment_at after validation.
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'appointment_at' => Carbon::parse(
                $this->scheduled_date . ' ' . $this->scheduled_time
            ),
        ]);
    }
}