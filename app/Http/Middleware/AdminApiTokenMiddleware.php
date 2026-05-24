<?php

namespace App\Http\Middleware;

use App\Services\ApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiTokenMiddleware
{
    public function __construct(
        protected ApiTokenService $apiTokens,
    ) {}

    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        $plain = $request->bearerToken();
        if ($plain === null || $plain === '') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => (object) [],
            ], 401);
        }

        $token = $this->apiTokens->findActiveToken($plain);
        if ($token === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => (object) [],
            ], 401);
        }

        if ($ability !== null && $ability !== '' && ! $token->hasAbility($ability)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => (object) [],
            ], 403);
        }

        $request->attributes->set('open_ecommerce_laravel_api_token', $token);
        $token->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
