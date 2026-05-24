@php
    /** @var \App\Models\Coupon $coupon */
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="code" :label="__('admin.coupon_code')" :value="old('code', $coupon->code)" required />
    <x-admin.input name="name" :label="__('admin.name')" :value="old('name', $coupon->name)" />
</div>

<x-admin.textarea name="description" :label="__('admin.description')">{{ old('description', $coupon->description) }}</x-admin.textarea>

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.select name="type" :label="__('admin.discount_type')">
        @foreach(['percentage','fixed_cart','fixed_product','free_shipping'] as $t)
            <option value="{{ $t }}" @selected(old('type', $coupon->type) === $t)>{{ __('admin.'.$t) }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.input name="value" type="number" step="0.0001" :label="__('admin.value')" :value="old('value', $coupon->value ?? 0)" required />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="minimum_order_amount" type="number" step="0.0001" :label="__('admin.minimum_order_amount')" :value="old('minimum_order_amount', $coupon->minimum_order_amount)" />
    <x-admin.input name="maximum_discount_amount" type="number" step="0.0001" :label="__('admin.maximum_discount_amount')" :value="old('maximum_discount_amount', $coupon->maximum_discount_amount)" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="usage_limit" type="number" step="1" :label="__('admin.usage_limit')" :value="old('usage_limit', $coupon->usage_limit)" />
    <x-admin.input name="usage_limit_per_customer" type="number" step="1" :label="__('admin.usage_limit_per_customer')" :value="old('usage_limit_per_customer', $coupon->usage_limit_per_customer)" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="starts_at" type="date" :label="__('admin.starts_at')" :value="old('starts_at', optional($coupon->starts_at)->format('Y-m-d'))" />
    <x-admin.input name="ends_at" type="date" :label="__('admin.ends_at')" :value="old('ends_at', optional($coupon->ends_at)->format('Y-m-d'))" />
</div>

<x-admin.select name="status" :label="__('admin.status')">
    @foreach(['active','inactive','expired'] as $s)
        <option value="{{ $s }}" @selected(old('status', $coupon->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
    @endforeach
</x-admin.select>

<div class="grid gap-6 lg:grid-cols-3">
    <div>
        <p class="text-sm font-semibold text-slate-900">{{ __('admin.product_restrictions') }}</p>
        <div class="mt-2 max-h-72 space-y-2 overflow-auto rounded-lg border border-slate-200 p-3 text-sm">
            @foreach($products as $p)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="product_ids[]" value="{{ $p->id }}"
                           @checked(in_array($p->id, old('product_ids', $selected['product_ids'] ?? []), true)) />
                    <span class="truncate">{{ $p->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <p class="text-sm font-semibold text-slate-900">{{ __('admin.category_restrictions') }}</p>
        <div class="mt-2 max-h-72 space-y-2 overflow-auto rounded-lg border border-slate-200 p-3 text-sm">
            @foreach($categories as $c)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="category_ids[]" value="{{ $c->id }}"
                           @checked(in_array($c->id, old('category_ids', $selected['category_ids'] ?? []), true)) />
                    <span class="truncate">{{ $c->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <p class="text-sm font-semibold text-slate-900">{{ __('admin.customer_restrictions') }}</p>
        <div class="mt-2 max-h-72 space-y-2 overflow-auto rounded-lg border border-slate-200 p-3 text-sm">
            @foreach($customers as $c)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="customer_ids[]" value="{{ $c->id }}"
                           @checked(in_array($c->id, old('customer_ids', $selected['customer_ids'] ?? []), true)) />
                    <span class="truncate">{{ $c->email }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

