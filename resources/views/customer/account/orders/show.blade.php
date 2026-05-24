@extends('customer.account.layouts.app')

@section('title', __('customer.order_details'))

@section('account_content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        {{ __('customer.order') }} {{ $order->order_number }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ __('customer.placed_on') }} {{ optional($order->created_at)->format('Y-m-d H:i') }}
                    </p>
                </div>
                <div class="text-sm text-slate-700">
                    <p><span class="font-semibold">{{ __('customer.status') }}:</span> {{ ucfirst($order->status) }}</p>
                    <p class="mt-1"><span class="font-semibold">{{ __('customer.payment_status') }}:</span> {{ $order->isPaid() ? __('customer.paid') : __('customer.unpaid') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('customer.items') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="py-2 pr-4">{{ __('customer.product') }}</th>
                            <th class="py-2 pr-4">{{ __('customer.qty') }}</th>
                            <th class="py-2 pr-4">{{ __('customer.unit_price') }}</th>
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
                                <td class="py-3 pr-4 text-slate-600">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="py-3 text-right text-slate-600">{{ number_format((float) $item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">{{ __('customer.addresses') }}</h2>
                <div class="mt-4 space-y-4 text-sm text-slate-700">
                    @foreach($order->addresses as $addr)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ ucfirst($addr->type) }}</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $addr->first_name }} {{ $addr->last_name }}</p>
                            <p class="text-slate-600">{{ $addr->address_line_1 }} @if($addr->address_line_2), {{ $addr->address_line_2 }}@endif</p>
                            <p class="text-slate-600">{{ $addr->city }} @if($addr->postal_code) {{ $addr->postal_code }} @endif</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">{{ __('customer.order_summary') }}</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-600">{{ __('customer.subtotal') }}</dt>
                        <dd class="font-semibold text-slate-900">{{ number_format((float) $order->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-600">{{ __('customer.shipping') }}</dt>
                        <dd class="font-semibold text-slate-900">{{ number_format((float) $order->shipping_total, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-2">
                        <dt class="text-slate-600">{{ __('customer.total') }}</dt>
                        <dd class="text-base font-bold text-slate-900">{{ number_format((float) $order->grand_total, 2) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('customer.status_history') }}</h2>
            <div class="mt-4 space-y-3 text-sm">
                @forelse($order->statusHistories as $h)
                    <div class="flex items-start justify-between gap-4 rounded-xl bg-slate-50 p-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ ucfirst($h->to_status) }}</p>
                            @if($h->note)
                                <p class="mt-1 text-slate-600">{{ $h->note }}</p>
                            @endif
                        </div>
                        <p class="text-slate-500">{{ optional($h->created_at)->format('Y-m-d H:i') }}</p>
                    </div>
                @empty
                    <p class="text-slate-600">{{ __('customer.no_history') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

