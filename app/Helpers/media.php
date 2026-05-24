<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('media_url')) {
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        $path = media_public_path($path);
        if ($path === null || $path === '') {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (config('filesystems.disks.public.driver') === 'local') {
            return '/storage/'.ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('media_public_path')) {
    function media_public_path(?string $path): ?string
    {
        $path = $path !== null ? trim(str_replace('\\', '/', $path)) : null;
        if ($path === null || $path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'public/storage/')) {
            return Str::after($path, 'public/storage/');
        }

        if (Str::startsWith($path, 'storage/')) {
            return Str::after($path, 'storage/');
        }

        return $path;
    }
}

if (! function_exists('media_exists')) {
    function media_exists(?string $path): bool
    {
        $path = media_public_path($path);
        if ($path === null || $path === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }
}

