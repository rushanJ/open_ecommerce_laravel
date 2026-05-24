@extends('customer.layouts.app')

@section('title', __('customer.continue_to_payment'))

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-16 text-center sm:px-6">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.redirecting_to_payhere') }}</h1>
        <p class="mt-4 text-slate-600">{{ __('customer.payment_not_confirmed_by_return') }}</p>

        <form id="payhere-form" method="POST" action="{{ $checkoutUrl }}" class="mx-auto mt-10 max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
            @foreach ($payload as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
            @endforeach

            <button type="submit" class="mt-2 w-full rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                {{ __('customer.continue_to_payment') }}
            </button>
        </form>
    </div>

    <script>
        window.addEventListener('load', function () {
            var f = document.getElementById('payhere-form');
            if (f) f.submit();
        });
    </script>
@endsection

