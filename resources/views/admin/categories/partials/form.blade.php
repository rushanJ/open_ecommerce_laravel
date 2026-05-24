@props(['category', 'parentChoices'])

@php
    /** @var \App\Models\Category $category */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $parentChoices */
@endphp

<div class="space-y-4">
    <x-admin.select name="parent_id" :label="__('admin.parent_category')">
        <option value="">{{ __('admin.none') }}</option>
        @foreach ($parentChoices as $p)
            <option value="{{ $p->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $p->id)>
                {{ $p->name }}
            </option>
        @endforeach
    </x-admin.select>

    <x-admin.input
        name="name"
        :label="__('admin.name')"
        :value="$category->name"
        required
    />

    <x-admin.input
        name="slug"
        :label="__('admin.slug')"
        :value="$category->slug"
    />

    <x-admin.textarea
        name="description"
        :label="__('admin.description')"
        :value="$category->description"
    />

    <x-admin.input
        name="image_path"
        :label="__('admin.image_path')"
        :value="$category->image_path"
    />

    <div>
        <label class="block text-sm font-semibold text-slate-900">{{ __('admin.upload_images') }} ({{ __('admin.image_path') }})</label>
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif"
               class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" />
        @error('image_file') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
    </div>

    <x-admin.input
        name="banner_path"
        :label="__('admin.banner_path')"
        :value="$category->banner_path"
    />

    <div>
        <label class="block text-sm font-semibold text-slate-900">{{ __('admin.upload_images') }} ({{ __('admin.banner_path') }})</label>
        <input type="file" name="banner_file" accept="image/jpeg,image/png,image/webp,image/gif"
               class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" />
        @error('banner_file') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
    </div>

    <x-admin.select name="status" :label="__('admin.status')" required>
        <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $category->status ?? '') === 'inactive')>{{ __('admin.inactive') }}</option>
    </x-admin.select>

    <x-admin.input
        name="sort_order"
        type="number"
        :label="__('admin.sort_order')"
        :value="$category->sort_order ?? 0"
    />

    <x-admin.input
        name="meta_title"
        :label="__('admin.meta_title')"
        :value="$category->meta_title"
    />

    <x-admin.textarea
        name="meta_description"
        :label="__('admin.meta_description')"
        :value="$category->meta_description"
    />

    <x-admin.textarea
        name="meta_keywords"
        :label="__('admin.meta_keywords')"
        :value="$category->meta_keywords"
    />
</div>
