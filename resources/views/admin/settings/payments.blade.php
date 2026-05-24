@extends('admin.layouts.app')

@section('title', __('admin.payment_settings').' — '.config('app.name'))

@section('breadcrumb', __('admin.payment_settings'))

@section('content')
    <x-admin.page-header
        :title="__('admin.payment_settings')"
        :subtitle="__('admin.payment_settings_subtitle')"
    />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.settings.payments.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-3">
                <input type="hidden" name="payhere_enabled" value="0" />
                <input
                    type="checkbox"
                    name="payhere_enabled"
                    id="payhere_enabled"
                    value="1"
                    class="h-4 w-4 rounded border-gray-300 text-[color:var(--mk-admin-primary)]"
                    @checked(old('payhere_enabled', $payhereEnabled ? '1' : '0') === '1')
                />
                <label for="payhere_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('admin.payhere_enabled') }}</label>
            </div>

            <x-admin.select name="payhere_mode" :label="__('admin.payhere_mode')" required>
                <option value="sandbox" @selected(old('payhere_mode', $payhereMode) === 'sandbox')>Sandbox</option>
                <option value="live" @selected(old('payhere_mode', $payhereMode) === 'live')>Live</option>
            </x-admin.select>

            <x-admin.input name="payhere_merchant_id" :label="__('admin.payhere_merchant_id')" :value="old('payhere_merchant_id', $payhereMerchantId)" />

            <div>
                <x-admin.input name="payhere_merchant_secret" type="password" :label="__('admin.payhere_merchant_secret')" value="" autocomplete="new-password" />
                <p class="mt-1.5 text-sm text-amber-800 dark:text-amber-200/90">{{ __('admin.leave_blank_to_keep_existing') }}</p>
            </div>

            <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
        </form>
    </x-admin.card>
@endsection
