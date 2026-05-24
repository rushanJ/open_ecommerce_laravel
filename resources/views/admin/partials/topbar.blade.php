@php
    /** @var \App\Models\AdminUser|null $admin */
    $admin = auth('admin')->user();
@endphp

<header class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between gap-3 border-b border-gray-200 bg-white/95 px-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <button
            type="button"
            class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] focus-visible:ring-offset-2 dark:text-gray-300 dark:hover:bg-gray-800 dark:focus-visible:ring-offset-gray-900 lg:hidden"
            @click="sidebarOpen = true"
            aria-label="{{ __('admin.open_menu') }}"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="hidden min-w-0 flex-1 sm:block md:max-w-md lg:max-w-lg">
            <label for="admin-search" class="sr-only">{{ __('admin.search_placeholder') }}</label>
            <input
                id="admin-search"
                type="search"
                name="q"
                readonly
                autocomplete="off"
                placeholder="{{ __('admin.search_placeholder') }}"
                class="block w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-3 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-[color:var(--mk-admin-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--mk-admin-primary)] focus:ring-opacity-25 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-opacity-30"
            />
        </div>
        <span class="truncate text-sm font-medium text-gray-500 dark:text-gray-400 sm:hidden">{{ __('admin.store_admin_label') }}</span>
    </div>

    <div class="flex shrink-0 items-center gap-3 sm:gap-4">
        @if(Route::has('admin.notifications.index'))
            <a
                href="{{ route('admin.notifications.index') }}"
                class="relative inline-flex items-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] dark:text-gray-300 dark:hover:bg-gray-800"
                title="{{ __('admin.notifications') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if(($adminUnreadNotificationCount ?? 0) > 0)
                    <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[0.65rem] font-bold text-white">{{ $adminUnreadNotificationCount > 99 ? '99+' : $adminUnreadNotificationCount }}</span>
                @endif
            </a>
        @endif
        <span class="hidden text-sm font-medium text-gray-500 dark:text-gray-400 md:inline">{{ __('admin.store_admin_label') }}</span>
        <a
            href="{{ url('/') }}"
            class="hidden text-sm font-medium text-[color:var(--mk-admin-primary)] hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 sm:inline"
        >
            {{ __('admin.view_storefront') }}
        </a>
        @if ($admin)
            <span class="hidden max-w-[10rem] truncate text-sm text-gray-700 dark:text-gray-200 sm:inline sm:max-w-xs" title="{{ $admin->name }}">{{ $admin->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                @csrf
                <x-admin.button type="submit" variant="ghost" size="sm" class="!px-2 !py-1.5">
                    {{ __('admin.logout') }}
                </x-admin.button>
            </form>
        @endif
    </div>
</header>
