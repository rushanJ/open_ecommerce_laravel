@extends('customer.layouts.app')

@section('title', __('customer.cart'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.your_cart') }}</h1>

        @if ($cart->items->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center">
                <p class="text-slate-600">{{ __('customer.cart_empty') }}</p>
                <a href="{{ route('customer.products.index') }}" class="mt-6 inline-flex rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    {{ __('customer.continue_shopping') }}
                </a>
            </div>
        @else
            <div class="mt-10 flex flex-col gap-10 lg:flex-row">
                <div class="min-w-0 flex-1 space-y-4">
                    <div class="hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 w-24"></th>
                                    <th class="px-4 py-3">{{ __('customer.product_details') }}</th>
                                    <th class="px-4 py-3">{{ __('customer.unit_price') }}</th>
                                    <th class="px-4 py-3">{{ __('customer.quantity') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('customer.subtotal') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($cart->items as $item)
                                    <tr>
                                        <td class="px-4 py-4 w-20">
                                            @if ($item->product?->primaryImage?->path)
                                                <img src="{{ $item->product->primaryImage->path }}" alt="" class="h-16 w-16 rounded-lg object-cover" />
                                            @else
                                                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">{{ __('customer.shop') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="font-medium text-slate-900">{{ $item->product?->name }}</p>
                                            @if ($item->variant)
                                                <p class="mt-1 text-xs text-slate-500">{{ $item->variant->name }} @if ($item->variant->sku) · {{ $item->variant->sku }} @endif</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">{{ number_format((float) $item->unit_price, 2) }} {{ $cart->currency_code }}</td>
                                        <td class="px-4 py-4">
                                            <form action="{{ route('customer.cart.items.update', $item) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" step="1" class="w-20 rounded-lg border border-slate-200 px-2 py-1.5 text-sm" required />
                                                <button type="submit" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ __('customer.update_cart') }}</button>
                                            </form>
                                        </td>
                                        <td class="px-4 py-4 text-right font-medium text-slate-900">{{ number_format((float) $item->subtotal, 2) }} {{ $cart->currency_code }}</td>
                                        <td class="px-4 py-4 text-right">
                                            <form action="{{ route('customer.cart.items.destroy', $item) }}" method="POST" onsubmit="return confirm('{{ __('customer.remove') }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">{{ __('customer.remove') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="space-y-4 md:hidden">
                        @foreach ($cart->items as $item)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex gap-4">
                                    @if ($item->product?->primaryImage?->path)
                                        <img src="{{ $item->product->primaryImage->path }}" alt="" class="h-20 w-20 shrink-0 rounded-lg object-cover" />
                                    @else
                                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">{{ __('customer.shop') }}</div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-slate-900">{{ $item->product?->name }}</p>
                                        @if ($item->variant)
                                            <p class="mt-1 text-xs text-slate-500">{{ $item->variant->name }}</p>
                                        @endif
                                        <p class="mt-2 text-sm text-slate-600">{{ __('customer.unit_price') }}: {{ number_format((float) $item->unit_price, 2) }} {{ $cart->currency_code }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('customer.cart.items.update', $item) }}" method="POST" class="mt-4 flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <label class="text-xs font-medium text-slate-500">{{ __('customer.quantity') }}</label>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" step="1" class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-sm" required />
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">{{ __('customer.update_cart') }}</button>
                                </form>
                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                    <span class="text-sm font-semibold text-slate-900">{{ __('customer.subtotal') }}: {{ number_format((float) $item->subtotal, 2) }} {{ $cart->currency_code }}</span>
                                    <form action="{{ route('customer.cart.items.destroy', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600">{{ __('customer.remove') }}</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <form action="{{ route('customer.cart.clear') }}" method="POST" onsubmit="return confirm('{{ __('customer.clear_cart') }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-800 hover:bg-red-100">{{ __('customer.clear_cart') }}</button>
                        </form>
                        <a href="{{ route('customer.products.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">{{ __('customer.continue_shopping') }}</a>
                    </div>
                </div>

                <aside class="w-full shrink-0 lg:w-96">
                    <div class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-900">{{ __('customer.coupon_code') }}</h2>

                        @if($cart->coupon_code)
                            <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm">
                                <span class="font-semibold text-emerald-800">{{ $cart->coupon_code }}</span>
                                <form method="POST" action="{{ route('customer.cart.coupon.remove') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-emerald-800 hover:text-emerald-900">
                                        {{ __('customer.remove_coupon') }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('customer.cart.coupon.apply') }}" class="mt-3 flex gap-2">
                                @csrf
                                <input name="code" type="text" value="{{ old('code') }}" placeholder="WELCOME10"
                                       class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                                <button type="submit" class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                    {{ __('customer.apply_coupon') }}
                                </button>
                            </form>
                            @error('code') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        @endif
                    </div>
                    @include('customer.components.cart-summary', ['cart' => $cart])
                </aside>
            </div>
        @endif
    </div>
@endsection
