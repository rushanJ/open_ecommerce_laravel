@php
    use App\Services\SeoService;
    use Illuminate\Support\Str;

    $seo = app(SeoService::class);
    $storefrontPublicSettings = $storefrontPublicSettings ?? [];
    $storefrontName = $storefrontName ?? config('app.name');
    $seoKeywords = (string) ($storefrontPublicSettings['seo.default_keywords'] ?? '');

    $metaTitleCandidate = '';
    if ($__env->hasSection('meta_title')) {
        $metaTitleCandidate = trim($__env->yieldContent('meta_title'));
    }
    if ($metaTitleCandidate === '' && $__env->hasSection('title')) {
        $metaTitleCandidate = trim(strip_tags($__env->yieldContent('title')));
    }
    $documentTitle = $seo->metaTitle($metaTitleCandidate !== '' ? $metaTitleCandidate : null);

    $storeSuffix = trim((string) $storefrontName);
    $fullTitle = $documentTitle;
    if ($storeSuffix !== '' && ! Str::endsWith($documentTitle, $storeSuffix)) {
        $fullTitle = $documentTitle.' — '.$storeSuffix;
    }

    $metaDescCandidate = '';
    if ($__env->hasSection('meta_description')) {
        $metaDescCandidate = trim(strip_tags($__env->yieldContent('meta_description')));
    }
    $documentDescription = $seo->metaDescription($metaDescCandidate !== '' ? $metaDescCandidate : null);

    $canonicalOverride = '';
    if ($__env->hasSection('canonical_url')) {
        $canonicalOverride = trim($__env->yieldContent('canonical_url'));
    }
    $canonical = $seo->canonicalUrl($canonicalOverride !== '' ? $canonicalOverride : null);

    $ogImagePath = '';
    if ($__env->hasSection('og_image_path')) {
        $ogImagePath = trim($__env->yieldContent('og_image_path'));
    }
    $ogImageUrl = $seo->ogImage($ogImagePath !== '' ? $ogImagePath : null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $fullTitle }}</title>

        @if($documentDescription !== '')
            <meta name="description" content="{{ e($documentDescription) }}">
        @endif
        @if($seoKeywords !== '')
            <meta name="keywords" content="{{ e($seoKeywords) }}">
        @endif

        <link rel="canonical" href="{{ e($canonical) }}">

        <meta property="og:title" content="{{ e($fullTitle) }}">
        @if($documentDescription !== '')
            <meta property="og:description" content="{{ e($documentDescription) }}">
        @endif
        <meta property="og:url" content="{{ e($canonical) }}">
        <meta property="og:type" content="website">
        @if($ogImageUrl)
            <meta property="og:image" content="{{ e($ogImageUrl) }}">
        @endif

        @yield('meta_extra')

        @if($storefrontStore?->favicon_path)
            <link rel="icon" href="{{ media_url($storefrontStore->favicon_path) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('meta')
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        @include('customer.partials.flash')
        @include('customer.partials.header')

        <main class="pb-16">
            @yield('content')
        </main>

        @include('customer.partials.footer')
    </body>
</html>
