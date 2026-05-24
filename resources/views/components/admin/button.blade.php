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
        'secondary' => 'bg-white/85 text-slate-800 ring-1 ring-slate-900/10 hover:bg-white hover:shadow-lg dark:bg-white/10 dark:text-slate-100 dark:ring-white/10 dark:hover:bg-white/15 focus-visible:ring-slate-400',
        'danger' => 'bg-red-600 text-white shadow-lg shadow-red-600/20 hover:bg-red-700 focus-visible:ring-red-500',
        'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10 focus-visible:ring-slate-400',
        default => 'text-white shadow-lg shadow-indigo-600/25 hover:-translate-y-0.5 hover:shadow-indigo-600/35 focus-visible:ring-[color:var(--mk-admin-primary)]',
    };

    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950';

    $base = 'admin-ripple inline-flex items-center justify-center overflow-hidden font-semibold rounded-2xl transition duration-200 '.$focusRing.' '.$sizeClasses.' '.$variantClasses;

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
