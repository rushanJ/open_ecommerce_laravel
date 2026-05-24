@php
    /** @var \App\Models\Product $product */
@endphp

<div class="space-y-4">
    <div>
        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.manage_stock') }}</span>
        <input type="hidden" name="manage_stock" value="0">
        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="manage_stock" value="1" class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] focus:ring-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900"
                @checked(old('manage_stock', $product->manage_stock ?? false))>
            <span>{{ __('admin.yes') }}</span>
        </label>
    </div>

    <x-admin.input name="stock_quantity" type="number" step="0.0001" :label="__('admin.stock_quantity')" :value="$product->stock_quantity" />

    <x-admin.input name="low_stock_threshold" type="number" step="0.0001" :label="__('admin.low_stock_threshold')" :value="$product->low_stock_threshold" />

    <x-admin.select name="stock_status" :label="__('admin.stock_status')" required>
        @foreach ($stockStatusOptions as $val => $label)
            <option value="{{ $val }}" @selected(old('stock_status', $product->stock_status ?? 'in_stock') === $val)>{{ $label }}</option>
        @endforeach
    </x-admin.select>

    <div>
        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.backorders_allowed') }}</span>
        <input type="hidden" name="backorders_allowed" value="0">
        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="backorders_allowed" value="1" class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] focus:ring-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900"
                @checked(old('backorders_allowed', $product->backorders_allowed ?? false))>
            <span>{{ __('admin.yes') }}</span>
        </label>
    </div>

    <x-admin.input name="weight" type="number" step="0.0001" :label="__('admin.weight')" :value="$product->weight" />
    <x-admin.input name="length" type="number" step="0.0001" :label="__('admin.length')" :value="$product->length" />
    <x-admin.input name="width" type="number" step="0.0001" :label="__('admin.width')" :value="$product->width" />
    <x-admin.input name="height" type="number" step="0.0001" :label="__('admin.height')" :value="$product->height" />
</div>
