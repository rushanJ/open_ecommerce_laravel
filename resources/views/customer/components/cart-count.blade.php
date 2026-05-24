@props([
    'count' => 0,
])

<a href="{{ route('customer.cart.index') }}" class="relative inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-3 text-xs font-bold text-slate-800 shadow-sm transition duration-[180ms] ease-in-out hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100" aria-label="{{ __('customer.cart') }}">
    <span aria-hidden="true">Bag</span>
    @if ($count > 0)
        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[0.65rem] font-bold text-white">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
