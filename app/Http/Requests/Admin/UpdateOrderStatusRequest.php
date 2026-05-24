<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([
                'pending',
                'processing',
                'confirmed',
                'packed',
                'shipped',
                'delivered',
                'cancelled',
                'failed',
                'refunded',
            ])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

