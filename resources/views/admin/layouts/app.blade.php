<!DOCTYPE html>
@php
    $themeMode = config('open_ecommerce_laravel.admin.theme.default_mode', 'light');
    $isDark = $themeMode === 'dark';
    $primaryColor = config('open_ecommerce_laravel.admin.theme.primary_color', '#4F46E5');
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth {{ $isDark ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard').' — '.config('app.name'))</title>
    <link rel="preconnect" href="https://rsms.me">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <style>
        :root {
            --mk-admin-primary: {{ $primaryColor }};
            --mk-admin-accent: #7C3AED;
        }
    </style>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#F8FAFC] font-sans text-slate-900 antialiased dark:bg-[#0B1120] dark:text-slate-100">
<div
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('mk-admin-sidebar') === 'collapsed',
        darkMode: localStorage.getItem('mk-admin-theme')
            ? localStorage.getItem('mk-admin-theme') === 'dark'
            : document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.darkMode = ! this.darkMode;
            document.documentElement.classList.toggle('dark', this.darkMode);
            localStorage.setItem('mk-admin-theme', this.darkMode ? 'dark' : 'light');
        },
        toggleSidebarMode() {
            this.sidebarCollapsed = ! this.sidebarCollapsed;
            localStorage.setItem('mk-admin-sidebar', this.sidebarCollapsed ? 'collapsed' : 'expanded');
        }
    }"
    x-init="document.documentElement.classList.toggle('dark', darkMode)"
    @keydown.window.escape="sidebarOpen = false"
    class="min-h-screen"
>
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 left-20 h-80 w-80 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-400/10"></div>
        <div class="absolute right-0 top-20 h-96 w-96 rounded-full bg-violet-500/10 blur-3xl dark:bg-violet-400/10"></div>
        <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl dark:bg-cyan-400/10"></div>
    </div>
    @include('admin.partials.sidebar')

    <div
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-30 bg-slate-950/70 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
        x-cloak
        aria-hidden="true"
    ></div>

    <div
        class="relative flex min-h-screen min-w-0 flex-1 flex-col transition-all duration-300"
        :class="sidebarCollapsed ? 'lg:pl-24' : 'lg:pl-80'"
    >
        @include('admin.partials.topbar')

        <main class="flex-1 overflow-x-hidden">
            <div class="mx-auto max-w-[1600px] px-4 pb-28 pt-4 sm:px-6 lg:px-8 lg:pb-16 lg:pt-6">
                @include('admin.partials.breadcrumbs')
                @include('admin.partials.flash')
                @yield('content')
            </div>
        </main>
    </div>
</div>
@if (filled(config('open_ecommerce_laravel.admin.create_pin')))
    <template id="admin-create-pin-field-template">
        <div class="admin-create-pin-field mt-6 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-950 shadow-sm dark:border-amber-800/70 dark:bg-amber-950/30 dark:text-amber-100">
            <label for="admin_create_pin" class="block text-sm font-semibold">
                {{ __('admin.create_pin') }}
            </label>
            <input
                id="admin_create_pin"
                type="password"
                name="admin_create_pin"
                inputmode="numeric"
                autocomplete="off"
                required
                class="mt-2 w-full rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-amber-800 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-amber-950"
            >
            @error('admin_create_pin')
                <p class="mt-2 text-sm font-medium text-red-700 dark:text-red-300">{{ $message }}</p>
            @else
                <p class="mt-2 text-xs text-amber-800 dark:text-amber-200">{{ __('admin.create_pin_help') }}</p>
            @enderror
        </div>
    </template>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const template = document.getElementById('admin-create-pin-field-template');

            if (! template) {
                return;
            }

            document.querySelectorAll('form').forEach((form, index) => {
                const method = (form.getAttribute('method') || 'GET').toUpperCase();

                if (method !== 'POST' || form.querySelector('[name="admin_create_pin"]')) {
                    return;
                }

                const spoofedMethod = form.querySelector('input[name="_method"]');

                if (spoofedMethod && spoofedMethod.value.toUpperCase() !== 'POST') {
                    return;
                }

                const action = new URL(form.getAttribute('action') || window.location.href, window.location.href);
                const actionPath = action.pathname.replace(/\/+$/, '');

                if (! actionPath.includes('/admin/') || actionPath.endsWith('/logout')) {
                    return;
                }

                const field = template.content.firstElementChild.cloneNode(true);
                const input = field.querySelector('input[name="admin_create_pin"]');
                const label = field.querySelector('label');
                const inputId = `admin_create_pin_${index}`;
                const submit = form.querySelector('button[type="submit"], input[type="submit"]');
                const insertionTarget = submit?.closest('div') || submit || form.lastElementChild;

                input.id = inputId;
                label.setAttribute('for', inputId);

                if (insertionTarget) {
                    insertionTarget.before(field);
                } else {
                    form.append(field);
                }
            });
        });
    </script>
@endif
@stack('scripts')
</body>
</html>
