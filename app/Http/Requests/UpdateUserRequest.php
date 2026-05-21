<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $userId,
            'password'  => 'sometimes|string|min:8|confirmed',
            'role'      => 'sometimes|in:admin,lawyer,client',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }
}