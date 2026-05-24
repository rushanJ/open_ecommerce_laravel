@php
    /** @var \App\Models\Product $product */
@endphp

<div class="space-y-4">
    <x-admin.input name="regular_price" type="number" step="0.0001" :label="__('admin.regular_price')" :value="old('regular_price', $product->regular_price ?? 0)" required />

    <x-admin.input name="sale_price" type="number" step="0.0001" :label="__('admin.sale_price')" :value="$product->sale_price" />

    <x-admin.input name="cost_price" type="number" step="0.0001" :label="__('admin.cost_price')" :value="$product->cost_price" />

    <x-admin.select name="tax_class_id" :label="__('admin.tax_class')">
        <option value="">{{ __('admin.none') }}</option>
        @foreach ($taxClasses as $tc)
            <option value="{{ $tc->id }}" @selected(old('tax_class_id', $product->tax_class_id) == $tc->id)>{{ $tc->name }}</option>
        @endforeach
    </x-admin.select>
</div>
