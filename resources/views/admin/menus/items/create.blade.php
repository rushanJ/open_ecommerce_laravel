@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.create_menu_item')" :subtitle="$menu->name" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.menus.items.store') }}" class="space-y-6">
            @csrf
            @include('admin.menus.items.partials.form')

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.menus.show', $menu) }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection

