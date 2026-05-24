<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductAttributeValueRequest extends FormRequest
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
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $attribute = $this->route('attribute');
        $attributeId = $attribute->getKey();

        return [
            'value' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_attribute_values', 'slug')->where('attribute_id', $attributeId),
            ],
            'color_code' => ['nullable', 'string', 'max:30', Rule::requiredIf(fn () => $attribute->type === 'color')],
            'image_path' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
