@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.create_webhook')" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.webhooks.store') }}" class="space-y-6">
            @csrf
            @include('admin.webhooks.partials.form', ['endpoint' => $endpoint, 'availableEvents' => $availableEvents])

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection
