<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('parent_id') && $this->input('parent_id') === '') {
            $this->merge(['parent_id' => null]);
        }
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
        $category = $this->route('category');
        $categoryId = $category->getKey();
        $invalidParentMessage = __('admin.category_invalid_parent');

        return [
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id'),
                function (string $attribute, mixed $value, \Closure $fail) use ($category, $invalidParentMessage): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $parentId = (int) $value;
                    if ($parentId === (int) $category->getKey()) {
                        $fail($invalidParentMessage);
                    }
                    $blocked = array_merge([(int) $category->getKey()], Category::descendantIdsFor($category));
                    if (in_array($parentId, $blocked, true)) {
                        $fail($invalidParentMessage);
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'banner_path' => ['nullable', 'string', 'max:255'],
            'banner_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
        ];
    }
}
