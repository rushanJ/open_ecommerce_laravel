@php
    /** @var \App\Models\Product $product */
@endphp

<div class="space-y-4">
    <x-admin.input name="meta_title" :label="__('admin.meta_title')" :value="$product->meta_title" />
    <x-admin.textarea name="meta_description" :label="__('admin.meta_description')" :value="$product->meta_description" />
</div>
