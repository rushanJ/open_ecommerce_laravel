@extends('admin.layouts.app')

@section('title', __('admin.edit_attribute').' — '.config('app.name'))

@section('breadcrumb', __('admin.edit_attribute'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
    @endphp

    <x-admin.page-header :title="__('admin.edit_attribute')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.attributes.index') }}" variant="ghost" size="sm">
                {{ __('admin.back') }}
            </x-admin.button>
            @if ($admin->hasPermission('attributes.view'))
                <x-admin.button type="link" href="{{ route('admin.attributes.values.index', $attribute) }}" variant="secondary" size="sm">
                    {{ __('admin.manage_values') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.attributes.update', $attribute) }}" class="max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.attributes.partials.form', ['attribute' => $attribute])
        <div class="mt-8 flex flex-wrap gap-2">
            @if ($admin->hasPermission('attributes.update'))
                <x-admin.button type="submit" variant="primary">
                    {{ __('admin.save') }}
                </x-admin.button>
            @endif
            <x-admin.button type="link" href="{{ route('admin.attributes.index') }}" variant="secondary">
                {{ __('admin.cancel') }}
            </x-admin.button>
        </div>
    </form>
@endsection
