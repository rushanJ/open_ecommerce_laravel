@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'mb-8 flex flex-wrap items-end justify-between gap-5']) }}>
    <div class="min-w-0">
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600 dark:text-indigo-300">{{ __('admin.store_admin_label') }}</p>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl">
            {{ $title }}
        </h1>
        @if ($subtitle)
            <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600 dark:text-slate-400">
                {{ $subtitle }}
            </p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
