<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxRateRequest extends FormRequest
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
            'tax_class_id' => ['required', 'exists:tax_classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:5'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'is_compound' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}

