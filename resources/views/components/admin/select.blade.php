@props([
    'name',
    'label' => null,
    'required' => false,
    'error' => null,
])

@php
    $fieldId = $attributes->get('id') ?? 'field_'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '_', $name), '_');
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $fieldId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $fieldId }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-[color:var(--mk-admin-primary)] focus:ring-2 focus:ring-[color:var(--mk-admin-primary)] focus:ring-opacity-20 dark:focus:ring-opacity-30',
        ])->except(['id']) }}
    >
        {{ $slot }}
    </select>
    @php($errMsg = $error ?: $errors->first($name))
    @if ($errMsg)
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $errMsg }}</p>
    @endif
</div>
