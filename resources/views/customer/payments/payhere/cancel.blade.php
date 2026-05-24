@extends('customer.layouts.app')

@section('title', __('customer.payment_cancelled'))

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.payment_cancelled') }}</h1>
        <p class="mt-4 text-slate-600">{{ __('customer.payment_not_confirmed_by_return') }}</p>

        <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            @if ($payment->order)
                <a href="{{ route('customer.payments.payhere.start', $payment->order) }}" class="inline-flex w-full justify-center rounded-xl bg-slate-900 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 sm:w-auto">
                    {{ __('customer.retry_payment') }}
                </a>
            @endif
            <a href="{{ route('customer.products.index') }}" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:w-auto">
                {{ __('customer.continue_shopping') }}
            </a>
        </div>
    </div>
@endsection

