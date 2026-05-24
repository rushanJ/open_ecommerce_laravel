@extends('admin.layouts.app')

@section('title', __('admin.inventory_movements').' — '.config('app.name'))

@section('breadcrumb', __('admin.inventory_movements'))

@section('content')
    @php
        $types = [
            '' => __('admin.filter_all'),
            'purchase' => __('admin.purchase'),
            'sale' => __('admin.sale'),
            'return' => __('admin.return'),
            'adjustment' => __('admin.adjustment'),
            'reservation' => __('admin.reservation'),
            'release' => __('admin.release'),
        ];
        $typeLabel = fn (string $t): string => $types[$t] ?? $t;
    @endphp

    <x-admin.page-header :title="__('admin.inventory_movements')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.inventory.index') }}" variant="secondary" size="sm">{{ __('admin.inventory') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="mb-6 space-y-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="mov_wh" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.warehouse') }}</label>
                <select id="mov_wh" name="warehouse_id" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string) request('warehouse_id') === (string) $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mov_p" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.product') }}</label>
                <select id="mov_p" name="product_id" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected((string) request('product_id') === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mov_type" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.movement_type') }}</label>
                <select id="mov_type" name="type" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    @foreach ($types as $val => $label)
                        <option value="{{ $val }}" @selected(request('type') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mov_df" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.date_from') }}</label>
                <input id="mov_df" type="date" name="date_from" value="{{ request('date_from', '') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
            </div>
            <div>
                <label for="mov_dt" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.date_to') }}</label>
                <input id="mov_dt" type="date" name="date_to" value="{{ request('date_to', '') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.filter') }}</x-admin.button>
            <x-admin.button type="link" href="{{ route('admin.inventory.movements') }}" variant="secondary" size="sm">{{ __('admin.reset') }}</x-admin.button>
        </div>
    </form>

    @if ($movements->isEmpty())
        <x-admin.empty-state :title="__('admin.no_records_found')" :message="__('admin.no_records_found')" />
    @else
        <x-admin.table>
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('admin.date') }}</th>
                        <th class="px-4 py-3">{{ __('admin.warehouse') }}</th>
                        <th class="px-4 py-3">{{ __('admin.product') }}</th>
                        <th class="px-4 py-3">{{ __('admin.variant') }}</th>
                        <th class="px-4 py-3">{{ __('admin.movement_type') }}</th>
                        <th class="px-4 py-3">{{ __('admin.quantity') }}</th>
                        <th class="px-4 py-3">{{ __('admin.reference') }}</th>
                        <th class="px-4 py-3">{{ __('admin.administrator') }}</th>
                        <th class="px-4 py-3">{{ __('admin.note') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach ($movements as $m)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $m->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $m->warehouse?->name }}</td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-900 dark:text-gray-100">{{ $m->product?->name }}</td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $m->variant?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $typeLabel($m->type) }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $m->quantity, 2) }}</td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">
                                @if ($m->reference_type)
                                    {{ $m->reference_type }}@if ($m->reference_id !== null)#{{ $m->reference_id }}@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="max-w-[8rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $m->adminUser?->name ?? '—' }}</td>
                            <td class="max-w-[12rem] truncate px-4 py-3 text-gray-600 dark:text-gray-300">{{ $m->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table>
        @include('admin.shared.pagination', ['paginator' => $movements])
    @endif
@endsection
