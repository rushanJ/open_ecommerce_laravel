<?php

namespace App\Http\Requests\Admin;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
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
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['page', 'category', 'product', 'custom'])],
            'reference_id' => ['nullable', 'integer'],
            'url' => ['nullable', 'string', 'max:255'],
            'target' => ['required', 'string', Rule::in(['self', 'blank'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = (string) $this->input('type');

            if ($type === 'custom' && ! $this->filled('url')) {
                $validator->errors()->add('url', __('validation.required'));
            }

            if (in_array($type, ['page', 'category', 'product'], true) && ! $this->filled('reference_id')) {
                $validator->errors()->add('reference_id', __('validation.required'));
            }

            if ($this->filled('parent_id')) {
                /** @var MenuItem|null $parent */
                $parent = MenuItem::query()->find($this->integer('parent_id'));
                if ($parent && (int) $parent->menu_id !== (int) $this->integer('menu_id')) {
                    $validator->errors()->add('parent_id', __('validation.invalid'));
                }
            }
        });
    }
}

