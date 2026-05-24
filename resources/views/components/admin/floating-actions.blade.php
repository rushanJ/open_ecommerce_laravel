@props([
    'actions' => [],
])

<div {{ $attributes->merge(['class' => 'pointer-events-none fixed inset-x-4 bottom-4 z-30 flex justify-center lg:inset-x-auto lg:right-6']) }}>
    <div class="pointer-events-auto flex max-w-full gap-2 overflow-x-auto rounded-3xl border border-white/70 bg-white/90 p-2 shadow-[0_24px_70px_-28px_rgba(15,23,42,0.55)] ring-1 ring-slate-900/5 backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/85 dark:ring-white/10">
        @foreach ($actions as $action)
            <a
                href="{{ $action['href'] ?? '#' }}"
                class="group inline-flex shrink-0 items-center gap-2 rounded-2xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-slate-200 dark:hover:bg-white/10"
            >
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-600/20 transition group-hover:scale-105" aria-hidden="true">
                    {!! $action['icon'] ?? '<span class="h-2 w-2 rounded-full bg-current"></span>' !!}
                </span>
                <span>{{ $action['label'] ?? 'Action' }}</span>
            </a>
        @endforeach
    </div>
</div>
