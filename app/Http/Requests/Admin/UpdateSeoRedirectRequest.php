<?php

namespace App\Http\Requests\Admin;

use App\Models\SeoRedirect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoRedirectRequest extends FormRequest
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
        /** @var SeoRedirect|null $redirect */
        $redirect = $this->route('seo_redirect');
        $id = $redirect?->getKey();

        return [
            'from_url' => ['required', 'string', 'max:255', Rule::unique('seo_redirects', 'from_url')->ignore($id)],
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', Rule::in([301, 302])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
