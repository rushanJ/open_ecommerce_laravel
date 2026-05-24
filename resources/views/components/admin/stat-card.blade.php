@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'trend' => null,
    'trendDirection' => 'up',
    'comparison' => null,
    'sparkline' => [],
    'accent' => 'indigo',
])

@php
    $accentClasses = [
        'indigo' => 'from-indigo-500/20 via-violet-500/10 to-transparent text-indigo-600 dark:text-indigo-300 ring-indigo-500/20',
        'violet' => 'from-violet-500/20 via-fuchsia-500/10 to-transparent text-violet-600 dark:text-violet-300 ring-violet-500/20',
        'emerald' => 'from-emerald-500/20 via-teal-500/10 to-transparent text-emerald-600 dark:text-emerald-300 ring-emerald-500/20',
        'amber' => 'from-amber-500/20 via-orange-500/10 to-transparent text-amber-600 dark:text-amber-300 ring-amber-500/20',
        'rose' => 'from-rose-500/20 via-red-500/10 to-transparent text-rose-600 dark:text-rose-300 ring-rose-500/20',
        'sky' => 'from-sky-500/20 via-cyan-500/10 to-transparent text-sky-600 dark:text-sky-300 ring-sky-500/20',
    ][$accent] ?? 'from-indigo-500/20 via-violet-500/10 to-transparent text-indigo-600 dark:text-indigo-300 ring-indigo-500/20';

    $trendClasses = $trendDirection === 'down'
        ? 'bg-red-50 text-red-700 ring-red-600/10 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-400/20'
        : 'bg-emerald-50 text-emerald-700 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20';

    $sparkValues = collect($sparkline)->map(fn ($value) => (float) $value)->values();
    $sparkPoints = '';

    if ($sparkValues->count() > 1) {
        $min = $sparkValues->min();
        $max = $sparkValues->max();
        $range = max(1, $max - $min);
        $step = 100 / max(1, $sparkValues->count() - 1);
        $sparkPoints = $sparkValues
            ->map(fn ($value, $index) => round($index * $step, 2).','.round(34 - ((($value - $min) / $range) * 28), 2))
            ->implode(' ');
    }
@endphp

<div {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-3xl border border-white/70 bg-white/90 p-5 shadow-[0_24px_70px_-36px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_90px_-36px_rgba(79,70,229,0.55)] dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-br {{ $accentClasses }} opacity-80 transition duration-300 group-hover:opacity-100" aria-hidden="true"></div>
    <div class="relative flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <div class="mt-4 flex flex-wrap items-end gap-2">
                <p class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $value }}</p>
                @if ($trend)
                    <span class="mb-1 inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ring-1 {{ $trendClasses }}">
                        <svg class="mr-1 h-3 w-3 {{ $trendDirection === 'down' ? 'rotate-180' : '' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3.75a.75.75 0 0 1 .53.22l4.75 4.75a.75.75 0 0 1-1.06 1.06l-3.47-3.47v9.94a.75.75 0 0 1-1.5 0V6.31L5.78 9.78a.75.75 0 0 1-1.06-1.06l4.75-4.75A.75.75 0 0 1 10 3.75Z" clip-rule="evenodd" />
                        </svg>
                        {{ $trend }}
                    </span>
                @endif
            </div>
            @if ($comparison || $hint)
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $comparison ?? $hint }}</p>
            @endif
        </div>
        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/80 shadow-sm ring-1 {{ $accentClasses }} dark:bg-white/10" aria-hidden="true">
            @if ($icon)
                {!! $icon !!}
            @else
                <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
            @endif
        </div>
    </div>
    @if ($sparkPoints !== '')
        <svg class="relative mt-5 h-10 w-full overflow-visible text-current opacity-80" viewBox="0 0 100 38" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="spark-{{ md5($label.$value) }}" x1="0" x2="1" y1="0" y2="0">
                    <stop offset="0%" stop-color="currentColor" stop-opacity="0.15" />
                    <stop offset="100%" stop-color="currentColor" stop-opacity="0.8" />
                </linearGradient>
            </defs>
            <polyline points="{{ $sparkPoints }}" fill="none" stroke="url(#spark-{{ md5($label.$value) }})" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
        </svg>
    @endif
</div>
