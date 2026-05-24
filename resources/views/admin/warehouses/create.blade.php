@extends('admin.layouts.app')

@section('title', __('admin.create_warehouse').' — '.config('app.name'))

@section('breadcrumb', __('admin.create_warehouse'))

@section('content')
    @php $admin = auth('admin')->user(); @endphp
    <x-admin.page-header :title="__('admin.create_warehouse')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.warehouses.index') }}" variant="ghost" size="sm">{{ __('admin.back') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.warehouses.store') }}" class="max-w-2xl">
        @csrf
        @include('admin.warehouses.partials.form', ['warehouse' => $warehouse])
        <div class="mt-8 flex flex-wrap gap-2">
            @if ($admin->hasPermission('inventory.create'))
                <x-admin.button type="submit" variant="primary">{{ __('admin.save') }}</x-admin.button>
            @endif
            <x-admin.button type="link" href="{{ route('admin.warehouses.index') }}" variant="secondary">{{ __('admin.cancel') }}</x-admin.button>
        </div>
    </form>
@endsection
