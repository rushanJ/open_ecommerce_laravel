@props([
    'cart',
])

@php
    /** @var \App\Models\Cart $cart */
    $code = $cart->currency_code ?? 'LKR';
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold text-slate-900">{{ __('customer.cart_totals') }}</h2>
    <dl class="mt-4 space-y-3 text-sm">
        <div class="flex justify-between gap-4">
            <dt class="text-slate-600">{{ __('customer.subtotal') }}</dt>
            <dd class="font-medium text-slate-900">{{ number_format((float) $cart->subtotal, 2) }} {{ $code }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-slate-600">{{ __('customer.discount') }}</dt>
            <dd class="font-medium text-slate-900">-{{ number_format((float) $cart->discount_total, 2) }} {{ $code }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-slate-600">{{ __('customer.tax') }}</dt>
            <dd class="font-medium text-slate-900">{{ number_format((float) $cart->tax_total, 2) }} {{ $code }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-slate-600">{{ __('customer.shipping') }}</dt>
            <dd class="text-slate-500">{{ __('customer.calculated_at_checkout') }} — {{ number_format((float) $cart->shipping_total, 2) }} {{ $code }}</dd>
        </div>
        <div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-base font-semibold">
            <dt class="text-slate-900">{{ __('customer.grand_total') }}</dt>
            <dd class="text-emerald-800">{{ number_format((float) $cart->grand_total, 2) }} {{ $code }}</dd>
        </div>
    </dl>
    @if($cart->coupon_code)
        <p class="mt-4 text-xs text-slate-500">{{ __('customer.coupon_code') }}: <span class="font-semibold text-slate-700">{{ $cart->coupon_code }}</span></p>
    @endif
    @if ($cart->relationLoaded('items') && $cart->items->isNotEmpty())
        <a href="{{ route('customer.checkout.index') }}" class="mt-4 block w-full rounded-xl bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            {{ __('customer.checkout') }}
        </a>
    @else
        <button type="button" disabled class="mt-4 w-full cursor-not-allowed rounded-xl bg-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-500">
            {{ __('customer.checkout') }}
        </button>
    @endif
</div>
