@extends('admin.layouts.app')

@section('title', __('admin.create_attribute_value').' — '.config('app.name'))

@section('breadcrumb', __('admin.create_attribute_value'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
    @endphp

    <x-admin.page-header :title="__('admin.create_attribute_value')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.attributes.values.index', $attribute) }}" variant="ghost" size="sm">
                {{ __('admin.back') }}
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
        {{ __('admin.attribute') }}: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $attribute->name }}</span>
        (<x-admin.badge variant="info">{{ $attribute->type }}</x-admin.badge>)
    </p>

    <form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}" class="max-w-3xl">
        @csrf
        @include('admin.attributes.values.partials.form', ['attributeValue' => $value])
        <div class="mt-8 flex flex-wrap gap-2">
            @if ($admin->hasPermission('attributes.create'))
                <x-admin.button type="submit" variant="primary">
                    {{ __('admin.save') }}
                </x-admin.button>
            @endif
            <x-admin.button type="link" href="{{ route('admin.attributes.values.index', $attribute) }}" variant="secondary">
                {{ __('admin.cancel') }}
            </x-admin.button>
        </div>
    </form>
@endsection
