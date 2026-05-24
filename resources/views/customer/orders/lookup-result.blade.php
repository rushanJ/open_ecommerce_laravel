@extends('customer.layouts.app')

@section('title', __('customer.order_lookup'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.order_lookup') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ __('customer.guest_order_lookup_help') }}</p>
            </div>
            <a href="{{ route('customer.order.lookup') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">{{ __('customer.back') }}</a>
        </div>

        @if($notFound ?? false)
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-800">
                {{ __('customer.order_not_found') }}
            </div>
        @elseif($order)
            <div class="mt-6 space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('customer.order') }} {{ $order->order_number }}</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ __('customer.placed_on') }} {{ optional($order->created_at)->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="text-sm text-slate-700">
                            <p><span class="font-semibold">{{ __('customer.status') }}:</span> {{ ucfirst($order->status) }}</p>
                            <p class="mt-1"><span class="font-semibold">{{ __('customer.payment_status') }}:</span> {{ $order->isPaid() ? __('customer.paid') : __('customer.unpaid') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('customer.items') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="py-2 pr-4">{{ __('customer.product') }}</th>
                                    <th class="py-2 pr-4">{{ __('customer.qty') }}</th>
                                    <th class="py-2 text-right">{{ __('customer.subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="py-3 pr-4">
                                            <p class="font-semibold text-slate-900">{{ $item->product?->name ?? __('customer.product') }}</p>
                                            @if($item->variant)
                                                <p class="text-xs text-slate-500">{{ $item->variant->name }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4 text-slate-600">{{ (float) $item->quantity }}</td>
                                        <td class="py-3 text-right text-slate-600">{{ number_format((float) $item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

