@php
    /** @var \App\Models\CustomerAddress|null $address */
    $address = $address ?? null;
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.address_type') }}</label>
        <select name="type" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2">
            @php($t = old('type', $address?->type ?? 'shipping'))
            <option value="shipping" @selected($t === 'shipping')>{{ __('customer.shipping_address') }}</option>
            <option value="billing" @selected($t === 'billing')>{{ __('customer.billing_address') }}</option>
        </select>
        @error('type') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', (bool) ($address?->is_default ?? false)))
                   class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" />
            <span>{{ __('customer.make_default') }}</span>
        </label>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.first_name') }}</label>
        <input name="first_name" type="text" value="{{ old('first_name', $address?->first_name ?? auth('customer')->user()?->first_name) }}" required
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('first_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.last_name') }}</label>
        <input name="last_name" type="text" value="{{ old('last_name', $address?->last_name ?? auth('customer')->user()?->last_name) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('last_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.phone') }}</label>
        <input name="phone" type="text" value="{{ old('phone', $address?->phone) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.email') }}</label>
        <input name="email" type="email" value="{{ old('email', $address?->email) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label class="text-sm font-medium text-slate-700">{{ __('customer.address_line_1') }}</label>
    <input name="address_line_1" type="text" value="{{ old('address_line_1', $address?->address_line_1) }}" required
           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
    @error('address_line_1') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>

<div>
    <label class="text-sm font-medium text-slate-700">{{ __('customer.address_line_2') }}</label>
    <input name="address_line_2" type="text" value="{{ old('address_line_2', $address?->address_line_2) }}"
           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
    @error('address_line_2') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.city') }}</label>
        <input name="city" type="text" value="{{ old('city', $address?->city) }}" required
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('city') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.district') }}</label>
        <input name="district" type="text" value="{{ old('district', $address?->district) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('district') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.province') }}</label>
        <input name="province" type="text" value="{{ old('province', $address?->province) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('province') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('customer.postal_code') }}</label>
        <input name="postal_code" type="text" value="{{ old('postal_code', $address?->postal_code) }}"
               class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
        @error('postal_code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label class="text-sm font-medium text-slate-700">{{ __('customer.country_code') }}</label>
    <input name="country_code" type="text" value="{{ old('country_code', $address?->country_code ?? 'LK') }}" required
           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
    @error('country_code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>

