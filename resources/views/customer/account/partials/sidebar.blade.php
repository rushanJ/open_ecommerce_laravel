<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-3">
        <p class="text-sm font-semibold text-slate-900">{{ __('customer.account_dashboard') }}</p>
        <p class="text-xs text-slate-500">{{ auth('customer')->user()?->email }}</p>
    </div>

    <nav class="space-y-1 text-sm">
        <a href="{{ route('customer.account.dashboard') }}"
           class="flex items-center justify-between rounded-xl px-3 py-2 transition hover:bg-slate-50 @if(request()->routeIs('customer.account.dashboard')) bg-slate-50 font-semibold text-slate-900 @else text-slate-700 @endif">
            <span>{{ __('customer.account_dashboard') }}</span>
        </a>
        <a href="{{ route('customer.account.profile.edit') }}"
           class="flex items-center justify-between rounded-xl px-3 py-2 transition hover:bg-slate-50 @if(request()->routeIs('customer.account.profile.*')) bg-slate-50 font-semibold text-slate-900 @else text-slate-700 @endif">
            <span>{{ __('customer.profile') }}</span>
        </a>
        <a href="{{ route('customer.account.addresses.index') }}"
           class="flex items-center justify-between rounded-xl px-3 py-2 transition hover:bg-slate-50 @if(request()->routeIs('customer.account.addresses.*')) bg-slate-50 font-semibold text-slate-900 @else text-slate-700 @endif">
            <span>{{ __('customer.addresses') }}</span>
        </a>
        <a href="{{ route('customer.account.orders.index') }}"
           class="flex items-center justify-between rounded-xl px-3 py-2 transition hover:bg-slate-50 @if(request()->routeIs('customer.account.orders.*')) bg-slate-50 font-semibold text-slate-900 @else text-slate-700 @endif">
            <span>{{ __('customer.orders') }}</span>
        </a>
        <a href="{{ route('customer.account.notifications.index') }}"
           class="flex items-center justify-between rounded-xl px-3 py-2 transition hover:bg-slate-50 @if(request()->routeIs('customer.account.notifications.*')) bg-slate-50 font-semibold text-slate-900 @else text-slate-700 @endif">
            <span>{{ __('customer.notifications') }}</span>
        </a>

        <div class="pt-2">
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-slate-700 transition hover:bg-slate-50">
                    {{ __('customer.logout') }}
                </button>
            </form>
        </div>
    </nav>
</div>

