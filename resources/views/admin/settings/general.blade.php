@extends('admin.layouts.app')

@section('title', __('admin.general_settings').' — '.config('app.name'))

@section('breadcrumb', __('admin.general_settings'))

@section('content')
    <x-admin.page-header
        :title="__('admin.general_settings')"
        :subtitle="__('admin.general_settings_subtitle')"
    />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.settings.general.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <x-admin.input name="currency_code" :label="__('admin.currency_code')" :value="old('currency_code', $store->currency_code)" required />
                <x-admin.input name="timezone" :label="__('admin.timezone')" :value="old('timezone', $store->timezone)" required />
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/40">
                <input type="hidden" name="maintenance_mode" value="0" />
                <input
                    type="checkbox"
                    name="maintenance_mode"
                    id="maintenance_mode"
                    value="1"
                    class="mt-1 h-4 w-4 rounded border-gray-300 text-[color:var(--mk-admin-primary)]"
                    @checked(old('maintenance_mode', $maintenanceMode ? '1' : '0') === '1')
                />
                <div>
                    <label for="maintenance_mode" class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('admin.maintenance_mode') }}</label>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('admin.maintenance_mode_help') }}</p>
                </div>
            </div>

            <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
        </form>
    </x-admin.card>
@endsection
