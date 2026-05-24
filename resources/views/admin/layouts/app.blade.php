<!DOCTYPE html>
@php
    $themeMode = config('open_ecommerce_laravel.admin.theme.default_mode', 'light');
    $isDark = $themeMode === 'dark';
    $primaryColor = config('open_ecommerce_laravel.admin.theme.primary_color', '#2563eb');
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth {{ $isDark ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard').' — '.config('app.name'))</title>
    <style>
        :root {
            --mk-admin-primary: {{ $primaryColor }};
        }
    </style>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 dark:text-gray-100 min-h-full bg-gray-100 dark:bg-gray-950">
<div
    x-data="{ sidebarOpen: false }"
    @keydown.window.escape="sidebarOpen = false"
    class="min-h-screen flex"
>
    @include('admin.partials.sidebar')

    <div
        x-show="sidebarOpen"
        class="fixed inset-0 z-30 bg-gray-900/60 backdrop-blur-[2px] lg:hidden"
        @click="sidebarOpen = false"
        x-cloak
        aria-hidden="true"
    ></div>

    <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-0">
        @include('admin.partials.topbar')
        @include('admin.partials.breadcrumbs')
        @include('admin.partials.flash')

        <main class="flex-1 overflow-x-auto">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </div>
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
