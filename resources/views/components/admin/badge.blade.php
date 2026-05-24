@props([
    'variant' => 'neutral',
])

@php
    $classes = match ($variant) {
        'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
        'warning' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        'info' => 'bg-sky-100 text-sky-900 dark:bg-sky-900/40 dark:text-sky-200',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium '.$classes]) }}>
    {{ $slot }}
</span>
