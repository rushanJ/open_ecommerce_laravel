@extends('customer.account.layouts.app')

@section('title', __('customer.edit_profile'))

@section('account_content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.edit_profile') }}</h1>

        <form method="POST" action="{{ route('customer.account.profile.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.first_name') }}</label>
                    <input name="first_name" type="text" value="{{ old('first_name', $customer?->first_name) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('first_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.last_name') }}</label>
                    <input name="last_name" type="text" value="{{ old('last_name', $customer?->last_name) }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('last_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.phone') }}</label>
                    <input name="phone" type="text" value="{{ old('phone', $customer?->phone) }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('customer.dob') }}</label>
                    <input name="dob" type="date" value="{{ old('dob', optional($customer?->dob)->format('Y-m-d')) }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @error('dob') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('customer.gender') }}</label>
                <select name="gender" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2">
                    @php($g = old('gender', $customer?->gender))
                    <option value="">{{ __('customer.select_optional') }}</option>
                    <option value="male" @selected($g === 'male')>{{ __('customer.gender_male') }}</option>
                    <option value="female" @selected($g === 'female')>{{ __('customer.gender_female') }}</option>
                    <option value="other" @selected($g === 'other')>{{ __('customer.gender_other') }}</option>
                    <option value="unknown" @selected($g === 'unknown')>{{ __('customer.gender_unknown') }}</option>
                </select>
                @error('gender') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="accepts_marketing" value="1" @checked(old('accepts_marketing', (bool) ($customer?->accepts_marketing ?? false)))
                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" />
                <span>{{ __('customer.accepts_marketing') }}</span>
            </label>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    {{ __('customer.save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

