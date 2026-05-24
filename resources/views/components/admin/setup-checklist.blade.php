@props([
    'title' => 'Launch checklist',
    'tasks' => [],
])

@php
    $total = max(1, count($tasks));
    $completed = collect($tasks)->where('complete', true)->count();
    $progress = (int) round(($completed / $total) * 100);
    $dash = min(100, $progress);
@endphp

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-3xl border border-white/70 bg-white/90 shadow-[0_24px_70px_-38px_rgba(15,23,42,0.45)] ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/80 dark:ring-white/10']) }}>
    <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-5 dark:border-white/10">
        <div class="relative grid h-16 w-16 shrink-0 place-items-center rounded-full bg-slate-50 dark:bg-white/5" aria-label="{{ $progress }} percent complete">
            <svg class="absolute inset-0 h-16 w-16 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                <circle cx="18" cy="18" r="15.5" fill="none" stroke="currentColor" class="text-slate-200 dark:text-white/10" stroke-width="3" />
                <circle cx="18" cy="18" r="15.5" fill="none" stroke="currentColor" class="text-indigo-600 dark:text-indigo-300" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $dash }} 100" />
            </svg>
            <span class="text-sm font-bold text-slate-950 dark:text-white">{{ $progress }}%</span>
        </div>
        <div>
            <h2 class="text-base font-semibold tracking-tight text-slate-950 dark:text-white">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $completed }} of {{ $total }} steps complete.</p>
        </div>
    </div>

    <div class="space-y-2 p-3">
        @foreach ($tasks as $task)
            <details class="group rounded-2xl bg-slate-50/80 px-4 py-3 ring-1 ring-slate-900/5 transition hover:bg-white dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10" @if($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                    <span class="flex min-w-0 items-center gap-3">
                        <span @class([
                            'grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold',
                            'bg-emerald-500 text-white' => $task['complete'] ?? false,
                            'bg-white text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:ring-white/10' => ! ($task['complete'] ?? false),
                        ])>
                            {{ ($task['complete'] ?? false) ? 'OK' : $loop->iteration }}
                        </span>
                        <span class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $task['title'] ?? 'Setup task' }}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </summary>
                <p class="mt-3 pl-10 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $task['description'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</section>
