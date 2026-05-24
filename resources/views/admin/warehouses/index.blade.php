@extends('admin.layouts.app')

@section('title', __('admin.warehouses').' — '.config('app.name'))

@section('breadcrumb', __('admin.warehouses'))

@section('content')
    @php
        $admin = auth('admin')->user();
        $canCreate = $admin->hasPermission('inventory.create');
        $canEdit = $admin->hasPermission('inventory.update');
        $canDelete = $admin->hasPermission('inventory.delete');
    @endphp

    <x-admin.page-header :title="__('admin.warehouses')">
        <x-slot:actions>
            @if ($canCreate)
                <x-admin.button type="link" href="{{ route('admin.warehouses.create') }}" variant="primary" size="sm">
                    {{ __('admin.create_warehouse') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="mb-6 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end">
        <div class="flex-1 min-w-[12rem]">
            <label for="wh_q" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.search_placeholder') }}</label>
            <input id="wh_q" type="search" name="q" value="{{ request('q', '') }}" placeholder="{{ __('admin.search_placeholder') }}"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
        </div>
        <div class="min-w-[10rem]">
            <label for="wh_status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.status') }}</label>
            <select id="wh_status" name="status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">{{ __('admin.filter_all') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('admin.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('admin.inactive') }}</option>
            </select>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.filter') }}</x-admin.button>
            <x-admin.button type="link" href="{{ route('admin.warehouses.index') }}" variant="secondary" size="sm">{{ __('admin.reset') }}</x-admin.button>
        </div>
    </form>

    @if ($warehouses->isEmpty())
        <x-admin.empty-state :title="__('admin.no_records_found')" :message="__('admin.no_records_found')" />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('admin.name') }}</th>
                        <th class="px-4 py-3">{{ __('admin.code') }}</th>
                        <th class="px-4 py-3">{{ __('admin.status') }}</th>
                        <th class="px-4 py-3">{{ __('admin.created') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($warehouses as $wh)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $wh->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wh->code }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :variant="$wh->status === 'active' ? 'success' : 'neutral'">
                                    {{ $wh->status === 'active' ? __('admin.active') : __('admin.inactive') }}
                                </x-admin.badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $wh->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @include('admin.shared.table-actions', [
                                    'canView' => false,
                                    'viewRoute' => null,
                                    'editRoute' => $canEdit ? route('admin.warehouses.edit', $wh) : null,
                                    'deleteRoute' => $canDelete ? route('admin.warehouses.destroy', $wh) : null,
                                    'canEdit' => $canEdit,
                                    'canDelete' => $canDelete,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>
        @include('admin.shared.pagination', ['paginator' => $warehouses])
    @endif
@endsection
