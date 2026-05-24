@props([
    'order',
])

@php
    /** @var \App\Models\Order $order */
@endphp

<x-admin.card :title="__('admin.order_items')">
    <x-admin.table>
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">{{ __('admin.products') }}</th>
                    <th class="px-4 py-3">{{ __('admin.sku') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.amount') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.quantity') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.subtotal') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.tax') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-900">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->product_name }}</div>
                            @if ($item->variant)
                                <div class="text-xs text-gray-500">{{ $item->variant->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-200">{{ $item->sku ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $item->unit_price, 2) }} {{ $order->currency_code }}</td>
                        <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $item->subtotal, 2) }} {{ $order->currency_code }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $item->tax_total, 2) }} {{ $order->currency_code }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format((float) $item->total, 2) }} {{ $order->currency_code }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.table>
</x-admin.card>

