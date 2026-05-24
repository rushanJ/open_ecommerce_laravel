<nav class="mb-6 rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm shadow-sm ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-white/5 dark:ring-white/10" aria-label="{{ __('admin.breadcrumb') }}">
    <ol class="flex flex-wrap items-center gap-2 text-slate-500 dark:text-slate-400">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg font-semibold text-indigo-600 transition hover:text-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--mk-admin-primary)] focus-visible:ring-offset-2 dark:text-indigo-300 dark:focus-visible:ring-offset-slate-950">
                {{ __('admin.home') }}
            </a>
        </li>
        @hasSection('breadcrumb')
            <li class="text-slate-300 dark:text-slate-600" aria-hidden="true">/</li>
            <li class="font-semibold text-slate-950 dark:text-white">
                @yield('breadcrumb')
            </li>
        @endif
    </ol>
</nav>
