@props(['attribute'])

@php
    /** @var \App\Models\ProductAttribute $attribute */
@endphp

<div class="space-y-4">
    <x-admin.input
        name="name"
        :label="__('admin.name')"
        :value="$attribute->name"
        required
    />

    <x-admin.input
        name="slug"
        :label="__('admin.slug')"
        :value="$attribute->slug"
    />

    <x-admin.select name="type" :label="__('admin.type')" required>
        @foreach (['select' => 'Select', 'text' => 'Text', 'color' => 'Color', 'image' => 'Image'] as $t => $label)
            <option value="{{ $t }}" @selected(old('type', $attribute->type ?? 'select') === $t)>{{ $label }}</option>
        @endforeach
    </x-admin.select>

    <div>
        <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.filterable') }}</span>
        <input type="hidden" name="is_filterable" value="0">
        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input
                type="checkbox"
                name="is_filterable"
                value="1"
                class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] focus:ring-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900"
                @checked(old('is_filterable', $attribute->is_filterable ?? false))
            />
            <span>{{ __('admin.filterable') }}</span>
        </label>
    </div>

    <x-admin.input
        name="sort_order"
        type="number"
        :label="__('admin.sort_order')"
        :value="$attribute->sort_order ?? 0"
    />
</div>
