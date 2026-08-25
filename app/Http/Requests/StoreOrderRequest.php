<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chefId' => ['required', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dishId' => ['required', 'integer', 'exists:dishes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['sometimes', 'nullable', 'string', 'max:255'],
            'expectedTime' => ['sometimes', 'nullable', 'date'],
            'note' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'contactPhone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
