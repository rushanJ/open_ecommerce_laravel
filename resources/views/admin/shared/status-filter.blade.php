@props([
    'name' => 'status',
    'statuses' => [],
    'selected' => null,
])

@php
    $selectedValue = $selected !== null ? $selected : request($name);
    $fieldId = 'filter_'.$name;
@endphp

<form method="GET" {{ $attributes->merge(['class' => 'flex flex-wrap items-end gap-2']) }}>
    @foreach (request()->except(['page', $name]) as $key => $value)
        @if (is_array($value))
            @continue
        @endif
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <div>
        <label for="{{ $fieldId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('admin.status') }}
        </label>
        <select
            id="{{ $fieldId }}"
            name="{{ $name }}"
            class="block min-w-[10rem] rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[color:var(--mk-admin-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--mk-admin-primary)] focus:ring-opacity-20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-opacity-30"
        >
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected((string) $selectedValue === (string) $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <x-admin.button type="submit" variant="secondary" size="sm" class="!mt-0">
        {{ __('admin.filter') }}
    </x-admin.button>
</form>
