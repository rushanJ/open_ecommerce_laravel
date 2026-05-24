@props([
    'title',
    'subtitle' => null,
    'searchPlaceholder' => 'Search records',
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-[0_24px_70px_-38px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 dark:border-white/10">
        <div class="min-w-0">
            <h2 class="text-base font-semibold tracking-tight text-slate-950 dark:text-white">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex flex-1 items-center justify-end gap-2 sm:max-w-md">
            <label class="sr-only" for="table-card-search-{{ md5($title) }}">{{ $searchPlaceholder }}</label>
            <div class="relative hidden flex-1 sm:block">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.766l2.63 2.63a.75.75 0 1 0 1.061-1.06l-2.63-2.631A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                </svg>
                <input id="table-card-search-{{ md5($title) }}" type="search" readonly placeholder="{{ $searchPlaceholder }}" class="w-full rounded-2xl border-0 bg-slate-100/80 py-2 pl-9 pr-3 text-sm text-slate-700 ring-1 ring-slate-900/5 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 dark:bg-white/10 dark:text-slate-200 dark:ring-white/10" />
            </div>
            @isset($actions)
                {{ $actions }}
            @endisset
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-white/10">
            @isset($head)
                <thead class="sticky top-0 z-10 bg-slate-50/90 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 backdrop-blur dark:bg-slate-950/70 dark:text-slate-400">
                    {{ $head }}
                </thead>
            @endisset
            <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</section>
