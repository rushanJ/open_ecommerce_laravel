@props([
    'title',
    'subtitle' => null,
    'metric' => null,
    'trend' => null,
    'chart' => [],
    'height' => 'h-80',
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-[0_24px_70px_-38px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 px-5 py-5 dark:border-white/10">
        <div class="min-w-0">
            <h2 class="text-base font-semibold tracking-tight text-slate-950 dark:text-white">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($metric || $trend)
            <div class="text-right">
                @if ($metric)
                    <p class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $metric }}</p>
                @endif
                @if ($trend)
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-300">{{ $trend }}</p>
                @endif
            </div>
        @endif
    </div>

    <div class="p-5">
        <div class="{{ $height }} relative">
            <canvas class="js-admin-chart h-full w-full" aria-label="{{ $title }}" role="img"></canvas>
            <script type="application/json" data-admin-chart-config>@json($chart)</script>
        </div>
    </div>
</section>
