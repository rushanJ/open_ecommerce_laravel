<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Future: dedicated admin guard, Spatie roles, and two-factor policies.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
