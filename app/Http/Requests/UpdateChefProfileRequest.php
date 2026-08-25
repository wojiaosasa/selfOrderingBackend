<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChefProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'displayName' => ['sometimes', 'string', 'max:60'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'serviceNote' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'isAcceptingOrders' => ['sometimes', 'boolean'],
        ];
    }
}
