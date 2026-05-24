@props([
    'title',
    'message' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50 px-6 py-12 text-center']) }}>
    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $title }}</p>
    @if ($message)
        <p class="mx-auto mt-2 max-w-md text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>
    @endif
    @if ($actionLabel && $actionUrl)
        <div class="mt-5">
            <x-admin.button type="link" href="{{ $actionUrl }}" variant="primary">
                {{ $actionLabel }}
            </x-admin.button>
        </div>
    @endif
</div>
