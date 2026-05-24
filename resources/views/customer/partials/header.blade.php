@php
    /** @var \App\Services\Cms\MenuService $menuService */
    $menuService = app(\App\Services\Cms\MenuService::class);
    $headerMenuItems = $menuService->getMenuItems('header');
    $headerCategories = \App\Models\Category::query()->active()->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->limit(6)->get();
@endphp

<header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/90 shadow-sm shadow-slate-200/60 backdrop-blur-xl">
    <div class="mx-auto flex max-w-[1320px] flex-wrap items-center gap-3 px-4 py-3 sm:px-6 lg:flex-nowrap lg:px-8">
        <a href="{{ route('customer.home') }}" class="flex shrink-0 items-center gap-2 text-base font-black tracking-tight text-slate-950 transition hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
            @if($storefrontStore?->logo_path)
                <img src="{{ media_url($storefrontStore->logo_path) }}" alt="{{ $storefrontName }}" class="h-9 w-auto object-contain" />
            @else
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-blue-600 text-sm font-black text-white shadow-lg shadow-blue-200">M</span>
            @endif
            <span>{{ $storefrontName }}</span>
        </a>

        <form action="{{ route('customer.products.index') }}" method="GET" class="order-last flex w-full min-w-0 flex-1 items-center gap-0 rounded-2xl border border-slate-200 bg-slate-50/90 p-1 shadow-inner lg:order-none lg:max-w-2xl" role="search">
            <label for="store-search" class="sr-only">{{ __('customer.search') }}</label>
            <input
                id="store-search"
                type="search"
                name="q"
                value="{{ request('q', '') }}"
                placeholder="{{ __('customer.search_placeholder') }}"
                class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0"
            />
            <select name="category" class="hidden max-w-44 border-0 bg-transparent px-2 py-2.5 text-xs font-semibold text-slate-600 focus:ring-0 md:block" aria-label="{{ __('customer.category') }}">
                <option value="">{{ __('customer.all_categories') }}</option>
                @foreach ($headerCategories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="shrink-0 rounded-xl bg-gradient-to-r from-violet-600 to-blue-600 px-4 py-2.5 text-xs font-black text-white shadow-sm transition duration-[180ms] ease-in-out hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-100">
                {{ __('customer.search_short') }}
            </button>
        </form>

        <div class="flex shrink-0 items-center gap-2 text-xs font-semibold text-slate-600">
            <a href="{{ route('customer.products.index') }}" class="hidden rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-slate-950 md:inline-flex">{{ __('customer.deals') }}</a>
            <a href="{{ route('customer.register') }}" class="hidden rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-slate-950 md:inline-flex">{{ __('customer.sell_on_open_ecommerce_laravel') }}</a>
            @if(auth('customer')->check())
                <a href="{{ route('customer.account.dashboard') }}" class="hidden rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900 sm:inline-block">
                    {{ auth('customer')->user()?->first_name ?? __('customer.account') }}
                </a>
                <form method="POST" action="{{ route('customer.logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="rounded-xl px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900">
                        {{ __('customer.logout') }}
                    </button>
                </form>
            @else
                <a href="{{ route('customer.login') }}" class="hidden h-10 w-10 items-center justify-center rounded-full bg-slate-950 text-white transition hover:bg-blue-700 sm:inline-flex" aria-label="{{ __('customer.login') }}">{{ \Illuminate\Support\Str::substr(__('customer.account'), 0, 1) }}</a>
            @endif
            @include('customer.components.cart-count', ['count' => $storefrontCartItemCount ?? 0])
        </div>
    </div>
</header>
