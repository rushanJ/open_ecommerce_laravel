@extends('admin.layouts.app')

@section('title', __('admin.categories').' — '.config('app.name'))

@section('breadcrumb', __('admin.categories'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
        $canCreate = $admin->hasPermission('categories.create');
        $canEdit = $admin->hasPermission('categories.update');
        $canDelete = $admin->hasPermission('categories.delete');
        $statuses = [
            '' => __('admin.filter_all'),
            'active' => __('admin.active'),
            'inactive' => __('admin.inactive'),
        ];
    @endphp

    <x-admin.page-header :title="__('admin.categories')">
        <x-slot:actions>
            @if ($canCreate)
                <x-admin.button type="link" href="{{ route('admin.categories.create') }}" variant="primary" size="sm">
                    {{ __('admin.create_category') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-end lg:justify-between">
        @include('admin.shared.search-filter', ['class' => 'w-full lg:flex-1'])
        @include('admin.shared.status-filter', ['statuses' => $statuses, 'name' => 'status'])
    </div>

    @if ($categories->isEmpty())
        <x-admin.empty-state
            :title="__('admin.no_records_found')"
            :message="__('admin.no_records_found')"
            :action-label="$canCreate ? __('admin.create_category') : null"
            :action-url="$canCreate ? route('admin.categories.create') : null"
        />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">{{ __('admin.name') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.parent_category') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.slug') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.status') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.sort_order') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.created') }}</th>
                        <th scope="col" class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($categories as $category)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $category->name }}
                            </td>
                            <td class="max-w-[10rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $category->parent?->name ?? __('admin.none') }}
                            </td>
                            <td class="max-w-[12rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $category->slug }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge :variant="$category->status === 'active' ? 'success' : 'neutral'">
                                    {{ $category->status === 'active' ? __('admin.active') : __('admin.inactive') }}
                                </x-admin.badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $category->sort_order }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $category->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @include('admin.shared.table-actions', [
                                    'canView' => false,
                                    'viewRoute' => null,
                                    'editRoute' => $canEdit ? route('admin.categories.edit', $category) : null,
                                    'deleteRoute' => $canDelete ? route('admin.categories.destroy', $category) : null,
                                    'canEdit' => $canEdit,
                                    'canDelete' => $canDelete,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>

        @include('admin.shared.pagination', ['paginator' => $categories])
    @endif
@endsection
