<nav class="border-b border-gray-200 bg-white px-4 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-900 sm:px-6 lg:px-8" aria-label="{{ __('admin.breadcrumb') }}">
    <ol class="flex flex-wrap items-center gap-1.5 text-gray-600 dark:text-gray-400">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="font-medium text-[color:var(--mk-admin-primary)] hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                {{ __('admin.home') }}
            </a>
        </li>
        @hasSection('breadcrumb')
            <li class="text-gray-300 dark:text-gray-600" aria-hidden="true">/</li>
            <li class="font-medium text-gray-900 dark:text-gray-100">
                @yield('breadcrumb')
            </li>
        @endif
    </ol>
</nav>
