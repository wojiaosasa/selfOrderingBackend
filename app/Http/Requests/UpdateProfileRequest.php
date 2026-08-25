<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nickname' => ['sometimes', 'string', 'max:60'],
            'avatarUrl' => ['sometimes', 'string', 'max:500'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
