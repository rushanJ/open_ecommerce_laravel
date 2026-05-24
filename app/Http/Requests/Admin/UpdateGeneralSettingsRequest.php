<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
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
            'currency_code' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:100'],
            'maintenance_mode' => ['nullable', 'boolean'],
        ];
    }
}
