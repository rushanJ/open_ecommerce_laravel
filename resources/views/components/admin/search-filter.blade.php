@props([
    'placeholder' => null,
])

@php
    $placeholderText = $placeholder ?? __('admin.search_placeholder');
@endphp

<form method="GET" {{ $attributes->merge(['class' => 'flex w-full max-w-md flex-wrap items-stretch gap-2 sm:flex-nowrap']) }}>
    @foreach (request()->except(['page', 'q']) as $key => $value)
        @if (is_array($value))
            @continue
        @endif
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <input
        type="search"
        name="q"
        value="{{ request('q', '') }}"
        placeholder="{{ $placeholderText }}"
        autocomplete="off"
        class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-[color:var(--mk-admin-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--mk-admin-primary)] focus:ring-opacity-25 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-opacity-30"
    />

    <x-admin.button type="submit" variant="primary" size="sm" class="shrink-0">
        {{ __('admin.search') }}
    </x-admin.button>
</form>
