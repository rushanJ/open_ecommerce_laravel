@extends('customer.layouts.app')

@section('title', __('customer.payment_verifying'))

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.payment_verifying') }}</h1>
        <p class="mt-4 text-slate-600">{{ __('customer.payment_not_confirmed_by_return') }}</p>

        <div class="mx-auto mt-10 max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
            <p class="text-sm text-slate-600">{{ __('customer.order_number') }}:</p>
            <p class="mt-1 font-mono text-sm font-semibold text-slate-900">{{ $payment->order?->order_number ?? '—' }}</p>
            <p class="mt-4 text-sm text-slate-600">{{ __('customer.status') }}:</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->status }}</p>
        </div>

        <a href="{{ route('customer.products.index') }}" class="mt-10 inline-flex rounded-xl bg-emerald-600 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            {{ __('customer.continue_shopping') }}
        </a>
    </div>
@endsection

