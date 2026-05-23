<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required_without:user_id', 'nullable', 'email'],
            'user_id' => ['required_without:email', 'nullable', 'exists:users,id'],
        ];
    }
}
