@extends('customer.layouts.app')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.register') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('customer.create_account') }}</p>

            <form method="POST" action="{{ route('customer.register.store') }}" class="mt-6 space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('customer.first_name') }}</label>
                        <input name="first_name" type="text" value="{{ old('first_name') }}" required
                               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                        @error('first_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('customer.last_name') }}</label>
                        <input name="last_name" type="text" value="{{ old('last_name') }}"
                               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                        @error('last_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.email') }}</label>
                    <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.phone') }}</label>
                    <input name="phone" type="text" value="{{ old('phone') }}" autocomplete="tel"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.password') }}</label>
                    <input name="password" type="password" required autocomplete="new-password"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.confirm_password') }}</label>
                    <input name="password_confirmation" type="password" required autocomplete="new-password"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="accepts_marketing" value="1" @checked(old('accepts_marketing'))
                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" />
                    <span>{{ __('customer.accepts_marketing') }}</span>
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    {{ __('customer.register') }}
                </button>

                <p class="text-sm text-slate-600">
                    {{ __('customer.have_account') }}
                    <a href="{{ route('customer.login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">{{ __('customer.login') }}</a>
                </p>
            </form>
        </div>
    </div>
@endsection

<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
