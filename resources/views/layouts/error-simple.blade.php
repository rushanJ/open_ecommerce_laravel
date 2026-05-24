<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; color: #0f172a; }
        .box { max-width: 28rem; padding: 2rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 1px 2px rgb(0 0 0 / 0.05); text-align: center; }
        h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem; }
        p { margin: 0; font-size: 0.9375rem; color: #475569; line-height: 1.5; }
        a { color: #059669; font-weight: 600; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        @yield('message')
        <p style="margin-top: 1rem;"><a href="{{ url('/') }}">{{ __('customer.home') }}</a></p>
    </div>
</body>
</html>
