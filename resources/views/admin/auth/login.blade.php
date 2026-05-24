<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('open_ecommerce_laravel Admin') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-100">
<div class="min-h-screen flex flex-col items-center justify-center p-6">
    <div class="w-full max-w-md bg-white shadow-lg rounded-xl border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-center text-gray-900 tracking-tight">{{ __('open_ecommerce_laravel Admin') }}</h1>
        <p class="mt-1 text-center text-sm text-gray-500">{{ __('Sign in to continue') }}</p>

        @if (session('status'))
            <div class="mt-6 p-3 rounded-lg bg-green-50 text-sm text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 p-3 rounded-lg bg-red-50 text-sm text-red-800" role="alert">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2" />
            </div>

            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox" value="1"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                <label for="remember" class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</label>
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Sign in') }}
            </button>
        </form>
    </div>
</div>
</body>
</html>
