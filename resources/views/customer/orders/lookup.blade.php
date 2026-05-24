@extends('customer.layouts.app')

@section('title', __('customer.order_lookup'))

@section('content')
    <div class="mx-auto max-w-lg px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.lookup_order') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('customer.guest_order_lookup_help') }}</p>

            <form method="POST" action="{{ route('customer.order.lookup.submit') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.order_number') }}</label>
                    <input name="order_number" type="text" value="{{ old('order_number') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('order_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.email_or_phone') }}</label>
                    <input name="contact" type="text" value="{{ old('contact') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('contact') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    {{ __('customer.lookup_order') }}
                </button>
            </form>
        </div>
    </div>
@endsection

