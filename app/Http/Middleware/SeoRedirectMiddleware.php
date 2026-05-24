<?php

namespace App\Http\Middleware;

use App\Models\SeoRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SeoRedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        if (! Schema::hasTable('seo_redirects')) {
            return $next($request);
        }

        $current = $this->normalizeRequestPath($request);

        $redirect = SeoRedirect::query()
            ->active()
            ->where('from_url', $current)
            ->first();

        if ($redirect === null && $current !== '/') {
            $redirect = SeoRedirect::query()
                ->active()
                ->where('from_url', $current.'/')
                ->first();
        }

        if ($redirect === null) {
            return $next($request);
        }

        $target = trim((string) $redirect->to_url);
        if ($target === '') {
            return $next($request);
        }

        if ($this->pathsEqual($current, $target)) {
            return $next($request);
        }

        $code = in_array((int) $redirect->status_code, [301, 302], true)
            ? (int) $redirect->status_code
            : 301;

        if (Str::startsWith(strtolower($target), ['http://', 'https://'])) {
            return redirect()->away($target, $code);
        }

        if (! str_starts_with($target, '/')) {
            $target = '/'.$target;
        }

        if ($this->pathsEqual($current, $target)) {
            return $next($request);
        }

        return redirect($target, $code);
    }

    private function normalizeRequestPath(Request $request): string
    {
        $path = (string) $request->path();
        $p = '/'.$path;
        if ($p !== '/') {
            $p = rtrim($p, '/') ?: '/';
        }

        return $p;
    }

    private function pathsEqual(string $from, string $to): bool
    {
        if (Str::startsWith(strtolower($to), ['http://', 'https://'])) {
            $parsed = parse_url($to);
            $path = $parsed['path'] ?? '/';
            $toPath = $this->normalizePathOnly($path);
            $query = $parsed['query'] ?? null;

            return $toPath === $from && $query === null;
        }

        return $this->normalizePathOnly($to) === $from;
    }

    private function normalizePathOnly(string $path): string
    {
        $path = trim($path);
        $p = '/'.ltrim($path, '/');
        if ($p !== '/') {
            $p = rtrim($p, '/') ?: '/';
        }

        return $p;
    }
}
