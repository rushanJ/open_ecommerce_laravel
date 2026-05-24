<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('customer.maintenance_title') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-6 text-center">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ __('customer.maintenance_title') }}</h1>
            <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ __('customer.maintenance_message') }}</p>
        </main>
    </body>
</html>
