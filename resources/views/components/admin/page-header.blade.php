@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'mb-8 flex flex-wrap items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">
            {{ $title }}
        </h1>
        @if ($subtitle)
            <p class="mt-1 max-w-2xl text-sm text-gray-600 dark:text-gray-400">
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
