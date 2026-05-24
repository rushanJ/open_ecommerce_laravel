@props([
    'title' => 'Live activity',
    'items' => [],
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-[0_24px_70px_-38px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 dark:border-white/10">
        <div>
            <h2 class="text-base font-semibold tracking-tight text-slate-950 dark:text-white">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Operational events from orders, payments, and stock.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20">
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
            Live
        </span>
    </div>

    <div class="space-y-1 p-3">
        @forelse ($items as $item)
            @php
                $tone = $item['tone'] ?? 'indigo';
                $toneClasses = [
                    'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300',
                    'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
                    'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
                    'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
                    'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
                ][$tone] ?? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300';
            @endphp
            <article class="flex gap-3 rounded-2xl px-3 py-3 transition hover:bg-slate-50 dark:hover:bg-white/5">
                <div class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-2xl {{ $toneClasses }}" aria-hidden="true">
                    {!! $item['icon'] ?? '<span class="h-2 w-2 rounded-full bg-current"></span>' !!}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $item['title'] ?? 'Activity' }}</h3>
                        <time class="text-xs font-medium text-slate-400">{{ $item['time'] ?? 'Just now' }}</time>
                    </div>
                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $item['description'] ?? '' }}</p>
                </div>
            </article>
        @empty
            <div class="px-5 py-10 text-sm text-slate-500 dark:text-slate-400">No activity yet.</div>
        @endforelse
    </div>
</section>
