@props([
    'type' => 'button',
    'variant' => 'primary',
    'href' => null,
    'size' => 'md',
])

@php
    $linkHref = ($type === 'link') ? ($href ?? '#') : null;
    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'lg' => 'px-5 py-2.5 text-base gap-2',
        default => 'px-4 py-2 text-sm gap-2',
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 focus-visible:ring-gray-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500',
        'ghost' => 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:ring-gray-400',
        default => 'text-white hover:opacity-90 focus-visible:ring-[color:var(--mk-admin-primary)]',
    };

    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950';

    $base = 'inline-flex items-center justify-center font-medium rounded-lg transition '.$focusRing.' '.$sizeClasses.' '.$variantClasses;

    $primaryStyle = $variant === 'primary' ? 'background-color: var(--mk-admin-primary);' : '';
@endphp

@if ($type === 'link' && $linkHref)
    <a href="{{ $linkHref }}" {{ $attributes->merge(['class' => $base]) }} @if($primaryStyle) style="{{ $primaryStyle }}" @endif>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type === 'submit' ? 'submit' : 'button' }}"
        {{ $attributes->merge(['class' => $base]) }}
        @if($variant === 'primary' && $primaryStyle) style="{{ $primaryStyle }}" @endif
    >
        {{ $slot }}
    </button>
@endif
