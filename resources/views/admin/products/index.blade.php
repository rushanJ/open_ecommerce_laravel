@extends('admin.layouts.app')

@section('title', __('admin.products').' — '.config('app.name'))

@section('breadcrumb', __('admin.products'))

@section('content')
    @php
        $admin = auth('admin')->user();
        $canCreate = $admin->hasPermission('products.create');
        $canEdit = $admin->hasPermission('products.update');
        $canDelete = $admin->hasPermission('products.delete');
        $canExport = $admin->hasPermission('products.export');
        $canImport = $canCreate && $canEdit;
    @endphp

    <x-admin.page-header :title="__('admin.products')">
        <x-slot:actions>
            @if ($canExport)
                <x-admin.button type="link" href="{{ route('admin.products.export', request()->query()) }}" variant="secondary" size="sm">
                    {{ __('admin.export_products') }}
                </x-admin.button>
            @endif
            @if ($canImport)
                <x-admin.button type="link" href="{{ route('admin.products.import') }}" variant="secondary" size="sm">
                    {{ __('admin.import_products') }}
                </x-admin.button>
            @endif
            @if ($canCreate)
                <x-admin.button type="link" href="{{ route('admin.products.create') }}" variant="primary" size="sm">
                    {{ __('admin.create_product') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="mb-6 space-y-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="filter_q" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.search_placeholder') }}</label>
                <input
                    id="filter_q"
                    type="search"
                    name="q"
                    value="{{ request('q', '') }}"
                    placeholder="{{ __('admin.search_placeholder') }}"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                />
            </div>
            <div>
                <label for="filter_status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.status') }}</label>
                <select id="filter_status" name="status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach (['draft', 'active', 'inactive', 'archived'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('admin.'.$st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_type" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.product_type') }}</label>
                <select id="filter_type" name="product_type" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach (['simple', 'variable', 'digital', 'bundle'] as $pt)
                        <option value="{{ $pt }}" @selected(request('product_type') === $pt)>{{ __('admin.'.$pt) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_brand" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.brand') }}</label>
                <select id="filter_brand" name="brand_id" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b->id }}" @selected((string) request('brand_id') === (string) $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_category" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.categories') }}</label>
                <select id="filter_category" name="category_id" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected((string) request('category_id') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.filter') }}</x-admin.button>
            <x-admin.button type="link" href="{{ route('admin.products.index') }}" variant="secondary" size="sm">{{ __('admin.reset') }}</x-admin.button>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-admin.empty-state
            :title="__('admin.no_records_found')"
            :message="__('admin.no_records_found')"
            :action-label="$canCreate ? __('admin.create_product') : null"
            :action-url="$canCreate ? route('admin.products.create') : null"
        />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('admin.primary_image') }}</th>
                        <th class="px-4 py-3">{{ __('admin.name') }}</th>
                        <th class="px-4 py-3">{{ __('admin.sku') }}</th>
                        <th class="px-4 py-3">{{ __('admin.product_type') }}</th>
                        <th class="px-4 py-3">{{ __('admin.brand') }}</th>
                        <th class="px-4 py-3">{{ __('admin.categories') }}</th>
                        <th class="px-4 py-3">{{ __('admin.regular_price') }}</th>
                        <th class="px-4 py-3">{{ __('admin.stock_quantity') }}</th>
                        <th class="px-4 py-3">{{ __('admin.status') }}</th>
                        <th class="px-4 py-3">{{ __('admin.created') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($products as $p)
                        @php
                            $thumbnail = $p->primaryImage;
                            $uploadedThumbnail = $p->images->first(fn ($image) => $image->path
                                && media_exists($image->path)
                                && ! \Illuminate\Support\Str::startsWith((string) media_public_path($image->path), 'catalog/'));

                            if ($uploadedThumbnail && \Illuminate\Support\Str::startsWith((string) media_public_path($thumbnail?->path), 'catalog/')) {
                                $thumbnail = $uploadedThumbnail;
                            }

                            if (! $thumbnail?->path || ! media_exists($thumbnail->path)) {
                                $thumbnail = $uploadedThumbnail ?? $p->images->first(fn ($image) => $image->path && media_exists($image->path)) ?? $thumbnail;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="px-4 py-3">
                                @if ($thumbnail?->path)
                                    <img src="{{ media_url($thumbnail->path) }}" alt="" class="h-12 w-12 rounded-lg object-cover bg-gray-100 dark:bg-gray-800">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-800">—</div>
                                @endif
                            </td>
                            <td class="max-w-[10rem] truncate px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $p->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">{{ $p->sku ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge variant="neutral">{{ $p->product_type }}</x-admin.badge>
                            </td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $p->brand?->name ?? '—' }}</td>
                            <td class="max-w-[10rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $p->categories->pluck('name')->take(2)->join(', ') }}{{ $p->categories->count() > 2 ? '…' : '' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">{{ number_format((float) $p->regular_price, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                @if ($p->manage_stock)
                                    {{ $p->stock_quantity !== null ? number_format((float) $p->stock_quantity, 2) : '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.badge :variant="$p->status === 'active' ? 'success' : 'neutral'">{{ __('admin.'.$p->status) }}</x-admin.badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $p->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @include('admin.shared.table-actions', [
                                    'canView' => false,
                                    'viewRoute' => null,
                                    'editRoute' => $canEdit ? route('admin.products.edit', $p) : null,
                                    'deleteRoute' => $canDelete ? route('admin.products.destroy', $p) : null,
                                    'canEdit' => $canEdit,
                                    'canDelete' => $canDelete,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>

        @include('admin.shared.pagination', ['paginator' => $products])
    @endif
@endsection
