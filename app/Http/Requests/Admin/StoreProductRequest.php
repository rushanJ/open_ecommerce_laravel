<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductAttributeValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
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
            'is_featured' => $this->boolean('is_featured'),
            'manage_stock' => $this->boolean('manage_stock'),
            'backorders_allowed' => $this->boolean('backorders_allowed'),
        ]);
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['nullable', 'exists:brands,id'],
            'product_type' => ['required', Rule::in($this->productTypes())],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in($this->statuses())],
            'visibility' => ['required', Rule::in($this->visibilities())],
            'is_featured' => ['boolean'],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:regular_price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'tax_class_id' => ['nullable', 'exists:tax_classes,id'],
            'manage_stock' => ['boolean'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'stock_status' => ['required', Rule::in($this->stockStatuses())],
            'backorders_allowed' => ['boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'digital_file_path' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],

            'attributes' => ['nullable', 'array'],
            'attributes.*.attribute_id' => ['required', 'exists:product_attributes,id'],
            'attributes.*.attribute_value_id' => ['nullable', 'exists:product_attribute_values,id'],
            'attributes.*.custom_value' => ['nullable', 'string', 'max:255'],

            'images' => ['nullable', 'array'],
            'images.*.path' => ['nullable', 'string', 'max:255'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'images.*.is_primary' => ['nullable', 'boolean'],
            'uploaded_images' => ['nullable', 'array'],
            'uploaded_images.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],

            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.barcode' => ['nullable', 'string', 'max:100'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.regular_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_status' => ['nullable', Rule::in($this->stockStatuses())],
            'variants.*.weight' => ['nullable', 'numeric', 'min:0'],
            'variants.*.image_path' => ['nullable', 'string', 'max:255'],
            'variants.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
            'variants.*.attribute_values' => ['nullable', 'array'],
            'variants.*.attribute_values.*.attribute_id' => ['required', 'exists:product_attributes,id'],
            'variants.*.attribute_values.*.attribute_value_id' => ['nullable', 'exists:product_attribute_values,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('attributes', []) as $key => $row) {
                $vid = $row['attribute_value_id'] ?? null;
                $aid = $row['attribute_id'] ?? null;
                if ($vid && $aid) {
                    $val = ProductAttributeValue::query()->find($vid);
                    if (! $val || (int) $val->attribute_id !== (int) $aid) {
                        $validator->errors()->add("attributes.{$key}.attribute_value_id", __('validation.exists'));
                    }
                }
            }

            $variantSkus = [];
            foreach ($this->input('variants', []) as $key => $row) {
                $sku = $row['sku'] ?? null;
                if (is_string($sku) && trim($sku) !== '') {
                    if (in_array($sku, $variantSkus, true)) {
                        $validator->errors()->add("variants.{$key}.sku", __('validation.distinct'));
                    }
                    $variantSkus[] = $sku;
                }

                $reg = $row['regular_price'] ?? null;
                $sale = $row['sale_price'] ?? null;
                if ($sale !== null && $sale !== '' && is_numeric($sale) && $reg !== null && $reg !== '' && is_numeric($reg) && (float) $sale > (float) $reg) {
                    $validator->errors()->add("variants.{$key}.sale_price", __('validation.lte.numeric', ['attribute' => 'sale_price', 'value' => 'regular_price']));
                }

                foreach ($row['attribute_values'] ?? [] as $vk => $pivot) {
                    $vid = $pivot['attribute_value_id'] ?? null;
                    $aid = $pivot['attribute_id'] ?? null;
                    if ($vid && $aid) {
                        $val = ProductAttributeValue::query()->find($vid);
                        if (! $val || (int) $val->attribute_id !== (int) $aid) {
                            $validator->errors()->add("variants.{$key}.attribute_values.{$vk}.attribute_value_id", __('validation.exists'));
                        }
                    }
                }
            }
        });
    }

    /**
     * @return list<string>
     */
    private function productTypes(): array
    {
        return ['simple', 'variable', 'digital', 'bundle'];
    }

    /**
     * @return list<string>
     */
    private function statuses(): array
    {
        return ['draft', 'active', 'inactive', 'archived'];
    }

    /**
     * @return list<string>
     */
    private function visibilities(): array
    {
        return ['visible', 'hidden', 'catalog', 'search'];
    }

    /**
     * @return list<string>
     */
    private function stockStatuses(): array
    {
        return ['in_stock', 'out_of_stock', 'on_backorder'];
    }
}
