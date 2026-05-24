@props([
    'title',
    'description',
    'tone' => 'indigo',
    'icon' => null,
])

@php
    $toneClasses = [
        'indigo' => 'from-indigo-500/15 to-violet-500/10 text-indigo-600 dark:text-indigo-300',
        'emerald' => 'from-emerald-500/15 to-teal-500/10 text-emerald-600 dark:text-emerald-300',
        'amber' => 'from-amber-500/15 to-orange-500/10 text-amber-600 dark:text-amber-300',
        'rose' => 'from-rose-500/15 to-red-500/10 text-rose-600 dark:text-rose-300',
        'sky' => 'from-sky-500/15 to-cyan-500/10 text-sky-600 dark:text-sky-300',
    ][$tone] ?? 'from-indigo-500/15 to-violet-500/10 text-indigo-600 dark:text-indigo-300';
@endphp

<article {{ $attributes->merge(['class' => 'group rounded-3xl border border-white/70 bg-gradient-to-br '.$toneClasses.' p-4 ring-1 ring-slate-900/5 transition duration-300 hover:-translate-y-0.5 hover:bg-white/80 dark:border-white/10 dark:ring-white/10']) }}>
    <div class="flex gap-3">
        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white/85 shadow-sm ring-1 ring-slate-900/5 dark:bg-white/10 dark:ring-white/10" aria-hidden="true">
            @if ($icon)
                {!! $icon !!}
            @else
                <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
            @endif
        </div>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $title }}</h3>
            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
        </div>
    </div>
</article>
