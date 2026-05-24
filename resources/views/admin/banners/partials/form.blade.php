@php /** @var \App\Models\Banner $banner */ @endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="title" :label="__('admin.title')" :value="old('title', $banner->title)" required />
    <x-admin.input name="subtitle" :label="__('admin.subtitle')" :value="old('subtitle', $banner->subtitle)" />
</div>

<x-admin.input name="image_path" :label="__('admin.image_path')" :value="old('image_path', $banner->image_path)" required />
<div>
    <label class="block text-sm font-semibold text-slate-900">{{ __('admin.upload_images') }}</label>
    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif"
           class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" />
    @error('image_file') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
</div>
<x-admin.input name="link_url" :label="__('admin.link_url')" :value="old('link_url', $banner->link_url)" />

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="position" :label="__('admin.position')" :value="old('position', $banner->position)" required />
    <x-admin.select name="status" :label="__('admin.status')">
        <option value="active" @selected(old('status', $banner->status ?? 'active') === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $banner->status ?? 'active') === 'inactive')>{{ __('admin.inactive') }}</option>
    </x-admin.select>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="starts_at" type="datetime-local" :label="__('admin.starts_at')" :value="old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i'))" />
    <x-admin.input name="ends_at" type="datetime-local" :label="__('admin.ends_at')" :value="old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i'))" />
</div>

