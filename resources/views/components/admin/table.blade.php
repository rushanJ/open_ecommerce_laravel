<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-900 shadow-sm overflow-hidden']) }}>
    <div class="-mx-px overflow-x-auto">
        @isset($head)
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800/80 dark:text-gray-400">
                    {{ $head }}
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    {{ $slot }}
                </tbody>
            </table>
        @else
            {{ $slot }}
        @endisset
    </div>
</div>
