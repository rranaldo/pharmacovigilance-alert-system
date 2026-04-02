<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'The username is required.',
            'password.required' => 'The password is required.',
        ];
    }
}
