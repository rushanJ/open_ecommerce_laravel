@extends('admin.layouts.app')

@section('title', __('admin.orders').' — '.config('app.name'))

@section('breadcrumb', __('admin.orders'))

@section('content')
    <x-admin.page-header :title="__('admin.orders')">
        <x-slot:actions>
            <x-admin.search-filter />
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="mb-6 space-y-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label for="filter_q" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.search_placeholder') }}</label>
                <input id="filter_q" type="search" name="q" value="{{ request('q', '') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
            </div>
            <div>
                <label for="filter_status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.order_status') }}</label>
                <select id="filter_status" name="status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach (['pending','processing','confirmed','packed','shipped','delivered','cancelled','failed','refunded'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('admin.'.$st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_payment_status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.payment_status') }}</label>
                <select id="filter_payment_status" name="payment_status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach (['unpaid','pending','paid','partially_paid','failed','refunded','partially_refunded'] as $st)
                        <option value="{{ $st }}" @selected(request('payment_status') === $st)>{{ __('admin.'.$st) ?? $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_fulfillment_status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('admin.fulfillment_status') }}</label>
                <select id="filter_fulfillment_status" name="fulfillment_status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                    <option value="">{{ __('admin.filter_all') }}</option>
                    @foreach (['unfulfilled','partial','fulfilled'] as $st)
                        <option value="{{ $st }}" @selected(request('fulfillment_status') === $st)>{{ __('admin.'.$st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_date_from" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">From</label>
                <input id="filter_date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
            </div>
            <div>
                <label for="filter_date_to" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">To</label>
                <input id="filter_date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900" />
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.filter') }}</x-admin.button>
            <x-admin.button type="link" href="{{ route('admin.orders.index') }}" variant="secondary" size="sm">{{ __('admin.reset') }}</x-admin.button>
        </div>
    </form>

    <x-admin.table>
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">{{ __('admin.order_number') }}</th>
                    <th class="px-4 py-3">{{ __('admin.customer_details') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.amount') }}</th>
                    <th class="px-4 py-3">{{ __('admin.payment_status') }}</th>
                    <th class="px-4 py-3">{{ __('admin.order_status') }}</th>
                    <th class="px-4 py-3">{{ __('admin.fulfillment_status') }}</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-900">
                @forelse ($orders as $order)
                    <tr class="text-gray-700 dark:text-gray-200">
                        <td class="px-4 py-3 font-mono text-xs">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $order->customer?->first_name }} {{ $order->customer?->last_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->customer_email ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format((float) $order->grand_total, 2) }} {{ $order->currency_code }}</td>
                        <td class="px-4 py-3">
                            <x-admin.badge :variant="in_array($order->payment_status, ['paid'], true) ? 'success' : (in_array($order->payment_status, ['failed'], true) ? 'danger' : 'neutral')">
                                {{ __('admin.'.$order->payment_status) ?? $order->payment_status }}
                            </x-admin.badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-admin.badge :variant="in_array($order->status, ['delivered','confirmed','processing'], true) ? 'info' : (in_array($order->status, ['cancelled','failed'], true) ? 'danger' : 'neutral')">
                                {{ __('admin.'.$order->status) ?? $order->status }}
                            </x-admin.badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-admin.badge :variant="$order->fulfillment_status === 'fulfilled' ? 'success' : ($order->fulfillment_status === 'partial' ? 'warning' : 'neutral')">
                                {{ __('admin.'.$order->fulfillment_status) ?? $order->fulfillment_status }}
                            </x-admin.badge>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">{{ __('admin.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">{{ __('admin.no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@endsection

