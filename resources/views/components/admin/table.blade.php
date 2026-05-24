<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-[0_24px_70px_-38px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    <div class="-mx-px overflow-x-auto">
        @isset($head)
            <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-white/10">
                <thead class="sticky top-0 z-10 bg-slate-50/90 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 backdrop-blur dark:bg-slate-950/60 dark:text-slate-400">
                    {{ $head }}
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                    {{ $slot }}
                </tbody>
            </table>
        @else
            {{ $slot }}
        @endisset
    </div>
</div>
