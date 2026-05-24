@props([
    'taxRate',
    'taxClasses',
])

@php
    /** @var \App\Models\TaxRate $taxRate */
    /** @var \Illuminate\Support\Collection<int, \App\Models\TaxClass> $taxClasses */
@endphp

<div class="space-y-4">
    <x-admin.select name="tax_class_id" :label="__('admin.tax_class')" required>
        <option value="">{{ __('admin.select_option') }}</option>
        @foreach($taxClasses as $c)
            <option value="{{ $c->id }}" @selected((string) old('tax_class_id', $taxRate->tax_class_id) === (string) $c->id)>{{ $c->name }}</option>
        @endforeach
    </x-admin.select>

    <x-admin.input name="name" :label="__('admin.name')" :value="old('name', $taxRate->name)" required />

    <div class="grid gap-4 sm:grid-cols-3">
        <x-admin.input name="country_code" :label="__('admin.country')" :value="old('country_code', $taxRate->country_code)" required />
        <x-admin.input name="province" :label="__('admin.province')" :value="old('province', $taxRate->province)" />
        <x-admin.input name="district" :label="__('admin.district')" :value="old('district', $taxRate->district)" />
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-admin.input name="rate" type="number" step="0.0001" :label="__('admin.rate_percent')" :value="old('rate', $taxRate->rate)" required />
        <x-admin.input name="priority" type="number" :label="__('admin.priority')" :value="old('priority', $taxRate->priority ?? 0)" />
        <div>
            <input type="hidden" name="is_compound" value="0">
            <label class="mt-7 inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_compound" value="1" class="rounded border-slate-300 text-[color:var(--mk-admin-primary)]"
                       @checked((bool) old('is_compound', $taxRate->is_compound))>
                {{ __('admin.compound') }}
            </label>
        </div>
    </div>

    <x-admin.select name="status" :label="__('admin.status')" required>
        <option value="active" @selected(old('status', $taxRate->status ?? 'active') === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $taxRate->status ?? 'active') === 'inactive')>{{ __('admin.inactive') }}</option>
    </x-admin.select>
</div>

