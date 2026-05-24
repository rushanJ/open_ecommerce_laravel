@extends('admin.layouts.app')

@php
    $title = $attribute->name.' — '.__('admin.attribute_values');
@endphp

@section('title', $title.' — '.config('app.name'))

@section('breadcrumb', __('admin.attribute_values'))

@section('content')
    @php
        /** @var \App\Models\AdminUser $admin */
        $admin = auth('admin')->user();
        $canCreate = $admin->hasPermission('attributes.create');
        $canEdit = $admin->hasPermission('attributes.update');
        $canDelete = $admin->hasPermission('attributes.delete');
    @endphp

    <x-admin.page-header :title="$attribute->name.': '.__('admin.attribute_values')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.attributes.index') }}" variant="ghost" size="sm">
                {{ __('admin.attributes') }}
            </x-admin.button>
            @if ($canCreate)
                <x-admin.button type="link" href="{{ route('admin.attributes.values.create', $attribute) }}" variant="primary" size="sm">
                    {{ __('admin.create_attribute_value') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-6">
        @include('admin.shared.search-filter', ['class' => 'w-full max-w-md'])
    </div>

    @if ($values->isEmpty())
        <x-admin.empty-state
            :title="__('admin.no_records_found')"
            :message="__('admin.no_records_found')"
            :action-label="$canCreate ? __('admin.create_attribute_value') : null"
            :action-url="$canCreate ? route('admin.attributes.values.create', $attribute) : null"
        />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">{{ __('admin.value') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.slug') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.color_code') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.image_path') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.sort_order') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('admin.created') }}</th>
                        <th scope="col" class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($values as $val)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $val->value }}</td>
                            <td class="max-w-[10rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $val->slug }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $val->color_code ?? '—' }}</td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $val->image_path ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $val->sort_order }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $val->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @include('admin.shared.table-actions', [
                                    'canView' => false,
                                    'viewRoute' => null,
                                    'editRoute' => $canEdit ? route('admin.attributes.values.edit', [$attribute, $val]) : null,
                                    'deleteRoute' => $canDelete ? route('admin.attributes.values.destroy', [$attribute, $val]) : null,
                                    'canEdit' => $canEdit,
                                    'canDelete' => $canDelete,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>

        @include('admin.shared.pagination', ['paginator' => $values])
    @endif
@endsection
