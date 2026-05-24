@extends('admin.layouts.app')

@section('title', __('admin.create_product').' — '.config('app.name'))

@section('breadcrumb', __('admin.create_product'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
    @endphp

    <x-admin.page-header :title="__('admin.create_product')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.products.index') }}" variant="ghost" size="sm">
                {{ __('admin.back') }}
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.products.store') }}" class="max-w-4xl" enctype="multipart/form-data">
        @csrf
        @include('admin.products.partials.form', ['product' => $product])
        <div class="mt-8 flex flex-wrap gap-2">
            @if ($admin->hasPermission('products.create'))
                <x-admin.button type="submit" variant="primary">
                    {{ __('admin.save') }}
                </x-admin.button>
            @endif
            <x-admin.button type="link" href="{{ route('admin.products.index') }}" variant="secondary">
                {{ __('admin.cancel') }}
            </x-admin.button>
        </div>
    </form>
@endsection
