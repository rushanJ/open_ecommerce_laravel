@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-[0_24px_70px_-38px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    @if ($title || $subtitle || isset($actions))
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-white/10">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-base font-semibold tracking-tight text-slate-950 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif
    <div class="px-5 py-4">
        {{ $slot }}
    </div>
</div>
