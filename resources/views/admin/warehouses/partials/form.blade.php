@php
    /** @var \App\Models\Warehouse $warehouse */
@endphp

<div class="space-y-4">
    <x-admin.input name="name" :label="__('admin.name')" :value="$warehouse->name" required />
    <x-admin.input name="code" :label="__('admin.code')" :value="$warehouse->code" :placeholder="__('admin.code')" />
    <x-admin.textarea name="address" :label="__('admin.address')" :value="$warehouse->address" />
    <x-admin.select name="status" :label="__('admin.status')" required>
        <option value="active" @selected(old('status', $warehouse->status ?? 'active') === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $warehouse->status ?? '') === 'inactive')>{{ __('admin.inactive') }}</option>
    </x-admin.select>
</div>
