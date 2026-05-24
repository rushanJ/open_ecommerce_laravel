@php
    /** @var \App\Models\Product $product */
@endphp

<div class="space-y-4">
    <x-admin.select name="product_type" :label="__('admin.product_type')" required>
        @foreach ($productTypeOptions as $val => $label)
            <option value="{{ $val }}" @selected(old('product_type', $product->product_type ?? 'simple') === $val)>{{ $label }}</option>
        @endforeach
    </x-admin.select>

    <x-admin.input name="name" :label="__('admin.name')" :value="$product->name" required />

    <x-admin.input name="slug" :label="__('admin.slug')" :value="$product->slug" />

    <x-admin.input name="sku" :label="__('admin.sku')" :value="$product->sku" />

    <x-admin.input name="barcode" :label="__('admin.barcode')" :value="$product->barcode" />

    <x-admin.select name="brand_id" :label="__('admin.brand')">
        <option value="">{{ __('admin.none') }}</option>
        @foreach ($brands as $b)
            <option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id) == $b->id)>{{ $b->name }}</option>
        @endforeach
    </x-admin.select>

    <x-admin.select name="status" :label="__('admin.status')" required>
        @foreach ($statusOptions as $val => $label)
            <option value="{{ $val }}" @selected(old('status', $product->status ?? 'draft') === $val)>{{ $label }}</option>
        @endforeach
    </x-admin.select>

    <x-admin.select name="visibility" :label="__('admin.visibility')" required>
        @foreach ($visibilityOptions as $val => $label)
            <option value="{{ $val }}" @selected(old('visibility', $product->visibility ?? 'visible') === $val)>{{ $label }}</option>
        @endforeach
    </x-admin.select>

    <div>
        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.featured') }}</span>
        <input type="hidden" name="is_featured" value="0">
        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="is_featured" value="1" class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] focus:ring-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900"    
                @checked(old('is_featured', $product->is_featured ?? false))>
            <span>{{ __('admin.yes') }}</span>
        </label>
    </div>

    <x-admin.textarea name="short_description" :label="__('admin.short_description')" :value="$product->short_description" />

    <x-admin.textarea name="description" :label="__('admin.description')" :value="$product->description" rows="6" />

    <x-admin.input
        name="digital_file_path"
        :label="__('admin.digital_file_path')"
        :value="$product->digital_file_path"
    />

    <x-admin.input
        name="published_at"
        type="datetime-local"
        :label="__('admin.published_at')"
        :value="old('published_at', $product->published_at?->format('Y-m-d\TH:i'))"
    />
</div>
