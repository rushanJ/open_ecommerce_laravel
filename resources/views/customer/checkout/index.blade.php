@extends('customer.layouts.app')

@section('title', __('customer.checkout'))

@section('content')
    @php
        /** @var \Illuminate\Support\Collection $shippingRates */
        /** @var \App\Models\Cart $cart */
        $code = $cart->currency_code ?? 'LKR';
        $def = $defaults ?? [];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.checkout') }}</h1>

        @if ($shippingRates->isEmpty())
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                {{ __('customer.no_shipping_methods') }}
            </div>
        @endif

        <form action="{{ route('customer.checkout.store') }}" method="POST" class="mt-8 flex flex-col gap-10 lg:flex-row">
            @csrf

            <div class="min-w-0 flex-1 space-y-10">
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('customer.contact_information') }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="cemail" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.email') }}</label>
                            <input id="cemail" type="email" name="customer_email" required value="{{ old('customer_email', $def['customer_email'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div class="sm:col-span-2">
                            <label for="cphone" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.phone') }}</label>
                            <input id="cphone" type="text" name="customer_phone" required value="{{ old('customer_phone', $def['customer_phone'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('customer.billing_address') }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.first_name') }}</label>
                            <input type="text" name="billing_first_name" required value="{{ old('billing_first_name', $def['billing_first_name'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.last_name') }}</label>
                            <input type="text" name="billing_last_name" value="{{ old('billing_last_name', $def['billing_last_name'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.phone') }}</label>
                            <input type="text" name="billing_phone" value="{{ old('billing_phone') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.email') }}</label>
                            <input type="email" name="billing_email" value="{{ old('billing_email') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.address_line_1') }}</label>
                            <input type="text" name="billing_address_line_1" required value="{{ old('billing_address_line_1') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.address_line_2') }}</label>
                            <input type="text" name="billing_address_line_2" value="{{ old('billing_address_line_2') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.city') }}</label>
                            <input type="text" name="billing_city" required value="{{ old('billing_city') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.district') }}</label>
                            <input type="text" name="billing_district" value="{{ old('billing_district') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.province') }}</label>
                            <input type="text" name="billing_province" value="{{ old('billing_province') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.postal_code') }}</label>
                            <input type="text" name="billing_postal_code" value="{{ old('billing_postal_code') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500">{{ __('customer.country') }}</label>
                            <input type="text" name="billing_country_code" required value="{{ old('billing_country_code', 'LK') }}" maxlength="5" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-800">
                        <input type="checkbox" name="ship_to_different_address" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            @checked(old('ship_to_different_address', false))
                            onchange="document.getElementById('shipping-block').classList.toggle('hidden', !this.checked)"
                        />
                        {{ __('customer.ship_to_different_address') }}
                    </label>

                    <div id="shipping-block" class="mt-6 @if(!old('ship_to_different_address', false)) hidden @endif space-y-4">
                        <h3 class="text-base font-semibold text-slate-900">{{ __('customer.shipping_address') }}</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.first_name') }}</label>
                                <input type="text" name="shipping_first_name" value="{{ old('shipping_first_name') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.last_name') }}</label>
                                <input type="text" name="shipping_last_name" value="{{ old('shipping_last_name') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.address_line_1') }}</label>
                                <input type="text" name="shipping_address_line_1" value="{{ old('shipping_address_line_1') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.address_line_2') }}</label>
                                <input type="text" name="shipping_address_line_2" value="{{ old('shipping_address_line_2') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.city') }}</label>
                                <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.district') }}</label>
                                <input type="text" name="shipping_district" value="{{ old('shipping_district') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.province') }}</label>
                                <input type="text" name="shipping_province" value="{{ old('shipping_province') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.postal_code') }}</label>
                                <input type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500">{{ __('customer.country') }}</label>
                                <input type="text" name="shipping_country_code" value="{{ old('shipping_country_code') }}" maxlength="5" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('customer.shipping_method') }}</h2>
                    <label for="shipping_rate_id" class="mt-2 block text-xs font-semibold text-slate-500">{{ __('customer.select_shipping_method') }}</label>
                    <select id="shipping_rate_id" name="shipping_rate_id" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">{{ __('customer.select_shipping_method') }}</option>
                        @foreach ($shippingRates as $row)
                            @php
                                /** @var \App\Models\ShippingRate $r */
                                $r = $row['rate'];
                                $amt = $row['amount'];
                            @endphp
                            <option value="{{ $r->id }}" @selected((string) old('shipping_rate_id') === (string) $r->id)>
                                {{ $row['label'] }} — {{ number_format($amt, 2) }} {{ $code }}
                            </option>
                        @endforeach
                    </select>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <label for="customer_note" class="block text-lg font-semibold text-slate-900">{{ __('customer.customer_note') }}</label>
                    <textarea id="customer_note" name="customer_note" rows="3" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('customer_note') }}</textarea>
                </section>

                <button type="submit" @disabled($shippingRates->isEmpty()) class="w-full rounded-xl bg-emerald-600 px-6 py-4 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    {{ __('customer.place_order') }}
                </button>
            </div>

            <aside class="w-full shrink-0 lg:w-96">
                <div class="sticky top-24 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">{{ __('customer.order_summary') }}</h2>
                    <ul class="mt-4 divide-y divide-slate-100 text-sm">
                        @foreach ($cart->items as $item)
                            <li class="flex gap-3 py-3">
                                @if ($item->product?->primaryImage?->path)
                                    <img src="{{ $item->product->primaryImage->path }}" alt="" class="h-14 w-14 shrink-0 rounded-lg object-cover" />
                                @else
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">—</div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-900">{{ $item->product?->name }}</p>
                                    @if ($item->variant)
                                        <p class="text-xs text-slate-500">{{ $item->variant->name }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-slate-600">{{ __('customer.quantity') }}: {{ $item->quantity }} × {{ number_format((float) $item->unit_price, 2) }} {{ $code }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="mt-4 space-y-2 border-t border-slate-200 pt-4 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-600">{{ __('customer.subtotal') }}</dt>
                            <dd>{{ number_format((float) $cart->subtotal, 2) }} {{ $code }}</dd>
                        </div>
                        @if($cart->coupon_code)
                            <div class="flex justify-between">
                                <dt class="text-slate-600">{{ __('customer.coupon_code') }}</dt>
                                <dd class="font-semibold text-slate-900">{{ $cart->coupon_code }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-slate-600">{{ __('customer.discount') }}</dt>
                            <dd>-{{ number_format((float) $cart->discount_total, 2) }} {{ $code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-600">{{ __('customer.tax') }}</dt>
                            <dd>{{ number_format((float) $cart->tax_total, 2) }} {{ $code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-600">{{ __('customer.shipping') }}</dt>
                            <dd>{{ __('customer.calculated_at_checkout') }}</dd>
                        </div>
                        <div class="flex justify-between font-semibold text-slate-900">
                            <dt>{{ __('customer.grand_total') }}</dt>
                            <dd>{{ number_format((float) $cart->grand_total, 2) }} {{ $code }}</dd>
                        </div>
                    </dl>
                    @if($cart->coupon_code)
                        <p class="mt-3 text-xs text-slate-500">{{ __('customer.manage_coupon_in_cart') }}</p>
                    @endif
                </div>
            </aside>
        </form>
    </div>
@endsection
