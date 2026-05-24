@props([
    'count' => 0,
    'items' => [],
])

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button
        type="button"
        class="relative grid h-10 w-10 place-items-center rounded-2xl bg-white/80 text-slate-600 ring-1 ring-slate-900/10 transition hover:-translate-y-0.5 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:bg-white/10 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/15"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        title="{{ __('admin.notifications') }}"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
        </svg>
        @if ((int) $count > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[0.65rem] font-bold text-white ring-2 ring-white dark:ring-slate-950">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition.origin.top.right
        @click.outside="open = false"
        class="absolute right-0 mt-3 w-80 overflow-hidden rounded-3xl border border-white/70 bg-white/95 shadow-[0_24px_70px_-28px_rgba(15,23,42,0.55)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/95 dark:ring-white/10"
        x-cloak
    >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/10">
            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('admin.notifications') }}</h2>
            @if(Route::has('admin.notifications.index'))
                <a href="{{ route('admin.notifications.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-300">View all</a>
            @endif
        </div>
        <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-white/10">
            @forelse ($items as $item)
                <a href="{{ $item['href'] ?? '#' }}" class="block px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-white/5">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['title'] ?? 'Notification' }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $item['description'] ?? '' }}</p>
                </a>
            @empty
                <div class="px-4 py-8 text-sm text-slate-500 dark:text-slate-400">No new notifications.</div>
            @endforelse
        </div>
    </div>
</div>
