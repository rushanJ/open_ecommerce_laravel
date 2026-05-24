@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-900 p-5 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
