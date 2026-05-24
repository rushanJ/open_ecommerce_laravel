@props([
    'order',
])

@php
    /** @var \App\Models\Order $order */
    $refundable = max(0, (float) $order->paid_total - (float) $order->refunded_total);
@endphp

<x-admin.card :title="__('admin.request_refund')" :subtitle="__('admin.refundable_amount').': '.number_format($refundable, 2).' '.$order->currency_code">
    @if (! $order->isRefundable())
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.refund_not_allowed') }}</p>
    @else
        <form method="POST" action="{{ route('admin.orders.refunds.store', $order) }}" class="space-y-4">
            @csrf

            <x-admin.input name="amount" type="number" step="0.01" min="0.01" :label="__('admin.amount')" required value="{{ old('amount') }}" />
            <x-admin.textarea name="reason" :label="__('admin.reason')" rows="3">{{ old('reason') }}</x-admin.textarea>

            <x-admin.button type="submit" variant="danger" size="sm">{{ __('admin.request_refund') }}</x-admin.button>
        </form>
    @endif
</x-admin.card>

