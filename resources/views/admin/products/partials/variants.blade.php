<div class="space-y-8">
    @foreach ($variantSlots as $vi => $slot)
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ __('admin.variant') }} {{ $vi + 1 }}
            </p>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input
                    name="variants[{{ $vi }}][sku]"
                    :label="__('admin.sku')"
                    :value="old('variants.'.$vi.'.sku', $slot['sku'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][barcode]"
                    :label="__('admin.barcode')"
                    :value="old('variants.'.$vi.'.barcode', $slot['barcode'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][name]"
                    :label="__('admin.name')"
                    :value="old('variants.'.$vi.'.name', $slot['name'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][regular_price]"
                    type="number"
                    step="0.0001"
                    :label="__('admin.regular_price')"
                    :value="old('variants.'.$vi.'.regular_price', $slot['regular_price'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][sale_price]"
                    type="number"
                    step="0.0001"
                    :label="__('admin.sale_price')"
                    :value="old('variants.'.$vi.'.sale_price', $slot['sale_price'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][cost_price]"
                    type="number"
                    step="0.0001"
                    :label="__('admin.cost_price')"
                    :value="old('variants.'.$vi.'.cost_price', $slot['cost_price'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][stock_quantity]"
                    type="number"
                    step="0.0001"
                    :label="__('admin.stock_quantity')"
                    :value="old('variants.'.$vi.'.stock_quantity', $slot['stock_quantity'] ?? '')"
                />
                <x-admin.select name="variants[{{ $vi }}][stock_status]" :label="__('admin.stock_status')">
                    @foreach ($stockStatusOptions as $val => $label)
                        <option value="{{ $val }}" @selected(old('variants.'.$vi.'.stock_status', $slot['stock_status'] ?? 'in_stock') === $val)>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-admin.select>
                <x-admin.input
                    name="variants[{{ $vi }}][weight]"
                    type="number"
                    step="0.0001"
                    :label="__('admin.weight')"
                    :value="old('variants.'.$vi.'.weight', $slot['weight'] ?? '')"
                />
                <x-admin.input
                    name="variants[{{ $vi }}][image_path]"
                    :label="__('admin.image_path')"
                    :value="old('variants.'.$vi.'.image_path', $slot['image_path'] ?? '')"
                />
                <x-admin.select name="variants[{{ $vi }}][status]" :label="__('admin.status')">
                    <option value="active" @selected(old('variants.'.$vi.'.status', $slot['status'] ?? 'active') === 'active')>{{ __('admin.active') }}</option>
                    <option value="inactive" @selected(old('variants.'.$vi.'.status', $slot['status'] ?? 'active') === 'inactive')>{{ __('admin.inactive') }}</option>
                </x-admin.select>
            </div>

            @if ($productAttributes->isNotEmpty())
                <div class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.product_attributes') }}</p>
                    @foreach ($productAttributes as $ai => $attr)
                        @php
                            $pair = $slot['attribute_values'][$ai] ?? ['attribute_id' => $attr->id, 'attribute_value_id' => null];
                        @endphp
                        <input type="hidden" name="variants[{{ $vi }}][attribute_values][{{ $ai }}][attribute_id]" value="{{ $attr->id }}">
                        <x-admin.select
                            name="variants[{{ $vi }}][attribute_values][{{ $ai }}][attribute_value_id]"
                            :label="$attr->name"
                        >
                            <option value="">{{ __('admin.none') }}</option>
                            @foreach ($attr->values as $v)
                                <option
                                    value="{{ $v->id }}"
                                    @selected(old('variants.'.$vi.'.attribute_values.'.$ai.'.attribute_value_id', $pair['attribute_value_id'] ?? null) == $v->id)
                                >
                                    {{ $v->value }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
