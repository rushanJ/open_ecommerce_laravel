@extends('admin.layouts.app')

@section('title', __('admin.inventory').' — '.config('app.name'))

@section('breadcrumb', __('admin.inventory'))

@section('content')
    @php
        $admin = auth('admin')->user();
        $canAdjust = $admin->hasPermission('inventory.update');
        $stockStatuses = [
            '' => __('admin.filter_all'),
            'in_stock' => __('admin.in_stock'),
            'out_of_stock' => __('admin.out_of_stock'),
            'on_backorder' => __('admin.on_backorder'),
        ];
    @endphp

    <x-admin.page-header :title="__('admin.inventory')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.inventory.movements') }}" variant="secondary" size="sm">
                {{ __('admin.inventory_movements') }}
            </x-admin.button>
            @if ($canAdjust)
                <x-admin.button type="link" href="{{ route('admin.inventory.adjust.form') }}" variant="primary" size="sm">
                    {{ __('admin.adjust_inventory') }}
                </x-admin.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="mb-6 space-y-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="inv_q" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.search_placeholder') }}</label>
                <input id="inv_q" type="search" name="q" value="{{ request('q', '') }}" placeholder="{{ __('admin.search_placeholder') }}"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
            </div>
            <div>
                <label for="inv_wh" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.warehouse') }}</label>
                <select id="inv_wh" name="warehouse_id" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string) request('warehouse_id') === (string) $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="inv_p" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.product') }}</label>
                <select id="inv_p" name="product_id" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected((string) request('product_id') === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="inv_ss" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.stock_status') }}</label>
                <select id="inv_ss" name="stock_status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    @foreach ($stockStatuses as $val => $label)
                        <option value="{{ $val }}" @selected(request('stock_status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end pb-1">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="low_stock" value="1" class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900" @checked(request()->boolean('low_stock'))>
                    {{ __('admin.low_stock') }}
                </label>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.filter') }}</x-admin.button>
            <x-admin.button type="link" href="{{ route('admin.inventory.index') }}" variant="secondary" size="sm">{{ __('admin.reset') }}</x-admin.button>
        </div>
    </form>

    @if ($stocks->isEmpty())
        <x-admin.empty-state :title="__('admin.no_records_found')" :message="__('admin.no_records_found')" />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('admin.warehouse') }}</th>
                        <th class="px-4 py-3">{{ __('admin.product') }}</th>
                        <th class="px-4 py-3">{{ __('admin.variant') }}</th>
                        <th class="px-4 py-3">{{ __('admin.sku') }}</th>
                        <th class="px-4 py-3">{{ __('admin.quantity') }}</th>
                        <th class="px-4 py-3">{{ __('admin.reserved_quantity') }}</th>
                        <th class="px-4 py-3">{{ __('admin.available_quantity') }}</th>
                        <th class="px-4 py-3">{{ __('admin.stock_status') }}</th>
                        <th class="px-4 py-3">{{ __('admin.updated') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($stocks as $row)
                        @php
                            $sku = $row->variant?->sku ?? $row->product?->sku ?? '—';
                            $status = $row->variant?->stock_status ?? $row->product?->stock_status ?? '—';
                            $statusLabel = match ($status) {
                                'in_stock' => __('admin.in_stock'),
                                'out_of_stock' => __('admin.out_of_stock'),
                                'on_backorder' => __('admin.on_backorder'),
                                default => $status,
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $row->warehouse?->name }}</td>
                            <td class="max-w-[10rem] truncate px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $row->product?->name }}</td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $row->variant?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">{{ $sku }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ number_format((float) $row->quantity, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ number_format((float) $row->reserved_quantity, 2) }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ number_format((float) $row->available_quantity, 2) }}</td>
                            <td class="px-4 py-3">
                                <x-admin.badge variant="neutral">{{ $statusLabel }}</x-admin.badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $row->updated_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>
        @include('admin.shared.pagination', ['paginator' => $stocks])
    @endif
@endsection
