@extends('admin.layouts.app')

@section('title', __('admin.attributes').' — '.config('app.name'))

@section('breadcrumb', __('admin.attributes'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
        $canCreate = $admin->hasPermission('attributes.create');
        $canEdit = $admin->hasPermission('attributes.update');
        $canDelete = $admin->hasPermission('attributes.delete');
        $canViewValues = $admin->hasPermission('attributes.view');
    @endphp

    <x-admin.page-header :title="__('admin.attributes')">
        <x-slot:actions>
            @if ($canCreate)
                <x-admin.button type="link" href="{{ route('admin.attributes.create') }}" variant="primary" size="sm">
                    {{ __('admin.create_attribute') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-6">
        @include('admin.shared.search-filter', ['class' => 'w-full max-w-md'])
    </div>

    @if ($attributes->isEmpty())
        <x-admin.empty-state
            :title="__('admin.no_records_found')"
            :message="__('admin.no_records_found')"
            :action-label="$canCreate ? __('admin.create_attribute') : null"
            :action-url="$canCreate ? route('admin.attributes.create') : null"
        />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">{{ __('admin.name') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.slug') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.type') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.filterable') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.values_count') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.sort_order') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.created') }}</th>
                        <th scope="col" class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($attributes as $attr)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $attr->name }}</td>
                            <td class="max-w-[10rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $attr->slug }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge variant="info">{{ $attr->type }}</x-admin.badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $attr->is_filterable ? __('admin.yes') : __('admin.no') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $attr->values_count }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $attr->sort_order }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $attr->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    @if ($canViewValues)
                                        <x-admin.button type="link" href="{{ route('admin.attributes.values.index', $attr) }}" variant="secondary" size="sm">
                                            {{ __('admin.manage_values') }}
                                        </x-admin.button>
                                    @endif
                                    @include('admin.shared.table-actions', [
                                        'canView' => false,
                                        'viewRoute' => null,
                                        'editRoute' => $canEdit ? route('admin.attributes.edit', $attr) : null,
                                        'deleteRoute' => $canDelete ? route('admin.attributes.destroy', $attr) : null,
                                        'canEdit' => $canEdit,
                                        'canDelete' => $canDelete,
                                    ])
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>

        @include('admin.shared.pagination', ['paginator' => $attributes])
    @endif
@endsection
