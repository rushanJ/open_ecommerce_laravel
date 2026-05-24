@extends('admin.layouts.app')

@section('title', __('admin.create_brand').' — '.config('app.name'))

@section('breadcrumb', __('admin.create_brand'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
    @endphp

    <x-admin.page-header :title="__('admin.create_brand')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.brands.index') }}" variant="ghost" size="sm">
                {{ __('admin.back') }}
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.brands.store') }}" class="max-w-3xl" enctype="multipart/form-data">
        @csrf
        @include('admin.brands.partials.form', ['brand' => $brand])
        <div class="mt-8 flex flex-wrap gap-2">
            @if ($admin->hasPermission('brands.create'))
                <x-admin.button type="submit" variant="primary">
                    {{ __('admin.save') }}
                </x-admin.button>
            @endif
            <x-admin.button type="link" href="{{ route('admin.brands.index') }}" variant="secondary">
                {{ __('admin.cancel') }}
            </x-admin.button>
        </div>
    </form>
@endsection
