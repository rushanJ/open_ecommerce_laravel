@php
    /** @var \App\Models\AdminUser|null $admin */
    $admin = auth('admin')->user();
    $brandName = config('open_ecommerce_laravel.admin.theme.brand_name', config('app.name'));
    $notificationItems = [
        ['title' => 'Order queue updated', 'description' => 'New orders and payment events are ready for review.', 'href' => Route::has('admin.orders.index') ? route('admin.orders.index') : '#'],
        ['title' => 'Inventory watchlist', 'description' => 'Low stock products need restock decisions.', 'href' => Route::has('admin.inventory.index') ? route('admin.inventory.index') : '#'],
    ];
@endphp

<header class="sticky top-0 z-20 border-b border-white/70 bg-[#F8FAFC]/85 px-4 py-3 backdrop-blur-2xl dark:border-white/10 dark:bg-[#0B1120]/80 sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-[1600px] items-center gap-3">
        <button
            type="button"
            class="grid h-10 w-10 place-items-center rounded-2xl bg-white/80 text-slate-600 ring-1 ring-slate-900/10 transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:bg-white/10 dark:text-slate-300 dark:ring-white/10 lg:hidden"
            @click="sidebarOpen = true"
            aria-label="{{ __('admin.open_menu') }}"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="hidden min-w-0 items-center gap-3 rounded-2xl bg-white/75 px-3 py-2 ring-1 ring-slate-900/10 backdrop-blur-xl dark:bg-white/10 dark:ring-white/10 md:flex">
            <span class="grid h-8 w-8 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-black text-white shadow-lg shadow-indigo-600/20">{{ \Illuminate\Support\Str::of($brandName)->substr(0, 2)->upper() }}</span>
            <div class="min-w-0">
                <p class="truncate text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Store</p>
                <p class="truncate text-sm font-semibold text-slate-800 dark:text-white">{{ $brandName }}</p>
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <label for="admin-search" class="sr-only">{{ __('admin.search_placeholder') }}</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
                <input
                    id="admin-search"
                    type="search"
                    name="q"
                    readonly
                    autocomplete="off"
                    placeholder="{{ __('admin.search_placeholder') }}"
                    class="block h-11 w-full rounded-2xl border-0 bg-white/80 py-2 pl-12 pr-20 text-sm font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 placeholder:text-slate-400 transition focus:ring-2 focus:ring-indigo-500 dark:bg-white/10 dark:text-white dark:ring-white/10 dark:placeholder:text-slate-500"
                />
                <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-lg bg-slate-100 px-2 py-1 text-[0.65rem] font-bold text-slate-500 ring-1 ring-slate-900/5 dark:bg-white/10 dark:text-slate-400 dark:ring-white/10 sm:block">Ctrl K</kbd>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <div class="relative hidden sm:block" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button
                    type="button"
                    class="inline-flex h-10 items-center gap-2 rounded-2xl bg-white/80 px-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-900/10 transition hover:-translate-y-0.5 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:bg-white/10 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/15"
                    @click="open = !open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()"
                >
                    <svg class="h-4 w-4 text-indigo-500 dark:text-indigo-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z"/></svg>
                    Quick
                </button>
                <div x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute right-0 mt-3 w-56 overflow-hidden rounded-3xl border border-white/70 bg-white/95 p-2 shadow-[0_24px_70px_-28px_rgba(15,23,42,0.55)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/95 dark:ring-white/10" x-cloak>
                    @foreach ([
                        ['label' => 'Add product', 'route' => 'admin.products.create'],
                        ['label' => 'Create coupon', 'route' => 'admin.coupons.create'],
                        ['label' => 'Add banner', 'route' => 'admin.banners.create'],
                    ] as $action)
                        @if(Route::has($action['route']))
                            <a href="{{ route($action['route']) }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10">{{ $action['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                class="grid h-10 w-10 place-items-center rounded-2xl bg-white/80 text-slate-600 ring-1 ring-slate-900/10 transition hover:-translate-y-0.5 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:bg-white/10 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/15"
                @click="toggleTheme()"
                :aria-label="darkMode ? 'Use light theme' : 'Use dark theme'"
            >
                <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36-6.36-1.42 1.42M7.06 16.94l-1.42 1.42m12.72 0-1.42-1.42M7.06 7.06 5.64 5.64M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.5 6.5 0 0 0 21 12.8Z"/></svg>
            </button>

            <x-admin.notification-dropdown :count="$adminUnreadNotificationCount ?? 0" :items="$notificationItems" />

            @if ($admin)
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        class="flex h-10 items-center gap-2 rounded-2xl bg-white/80 pl-1.5 pr-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-900/10 transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:bg-white/10 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/15"
                        @click="open = !open"
                        aria-haspopup="true"
                        :aria-expanded="open.toString()"
                    >
                        <span class="grid h-7 w-7 place-items-center rounded-xl bg-slate-950 text-xs font-bold text-white dark:bg-white dark:text-slate-950">{{ \Illuminate\Support\Str::of($admin->name)->substr(0, 1)->upper() }}</span>
                        <span class="hidden max-w-28 truncate sm:block">{{ $admin->name }}</span>
                    </button>
                    <div x-show="open" x-transition.origin.top.right @click.outside="open = false" class="absolute right-0 mt-3 w-56 overflow-hidden rounded-3xl border border-white/70 bg-white/95 p-2 shadow-[0_24px_70px_-28px_rgba(15,23,42,0.55)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/95 dark:ring-white/10" x-cloak>
                        <a href="{{ url('/') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10">{{ __('admin.view_storefront') }}</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-2xl px-3 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10">{{ __('admin.logout') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>
