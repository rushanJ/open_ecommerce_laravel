@props(['brand'])

@php
    /** @var \App\Models\Brand $brand */
@endphp

<div class="space-y-4">
    <x-admin.input
        name="name"
        :label="__('admin.name')"
        :value="$brand->name"
        required
    />

    <x-admin.input
        name="slug"
        :label="__('admin.slug')"
        :value="$brand->slug"
        :placeholder="__('admin.slug')"
    />

    <x-admin.textarea
        name="description"
        :label="__('admin.description')"
        :value="$brand->description"
    />

    <x-admin.input
        name="logo_path"
        :label="__('admin.logo_path')"
        :value="$brand->logo_path"
    />

    <div>
        <label class="block text-sm font-semibold text-slate-900">{{ __('admin.upload_images') }} ({{ __('admin.logo_path') }})</label>
        <input type="file" name="logo_file" accept="image/jpeg,image/png,image/webp,image/gif"
               class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" />
        @error('logo_file') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
    </div>

    <x-admin.select name="status" :label="__('admin.status')" required>
        <option value="active" @selected(old('status', $brand->status ?? 'active') === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $brand->status ?? '') === 'inactive')>{{ __('admin.inactive') }}</option>
    </x-admin.select>

    <x-admin.input
        name="sort_order"
        type="number"
        :label="__('admin.sort_order')"
        :value="$brand->sort_order ?? 0"
    />
</div>
