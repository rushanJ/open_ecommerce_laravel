@extends('admin.layouts.app')

@section('title', __('admin.order_details').' — '.$order->order_number)

@section('breadcrumb', __('admin.orders'))

@section('content')
    <x-admin.page-header :title="__('admin.order').' '.$order->order_number" :subtitle="__('admin.order_details')">
        <x-slot:actions>
            <x-admin.badge :variant="in_array($order->payment_status, ['paid'], true) ? 'success' : (in_array($order->payment_status, ['failed'], true) ? 'danger' : 'neutral')">
                {{ __('admin.'.$order->payment_status) ?? $order->payment_status }}
            </x-admin.badge>
            <x-admin.badge :variant="in_array($order->status, ['delivered','processing','confirmed'], true) ? 'info' : (in_array($order->status, ['cancelled','failed'], true) ? 'danger' : 'neutral')">
                {{ __('admin.'.$order->status) ?? $order->status }}
            </x-admin.badge>
            <x-admin.badge :variant="$order->fulfillment_status === 'fulfilled' ? 'success' : ($order->fulfillment_status === 'partial' ? 'warning' : 'neutral')">
                {{ __('admin.'.$order->fulfillment_status) ?? $order->fulfillment_status }}
            </x-admin.badge>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card :title="__('admin.order_details')">
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500">{{ __('admin.order_number') }}</dt>
                        <dd class="mt-1 font-mono text-xs text-gray-900 dark:text-gray-100">{{ $order->order_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.amount') }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float) $order->grand_total, 2) }} {{ $order->currency_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.payment_status') }}</dt>
                        <dd class="mt-1">{{ __('admin.'.$order->payment_status) ?? $order->payment_status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.fulfillment_status') }}</dt>
                        <dd class="mt-1">{{ __('admin.'.$order->fulfillment_status) ?? $order->fulfillment_status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.customer_details') }}</dt>
                        <dd class="mt-1">{{ $order->customer_email ?? '—' }} @if($order->customer_phone) · {{ $order->customer_phone }} @endif</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ optional($order->created_at)->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            <x-admin.card :title="__('admin.summary')">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('admin.subtotal') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $order->subtotal, 2) }} {{ $order->currency_code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('admin.discount') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">-{{ number_format((float) $order->discount_total, 2) }} {{ $order->currency_code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('admin.tax') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $order->tax_total, 2) }} {{ $order->currency_code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('admin.shipping') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $order->shipping_total, 2) }} {{ $order->currency_code }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-gray-200 pt-3 text-base font-semibold dark:border-gray-800">
                        <dt class="text-gray-900 dark:text-gray-100">{{ __('admin.grand_total') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ number_format((float) $order->grand_total, 2) }} {{ $order->currency_code }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            @include('admin.orders.partials.items-table', ['order' => $order])

            @include('admin.orders.partials.payment-card', ['order' => $order])

            <x-admin.card :title="__('admin.refunds')">
                @if ($order->refunds->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">—</p>
                @else
                    <ul class="space-y-3 text-sm">
                        @foreach ($order->refunds as $r)
                            <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-mono text-xs">{{ $r->refund_number }}</div>
                                    <x-admin.badge :variant="$r->status === 'approved' ? 'info' : ($r->status === 'rejected' ? 'danger' : 'neutral')">{{ $r->status }}</x-admin.badge>
                                </div>
                                <div class="mt-2 text-sm font-medium">{{ number_format((float) $r->amount, 2) }} {{ $order->currency_code }}</div>
                                @if ($r->reason)
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $r->reason }}</div>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($r->status === 'requested')
                                        <form method="POST" action="{{ route('admin.refunds.approve', $r) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.approve') }}</x-admin.button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.refunds.reject', $r) }}" class="flex items-end gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <x-admin.input name="reason" :label="__('admin.reason')" size="sm" />
                                            <x-admin.button type="submit" variant="danger" size="sm">{{ __('admin.reject') }}</x-admin.button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            @include('admin.orders.partials.history', ['order' => $order])

            <x-admin.card :title="__('admin.add_note')">
                @if ($order->notes->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">—</p>
                @else
                    <div class="space-y-3">
                        @foreach ($order->notes as $n)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center justify-between gap-2 text-xs text-gray-500">
                                    <div>
                                        {{ $n->adminUser?->name ?? '—' }}
                                        @if ($n->is_customer_visible)
                                            · <span class="font-medium">{{ __('admin.customer_visible') }}</span>
                                        @endif
                                    </div>
                                    <div>{{ optional($n->created_at)->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="mt-2 text-gray-800 dark:text-gray-100">{{ $n->note }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        </div>

        <aside class="space-y-6">
            @include('admin.orders.partials.status-form', ['order' => $order])
            @include('admin.orders.partials.note-form', ['order' => $order])
            @include('admin.orders.partials.refund-form', ['order' => $order])

            <x-admin.card :title="__('admin.shipments')">
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.shipment_placeholder') }}</p>
            </x-admin.card>

            @include('admin.orders.partials.address-card', ['title' => __('admin.billing_address'), 'address' => $order->billingAddress])
            @include('admin.orders.partials.address-card', ['title' => __('admin.shipping_address'), 'address' => $order->shippingAddress])
        </aside>
    </div>
@endsection

