<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
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
            'disk' => ['required', 'string', 'max:100'],
            'path' => ['required', 'string', 'max:255'],
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'size' => ['nullable', 'integer', 'min:0'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}

