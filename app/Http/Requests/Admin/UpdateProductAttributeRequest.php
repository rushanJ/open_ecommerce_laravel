<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $slug = $this->input('slug');
            $this->merge([
                'slug' => (is_string($slug) && trim($slug) !== '') ? Str::slug($slug) : null,
            ]);
        }

        $this->merge([
            'is_filterable' => $this->boolean('is_filterable'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $attribute = $this->route('attribute');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_attributes', 'slug')->ignore($attribute->getKey())],
            'type' => ['required', Rule::in(['text', 'color', 'image', 'select'])],
            'is_filterable' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
