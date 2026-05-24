@extends('customer.account.layouts.app')

@section('title', __('customer.addresses'))

@section('account_content')
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.addresses') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('customer.manage_addresses_help') }}</p>
        </div>
        <a href="{{ route('customer.account.addresses.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
            {{ __('customer.add_address') }}
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4">
        @forelse($addresses as $address)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $address->first_name }} {{ $address->last_name }}
                            </p>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                {{ $address->type === 'billing' ? __('customer.billing_address') : __('customer.shipping_address') }}
                            </span>
                            @if($address->is_default)
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                    {{ __('customer.default') }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $address->address_line_1 }}
                            @if($address->address_line_2) , {{ $address->address_line_2 }} @endif
                            , {{ $address->city }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            @if($address->phone) {{ $address->phone }} @endif
                            @if($address->email) <span class="ml-2">{{ $address->email }}</span> @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if(! $address->is_default)
                            <form method="POST" action="{{ route('customer.account.addresses.default', $address) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    {{ __('customer.make_default') }}
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('customer.account.addresses.edit', $address) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            {{ __('customer.edit') }}
                        </a>
                        <form method="POST" action="{{ route('customer.account.addresses.destroy', $address) }}"
                              onsubmit="return confirm('{{ __('customer.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                {{ __('customer.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <p class="text-sm text-slate-600">{{ __('customer.no_addresses_yet') }}</p>
                <div class="mt-4">
                    <a href="{{ route('customer.account.addresses.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        {{ __('customer.add_address') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>
@endsection

