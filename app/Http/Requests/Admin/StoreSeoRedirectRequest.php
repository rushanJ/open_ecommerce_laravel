<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeoRedirectRequest extends FormRequest
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
            'from_url' => ['required', 'string', 'max:255', 'unique:seo_redirects,from_url'],
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', Rule::in([301, 302])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
