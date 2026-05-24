<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMailSettingsRequest extends FormRequest
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
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'string', Rule::in(['tls', 'ssl', ''])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('smtp_port') && $this->input('smtp_port') === '') {
            $this->merge(['smtp_port' => null]);
        }
        if ($this->input('smtp_encryption') === '') {
            $this->merge(['smtp_encryption' => null]);
        }
    }
}
