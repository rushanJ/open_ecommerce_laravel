@extends('admin.layouts.app')

@section('title', __('admin.mail_settings').' — '.config('app.name'))

@section('breadcrumb', __('admin.mail_settings'))

@section('content')
    <x-admin.page-header
        :title="__('admin.mail_settings')"
        :subtitle="__('admin.mail_settings_subtitle')"
    />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.settings.mail.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <x-admin.input name="mail_from_name" :label="__('admin.mail_from_name')" :value="old('mail_from_name', $mailFromName)" required />
                <x-admin.input name="mail_from_address" type="email" :label="__('admin.mail_from_address')" :value="old('mail_from_address', $mailFromAddress)" required />
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <x-admin.input name="smtp_host" :label="__('admin.smtp_host')" :value="old('smtp_host', $smtpHost)" />
                <x-admin.input name="smtp_port" type="number" :label="__('admin.smtp_port')" :value="old('smtp_port', $smtpPort)" />
                <x-admin.input name="smtp_username" :label="__('admin.smtp_username')" :value="old('smtp_username', $smtpUsername)" />
                <div>
                    <x-admin.input name="smtp_password" type="password" :label="__('admin.smtp_password')" value="" autocomplete="new-password" />
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400">{{ __('admin.leave_blank_to_keep_existing') }}</p>
                </div>
                <x-admin.select name="smtp_encryption" :label="__('admin.smtp_encryption')">
                    <option value="">{{ __('admin.smtp_encryption_none') }}</option>
                    <option value="tls" @selected(old('smtp_encryption', $smtpEncryption) === 'tls')>TLS</option>
                    <option value="ssl" @selected(old('smtp_encryption', $smtpEncryption) === 'ssl')>SSL</option>
                </x-admin.select>
            </div>

            <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
        </form>
    </x-admin.card>
@endsection
