<?php

namespace App\Http\Waiter\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTableProductRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
