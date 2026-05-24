@extends('admin.layouts.app')

@section('title', __('admin.store_settings').' — '.config('app.name'))

@section('breadcrumb', __('admin.store_settings'))

@section('content')
    <x-admin.page-header
        :title="__('admin.store_settings')"
        :subtitle="__('admin.store_settings_subtitle')"
    />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <x-admin.input name="name" :label="__('admin.name')" :value="$store->name" required />
                <x-admin.input name="legal_name" :label="__('admin.legal_name')" :value="$store->legal_name" />
                <x-admin.input name="domain" :label="__('admin.domain')" :value="$store->domain" />
                <x-admin.input name="email" type="email" :label="__('admin.email')" :value="$store->email" required />
                <x-admin.input name="phone" :label="__('admin.phone')" :value="$store->phone" />
                <x-admin.select name="status" :label="__('admin.status')" required>
                    <option value="active" @selected(old('status', $store->status) === 'active')>{{ __('admin.active') }}</option>
                    <option value="inactive" @selected(old('status', $store->status) === 'inactive')>{{ __('admin.inactive') }}</option>
                    <option value="maintenance" @selected(old('status', $store->status) === 'maintenance')>{{ __('admin.maintenance_mode') }}</option>
                </x-admin.select>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.logo') }}</p>
                    @if($store->logo_path)
                        <img src="{{ media_url($store->logo_path) }}" alt="" class="mb-2 h-16 w-auto rounded border border-gray-200 dark:border-gray-700" />
                    @endif
                    <input type="file" name="logo_file" accept="image/jpeg,image/png,image/webp,image/gif"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold dark:text-gray-200 dark:file:bg-slate-800" />
                    @error('logo_file')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.favicon') }}</p>
                    @if($store->favicon_path)
                        <img src="{{ media_url($store->favicon_path) }}" alt="" class="mb-2 h-10 w-10 rounded border border-gray-200 dark:border-gray-700" />
                    @endif
                    <input type="file" name="favicon_file" accept="image/jpeg,image/png,image/webp,image/gif"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold dark:text-gray-200 dark:file:bg-slate-800" />
                    @error('favicon_file')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <x-admin.input name="address_line_1" :label="__('admin.address_line_1')" :value="$store->address_line_1" />
                <x-admin.input name="address_line_2" :label="__('admin.address_line_2')" :value="$store->address_line_2" />
                <x-admin.input name="city" :label="__('admin.city')" :value="$store->city" />
                <x-admin.input name="district" :label="__('admin.district')" :value="$store->district" />
                <x-admin.input name="province" :label="__('admin.province')" :value="$store->province" />
                <x-admin.input name="postal_code" :label="__('admin.postal_code')" :value="$store->postal_code" />
                <x-admin.input name="country_code" :label="__('admin.country_code')" :value="$store->country_code" required />
            </div>

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
