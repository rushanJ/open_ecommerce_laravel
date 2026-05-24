<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-900 shadow-sm overflow-hidden']) }}>
    <div class="-mx-px overflow-x-auto">
        {{ $slot }}
    </div>
</div>
