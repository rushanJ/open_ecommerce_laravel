@extends('customer.account.layouts.app')

@section('title', __('customer.account_dashboard'))

@section('account_content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.account_dashboard') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ __('customer.hello_name', ['name' => $customer?->first_name ?? __('customer.account')]) }}</p>
                </div>
                <a href="{{ route('customer.order.lookup') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                    {{ __('customer.order_lookup') }}
                </a>
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.orders') }}</dt>
                    <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $orderCount }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.total_spent') }}</dt>
                    <dd class="mt-1 text-2xl font-bold text-slate-900">{{ number_format((float) $totalSpent, 2) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.latest_orders') }}</dt>
                    <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $latestOrders->count() }}</dd>
                </div>
            </dl>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">{{ __('customer.latest_orders') }}</h2>

                @if($latestOrders->isEmpty())
                    <p class="mt-3 text-sm text-slate-600">{{ __('customer.no_orders_yet') }}</p>
                @else
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('customer.order_number') }}</th>
                                        <th class="px-4 py-3">{{ __('customer.date') }}</th>
                                        <th class="px-4 py-3">{{ __('customer.total') }}</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($latestOrders as $order)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $order->order_number }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ optional($order->created_at)->format('Y-m-d') }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ number_format((float) $order->grand_total, 2) }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('customer.account.orders.show', $order) }}" class="font-semibold text-emerald-700 hover:text-emerald-800">
                                                    {{ __('customer.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">{{ __('customer.addresses') }}</h2>
                <div class="mt-4 space-y-4 text-sm text-slate-700">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.shipping_address') }}</p>
                        @if($defaultShipping)
                            <p class="mt-1 font-semibold text-slate-900">{{ $defaultShipping->first_name }} {{ $defaultShipping->last_name }}</p>
                            <p class="text-slate-600">{{ $defaultShipping->address_line_1 }}@if($defaultShipping->city), {{ $defaultShipping->city }}@endif</p>
                        @else
                            <p class="mt-1 text-slate-600">{{ __('customer.no_default_shipping') }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.billing_address') }}</p>
                        @if($defaultBilling)
                            <p class="mt-1 font-semibold text-slate-900">{{ $defaultBilling->first_name }} {{ $defaultBilling->last_name }}</p>
                            <p class="text-slate-600">{{ $defaultBilling->address_line_1 }}@if($defaultBilling->city), {{ $defaultBilling->city }}@endif</p>
                        @else
                            <p class="mt-1 text-slate-600">{{ __('customer.no_default_billing') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('customer.account.addresses.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        {{ __('customer.manage_addresses') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

