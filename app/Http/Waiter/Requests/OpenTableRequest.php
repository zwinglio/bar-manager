<?php

namespace App\Http\Waiter\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'min:1'],
            'person_count' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
