<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'type' => ['required', Rule::in(['purchase', 'return', 'adjustment'])],
            'quantity' => ['required', 'numeric', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $pid = (int) ($this->input('product_id') ?? 0);
            $vid = $this->input('variant_id');

            if (! $pid) {
                return;
            }

            $product = Product::query()->find($pid);
            if (! $product) {
                return;
            }

            $hasVariants = $product->variants()->exists();

            if ($hasVariants && ($vid === null || $vid === '')) {
                $validator->errors()->add('variant_id', __('validation.required', ['attribute' => 'variant_id']));
            }

            if (! $hasVariants && $vid !== null && $vid !== '') {
                $validator->errors()->add('variant_id', __('validation.prohibited'));
            }

            if (! $hasVariants || $vid === null || $vid === '') {
                return;
            }

            $variant = ProductVariant::query()->find($vid);
            if (! $variant) {
                return;
            }

            if ((int) $variant->product_id !== $pid) {
                $validator->errors()->add('variant_id', __('validation.exists'));
            }
        });
    }
}
