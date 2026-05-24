@php
    $flashTypes = [
        'success' => [
            'wrap' => 'bg-emerald-50 text-emerald-900 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-100 dark:border-emerald-800/80',
            'label' => __('admin.flash_success'),
        ],
        'error' => [
            'wrap' => 'bg-red-50 text-red-900 border-red-200 dark:bg-red-950/50 dark:text-red-100 dark:border-red-800/80',
            'label' => __('admin.flash_error'),
        ],
        'warning' => [
            'wrap' => 'bg-amber-50 text-amber-950 border-amber-200 dark:bg-amber-950/40 dark:text-amber-100 dark:border-amber-800/80',
            'label' => __('admin.flash_warning'),
        ],
        'info' => [
            'wrap' => 'bg-sky-50 text-sky-950 border-sky-200 dark:bg-sky-950/40 dark:text-sky-100 dark:border-sky-800/80',
            'label' => __('admin.flash_info'),
        ],
    ];
@endphp

<div class="space-y-2 px-4 pt-4 sm:px-6 lg:px-8" aria-live="polite">
    @foreach ($flashTypes as $key => $meta)
        @if (session()->has($key))
            <div
                class="flex gap-3 rounded-lg border px-4 py-3 text-sm {{ $meta['wrap'] }}"
                role="{{ $key === 'error' ? 'alert' : 'status' }}"
            >
                <span class="sr-only">{{ $meta['label'] }}</span>
                <span class="min-w-0 flex-1">{{ session($key) }}</span>
            </div>
        @endif
    @endforeach
</div>
