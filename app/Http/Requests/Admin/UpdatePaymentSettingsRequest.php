<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentSettingsRequest extends FormRequest
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
            'payhere_enabled' => ['nullable', 'boolean'],
            'payhere_mode' => ['required', Rule::in(['sandbox', 'live'])],
            'payhere_merchant_id' => ['nullable', 'string', 'max:255'],
            'payhere_merchant_secret' => ['nullable', 'string', 'max:255'],
        ];
    }
}
