@extends('customer.layouts.app')

@section('title', __('customer.order_created'))

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6">
        @if (session('success'))
            <p class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</p>
        @endif

        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.order_created') }}</h1>
        <p class="mt-4 text-lg text-slate-600">{{ __('customer.payment_coming_next') }}</p>

        <dl class="mx-auto mt-10 max-w-md space-y-3 rounded-2xl border border-slate-200 bg-white p-6 text-left text-sm shadow-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('customer.order_number') }}</dt>
                <dd class="font-mono font-semibold text-slate-900">{{ $order->order_number }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('customer.status') }}</dt>
                <dd class="font-medium capitalize text-slate-900">{{ $order->status }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('customer.grand_total') }}</dt>
                <dd class="font-semibold text-emerald-800">{{ number_format((float) $order->grand_total, 2) }} {{ $order->currency_code }}</dd>
            </div>
        </dl>

        @if ($order->items->isNotEmpty())
            <div class="mx-auto mt-10 max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
                <h2 class="text-sm font-semibold text-slate-800">{{ __('customer.order_summary') }}</h2>
                <ul class="mt-3 divide-y divide-slate-100 text-sm">
                    @foreach ($order->items as $oi)
                        <li class="flex justify-between gap-4 py-2">
                            <span class="text-slate-700">{{ $oi->product_name }}</span>
                            <span class="shrink-0 text-slate-600">× {{ $oi->quantity }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('customer.payments.payhere.start', $order) }}" class="inline-flex w-full justify-center rounded-xl bg-slate-900 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 sm:w-auto">
                {{ __('customer.pay_with_payhere') }}
            </a>
            <a href="{{ route('customer.products.index') }}" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:w-auto">
                {{ __('customer.continue_shopping') }}
            </a>
        </div>

    </div>
@endsection
