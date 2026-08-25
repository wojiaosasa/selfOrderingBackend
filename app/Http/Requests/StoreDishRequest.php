<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'imageUrl' => ['sometimes', 'nullable', 'string', 'max:500'],
            'category' => ['required', 'string', Rule::in(['meat', 'small_meat', 'vegetable', 'soup'])],
            'recipe' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'tasteNote' => ['sometimes', 'nullable', 'string', 'max:255'],
            'price' => ['sometimes', 'nullable', 'string', 'max:60'],
            'portionNote' => ['sometimes', 'nullable', 'string', 'max:120'],
            'sortOrder' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
