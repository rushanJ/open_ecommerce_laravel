@props(['attributeValue'])

@php
    /** @var \App\Models\ProductAttributeValue $attributeValue */
@endphp

<div class="space-y-4">
    <x-admin.input
        name="value"
        :label="__('admin.value')"
        :value="$attributeValue->value"
        required
    />

    <x-admin.input
        name="slug"
        :label="__('admin.slug')"
        :value="$attributeValue->slug"
    />

    <x-admin.input
        name="color_code"
        :label="__('admin.color_code')"
        :value="$attributeValue->color_code"
    />

    <x-admin.input
        name="image_path"
        :label="__('admin.image_path')"
        :value="$attributeValue->image_path"
    />

    <x-admin.input
        name="sort_order"
        type="number"
        :label="__('admin.sort_order')"
        :value="$attributeValue->sort_order ?? 0"
    />
</div>
