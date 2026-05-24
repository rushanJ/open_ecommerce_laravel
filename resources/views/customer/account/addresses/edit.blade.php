@extends('customer.account.layouts.app')

@section('title', __('customer.edit_address'))

@section('account_content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.edit_address') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ __('customer.address_form_help') }}</p>
            </div>
            <a href="{{ route('customer.account.addresses.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                {{ __('customer.back') }}
            </a>
        </div>

        <form method="POST" action="{{ route('customer.account.addresses.update', $address) }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            @include('customer.account.addresses.partials.form', ['address' => $address])

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    {{ __('customer.save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

